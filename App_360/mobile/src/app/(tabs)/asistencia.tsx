import { router } from 'expo-router';
import { ArrowLeft, CheckCircle2, Clock, FileWarning, ShieldAlert } from 'lucide-react-native';
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';

import { AppText, Card, Chip, Screen, SectionTitle } from '@/components/ui';
import { asistencias, estudiante } from '@/data/mock';
import { colors, estadoAsistencia, radius, spacing } from '@/theme/tokens';

const filtros = ['TODOS', 'PRESENTE', 'AUSENTE', 'RETRASO', 'LICENCIA'] as const;

const conteo: Record<string, number> = {
  PRESENTE: 18,
  AUSENTE: 2,
  RETRASO: 1,
  LICENCIA: 1,
  RETIRO_JUSTIFICADO: 0,
};

export default function Asistencia() {
  const [filtro, setFiltro] = useState<(typeof filtros)[number]>('TODOS');

  const lista = asistencias.filter((a) => {
    if (filtro === 'TODOS') return true;
    return a.estado === filtro;
  });

  return (
    <Screen>
      <Pressable onPress={() => router.back()} style={styles.volver}>
        <ArrowLeft size={20} color={colors.slate[700]} />
        <AppText variant="caption" color={colors.text.secondary}>
          Volver a Inicio
        </AppText>
      </Pressable>

      <AppText variant="title">Asistencia Escolar</AppText>
      <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: 2 }}>
        {estudiante.curso} · Paralelo {estudiante.paralelo} · Gestión 2026
      </AppText>

      <Card style={styles.resumenCard}>
        <View style={{ flexDirection: 'row', alignItems: 'flex-end', gap: spacing.sm }}>
          <AppText variant="display" color={colors.primary[700]}>
            {estudiante.asistenciaPct}%
          </AppText>
          <AppText variant="caption" color={colors.text.secondary} style={{ marginBottom: 8 }}>
            de asistencia global (22 clases registradas)
          </AppText>
        </View>

        <View style={styles.grid}>
          <View style={styles.celda}>
            <CheckCircle2 size={16} color={estadoAsistencia.PRESENTE.color} />
            <AppText variant="bodyStrong">{conteo.PRESENTE}</AppText>
            <AppText variant="micro" color={colors.text.tertiary}>
              Presentes
            </AppText>
          </View>
          <View style={styles.celda}>
            <ShieldAlert size={16} color={estadoAsistencia.AUSENTE.color} />
            <AppText variant="bodyStrong">{conteo.AUSENTE}</AppText>
            <AppText variant="micro" color={colors.text.tertiary}>
              Faltas
            </AppText>
          </View>
          <View style={styles.celda}>
            <Clock size={16} color={estadoAsistencia.RETRASO.color} />
            <AppText variant="bodyStrong">{conteo.RETRASO}</AppText>
            <AppText variant="micro" color={colors.text.tertiary}>
              Retrasos
            </AppText>
          </View>
          <View style={styles.celda}>
            <FileWarning size={16} color={estadoAsistencia.LICENCIA.color} />
            <AppText variant="bodyStrong">{conteo.LICENCIA}</AppText>
            <AppText variant="micro" color={colors.text.tertiary}>
              Licencias
            </AppText>
          </View>
        </View>
      </Card>

      <View style={styles.filtros}>
        {filtros.map((f) => (
          <Pressable
            key={f}
            onPress={() => setFiltro(f)}
            style={[styles.filtro, filtro === f && styles.filtroActivo]}>
            <AppText
              variant="micro"
              color={filtro === f ? '#FFFFFF' : colors.text.secondary}>
              {f}
            </AppText>
          </Pressable>
        ))}
      </View>

      <View style={{ marginTop: spacing.lg }}>
        <SectionTitle title={`Registros (${lista.length})`} />
        {lista.map((a) => {
          const estilo = estadoAsistencia[a.estado];
          return (
            <Card key={a.id} style={{ marginBottom: spacing.sm }} padded={false}>
              <View style={styles.fila}>
                <View style={{ flex: 1 }}>
                  <AppText variant="bodyStrong">{a.materia}</AppText>
                  <AppText variant="micro" color={colors.text.tertiary}>
                    {a.fecha}
                    {a.justificacion ? ` · Motivo: ${a.justificacion}` : ''}
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
  volver: { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: spacing.md },
  resumenCard: { marginTop: spacing.md },
  grid: { flexDirection: 'row', justifyContent: 'space-between', marginTop: spacing.lg },
  celda: { alignItems: 'center', gap: 4, flex: 1 },
  filtros: { flexDirection: 'row', gap: spacing.xs, marginTop: spacing.lg, flexWrap: 'wrap' },
  filtro: {
    paddingHorizontal: spacing.md,
    paddingVertical: 6,
    borderRadius: radius.pill,
    backgroundColor: colors.slate[100],
  },
  filtroActivo: { backgroundColor: colors.primary[600] },
  fila: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: spacing.md,
    gap: spacing.sm,
  },
});
