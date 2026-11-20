import { Image } from 'expo-image';
import { Link, router } from 'expo-router';
import {
  Bell,
  BookOpen,
  CalendarDays,
  ChevronRight,
  Clock,
  FileText,
  ListChecks,
} from 'lucide-react-native';
import { StyleSheet, View } from 'react-native';

import { AppText, Card, Chip, ProgressRing, Screen, SectionTitle } from '@/components/ui';
import { comunicados, estudiante, materias } from '@/data/mock';
import { usePendientes } from '@/store/pendientes';
import { escalaNota } from '@/theme/tokens';
import { colors, radius, shadows, spacing } from '@/theme/tokens';

export default function Inicio() {
  const noLeidos = comunicados.filter((c) => !c.leido).length;
  const destacado = comunicados[0];
  const promedio = escalaNota(estudiante.promedio);
  const pendientes = usePendientes((s) => s.items.filter((i) => !i.hecho).length);

  const accesos = [
    { label: 'Pendientes', icon: ListChecks, to: '/pendientes', valor: pendientes },
    { label: 'Notas', icon: BookOpen, to: '/notas', valor: 0 },
    { label: 'Feriados', icon: CalendarDays, to: '/feriados', valor: 0 },
    { label: 'Perfil', icon: FileText, to: '/perfil', valor: 0 },
  ] as const;

  return (
    <Screen>
      <View style={styles.header}>
        <View style={styles.marca}>
          <Image source={require('@/assets/images/escudo.png')} style={styles.escudo} contentFit="contain" />
          <View>
            <AppText variant="bodyStrong">CSB 360</AppText>
            <AppText variant="micro" color={colors.text.tertiary}>
              Colegio San Bernardo
            </AppText>
          </View>
        </View>
        <View style={styles.campana}>
          <Bell size={20} color={colors.slate[700]} />
          {noLeidos > 0 ? (
            <View style={styles.badge}>
              <AppText variant="micro" color="#FFFFFF">
                {noLeidos}
              </AppText>
            </View>
          ) : null}
        </View>
      </View>

      <AppText variant="body" color={colors.text.secondary}>
        Buenos días,
      </AppText>
      <AppText variant="display">Hola, {estudiante.nombres}</AppText>
      <View style={{ marginVertical: spacing.md }}>
        <Chip
          label={`${estudiante.curso} · Paralelo ${estudiante.paralelo}`}
          background={colors.slate[100]}
          color={colors.slate[600]}
        />
      </View>

      <View style={styles.hero}>
        <View style={{ flex: 1 }}>
          <AppText variant="micro" color="#CDEBCD" style={{ letterSpacing: 1 }}>
            RENDIMIENTO GENERAL
          </AppText>
          <AppText variant="title" color="#FFFFFF" style={{ marginTop: 4 }}>
            Vas por buen camino
          </AppText>
          <AppText variant="caption" color="#D8F0D8" style={{ marginTop: 2 }}>
            {promedio.sigla} · {promedio.label}
          </AppText>
        </View>
        <ProgressRing value={estudiante.promedio} size={88} color="#FFFFFF">
          <AppText variant="title" color="#FFFFFF">
            {estudiante.promedio}
          </AppText>
          <AppText variant="micro" color="#D8F0D8">
            /100
          </AppText>
        </ProgressRing>
      </View>

      <View style={styles.stats}>
        {[
          [`${estudiante.asistenciaPct}%`, 'Asistencia'],
          [String(estudiante.materias), 'Materias'],
          [estudiante.trimestre.replace(' Trimestre', 'º'), 'Trimestre'],
        ].map(([valor, label]) => (
          <View key={label} style={styles.stat}>
            <AppText variant="section">{valor}</AppText>
            <AppText variant="micro" color={colors.text.tertiary}>
              {label}
            </AppText>
          </View>
        ))}
      </View>

      <View style={styles.seccion}>
        <SectionTitle title="Ahora" action="Ver horario" />
        <Card>
          <View style={styles.clase}>
            <View style={styles.hora}>
              <AppText variant="bodyStrong">09:20</AppText>
              <AppText variant="micro" color={colors.text.tertiary}>
                10:05
              </AppText>
            </View>
            <View style={styles.barra} />
            <View style={{ flex: 1 }}>
              <AppText variant="micro" color={colors.primary[700]}>
                EN CURSO · FALTAN 24 MIN
              </AppText>
              <AppText variant="bodyStrong" style={{ marginTop: 2 }}>
                Matemática
              </AppText>
              <AppText variant="caption" color={colors.text.secondary}>
                Prof. Marco Suárez · Aula 12
              </AppText>
            </View>
            <Clock size={18} color={colors.slate[300]} />
          </View>
        </Card>
      </View>

      <View style={styles.seccion}>
        <SectionTitle title="Accesos rápidos" />
        <View style={styles.accesos}>
          {accesos.map(({ label, icon: Icon, to, valor }) => (
            <View key={label} style={styles.acceso}>
              <View style={styles.accesoIcono}>
                <Icon size={20} color={colors.primary[700]} />
                {typeof valor === 'number' && valor > 0 ? (
                  <View style={styles.contador}>
                    <AppText variant="micro" color="#FFFFFF">
                      {valor}
                    </AppText>
                  </View>
                ) : null}
              </View>
              <AppText variant="micro" color={colors.text.secondary}>
                {label}
              </AppText>
            </View>
          ))}
        </View>
      </View>

      <View style={styles.seccion}>
        <SectionTitle title="Comunicados" action="Ver todos" />
        <Link href="/comunicados" asChild>
          <Card>
            <View style={styles.comunicado}>
              <View style={styles.comunicadoIcono}>
                <FileText size={18} color={colors.primary[700]} />
              </View>
              <View style={{ flex: 1 }}>
                <AppText variant="micro" color={colors.semantic.warning}>
                  {destacado.leido ? '' : 'NUEVO · '}
                  {destacado.fecha}
                </AppText>
                <AppText variant="bodyStrong" numberOfLines={2} style={{ marginTop: 2 }}>
                  {destacado.titulo}
                </AppText>
                <AppText variant="caption" color={colors.text.secondary} numberOfLines={2}>
                  {destacado.resumen}
                </AppText>
              </View>
              <ChevronRight size={18} color={colors.slate[300]} />
            </View>
          </Card>
        </Link>
      </View>

      <View style={styles.seccion}>
        <SectionTitle title="Mis materias" action="Ver todas" />
        {materias.slice(0, 3).map((m) => (
          <Card key={m.id} style={{ marginBottom: spacing.sm }} padded={false}>
            <View style={styles.materia}>
              <View style={[styles.inicial, { backgroundColor: m.color + '1A' }]}>
                <AppText variant="bodyStrong" color={m.color}>
                  {m.nombre[0]}
                </AppText>
              </View>
              <View style={{ flex: 1 }}>
                <AppText variant="bodyStrong">{m.nombre}</AppText>
                <AppText variant="micro" color={colors.text.tertiary}>
                  {m.docente}
                </AppText>
              </View>
              <AppText variant="bodyStrong" color={escalaNota(m.promedio).color}>
                {m.promedio}
              </AppText>
            </View>
          </Card>
        ))}
      </View>
    </Screen>
  );
}

const styles = StyleSheet.create({
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: spacing.xl,
  },
  marca: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
  escudo: { width: 38, height: 38 },
  campana: {
    width: 42,
    height: 42,
    borderRadius: 21,
    backgroundColor: '#FFFFFF',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: colors.surface.border,
  },
  badge: {
    position: 'absolute',
    top: 4,
    right: 4,
    minWidth: 16,
    height: 16,
    borderRadius: 8,
    backgroundColor: colors.semantic.danger,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 3,
  },
  hero: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    backgroundColor: colors.primary[600],
    borderRadius: radius.lg,
    padding: spacing.xl,
    ...shadows.card,
  },
  stats: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.surface.border,
    marginTop: spacing.md,
    paddingVertical: spacing.md,
  },
  stat: { flex: 1, alignItems: 'center', gap: 2 },
  seccion: { marginTop: spacing.xl },
  clase: { flexDirection: 'row', alignItems: 'center', gap: spacing.md },
  hora: { alignItems: 'center', width: 48 },
  barra: { width: 4, height: 42, borderRadius: 2, backgroundColor: colors.primary[500] },
  accesos: { flexDirection: 'row', justifyContent: 'space-between' },
  acceso: { alignItems: 'center', gap: 6, flex: 1 },
  accesoIcono: {
    width: 52,
    height: 52,
    borderRadius: radius.lg,
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: colors.surface.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  contador: {
    position: 'absolute',
    top: -4,
    right: -4,
    minWidth: 18,
    height: 18,
    borderRadius: 9,
    backgroundColor: colors.semantic.danger,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 3,
  },
  comunicado: { flexDirection: 'row', alignItems: 'center', gap: spacing.md },
  comunicadoIcono: {
    width: 40,
    height: 40,
    borderRadius: radius.md,
    backgroundColor: colors.primary[50],
    alignItems: 'center',
    justifyContent: 'center',
  },
  materia: { flexDirection: 'row', alignItems: 'center', gap: spacing.md, padding: spacing.md },
  inicial: {
    width: 40,
    height: 40,
    borderRadius: radius.md,
    alignItems: 'center',
    justifyContent: 'center',
  },
});
