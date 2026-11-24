import { router } from 'expo-router';
import { CheckCircle2, Download } from 'lucide-react-native';
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';

import { AppText, Button, Card, Chip, Screen, SectionTitle } from '@/components/ui';
import { estudiante, materias } from '@/data/mock';
import { colors, escalaNota, radius, spacing } from '@/theme/tokens';

const trimestres = ['1er', '2do', '3er'] as const;

const promedioPorTrimestre: Record<(typeof trimestres)[number], number> = {
  '1er': 85,
  '2do': 82,
  '3er': 86,
};

export default function Notas() {
  const [trimestre, setTrimestre] = useState<(typeof trimestres)[number]>('2do');
  const [descargando, setDescargando] = useState(false);
  const [mensajeDescarga, setMensajeDescarga] = useState<string | null>(null);

  const valorPromedio = promedioPorTrimestre[trimestre];
  const promedio = escalaNota(valorPromedio);

  async function descargarBoletin() {
    setDescargando(true);
    setMensajeDescarga(null);
    setTimeout(() => {
      setDescargando(false);
      setMensajeDescarga(`¡Boletín oficial del ${trimestre} Trimestre descargado en PDF!`);
      setTimeout(() => setMensajeDescarga(null), 5000);
    }, 1500);
  }

  return (
    <Screen>
      <AppText variant="micro" color={colors.text.tertiary} style={{ letterSpacing: 1 }}>
        GESTIÓN ACADÉMICA 2026
      </AppText>
      <AppText variant="title" style={{ marginTop: 2 }}>
        Mis Notas y Calificaciones
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
              {t} Trimestre
            </AppText>
          </Pressable>
        ))}
      </View>

      {mensajeDescarga ? (
        <Card style={styles.mensajeExito}>
          <CheckCircle2 size={18} color="#166534" />
          <AppText variant="caption" color="#166534" style={{ flex: 1, marginLeft: spacing.xs }}>
            {mensajeDescarga}
          </AppText>
        </Card>
      ) : null}

      <Card style={styles.resumen}>
        <View style={{ flex: 1 }}>
          <AppText variant="micro" color={colors.text.tertiary} style={{ letterSpacing: 1 }}>
            PROMEDIO DEL {trimestre.toUpperCase()} TRIMESTRE
          </AppText>
          <AppText variant="display" color={promedio.color}>
            {valorPromedio}
          </AppText>
          <AppText variant="caption" color={colors.text.secondary}>
            {promedio.sigla} · {promedio.label}
          </AppText>
        </View>
        <Chip label={`${trimestre} Trimestre`} />
      </Card>

      <View style={{ marginTop: spacing.xl }}>
        <SectionTitle title={`Materias evaluadas (${materias.length})`} />
        {materias.map((m) => {
          // Ajuste leve según trimestre para que sea dinámico
          const delta = trimestre === '1er' ? 2 : trimestre === '3er' ? 3 : 0;
          const notaAjustada = Math.min(100, Math.max(35, m.promedio + delta));
          const escala = escalaNota(notaAjustada);

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
                      {notaAjustada}
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
          ED 0–50 (En Desarrollo) · DA 51–68 (D. Aceptable) · DO 69–84 (D. Óptimo) · DP 85–100 (D. Pleno)
        </AppText>
      </Card>

      <Button
        label={descargando ? 'Generando PDF oficial…' : 'Descargar boletín PDF'}
        variant="secondary"
        onPress={descargarBoletin}
        loading={descargando}
        icon={<Download size={18} color={colors.primary[700]} />}
        style={{ marginTop: spacing.md }}
      />
      <AppText variant="micro" color={colors.text.tertiary} center style={{ marginTop: spacing.sm }}>
        Documento oficial validado con sistema SIE del Ministerio de Educación
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
  mensajeExito: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F0FDF4',
    borderColor: '#86EFAC',
    borderWidth: 1,
    marginTop: spacing.md,
  },
});
