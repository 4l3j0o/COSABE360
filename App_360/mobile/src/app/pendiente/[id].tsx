import { router, useLocalSearchParams } from 'expo-router';
import { ArrowLeft, CalendarDays, Pencil, Trash2 } from 'lucide-react-native';
import { Alert, Pressable, StyleSheet, View } from 'react-native';

import { AppText, Button, Card, Chip, Screen } from '@/components/ui';
import { usePendientes } from '@/store/pendientes';
import { colors, spacing } from '@/theme/tokens';

export default function DetallePendiente() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const pendiente = usePendientes((s) => s.items.find((i) => i.id === id));
  const alternar = usePendientes((s) => s.alternar);
  const eliminar = usePendientes((s) => s.eliminar);

  if (!pendiente) {
    return (
      <Screen>
        <AppText variant="body" color={colors.text.secondary}>
          No se encontró el pendiente.
        </AppText>
        <Button label="Volver" variant="secondary" onPress={() => router.back()} style={{ marginTop: spacing.lg }} />
      </Screen>
    );
  }

  function confirmarBorrado() {
    Alert.alert('Eliminar pendiente', '¿Seguro que querés borrarlo?', [
      { text: 'Cancelar', style: 'cancel' },
      {
        text: 'Eliminar',
        style: 'destructive',
        onPress: () => {
          eliminar(pendiente!.id);
          router.back();
        },
      },
    ]);
  }

  return (
    <Screen>
      <View style={styles.barra}>
        <Pressable onPress={() => router.back()} style={styles.volver}>
          <ArrowLeft size={20} color={colors.slate[700]} />
          <AppText variant="caption" color={colors.text.secondary}>
            Volver
          </AppText>
        </Pressable>
        <Chip label={pendiente.hecho ? 'HECHO' : 'PENDIENTE'} />
      </View>

      <AppText variant="title">{pendiente.titulo}</AppText>
      <View style={styles.meta}>
        <CalendarDays size={16} color={colors.text.tertiary} />
        <AppText variant="caption" color={colors.text.secondary}>
          {pendiente.fecha} · {pendiente.materia}
        </AppText>
      </View>
      <Chip
        label={`Prioridad ${pendiente.prioridad}`}
        small
        color={colors.slate[600]}
        background={colors.slate[100]}
      />

      <Card style={{ marginTop: spacing.xl }}>
        <AppText variant="micro" color={colors.text.tertiary}>
          DESCRIPCIÓN
        </AppText>
        <AppText variant="body" style={{ marginTop: 4 }}>
          {pendiente.titulo} para la materia {pendiente.materia}. Fecha límite: {pendiente.fecha}.
        </AppText>
      </Card>

      <Button
        label={pendiente.hecho ? 'Marcar como pendiente' : 'Marcar como hecho'}
        variant="secondary"
        onPress={() => alternar(pendiente.id)}
        style={{ marginTop: spacing.lg }}
      />
      <Button
        label="Editar"
        variant="ghost"
        onPress={() => router.push(`/pendiente/form?id=${pendiente.id}`)}
        icon={<Pencil size={16} color={colors.primary[700]} />}
        style={{ marginTop: spacing.sm }}
      />
      <Button
        label="Eliminar"
        variant="ghost"
        onPress={confirmarBorrado}
        icon={<Trash2 size={16} color={colors.semantic.danger} />}
        style={{ marginTop: spacing.xs }}
      />
    </Screen>
  );
}

const styles = StyleSheet.create({
  barra: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: spacing.lg },
  volver: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  meta: { flexDirection: 'row', alignItems: 'center', gap: 6, marginVertical: spacing.sm },
});
