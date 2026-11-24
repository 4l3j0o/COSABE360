import { router, useFocusEffect, useLocalSearchParams } from 'expo-router';
import {
  AlertTriangle,
  ArrowLeft,
  CalendarDays,
  CheckCircle2,
  Database,
  Pencil,
  RotateCcw,
  Trash2,
} from 'lucide-react-native';
import { useCallback, useState } from 'react';
import { ActivityIndicator, Modal, Pressable, StyleSheet, View } from 'react-native';

import { AppText, Button, Card, Chip, Screen } from '@/components/ui';
import { usePendientes } from '@/store/pendientes';
import { colors, radius, shadows, spacing } from '@/theme/tokens';

const prioridadesConfig: Record<string, { label: string; color: string; bg: string; border: string }> = {
  alta: { label: 'ALTA PRIORIDAD', color: colors.semantic.danger, bg: colors.accent.roseLight, border: colors.semantic.danger },
  media: { label: 'PRIORIDAD MEDIA', color: colors.semantic.warning, bg: colors.accent.amberLight, border: colors.semantic.warning },
  baja: { label: 'PRIORIDAD BAJA', color: colors.accent.emerald, bg: colors.accent.emeraldLight, border: colors.accent.emerald },
};

export default function DetallePendiente() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const pendiente = usePendientes((s) => s.items.find((i) => String(i.id) === id));
  const cargando = usePendientes((s) => s.cargando);
  const error = usePendientes((s) => s.error);
  const cargar = usePendientes((s) => s.cargar);
  const alternar = usePendientes((s) => s.alternar);
  const eliminar = usePendientes((s) => s.eliminar);

  const [confirmando, setConfirmando] = useState(false);
  const [borrando, setBorrando] = useState(false);
  const [errorBorrado, setErrorBorrado] = useState<string | null>(null);

  useFocusEffect(
    useCallback(() => {
      cargar();
    }, [cargar])
  );

  async function confirmarBorrado() {
    if (!pendiente) return;
    setBorrando(true);
    setErrorBorrado(null);
    const ok = await eliminar(pendiente.id);
    setBorrando(false);
    if (ok) {
      setConfirmando(false);
      router.back();
    } else {
      setErrorBorrado(usePendientes.getState().error ?? 'No se pudo eliminar el pendiente.');
    }
  }

  if (!pendiente) {
    return (
      <Screen>
        <View style={styles.barra}>
          <Pressable onPress={() => router.back()} style={styles.volver}>
            <ArrowLeft size={18} color={colors.primary[700]} />
            <AppText variant="captionStrong" color={colors.primary[700]}>
              Volver
            </AppText>
          </Pressable>
        </View>
        {cargando ? (
          <ActivityIndicator color={colors.primary[600]} style={{ marginTop: spacing['2xl'] }} />
        ) : (
          <Card style={{ marginTop: spacing.lg, alignItems: 'center', paddingVertical: spacing.xl }}>
            <AppText variant="bodyStrong" color={colors.text.secondary}>
              {error ?? 'No se encontró el pendiente seleccionado.'}
            </AppText>
            <Button
              label="Reintentar lectura"
              variant="secondary"
              onPress={cargar}
              style={{ marginTop: spacing.lg }}
            />
          </Card>
        )}
      </Screen>
    );
  }

  const pConfig = prioridadesConfig[pendiente.prioridad] || prioridadesConfig.media;

  return (
    <Screen>
      {/* Barra superior con volver y estado */}
      <View style={styles.barra}>
        <Pressable onPress={() => router.back()} style={styles.volver}>
          <ArrowLeft size={18} color={colors.primary[700]} />
          <AppText variant="captionStrong" color={colors.primary[700]}>
            Volver a la lista
          </AppText>
        </Pressable>

        <Chip
          label={pendiente.hecho ? 'COMPLETADO' : 'PENDIENTE'}
          variant={pendiente.hecho ? 'success' : 'neutral'}
          dot
          small
        />
      </View>

      {/* Tarjeta Principal de Detalle */}
      <Card style={styles.heroCard}>
        <View style={styles.chipsRow}>
          <View style={styles.materiaBadge}>
            <AppText variant="captionStrong" color={colors.primary[800]}>
              {pendiente.materia}
            </AppText>
          </View>
          <Chip
            label={pConfig.label}
            color={pConfig.color}
            background={pConfig.bg}
            borderColor={pConfig.border}
            small
          />
        </View>

        <AppText variant="title" style={styles.titulo}>
          {pendiente.titulo}
        </AppText>

        <View style={styles.metaRow}>
          <CalendarDays size={16} color={colors.text.tertiary} />
          <AppText variant="caption" color={colors.text.secondary}>
            Fecha límite de entrega: <AppText variant="captionStrong">{pendiente.fecha}</AppText>
          </AppText>
        </View>

        <View style={styles.divisor} />

        <View style={styles.sqlMeta}>
          <Database size={14} color={colors.primary[700]} />
          <AppText variant="micro" color={colors.text.secondary}>
            ID #{pendiente.id} · Almacenado en tabla MySQL `pendientes`
          </AppText>
        </View>
      </Card>

      {/* Acciones Rápidas */}
      <View style={styles.accionesContainer}>
        <Button
          label={pendiente.hecho ? 'Marcar como pendiente' : 'Marcar como completada'}
          variant={pendiente.hecho ? 'secondary' : 'primary'}
          onPress={() => alternar(pendiente.id)}
          icon={
            pendiente.hecho ? (
              <RotateCcw size={16} color={colors.primary[700]} />
            ) : (
              <CheckCircle2 size={16} color="#FFFFFF" strokeWidth={2.5} />
            )
          }
        />

        <Button
          label="Modificar datos (UPDATE)"
          variant="secondary"
          onPress={() => router.push(`/pendiente/form?id=${pendiente.id}`)}
          icon={<Pencil size={16} color={colors.primary[700]} />}
          style={{ marginTop: spacing.sm }}
        />

        <Button
          label="Eliminar de la base de datos (DELETE)"
          variant="ghost"
          onPress={() => {
            setErrorBorrado(null);
            setConfirmando(true);
          }}
          icon={<Trash2 size={16} color={colors.semantic.danger} />}
          style={{ marginTop: spacing.xs }}
        />
      </View>

      {/* Modal de confirmación para DELETE */}
      <Modal
        visible={confirmando}
        transparent
        animationType="fade"
        onRequestClose={() => setConfirmando(false)}>
        <View style={styles.overlay}>
          <View style={styles.modal}>
            <View style={styles.modalIconWrap}>
              <AlertTriangle size={24} color={colors.semantic.danger} />
            </View>
            <AppText variant="bodyStrong" center style={{ marginTop: spacing.sm }}>
              ¿Eliminar este pendiente?
            </AppText>
            <AppText
              variant="caption"
              color={colors.text.secondary}
              center
              style={{ marginTop: spacing.xs, lineHeight: 18 }}>
              Se ejecutará <AppText variant="captionStrong">DELETE FROM pendientes WHERE id = ?</AppText> en
              la base de datos MySQL local y se eliminará permanentemente.
            </AppText>

            {errorBorrado ? (
              <View style={styles.errorBox}>
                <AppText variant="micro" color={colors.semantic.danger}>
                  {errorBorrado}
                </AppText>
              </View>
            ) : null}

            <View style={styles.accionesModal}>
              <Button
                label="Cancelar"
                variant="secondary"
                onPress={() => setConfirmando(false)}
                fullWidth={false}
                style={{ flex: 1 }}
              />
              <Button
                label="Sí, eliminar"
                variant="danger"
                onPress={confirmarBorrado}
                loading={borrando}
                fullWidth={false}
                style={{ flex: 1 }}
              />
            </View>
          </View>
        </View>
      </Modal>
    </Screen>
  );
}

const styles = StyleSheet.create({
  barra: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: spacing.lg,
  },
  volver: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingVertical: 6,
    paddingHorizontal: 8,
    borderRadius: radius.md,
    backgroundColor: colors.primary[50],
  },
  heroCard: {
    borderRadius: radius.xl,
    padding: spacing.xl,
  },
  chipsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    marginBottom: spacing.md,
  },
  materiaBadge: {
    backgroundColor: colors.primary[50],
    paddingHorizontal: spacing.md,
    paddingVertical: 5,
    borderRadius: radius.pill,
    borderWidth: 1,
    borderColor: colors.primary[200],
  },
  titulo: {
    fontSize: 22,
    lineHeight: 28,
    marginBottom: spacing.md,
  },
  metaRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  divisor: {
    height: 1,
    backgroundColor: colors.surface.border,
    marginVertical: spacing.lg,
  },
  sqlMeta: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  accionesContainer: {
    marginTop: spacing.xl,
  },
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(15, 23, 42, 0.65)',
    alignItems: 'center',
    justifyContent: 'center',
    padding: spacing.xl,
  },
  modal: {
    width: '100%',
    maxWidth: 400,
    backgroundColor: colors.surface.card,
    borderRadius: radius['2xl'],
    padding: spacing.xl,
    alignItems: 'center',
    ...shadows.card,
  },
  modalIconWrap: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: colors.accent.roseLight,
    borderWidth: 1,
    borderColor: '#FECDD3',
    alignItems: 'center',
    justifyContent: 'center',
  },
  errorBox: {
    backgroundColor: colors.accent.roseLight,
    borderWidth: 1,
    borderColor: '#FECDD3',
    borderRadius: radius.sm,
    padding: spacing.sm,
    marginTop: spacing.sm,
    width: '100%',
  },
  accionesModal: {
    flexDirection: 'row',
    gap: spacing.sm,
    marginTop: spacing.xl,
    width: '100%',
  },
});

