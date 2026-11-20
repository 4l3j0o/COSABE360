import { StyleSheet, View } from 'react-native';

import { AppText, Card, Chip, Screen, SectionTitle } from '@/components/ui';
import { asistencias, estudiante } from '@/data/mock';
import { colors, estadoAsistencia, radius, spacing } from '@/theme/tokens';

const resumen = [
  ['PRESENTE', 'Presente'],
  ['AUSENTE', 'Ausente'],
  ['RETRASO', 'Retraso'],
  ['LICENCIA', 'Licencia'],
  ['RETIRO_JUSTIFICADO', 'Retiro'],
] as const;

const conteo: Record<string, number> = { PRESENTE: 18, AUSENTE: 2, RETRASO: 1, LICENCIA: 1, RETIRO_JUSTIFICADO: 0 };

export default function Asistencia() {
  return (
    <Screen>
      <AppText variant="title">Asistencia</AppText>
      <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: 2 }}>
        {estudiante.curso} · Paralelo {estudiante.paralelo}
      </AppText>

      <Card style={styles.resumenCard}>
        <View style={{ flexDirection: 'row', alignItems: 'flex-end', gap: spacing.sm }}>
          <AppText variant="display" color={colors.primary[700]}>
            {estudiante.asistenciaPct}%
          </AppText>
          <AppText variant="caption" color={colors.text.secondary} style={{ marginBottom: 8 }}>
            de asistencia
          </AppText>
        </View>
        <View style={styles.grid}>
          {resumen.map(([estado, label]) => {
            const estilo = estadoAsistencia[estado];
            return (
              <View key={estado} style={styles.celda}>
                <View style={[styles.punto, { backgroundColor: estilo.color }]} />
                <AppText variant="bodyStrong">{conteo[estado]}</AppText>
                <AppText variant="micro" color={colors.text.tertiary}>
                  {label}
                </AppText>
              </View>
            );
          })}
        </View>
      </Card>

      <View style={{ marginTop: spacing.xl }}>
        <SectionTitle title="Últimos registros" />
        {asistencias.map((a) => {
          const estilo = estadoAsistencia[a.estado];
          return (
            <Card key={a.id} style={{ marginBottom: spacing.sm }} padded={false}>
              <View style={styles.fila}>
                <View style={{ flex: 1 }}>
                  <AppText variant="bodyStrong">{a.materia}</AppText>
                  <AppText variant="micro" color={colors.text.tertiary}>
                    {a.fecha}
                    {a.justificacion ? ` · ${a.justificacion}` : ''}
                  </AppText>
                </View>
                <Chip label={estilo.label} color={estilo.color} background={estilo.bg} small />
              </View>
            </Card>
          );
        })}
      </View>
    </Screen>
  );
}

const styles = StyleSheet.create({
  resumenCard: { marginTop: spacing.lg },
  grid: { flexDirection: 'row', justifyContent: 'space-between', marginTop: spacing.lg },
  celda: { alignItems: 'center', gap: 2, flex: 1 },
  punto: { width: 8, height: 8, borderRadius: 4, marginBottom: 4 },
  fila: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: spacing.md,
    gap: spacing.sm,
  },
});
