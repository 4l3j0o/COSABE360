import { router, useFocusEffect } from 'expo-router';
import {
  CalendarDays,
  Check,
  CheckCircle2,
  Clock,
  ListTodo,
  Plus,
  RefreshCw,
  Sparkles,
} from 'lucide-react-native';
import { useCallback, useState } from 'react';
import { ActivityIndicator, Pressable, StyleSheet, View } from 'react-native';

import { AppText, Button, Card, Chip, Screen } from '@/components/ui';
import { usePendientes, type Pendiente } from '@/store/pendientes';
import { useSesion } from '@/store/session';
import { colors, radius, shadows, spacing } from '@/theme/tokens';

const filtros = ['Todos', 'Pendientes', 'Hechos'] as const;

const configPrioridad: Record<
  Pendiente['prioridad'],
  { color: string; bg: string; border: string; label: string }
> = {
  alta: {
    color: colors.semantic.danger,
    bg: colors.accent.roseLight,
    border: colors.semantic.danger,
    label: 'ALTA',
  },
  media: {
    color: colors.semantic.warning,
    bg: colors.accent.amberLight,
    border: colors.semantic.warning,
    label: 'MEDIA',
  },
  baja: {
    color: colors.accent.emerald,
    bg: colors.accent.emeraldLight,
    border: colors.accent.emerald,
    label: 'BAJA',
  },
};

export default function Pendientes() {
  const items = usePendientes((s) => s.items);
  const cargando = usePendientes((s) => s.cargando);
  const error = usePendientes((s) => s.error);
  const cargar = usePendientes((s) => s.cargar);
  const alternar = usePendientes((s) => s.alternar);
  const [filtro, setFiltro] = useState<(typeof filtros)[number]>('Todos');

  // Cada vez que se entra a la pestaña se vuelve a leer la tabla (SELECT).
  useFocusEffect(
    useCallback(() => {
      cargar();
    }, [cargar])
  );

  const lista = items.filter((i) => {
    if (filtro === 'Pendientes') return !i.hecho;
    if (filtro === 'Hechos') return i.hecho;
    return true;
  });

  const activos = items.filter((i) => !i.hecho).length;
  const completados = items.filter((i) => i.hecho).length;

  return (
    <Screen refreshing={cargando} onRefresh={cargar}>
      {/* Encabezado */}
      <View style={styles.encabezado}>
        <View>
          <View style={styles.badgeDb}>
            <View style={styles.dotOnline} />
            <AppText variant="micro" color={colors.primary[800]} style={{ fontWeight: '700' }}>
              MYSQL LOCAL · TABLA PENDIENTES
            </AppText>
          </View>
          <AppText variant="title">Mis pendientes</AppText>
          <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: 2 }}>
            Organizador escolar persistente para el estudiante
          </AppText>
        </View>

        <Pressable
          accessibilityRole="button"
          accessibilityLabel="Nuevo pendiente"
          style={({ pressed }) => [styles.nuevo, pressed && styles.nuevoPressed]}
          onPress={() => router.push('/pendiente/form')}>
          <Plus size={22} color="#FFFFFF" strokeWidth={2.5} />
        </Pressable>
      </View>

      {/* Mini Bento Stats */}
      <View style={styles.statsRow}>
        <View style={styles.statCard}>
          <Clock size={16} color={colors.semantic.warning} />
          <View style={{ marginLeft: spacing.xs }}>
            <AppText variant="captionStrong" color={colors.text.primary}>
              {activos}
            </AppText>
            <AppText variant="micro" color={colors.text.tertiary}>
              Por hacer
            </AppText>
          </View>
        </View>
        <View style={styles.statCard}>
          <CheckCircle2 size={16} color={colors.accent.emerald} />
          <View style={{ marginLeft: spacing.xs }}>
            <AppText variant="captionStrong" color={colors.text.primary}>
              {completados}
            </AppText>
            <AppText variant="micro" color={colors.text.tertiary}>
              Hechos
            </AppText>
          </View>
        </View>
        <View style={styles.statCard}>
          <ListTodo size={16} color={colors.primary[600]} />
          <View style={{ marginLeft: spacing.xs }}>
            <AppText variant="captionStrong" color={colors.text.primary}>
              {items.length}
            </AppText>
            <AppText variant="micro" color={colors.text.tertiary}>
              Total en BD
            </AppText>
          </View>
        </View>
      </View>

      {/* Filtros Segmentados */}
      <View style={styles.filtros}>
        {filtros.map((f) => {
          const count =
            f === 'Todos' ? items.length : f === 'Pendientes' ? activos : completados;
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
              <View style={[styles.filtroCount, activo && styles.filtroCountActivo]}>
                <AppText
                  variant="micro"
                  color={activo ? colors.primary[700] : colors.text.tertiary}>
                  {count}
                </AppText>
              </View>
            </Pressable>
          );
        })}
      </View>

      {/* Mensaje de Error */}
      {error ? (
        <Card style={styles.errorCard}>
          <AppText variant="bodyStrong" color={colors.semantic.danger}>
            No se pudo leer la base de datos
          </AppText>
          <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: 4 }}>
            {error}
          </AppText>
          <View style={{ flexDirection: 'row', gap: spacing.sm, marginTop: spacing.md }}>
            <Button
              label="Reintentar"
              variant="secondary"
              onPress={cargar}
              icon={<RefreshCw size={15} color={colors.primary[700]} />}
              style={{ flex: 1 }}
            />
            <Button
              label="Entrar a Demo"
              variant="primary"
              onPress={async () => {
                await useSesion.getState().signInDemo();
                await cargar();
              }}
              style={{ flex: 1 }}
            />
          </View>
        </Card>
      ) : null}

      {/* Indicador de Carga */}
      {cargando && items.length === 0 ? (
        <View style={styles.cargando}>
          <ActivityIndicator color={colors.primary[600]} size="large" />
          <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: spacing.sm }}>
            Sincronizando tareas desde MySQL…
          </AppText>
        </View>
      ) : null}

      {/* Lista Vacía */}
      {!error && !cargando && lista.length === 0 ? (
        <Card style={styles.emptyCard}>
          <View style={styles.emptyIconWrap}>
            <Sparkles size={28} color={colors.primary[600]} />
          </View>
          <AppText variant="bodyStrong" style={{ marginTop: spacing.sm }}>
            {filtro === 'Hechos' ? 'Aún no completaste tareas' : 'No tienes pendientes en esta lista'}
          </AppText>
          <AppText variant="caption" color={colors.text.tertiary} center style={{ marginTop: 4 }}>
            {filtro === 'Hechos'
              ? 'Marca tus tareas pendientes con el checkbox para archivarlas como hechas.'
              : 'Tocá el botón verde «+» en la esquina superior para registrar una nueva tarea.'}
          </AppText>
          <Button
            label="Crear nuevo pendiente"
            variant="outline"
            onPress={() => router.push('/pendiente/form')}
            icon={<Plus size={16} color={colors.primary[700]} />}
            style={{ marginTop: spacing.lg }}
          />
        </Card>
      ) : null}

      {/* Tarjetas de Tareas */}
      <View style={{ marginTop: spacing.xs }}>
        {lista.map((p) => {
          const cfg = configPrioridad[p.prioridad] || configPrioridad.media;
          return (
            <Pressable
              key={p.id}
              onPress={() => router.push(`/pendiente/${p.id}`)}
              style={({ pressed }) => [pressed && styles.itemPressed]}>
              <Card style={styles.tareaCard} padded={false}>
                {/* Borde izquierdo con el color de prioridad */}
                <View style={[styles.priorityBar, { backgroundColor: cfg.border }]} />

                <View style={styles.tareaContent}>
                  {/* Botón de Checkbox */}
                  <Pressable
                    accessibilityRole="checkbox"
                    accessibilityLabel={`Marcar ${p.titulo}`}
                    accessibilityState={{ checked: p.hecho }}
                    onPress={() => alternar(p.id)}
                    style={[styles.check, p.hecho && styles.checkActivo]}>
                    {p.hecho ? <Check size={14} color="#FFFFFF" strokeWidth={3} /> : null}
                  </Pressable>

                  {/* Datos del pendiente */}
                  <View style={{ flex: 1 }}>
                    <AppText
                      variant="bodyStrong"
                      color={p.hecho ? colors.text.tertiary : colors.text.primary}
                      style={p.hecho && styles.textoCompletado}
                      numberOfLines={2}>
                      {p.titulo}
                    </AppText>

                    <View style={styles.meta}>
                      <View style={styles.metaBadge}>
                        <AppText variant="micro" color={colors.text.secondary}>
                          {p.materia}
                        </AppText>
                      </View>
                      <View style={styles.metaItem}>
                        <CalendarDays size={12} color={colors.text.tertiary} />
                        <AppText variant="micro" color={colors.text.tertiary}>
                          {p.fecha}
                        </AppText>
                      </View>
                    </View>
                  </View>

                  {/* Chip de prioridad */}
                  <Chip
                    label={cfg.label}
                    color={cfg.color}
                    background={cfg.bg}
                    borderColor={cfg.border}
                    small
                  />
                </View>
              </Card>
            </Pressable>
          );
        })}
      </View>
    </Screen>
  );
}

const styles = StyleSheet.create({
  encabezado: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: spacing.md,
  },
  badgeDb: {
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
  dotOnline: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: colors.accent.emerald,
  },
  nuevo: {
    width: 44,
    height: 44,
    borderRadius: radius.pill,
    backgroundColor: colors.primary[600],
    alignItems: 'center',
    justifyContent: 'center',
    ...shadows.card,
  },
  nuevoPressed: {
    transform: [{ scale: 0.94 }],
    backgroundColor: colors.primary[700],
  },
  statsRow: {
    flexDirection: 'row',
    gap: spacing.sm,
    marginBottom: spacing.md,
  },
  statCard: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm + 2,
    backgroundColor: colors.surface.card,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.surface.border,
    ...shadows.sm,
  },
  filtros: {
    flexDirection: 'row',
    gap: spacing.xs,
    padding: 3,
    backgroundColor: colors.neutral[100],
    borderRadius: radius.pill,
    marginBottom: spacing.md,
  },
  filtro: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 7,
    borderRadius: radius.pill,
  },
  filtroActivo: {
    backgroundColor: colors.primary[600],
    ...shadows.sm,
  },
  filtroCount: {
    paddingHorizontal: 6,
    paddingVertical: 1,
    borderRadius: radius.pill,
    backgroundColor: colors.neutral[200],
  },
  filtroCountActivo: {
    backgroundColor: '#FFFFFF',
  },
  errorCard: {
    borderColor: colors.semantic.danger,
    marginBottom: spacing.md,
  },
  cargando: {
    alignItems: 'center',
    marginTop: spacing['2xl'],
    paddingVertical: spacing.xl,
  },
  emptyCard: {
    marginTop: spacing.sm,
    alignItems: 'center',
    paddingVertical: spacing['2xl'],
    paddingHorizontal: spacing.lg,
  },
  emptyIconWrap: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: colors.primary[50],
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: colors.primary[200],
  },
  tareaCard: {
    position: 'relative',
    overflow: 'hidden',
    marginBottom: spacing.sm,
  },
  priorityBar: {
    position: 'absolute',
    left: 0,
    top: 0,
    bottom: 0,
    width: 4,
  },
  tareaContent: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    padding: spacing.md,
    paddingLeft: spacing.md + 4,
  },
  check: {
    width: 24,
    height: 24,
    borderRadius: radius.sm,
    borderWidth: 1.5,
    borderColor: colors.neutral[300],
    backgroundColor: '#FFFFFF',
    alignItems: 'center',
    justifyContent: 'center',
  },
  checkActivo: {
    backgroundColor: colors.primary[600],
    borderColor: colors.primary[600],
  },
  textoCompletado: {
    textDecorationLine: 'line-through',
  },
  meta: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    marginTop: 4,
  },
  metaBadge: {
    backgroundColor: colors.neutral[100],
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: radius.xs,
  },
  metaItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  itemPressed: {
    opacity: 0.95,
    transform: [{ scale: 0.995 }],
  },
});

