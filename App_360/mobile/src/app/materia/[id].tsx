import { router, useLocalSearchParams } from 'expo-router';
import { ArrowLeft } from 'lucide-react-native';
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';

import { AppText, Card, Chip, ProgressRing, Screen } from '@/components/ui';
import { materias, notasMatematica } from '@/data/mock';
import { colors, escalaNota, radius, spacing } from '@/theme/tokens';

const tabs = ['Resumen', 'Notas', 'Asistencia'] as const;

export default function DetalleMateria() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const materia = materias.find((m) => m.id === id) ?? materias[0];
  const [tab, setTab] = useState<(typeof tabs)[number]>('Resumen');
  const escala = escalaNota(materia.promedio);

  return (
    <Screen>
      <Pressable onPress={() => router.back()} style={styles.volver}>
        <ArrowLeft size={20} color={colors.slate[700]} />
        <AppText variant="caption" color={colors.text.secondary}>
          Volver
        </AppText>
      </Pressable>

      <View style={styles.encabezado}>
        <View style={{ flex: 1 }}>
          <AppText variant="title">{materia.nombre}</AppText>
          <AppText variant="caption" color={colors.text.secondary}>
            {materia.docente}
          </AppText>
          <View style={{ marginTop: spacing.sm }}>
            <Chip label={`${escala.sigla} · ${escala.label}`} color={escala.color} background={escala.color + '1A'} />
          </View>
        </View>
        <ProgressRing value={materia.promedio} size={84} color={materia.color} trackColor={colors.slate[100]}>
          <AppText variant="section" color={materia.color}>
            {materia.promedio}
          </AppText>
        </ProgressRing>
      </View>

      <View style={styles.tabs}>
        {tabs.map((t) => (
          <Pressable key={t} onPress={() => setTab(t)} style={[styles.tab, tab === t && styles.tabActivo]}>
            <AppText variant="caption" color={tab === t ? colors.primary[700] : colors.text.tertiary}>
              {t}
            </AppText>
          </Pressable>
        ))}
      </View>

      {tab === 'Resumen' ? (
        <Card style={{ marginTop: spacing.md }}>
          <AppText variant="micro" color={colors.text.tertiary} style={{ letterSpacing: 1 }}>
            CÓMO SE CALCULA
          </AppText>
          {[
            ['SE', 'Ser', 10],
            ['SA', 'Saber', 45],
            ['HA', 'Hacer', 40],
            ['AU', 'Autoevaluación', 5],
          ].map(([sigla, label, peso]) => (
            <View key={sigla as string} style={styles.criterio}>
              <View style={styles.sigla}>
                <AppText variant="micro" color={colors.primary[700]}>
                  {sigla as string}
                </AppText>
              </View>
              <AppText variant="caption" style={{ flex: 1 }}>
                {label as string}
              </AppText>
              <AppText variant="caption" color={colors.text.secondary}>
                {peso as number} pts
              </AppText>
            </View>
          ))}
          <AppText variant="micro" color={colors.text.tertiary} style={{ marginTop: spacing.sm }}>
            Nota del trimestre sobre 100 · mínimo de aprobación 51.
          </AppText>
        </Card>
      ) : null}

      {tab === 'Notas' ? (
        <View style={{ marginTop: spacing.md }}>
          {notasMatematica.map((n) => (
            <Card key={n.id} style={{ marginBottom: spacing.sm }} padded={false}>
              <View style={styles.nota}>
                <View style={styles.sigla}>
                  <AppText variant="micro" color={colors.primary[700]}>
                    {n.criterio}
                  </AppText>
                </View>
                <View style={{ flex: 1 }}>
                  <AppText variant="bodyStrong">{n.titulo}</AppText>
                  <AppText variant="micro" color={colors.text.tertiary}>
                    {n.fecha} · {n.peso} pts
                  </AppText>
                </View>
                <AppText variant="bodyStrong">{n.nota}</AppText>
              </View>
            </Card>
          ))}
        </View>
      ) : null}

      {tab === 'Asistencia' ? (
        <Card style={{ marginTop: spacing.md }}>
          <AppText variant="body" color={colors.text.secondary}>
            Asistencia de la materia: 94 % (17 de 18 clases).
          </AppText>
        </Card>
      ) : null}
    </Screen>
  );
}

const styles = StyleSheet.create({
  volver: { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: spacing.lg },
  encabezado: { flexDirection: 'row', alignItems: 'center', gap: spacing.md },
  tabs: { flexDirection: 'row', gap: spacing.lg, marginTop: spacing.xl, borderBottomWidth: 1, borderBottomColor: colors.surface.border },
  tab: { paddingBottom: 8 },
  tabActivo: { borderBottomWidth: 2, borderBottomColor: colors.primary[600] },
  criterio: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, marginTop: spacing.sm },
  sigla: {
    width: 34,
    height: 26,
    borderRadius: radius.sm,
    backgroundColor: colors.primary[50],
    alignItems: 'center',
    justifyContent: 'center',
  },
  nota: { flexDirection: 'row', alignItems: 'center', gap: spacing.md, padding: spacing.md },
});
