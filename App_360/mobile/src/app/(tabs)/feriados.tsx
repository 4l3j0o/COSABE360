import { RefreshCw, WifiOff } from 'lucide-react-native';
import { ActivityIndicator, Pressable, StyleSheet, View } from 'react-native';

import { AppText, Button, Card, Chip, Screen } from '@/components/ui';
import { formatearFecha, useFeriados } from '@/lib/feriados';
import { colors, radius, spacing } from '@/theme/tokens';

export default function Feriados() {
  const { data, isLoading, isError, error, refetch, isRefetching } = useFeriados();

  const hoy = new Date().toISOString().slice(0, 10);
  const proximo = data?.find((f) => f.date >= hoy);

  return (
    <Screen>
      <AppText variant="title">Feriados 2026</AppText>
      <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: 2 }}>
        Calendario oficial de Bolivia · datos de la API Nager.Date
      </AppText>

      {isLoading ? (
        <Card style={styles.estado}>
          <ActivityIndicator color={colors.primary[600]} />
          <AppText variant="body" color={colors.text.secondary} style={{ marginTop: spacing.md }}>
            Cargando feriados…
          </AppText>
        </Card>
      ) : null}

      {isError ? (
        <Card style={styles.estado}>
          <WifiOff size={32} color={colors.semantic.danger} />
          <AppText variant="bodyStrong" style={{ marginTop: spacing.md }}>
            No pudimos cargar los feriados
          </AppText>
          <AppText variant="caption" color={colors.text.secondary} center style={{ marginTop: 4 }}>
            {error instanceof Error ? error.message : 'Revisá tu conexión a internet.'}
          </AppText>
          <Button
            label={isRefetching ? 'Reintentando…' : 'Reintentar'}
            variant="secondary"
            onPress={() => refetch()}
            loading={isRefetching}
            icon={<RefreshCw size={16} color={colors.primary[700]} />}
            style={{ marginTop: spacing.lg }}
          />
        </Card>
      ) : null}

      {data ? (
        <>
          {proximo ? (
            <Card style={styles.proximo}>
              <AppText variant="micro" color="#CDEBCD" style={{ letterSpacing: 1 }}>
                PRÓXIMO FERIADO
              </AppText>
              <AppText variant="section" color="#FFFFFF" style={{ marginTop: 4 }}>
                {proximo.localName}
              </AppText>
              <AppText variant="caption" color="#D8F0D8">
                {formatearFecha(proximo.date)}
              </AppText>
            </Card>
          ) : null}

          <View style={{ marginTop: spacing.lg }}>
            {data.map((f) => (
              <Pressable key={f.date}>
                <Card style={{ marginBottom: spacing.sm }} padded={false}>
                  <View style={styles.fila}>
                    <View style={styles.fecha}>
                      <AppText variant="bodyStrong" color={colors.primary[700]}>
                        {f.date.slice(8, 10)}
                      </AppText>
                      <AppText variant="micro" color={colors.text.tertiary}>
                        {new Date(f.date + 'T12:00:00').toLocaleDateString('es-BO', { month: 'short' })}
                      </AppText>
                    </View>
                    <View style={{ flex: 1 }}>
                      <AppText variant="bodyStrong">{f.localName}</AppText>
                      <AppText variant="micro" color={colors.text.tertiary}>
                        {formatearFecha(f.date)}
                      </AppText>
                    </View>
                    {f.date === proximo?.date ? <Chip label="PRÓXIMO" small /> : null}
                  </View>
                </Card>
              </Pressable>
            ))}
          </View>

          <Button
            label="Actualizar"
            variant="ghost"
            onPress={() => refetch()}
            loading={isRefetching}
            icon={<RefreshCw size={16} color={colors.primary[700]} />}
            style={{ marginTop: spacing.md }}
          />
        </>
      ) : null}
    </Screen>
  );
}

const styles = StyleSheet.create({
  estado: { marginTop: spacing.xl, alignItems: 'center', paddingVertical: spacing['2xl'] },
  proximo: { marginTop: spacing.lg, backgroundColor: colors.primary[600] },
  fila: { flexDirection: 'row', alignItems: 'center', gap: spacing.md, padding: spacing.md },
  fecha: {
    width: 46,
    height: 46,
    borderRadius: radius.md,
    backgroundColor: colors.primary[50],
    alignItems: 'center',
    justifyContent: 'center',
  },
});
