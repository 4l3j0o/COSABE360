import { router, useLocalSearchParams } from 'expo-router';
import { ArrowLeft, BookOpen, CheckCircle, Clock } from 'lucide-react-native';
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';

import { AppText, Card, Chip, ProgressRing, Screen } from '@/components/ui';
import { materias, type NotaActividad } from '@/data/mock';
import { colors, escalaNota, radius, spacing } from '@/theme/tokens';

const tabs = ['Resumen', 'Evaluaciones', 'Asistencia'] as const;

function generarActividadesPorMateria(nombreMateria: string, promedio: number): NotaActividad[] {
  const base = Math.round(promedio / 10);
  return [
    {
      id: 'a1',
      titulo: `Evaluación diagnóstica y valores - ${nombreMateria}`,
      criterio: 'SE',
      peso: 10,
      nota: Math.min(10, Math.max(5, base)),
      fecha: '12/06/2026',
    },
    {
      id: 'a2',
      titulo: `Prueba teórica del tema central - ${nombreMateria}`,
      criterio: 'SA',
      peso: 45,
      nota: Math.min(45, Math.max(20, Math.round(base * 4.4))),
      fecha: '26/06/2026',
    },
    {
      id: 'a3',
      titulo: `Práctica aplicada y carpeta de trabajo - ${nombreMateria}`,
      criterio: 'HA',
      peso: 40,
      nota: Math.min(40, Math.max(18, Math.round(base * 3.8))),
      fecha: '10/07/2026',
    },
    {
      id: 'a4',
      titulo: 'Autoevaluación y compromiso formativo',
      criterio: 'AU',
      peso: 5,
      nota: 5,
      fecha: '18/07/2026',
    },
  ];
}

export default function DetalleMateria() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const materia = materias.find((m) => m.id === id) ?? materias[0];
  const [tab, setTab] = useState<(typeof tabs)[number]>('Resumen');
  const escala = escalaNota(materia.promedio);

  const actividades = generarActividadesPorMateria(materia.nombre, materia.promedio);

  return (
    <Screen>
      <Pressable onPress={() => router.back()} style={styles.volver}>
        <ArrowLeft size={20} color={colors.slate[700]} />
        <AppText variant="caption" color={colors.text.secondary}>
          Volver a Materias
        </AppText>
      </Pressable>

      <View style={styles.encabezado}>
        <View style={{ flex: 1 }}>
          <AppText variant="title">{materia.nombre}</AppText>
          <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: 2 }}>
            {materia.docente} · Aula asignada
          </AppText>
          <View style={{ marginTop: spacing.sm }}>
            <Chip
              label={`${escala.sigla} · ${escala.label}`}
              color={escala.color}
              background={escala.color + '1A'}
            />
          </View>
        </View>
        <ProgressRing
          value={materia.promedio}
          size={84}
          color={materia.color}
          trackColor={colors.slate[100]}>
          <AppText variant="section" color={materia.color}>
            {materia.promedio}
          </AppText>
        </ProgressRing>
      </View>

      <View style={styles.tabs}>
        {tabs.map((t) => (
          <Pressable
            key={t}
            onPress={() => setTab(t)}
            style={[styles.tab, tab === t && styles.tabActivo]}>
            <AppText
              variant="caption"
              color={tab === t ? colors.primary[700] : colors.text.tertiary}>
              {t}
            </AppText>
          </Pressable>
        ))}
      </View>

      {tab === 'Resumen' ? (
        <Card style={{ marginTop: spacing.md }}>
          <AppText variant="micro" color={colors.text.tertiary} style={{ letterSpacing: 1 }}>
            SISTEMA DE CALIFICACIÓN (LEY 070)
          </AppText>
          {[
            ['SE', 'Ser (Valores socio-comunitarios)', 10],
            ['SA', 'Saber (Conocimientos y comprensión)', 45],
            ['HA', 'Hacer (Producción y destrezas)', 40],
            ['AU', 'Decidir / Autoevaluación', 5],
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
          <View style={styles.divisor} />
          <AppText variant="micro" color={colors.text.secondary}>
            Total: 100 puntos · Mínimo de aprobación: 51 puntos.
          </AppText>
        </Card>
      ) : null}

      {tab === 'Evaluaciones' ? (
        <View style={{ marginTop: spacing.md }}>
          {actividades.map((n) => (
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
                    {n.fecha} · Ponderación: {n.peso} pts
                  </AppText>
                </View>
                <View style={{ alignItems: 'flex-end' }}>
                  <AppText variant="bodyStrong" color={colors.primary[700]}>
                    {n.nota}/{n.peso}
                  </AppText>
                  <AppText variant="micro" color={colors.semantic.success}>
                    Registrada
                  </AppText>
                </View>
              </View>
            </Card>
          ))}
        </View>
      ) : null}

      {tab === 'Asistencia' ? (
        <Card style={{ marginTop: spacing.md }}>
          <View style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.sm }}>
            <CheckCircle size={22} color={colors.semantic.success} />
            <View>
              <AppText variant="bodyStrong">95 % de asistencia en {materia.nombre}</AppText>
              <AppText variant="caption" color={colors.text.secondary}>
                19 clases asistidas de 20 programadas en el trimestre.
              </AppText>
            </View>
          </View>
        </Card>
      ) : null}
    </Screen>
  );
}

const styles = StyleSheet.create({
  volver: { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: spacing.lg },
  encabezado: { flexDirection: 'row', alignItems: 'center', gap: spacing.md },
  tabs: {
    flexDirection: 'row',
    gap: spacing.lg,
    marginTop: spacing.xl,
    borderBottomWidth: 1,
    borderBottomColor: colors.surface.border,
  },
  tab: { paddingBottom: 8 },
  tabActivo: { borderBottomWidth: 2, borderBottomColor: colors.primary[600] },
  criterio: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, marginTop: spacing.sm },
  sigla: {
    width: 36,
    height: 28,
    borderRadius: radius.sm,
    backgroundColor: colors.primary[50],
    alignItems: 'center',
    justifyContent: 'center',
  },
  nota: { flexDirection: 'row', alignItems: 'center', gap: spacing.md, padding: spacing.md },
  divisor: { height: 1, backgroundColor: colors.surface.border, marginVertical: spacing.md },
});
