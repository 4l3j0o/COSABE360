import { Image } from 'expo-image';
import { Link, router } from 'expo-router';
import {
  Bell,
  BookOpen,
  CalendarDays,
  Check,
  CheckCheck,
  CheckCircle2,
  ChevronRight,
  Clock,
  FileText,
  ListChecks,
  Sparkles,
  X,
} from 'lucide-react-native';
import { useEffect, useState } from 'react';
import { Modal, Pressable, ScrollView, StyleSheet, View } from 'react-native';

import { AppText, Button, Card, Chip, ProgressRing, Screen, SectionTitle } from '@/components/ui';
import { estudiante, materias } from '@/data/mock';
import { formatearFecha, useFeriados } from '@/lib/feriados';
import { useNotificaciones } from '@/store/notificaciones';
import { usePendientes } from '@/store/pendientes';
import { useSesion } from '@/store/session';
import { escalaNota } from '@/theme/tokens';
import { colors, radius, shadows, spacing } from '@/theme/tokens';

export default function Inicio() {
  const nombreSesion = useSesion((s) => s.nombre);
  const cursoSesion = useSesion((s) => s.curso);
  const notificaciones = useNotificaciones((s) => s.notificaciones);
  const marcarTodasLeidas = useNotificaciones((s) => s.marcarTodasLeidas);
  const alternarLectura = useNotificaciones((s) => s.alternarLectura);
  const noLeidos = notificaciones.filter((n) => !n.leido).length;
  const destacado = notificaciones[0] || {
    id: 'c1',
    titulo: 'Entrega de libretas del segundo trimestre',
    resumen: 'La entrega se realizará el viernes 24 de julio de 08:00 a 12:00.',
    fecha: '15/07/2026',
    leido: false,
    categoria: 'ACADEMICO',
  };
  const promedio = escalaNota(estudiante.promedio);
  const pendientes = usePendientes((s) => s.items.filter((i) => !i.hecho).length);
  const cargarPendientes = usePendientes((s) => s.cargar);
  const { data: feriadosData } = useFeriados();
  const [modalHorario, setModalHorario] = useState(false);
  const [modalNotificaciones, setModalNotificaciones] = useState(false);

  useEffect(() => {
    cargarPendientes();
  }, [cargarPendientes]);

  const hoyIso = new Date().toISOString().slice(0, 10);
  const proximoFeriado = feriadosData?.find((f) => f.date >= hoyIso);

  const accesos = [
    { label: 'Pendientes', icon: ListChecks, to: '/(tabs)/pendientes', valor: pendientes },
    { label: 'Notas', icon: BookOpen, to: '/(tabs)/notas', valor: 0 },
    { label: 'Feriados', icon: CalendarDays, to: '/(tabs)/feriados', valor: 0 },
    { label: 'Perfil', icon: FileText, to: '/(tabs)/perfil', valor: 0 },
  ] as const;

  const horarioSemanal = [
    { hora: '07:30 - 08:15', lun: 'Lengua', mar: 'Matemática', mie: 'Ciencias', jue: 'Inglés', vie: 'Física' },
    { hora: '08:15 - 09:00', lun: 'Lengua', mar: 'Matemática', mie: 'Ciencias', jue: 'Inglés', vie: 'Física' },
    { hora: '09:00 - 09:20', lun: 'RECREO', mar: 'RECREO', mie: 'RECREO', jue: 'RECREO', vie: 'RECREO' },
    { hora: '09:20 - 10:05', lun: 'Matemática', mar: 'Sociales', mie: 'Lengua', jue: 'Ed. Física', vie: 'Química' },
    { hora: '10:05 - 10:50', lun: 'Matemática', mar: 'Sociales', mie: 'Lengua', jue: 'Ed. Física', vie: 'Química' },
    { hora: '11:10 - 11:55', lun: 'Informática', mar: 'Artes', mie: 'Religión', jue: 'Biología', vie: 'Música' },
  ];

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
        <Pressable
          accessibilityRole="button"
          accessibilityLabel="Ver notificaciones"
          style={styles.campana}
          onPress={() => setModalNotificaciones(true)}>
          <Bell size={20} color={colors.primary[800]} />
          {noLeidos > 0 ? (
            <View style={styles.badge}>
              <AppText variant="micro" color="#FFFFFF">
                {noLeidos}
              </AppText>
            </View>
          ) : null}
        </Pressable>
      </View>

      <AppText variant="body" color={colors.text.secondary}>
        Buenos días,
      </AppText>
      <AppText variant="display">Hola, {nombreSesion ? nombreSesion.split(' ')[0] : estudiante.nombres}</AppText>
      <View style={{ marginVertical: spacing.md }}>
        <Chip
          label={cursoSesion || `${estudiante.curso} · Paralelo ${estudiante.paralelo}`}
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
        <Pressable style={styles.stat} onPress={() => router.push('/(tabs)/asistencia')}>
          <AppText variant="section">{estudiante.asistenciaPct}%</AppText>
          <AppText variant="micro" color={colors.text.tertiary}>
            Asistencia ➜
          </AppText>
        </Pressable>
        <Pressable style={styles.stat} onPress={() => router.push('/(tabs)/notas')}>
          <AppText variant="section">{estudiante.materias}</AppText>
          <AppText variant="micro" color={colors.text.tertiary}>
            Materias ➜
          </AppText>
        </Pressable>
        <Pressable style={styles.stat} onPress={() => router.push('/(tabs)/notas')}>
          <AppText variant="section">{estudiante.trimestre.replace(' Trimestre', 'º')}</AppText>
          <AppText variant="micro" color={colors.text.tertiary}>
            Trimestre
          </AppText>
        </Pressable>
      </View>

      {proximoFeriado ? (
        <Card style={styles.feriadoCard}>
          <Pressable
            style={styles.feriadoRow}
            onPress={() => router.push('/(tabs)/feriados')}>
            <View style={styles.feriadoIcono}>
              <Sparkles size={20} color={colors.primary[700]} />
            </View>
            <View style={{ flex: 1 }}>
              <AppText variant="micro" color={colors.primary[700]}>
                PRÓXIMO FERIADO (API NAGER.DATE)
              </AppText>
              <AppText variant="bodyStrong">{proximoFeriado.localName}</AppText>
              <AppText variant="micro" color={colors.text.tertiary}>
                {formatearFecha(proximoFeriado.date)} · Toca para guardar en pendientes
              </AppText>
            </View>
            <ChevronRight size={18} color={colors.slate[400]} />
          </Pressable>
        </Card>
      ) : null}

      <View style={styles.seccion}>
        <SectionTitle
          title="Ahora"
          action="Ver horario"
          onAction={() => setModalHorario(true)}
        />
        <Pressable onPress={() => setModalHorario(true)}>
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
        </Pressable>
      </View>

      <View style={styles.seccion}>
        <SectionTitle title="Accesos rápidos" />
        <View style={styles.accesos}>
          {accesos.map(({ label, icon: Icon, to, valor }) => (
            <Pressable
              key={label}
              style={styles.acceso}
              onPress={() => router.push(to as any)}>
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
            </Pressable>
          ))}
        </View>
      </View>

      <View style={styles.seccion}>
        <SectionTitle
          title="Comunicados"
          action="Ver todos"
          onAction={() => router.push('/(tabs)/comunicados')}
        />
        <Link href="/(tabs)/comunicados" asChild>
          <Pressable>
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
          </Pressable>
        </Link>
      </View>

      <View style={styles.seccion}>
        <SectionTitle
          title="Mis materias"
          action="Ver todas"
          onAction={() => router.push('/(tabs)/notas')}
        />
        {materias.slice(0, 3).map((m) => (
          <Pressable key={m.id} onPress={() => router.push(`/materia/${m.id}`)}>
            <Card style={{ marginBottom: spacing.sm }} padded={false}>
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
          </Pressable>
        ))}
      </View>

      {/* Modal Horario Completo */}
      <Modal
        visible={modalHorario}
        transparent
        animationType="slide"
        onRequestClose={() => setModalHorario(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <View>
                <AppText variant="bodyStrong">Horario de Clases 2026</AppText>
                <AppText variant="micro" color={colors.text.secondary}>
                  4º Secundaria B · Turno Mañana
                </AppText>
              </View>
              <Pressable onPress={() => setModalHorario(false)} style={{ padding: 4 }}>
                <X size={22} color={colors.slate[600]} />
              </Pressable>
            </View>

            <ScrollView showsVerticalScrollIndicator={false} style={{ marginTop: spacing.md }}>
              {horarioSemanal.map((bloque, idx) => (
                <Card key={idx} style={{ marginBottom: spacing.xs, padding: spacing.sm }}>
                  <AppText variant="micro" color={colors.primary[700]}>
                    {bloque.hora}
                  </AppText>
                  <View style={styles.horarioFila}>
                    <AppText variant="caption">Lun: {bloque.lun}</AppText>
                    <AppText variant="caption">Mar: {bloque.mar}</AppText>
                    <AppText variant="caption">Mié: {bloque.mie}</AppText>
                  </View>
                  <View style={styles.horarioFila}>
                    <AppText variant="caption">Jue: {bloque.jue}</AppText>
                    <AppText variant="caption">Vie: {bloque.vie}</AppText>
                  </View>
                </Card>
              ))}
            </ScrollView>

            <Button
              label="Cerrar Horario"
              variant="secondary"
              onPress={() => setModalHorario(false)}
              style={{ marginTop: spacing.md }}
            />
          </View>
        </View>
      </Modal>

      {/* Modal Interactivo de Notificaciones */}
      <Modal
        visible={modalNotificaciones}
        transparent
        animationType="slide"
        onRequestClose={() => setModalNotificaciones(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <View style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.sm }}>
                <View style={styles.notifIconWrap}>
                  <Bell size={20} color={colors.primary[700]} />
                </View>
                <View>
                  <AppText variant="title">Notificaciones</AppText>
                  <AppText variant="micro" color={colors.text.tertiary}>
                    Colegio San Bernardo · Avisos recientes
                  </AppText>
                </View>
              </View>
              <Pressable onPress={() => setModalNotificaciones(false)} style={{ padding: 4 }}>
                <X size={22} color={colors.slate[600]} />
              </Pressable>
            </View>

            <View style={styles.notifSubHeader}>
              <Chip
                label={noLeidos > 0 ? `${noLeidos} sin leer` : 'Todo al día'}
                variant={noLeidos > 0 ? 'warning' : 'success'}
                dot
                small
              />
              {noLeidos > 0 ? (
                <Pressable
                  onPress={marcarTodasLeidas}
                  style={styles.marcarTodasBtn}
                  accessibilityRole="button">
                  <CheckCheck size={14} color={colors.primary[700]} />
                  <AppText variant="micro" color={colors.primary[700]} style={{ fontWeight: '600' }}>
                    Marcar todo leído
                  </AppText>
                </Pressable>
              ) : null}
            </View>

            <ScrollView showsVerticalScrollIndicator={false} style={{ marginTop: spacing.xs, maxHeight: 360 }}>
              {notificaciones.length === 0 ? (
                <View style={{ alignItems: 'center', paddingVertical: spacing.xl }}>
                  <AppText variant="caption" color={colors.text.tertiary}>
                    No tienes notificaciones
                  </AppText>
                </View>
              ) : (
                notificaciones.slice(0, 5).map((c) => (
                  <Card key={c.id} style={styles.notifItem} padded={false}>
                    <Pressable
                      style={styles.notifRow}
                      onPress={() => {
                        alternarLectura(c.id);
                        if (c.accionUrl) {
                          setModalNotificaciones(false);
                          router.push(c.accionUrl as any);
                        }
                      }}>
                      <View style={[styles.notifDot, !c.leido && styles.notifDotUnread]} />
                      <View style={{ flex: 1 }}>
                        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                          <Chip label={c.categoria} small variant={c.categoria === 'TAREA' ? 'warning' : c.categoria === 'FERIADO' ? 'success' : 'default'} />
                          <AppText variant="micro" color={colors.text.tertiary}>
                            {c.fecha}
                          </AppText>
                        </View>
                        <AppText
                          variant="bodyStrong"
                          numberOfLines={1}
                          color={c.leido ? colors.text.secondary : colors.text.primary}
                          style={{ marginTop: 4 }}>
                          {c.titulo}
                        </AppText>
                        <AppText variant="caption" color={colors.text.tertiary} numberOfLines={2} style={{ marginTop: 2 }}>
                          {c.resumen}
                        </AppText>
                      </View>

                      {/* Botón individual para marcar leída */}
                      <Pressable
                        onPress={() => alternarLectura(c.id)}
                        style={[styles.checkLeidoBtn, c.leido && styles.checkLeidoBtnActivo]}
                        accessibilityLabel={c.leido ? 'Marcar como no leído' : 'Marcar como leído'}>
                        <Check size={12} color={c.leido ? '#FFFFFF' : colors.neutral[400]} />
                      </Pressable>
                    </Pressable>
                  </Card>
                ))
              )}
            </ScrollView>

            <Button
              label="Ver todos los comunicados"
              variant="primary"
              onPress={() => {
                setModalNotificaciones(false);
                router.push('/(tabs)/comunicados');
              }}
              style={{ marginTop: spacing.md }}
            />
            <Button
              label="Cerrar"
              variant="ghost"
              onPress={() => setModalNotificaciones(false)}
              style={{ marginTop: spacing.xs }}
            />
          </View>
        </View>
      </Modal>
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
  feriadoCard: {
    marginTop: spacing.md,
    backgroundColor: '#F0FDF4',
    borderColor: '#86EFAC',
    borderWidth: 1,
  },
  feriadoRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.md },
  feriadoIcono: {
    width: 40,
    height: 40,
    borderRadius: radius.md,
    backgroundColor: colors.primary[50],
    alignItems: 'center',
    justifyContent: 'center',
  },
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
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(15, 23, 42, 0.55)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: radius.xl,
    borderTopRightRadius: radius.xl,
    padding: spacing.xl,
    maxHeight: '80%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  horarioFila: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 4,
  },
  notifIconWrap: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: colors.primary[50],
    borderWidth: 1,
    borderColor: colors.primary[200],
    alignItems: 'center',
    justifyContent: 'center',
  },
  notifItem: {
    marginBottom: spacing.xs,
    borderRadius: radius.md,
  },
  notifRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: spacing.sm,
    padding: spacing.md,
  },
  notifDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: colors.neutral[300],
    marginTop: 5,
  },
  notifDotUnread: {
    backgroundColor: colors.semantic.danger,
  },
  notifSubHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginVertical: spacing.sm,
  },
  marcarTodasBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: radius.pill,
    backgroundColor: colors.primary[50],
  },
  checkLeidoBtn: {
    width: 24,
    height: 24,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: colors.neutral[300],
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FFFFFF',
    alignSelf: 'center',
    marginLeft: 4,
  },
  checkLeidoBtnActivo: {
    backgroundColor: colors.primary[600],
    borderColor: colors.primary[600],
  },
});
