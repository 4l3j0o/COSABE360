import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';

import { AppText, Card, Chip, Screen } from '@/components/ui';
import { comunicados } from '@/data/mock';
import { colors, radius, spacing } from '@/theme/tokens';

const filtros = ['Todos', 'Sin leer', 'Destacados'] as const;

export default function Comunicados() {
  const [filtro, setFiltro] = useState<(typeof filtros)[number]>('Todos');
  const [abierto, setAbierto] = useState<string | null>(null);

  const lista = comunicados.filter((c) => {
    if (filtro === 'Sin leer') return !c.leido;
    if (filtro === 'Destacados') return c.destacado;
    return true;
  });

  return (
    <Screen>
      <AppText variant="title">Comunicados</AppText>
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
        <Card style={{ marginTop: spacing.lg, alignItems: 'center' }}>
          <AppText variant="body" color={colors.text.secondary}>
            No tienes comunicados
          </AppText>
        </Card>
      ) : (
        lista.map((c) => (
          <Pressable key={c.id} onPress={() => setAbierto(abierto === c.id ? null : c.id)}>
            <Card style={{ marginTop: spacing.md }}>
              <View style={styles.cabecera}>
                <Chip label={c.categoria} small />
                {!c.leido ? <Chip label="NUEVO" color={colors.semantic.warning} background="#FFFBEB" small /> : null}
              </View>
              <AppText variant="bodyStrong" style={{ marginTop: spacing.sm }}>
                {c.titulo}
              </AppText>
              <AppText variant="micro" color={colors.text.tertiary} style={{ marginTop: 2 }}>
                {c.autor} · {c.fecha}
              </AppText>
              <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: spacing.sm }}>
                {c.resumen}
              </AppText>
              {abierto === c.id
                ? c.cuerpo.map((p, i) => (
                    <AppText key={i} variant="caption" style={{ marginTop: spacing.sm }}>
                      {p}
                    </AppText>
                  ))
                : null}
            </Card>
          </Pressable>
        ))
      )}
    </Screen>
  );
}

const styles = StyleSheet.create({
  filtros: { flexDirection: 'row', gap: spacing.sm, marginTop: spacing.md },
  filtro: {
    paddingHorizontal: spacing.lg,
    paddingVertical: 8,
    borderRadius: radius.pill,
    backgroundColor: colors.slate[100],
  },
  filtroActivo: { backgroundColor: colors.primary[600] },
  cabecera: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
});
