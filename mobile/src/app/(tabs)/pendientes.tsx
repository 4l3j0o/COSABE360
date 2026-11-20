import { router } from 'expo-router';
import { CalendarDays, Check, Plus } from 'lucide-react-native';
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';

import { AppText, Card, Chip, Screen } from '@/components/ui';
import { usePendientes, type Pendiente } from '@/store/pendientes';
import { colors, radius, shadows, spacing } from '@/theme/tokens';

const filtros = ['Todos', 'Pendientes', 'Hechos'] as const;

const colorPrioridad: Record<Pendiente['prioridad'], { color: string; bg: string }> = {
  alta: { color: colors.semantic.danger, bg: '#FEF2F2' },
  media: { color: colors.semantic.warning, bg: '#FFFBEB' },
  baja: { color: colors.semantic.success, bg: colors.primary[50] },
};

export default function Pendientes() {
  const items = usePendientes((s) => s.items);
  const alternar = usePendientes((s) => s.alternar);
  const [filtro, setFiltro] = useState<(typeof filtros)[number]>('Todos');

  const lista = items.filter((i) => {
    if (filtro === 'Pendientes') return !i.hecho;
    if (filtro === 'Hechos') return i.hecho;
    return true;
  });

  const activos = items.filter((i) => !i.hecho).length;

  return (
    <Screen>
      <View style={styles.encabezado}>
        <View>
          <AppText variant="title">Mis pendientes</AppText>
          <AppText variant="caption" color={colors.text.secondary}>
            {activos} sin completar de {items.length}
          </AppText>
        </View>
        <Pressable style={styles.nuevo} onPress={() => router.push('/pendiente/form')}>
          <Plus size={22} color="#FFFFFF" />
        </Pressable>
      </View>

      <View style={styles.filtros}>
        {filtros.map((f) => (
          <Pressable key={f} onPress={() => setFiltro(f)} style={[styles.filtro, filtro === f && styles.filtroActivo]}>
            <AppText variant="caption" color={filtro === f ? '#FFFFFF' : colors.text.secondary}>
              {f}
            </AppText>
          </Pressable>
        ))}
      </View>

      {lista.length === 0 ? (
        <Card style={{ marginTop: spacing.lg, alignItems: 'center', paddingVertical: spacing['2xl'] }}>
          <AppText variant="body" color={colors.text.secondary}>
            No tienes pendientes en esta lista
          </AppText>
          <AppText variant="caption" color={colors.text.tertiary} style={{ marginTop: 4 }}>
            Tocá el botón + para crear uno
          </AppText>
        </Card>
      ) : (
        lista.map((p) => {
          const prioridad = colorPrioridad[p.prioridad];
          return (
            <Pressable key={p.id} onPress={() => router.push(`/pendiente/${p.id}`)}>
              <Card style={{ marginTop: spacing.sm }} padded={false}>
                <View style={styles.fila}>
                  <Pressable onPress={() => alternar(p.id)} style={[styles.check, p.hecho && styles.checkActivo]}>
                    {p.hecho ? <Check size={16} color="#FFFFFF" /> : null}
                  </Pressable>
                  <View style={{ flex: 1 }}>
                    <AppText
                      variant="bodyStrong"
                      color={p.hecho ? colors.text.tertiary : colors.text.primary}
                      style={p.hecho && { textDecorationLine: 'line-through' }}>
                      {p.titulo}
                    </AppText>
                    <View style={styles.meta}>
                      <CalendarDays size={13} color={colors.text.tertiary} />
                      <AppText variant="micro" color={colors.text.tertiary}>
                        {p.fecha} · {p.materia}
                      </AppText>
                    </View>
                  </View>
                  <Chip label={p.prioridad.toUpperCase()} color={prioridad.color} background={prioridad.bg} small />
                </View>
              </Card>
            </Pressable>
          );
        })
      )}
    </Screen>
  );
}

const styles = StyleSheet.create({
  encabezado: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.lg,
  },
  nuevo: {
    width: 46,
    height: 46,
    borderRadius: 23,
    backgroundColor: colors.primary[600],
    alignItems: 'center',
    justifyContent: 'center',
    ...shadows.card,
  },
  filtros: { flexDirection: 'row', gap: spacing.sm },
  filtro: {
    paddingHorizontal: spacing.lg,
    paddingVertical: 8,
    borderRadius: radius.pill,
    backgroundColor: colors.slate[100],
  },
  filtroActivo: { backgroundColor: colors.primary[600] },
  fila: { flexDirection: 'row', alignItems: 'center', gap: spacing.md, padding: spacing.md },
  check: {
    width: 26,
    height: 26,
    borderRadius: 13,
    borderWidth: 2,
    borderColor: colors.slate[300],
    alignItems: 'center',
    justifyContent: 'center',
  },
  checkActivo: { backgroundColor: colors.primary[600], borderColor: colors.primary[600] },
  meta: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 2 },
});
