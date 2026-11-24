import { router } from 'expo-router';
import {
  ArrowLeft,
  Bell,
  Check,
  CheckCheck,
  ChevronDown,
  ChevronRight,
  ChevronUp,
  Download,
  FileText,
  Sparkles,
} from 'lucide-react-native';
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';

import { AppText, Button, Card, Chip, Screen } from '@/components/ui';
import { useNotificaciones, type TipoNotificacion } from '@/store/notificaciones';
import { colors, radius, shadows, spacing } from '@/theme/tokens';

const filtros = ['Todas', 'Sin leer', 'Circulares', 'Académico', 'Tareas'] as const;

export default function Comunicados() {
  const notificaciones = useNotificaciones((s) => s.notificaciones);
  const alternarLectura = useNotificaciones((s) => s.alternarLectura);
  const marcarTodasLeidas = useNotificaciones((s) => s.marcarTodasLeidas);

  const [filtro, setFiltro] = useState<(typeof filtros)[number]>('Todas');
  const [abierto, setAbierto] = useState<string | null>(null);
  const [descargandoPdf, setDescargandoPdf] = useState<string | null>(null);
  const [pdfDescargado, setPdfDescargado] = useState<string | null>(null);

  const noLeidos = notificaciones.filter((n) => !n.leido).length;

  function toggleAbierto(id: string) {
    if (abierto !== id) {
      // Auto marcar como leído al abrir
      const item = notificaciones.find((n) => n.id === id);
      if (item && !item.leido) {
        alternarLectura(id);
      }
    }
    setAbierto(abierto === id ? null : id);
  }

  function descargarCircular(id: string, titulo: string) {
    setDescargandoPdf(id);
    setPdfDescargado(null);
    setTimeout(() => {
      setDescargandoPdf(null);
      setPdfDescargado(`Se descargó «${titulo.slice(0, 30)}…». Guardado en tu dispositivo.`);
      setTimeout(() => setPdfDescargado(null), 3500);
    }, 1200);
  }

  const lista = notificaciones.filter((c) => {
    if (filtro === 'Sin leer') return !c.leido;
    if (filtro === 'Circulares') return c.categoria === 'CIRCULAR';
    if (filtro === 'Académico') return c.categoria === 'ACADEMICO';
    if (filtro === 'Tareas') return c.categoria === 'TAREA';
    return true;
  });

  return (
    <Screen>
      {/* Barra superior de navegación */}
      <View style={styles.topBar}>
        <Pressable onPress={() => router.back()} style={styles.volver}>
          <ArrowLeft size={18} color={colors.primary[700]} />
          <AppText variant="captionStrong" color={colors.primary[700]}>
            Volver
          </AppText>
        </Pressable>

        {noLeidos > 0 ? (
          <Pressable
            onPress={marcarTodasLeidas}
            style={styles.btnMarcarTodo}
            accessibilityRole="button">
            <CheckCheck size={15} color={colors.primary[700]} />
            <AppText variant="micro" color={colors.primary[700]} style={{ fontWeight: '600' }}>
              Marcar todo leído
            </AppText>
          </Pressable>
        ) : null}
      </View>

      {/* Encabezado Principal */}
      <View style={styles.header}>
        <View style={styles.badgeOficial}>
          <Bell size={13} color={colors.primary[700]} />
          <AppText variant="micro" color={colors.primary[800]} style={{ fontWeight: '700' }}>
            CENTRO DE NOTIFICACIONES INSTITUCIONALES
          </AppText>
        </View>
        <AppText variant="title">Comunicados y Avisos</AppText>
        <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: 2 }}>
          Colegio San Bernardo · Circulares oficiales de Dirección
        </AppText>
      </View>

      {/* Banner de descarga de PDF */}
      {pdfDescargado ? (
        <Card style={styles.bannerDescarga}>
          <Check size={16} color="#065F46" strokeWidth={2.5} />
          <AppText variant="captionStrong" color="#065F46" style={{ flex: 1 }}>
            {pdfDescargado}
          </AppText>
        </Card>
      ) : null}

      {/* Filtros Segmentados */}
      <View style={styles.filtros}>
        {filtros.map((f) => {
          const activo = filtro === f;
          return (
            <Pressable
              key={f}
              onPress={() => setFiltro(f)}
              style={[styles.filtro, activo && styles.filtroActivo]}>
              <AppText
                variant="caption"
                color={activo ? '#FFFFFF' : colors.text.secondary}
                style={{ fontWeight: activo ? '700' : '500' }}>
                {f}
              </AppText>
            </Pressable>
          );
        })}
      </View>

      {/* Lista de Notificaciones */}
      {lista.length === 0 ? (
        <Card style={styles.emptyCard}>
          <FileText size={36} color={colors.neutral[300]} />
          <AppText variant="bodyStrong" style={{ marginTop: spacing.md }}>
            No tienes comunicados en esta categoría
          </AppText>
          <AppText variant="caption" color={colors.text.tertiary} center style={{ marginTop: 4 }}>
            {filtro === 'Sin leer'
              ? 'Has leído todas tus notificaciones. ¡Buen trabajo!'
              : 'Selecciona la pestaña «Todas» para ver el historial completo.'}
          </AppText>
          {filtro !== 'Todas' ? (
            <Button
              label="Ver todas las notificaciones"
              variant="outline"
              onPress={() => setFiltro('Todas')}
              style={{ marginTop: spacing.lg }}
            />
          ) : null}
        </Card>
      ) : (
        lista.map((c) => {
          const isExpandido = abierto === c.id;
          const esDescargando = descargandoPdf === c.id;

          let catVariant: 'default' | 'success' | 'warning' | 'info' = 'default';
          if (c.categoria === 'TAREA') catVariant = 'warning';
          if (c.categoria === 'FERIADO') catVariant = 'success';
          if (c.categoria === 'ACADEMICO') catVariant = 'info';

          return (
            <Card key={c.id} style={styles.cardComunicado} padded={false}>
              {/* Barra indicadora izquierda para no leídos */}
              {!c.leido && <View style={styles.unreadStripe} />}

              <Pressable
                onPress={() => toggleAbierto(c.id)}
                style={styles.cardHeaderPressable}>
                <View style={styles.cabecera}>
                  <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                    <Chip label={c.categoria} variant={catVariant} small />
                    {!c.leido ? (
                      <Chip label="NUEVO" variant="warning" dot small />
                    ) : null}
                  </View>

                  <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                    <AppText variant="micro" color={colors.text.tertiary}>
                      {c.fecha}
                    </AppText>
                    {isExpandido ? (
                      <ChevronUp size={18} color={colors.slate[500]} />
                    ) : (
                      <ChevronDown size={18} color={colors.slate[500]} />
                    )}
                  </View>
                </View>

                <AppText
                  variant="bodyStrong"
                  color={c.leido ? colors.text.secondary : colors.text.primary}
                  style={{ marginTop: spacing.sm }}>
                  {c.titulo}
                </AppText>

                <AppText variant="micro" color={colors.text.tertiary} style={{ marginTop: 2 }}>
                  {c.autor} {c.hora ? `· ${c.hora}` : ''}
                </AppText>

                <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: spacing.xs, lineHeight: 18 }}>
                  {c.resumen}
                </AppText>
              </Pressable>

              {/* Cuerpo Desplegado */}
              {isExpandido ? (
                <View style={styles.cuerpoDesplegado}>
                  <View style={styles.divisor} />

                  {c.cuerpo?.map((p, i) => (
                    <AppText
                      key={i}
                      variant="caption"
                      color={colors.text.primary}
                      style={{ marginTop: spacing.xs, lineHeight: 20 }}>
                      {p}
                    </AppText>
                  ))}

                  {/* Acciones del Comunicado */}
                  <View style={styles.accionesCuerpo}>
                    {/* Botón de Descargar PDF Oficial */}
                    <Pressable
                      style={styles.btnPdf}
                      onPress={() => descargarCircular(c.id, c.titulo)}
                      disabled={esDescargando}>
                      <Download size={15} color={colors.primary[700]} />
                      <AppText variant="micro" color={colors.primary[700]} style={{ fontWeight: '600' }}>
                        {esDescargando ? 'Generando PDF…' : 'Descargar circular oficial (PDF)'}
                      </AppText>
                    </Pressable>

                    {/* Botón de Ir a la pantalla de la acción si existe */}
                    {c.accionUrl ? (
                      <Pressable
                        style={styles.btnIrModulo}
                        onPress={() => router.push(c.accionUrl as any)}>
                        <AppText variant="micro" color={colors.primary[700]} style={{ fontWeight: '600' }}>
                          Ir al módulo
                        </AppText>
                        <ChevronRight size={14} color={colors.primary[700]} />
                      </Pressable>
                    ) : null}

                    {/* Botón de Marcar leído / no leído */}
                    <Pressable
                      style={styles.marcarLeido}
                      onPress={() => alternarLectura(c.id)}>
                      <Check
                        size={14}
                        color={c.leido ? colors.accent.emerald : colors.text.tertiary}
                        strokeWidth={2.5}
                      />
                      <AppText variant="micro" color={colors.text.secondary}>
                        {c.leido ? 'Marcado como leído' : 'Marcar como leído'}
                      </AppText>
                    </Pressable>
                  </View>
                </View>
              ) : null}
            </Card>
          );
        })
      )}
    </Screen>
  );
}

const styles = StyleSheet.create({
  topBar: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  volver: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingVertical: 6,
    paddingHorizontal: 8,
    borderRadius: radius.md,
    backgroundColor: colors.primary[50],
  },
  btnMarcarTodo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    paddingVertical: 6,
    paddingHorizontal: 10,
    borderRadius: radius.pill,
    backgroundColor: colors.primary[50],
    borderWidth: 1,
    borderColor: colors.primary[200],
  },
  header: {
    marginBottom: spacing.md,
  },
  badgeOficial: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: radius.pill,
    backgroundColor: colors.primary[50],
    borderWidth: 1,
    borderColor: colors.primary[200],
    alignSelf: 'flex-start',
    marginBottom: 6,
  },
  bannerDescarga: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    backgroundColor: '#ECFDF5',
    borderColor: '#A7F3D0',
    borderWidth: 1,
    padding: spacing.md,
    marginBottom: spacing.md,
  },
  filtros: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.xs,
    marginBottom: spacing.md,
  },
  filtro: {
    paddingHorizontal: spacing.md,
    paddingVertical: 7,
    borderRadius: radius.pill,
    backgroundColor: colors.neutral[100],
    borderWidth: 1,
    borderColor: colors.neutral[200],
  },
  filtroActivo: {
    backgroundColor: colors.primary[600],
    borderColor: colors.primary[600],
    ...shadows.sm,
  },
  cardComunicado: {
    position: 'relative',
    overflow: 'hidden',
    marginBottom: spacing.sm,
    borderRadius: radius.xl,
  },
  unreadStripe: {
    position: 'absolute',
    left: 0,
    top: 0,
    bottom: 0,
    width: 4,
    backgroundColor: colors.primary[600],
  },
  cardHeaderPressable: {
    padding: spacing.md,
    paddingLeft: spacing.lg,
  },
  cabecera: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  cuerpoDesplegado: {
    paddingHorizontal: spacing.lg,
    paddingBottom: spacing.lg,
  },
  divisor: {
    height: 1,
    backgroundColor: colors.surface.border,
    marginVertical: spacing.sm,
  },
  accionesCuerpo: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
    marginTop: spacing.md,
    alignItems: 'center',
  },
  btnPdf: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: spacing.md,
    paddingVertical: 7,
    borderRadius: radius.md,
    backgroundColor: colors.primary[50],
    borderWidth: 1,
    borderColor: colors.primary[200],
  },
  btnIrModulo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: spacing.md,
    paddingVertical: 7,
    borderRadius: radius.md,
    backgroundColor: colors.neutral[100],
  },
  marcarLeido: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: spacing.md,
    paddingVertical: 7,
    borderRadius: radius.md,
    backgroundColor: colors.neutral[100],
  },
  emptyCard: {
    marginTop: spacing.sm,
    alignItems: 'center',
    paddingVertical: spacing['2xl'],
    paddingHorizontal: spacing.xl,
  },
});

