import { router } from 'expo-router';
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import { Download } from 'lucide-react-native';

import { AppText, Button, Card, Chip, Screen, SectionTitle } from '@/components/ui';
import { estudiante, materias } from '@/data/mock';
import { colors, escalaNota, radius, spacing } from '@/theme/tokens';

const trimestres = ['1er', '2do', '3er'];

export default function Notas() {
  const [trimestre, setTrimestre] = useState('2do');
  const promedio = escalaNota(estudiante.promedio);

  return (
    <Screen>
      <AppText variant="micro" color={colors.text.tertiary} style={{ letterSpacing: 1 }}>
        GESTIÓN 2026
      </AppText>
      <AppText variant="title" style={{ marginTop: 2 }}>
        Notas
      </AppText>

      <View style={styles.segmentado}>
        {trimestres.map((t) => (
          <Pressable
            key={t}
            onPress={() => setTrimestre(t)}
            style={[styles.segmento, trimestre === t && styles.segmentoActivo]}>
            <AppText
              variant="bodyStrong"
              color={trimestre === t ? '#FFFFFF' : colors.text.secondary}>
              {t}
            </AppText>
          </Pressable>
        ))}
      </View>

      <Card style={styles.resumen}>
        <View style={{ flex: 1 }}>
          <AppText variant="micro" color={colors.text.tertiary} style={{ letterSpacing: 1 }}>
            PROMEDIO GENERAL
          </AppText>
          <AppText variant="display" color={promedio.color}>
            {estudiante.promedio}
          </AppText>
          <AppText variant="caption" color={colors.text.secondary}>
            {promedio.sigla} · {promedio.label}
          </AppText>
        </View>
        <Chip label={`${trimestre} Trimestre`} />
      </Card>

      <View style={{ marginTop: spacing.xl }}>
        <SectionTitle title="Materias" />
        {materias.map((m) => {
          const escala = escalaNota(m.promedio);
          return (
            <Pressable key={m.id} onPress={() => router.push(`/materia/${m.id}`)}>
              <Card style={{ marginBottom: spacing.sm }} padded={false}>
                <View style={styles.fila}>
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
                  <View style={{ alignItems: 'flex-end' }}>
                    <AppText variant="bodyStrong" color={escala.color}>
                      {m.promedio}
                    </AppText>
                    <AppText variant="micro" color={escala.color}>
                      {escala.sigla}
                    </AppText>
                  </View>
                </View>
              </Card>
            </Pressable>
          );
        })}
      </View>

      <Card style={styles.leyenda}>
        <AppText variant="micro" color={colors.text.secondary}>
          ED 0–50 · DA 51–68 · DO 69–84 · DP 85–100
        </AppText>
      </Card>

      <Button
        label="Descargar boletín PDF"
        variant="secondary"
        icon={<Download size={18} color={colors.primary[700]} />}
        style={{ marginTop: spacing.md }}
      />
      <AppText variant="micro" color={colors.text.tertiary} center style={{ marginTop: spacing.sm }}>
        Documento oficial SIE
      </AppText>
    </Screen>
  );
}

const styles = StyleSheet.create({
  segmentado: {
    flexDirection: 'row',
    backgroundColor: colors.slate[100],
    borderRadius: radius.md,
    padding: 4,
    marginTop: spacing.md,
    gap: 4,
  },
  segmento: { flex: 1, alignItems: 'center', paddingVertical: 8, borderRadius: radius.sm },
  segmentoActivo: { backgroundColor: colors.primary[600] },
  resumen: { flexDirection: 'row', alignItems: 'center', marginTop: spacing.lg },
  fila: { flexDirection: 'row', alignItems: 'center', gap: spacing.md, padding: spacing.md },
  inicial: {
    width: 42,
    height: 42,
    borderRadius: radius.md,
    alignItems: 'center',
    justifyContent: 'center',
  },
  leyenda: { marginTop: spacing.sm, paddingVertical: spacing.sm, alignItems: 'center' },
});
