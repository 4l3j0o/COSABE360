import {
  BookmarkCheck,
  BookmarkPlus,
  CalendarDays,
  Check,
  Globe2,
  RefreshCw,
  Sparkles,
  WifiOff,
} from 'lucide-react-native';
import { useState } from 'react';
import { ActivityIndicator, Pressable, StyleSheet, View } from 'react-native';

import { AppText, Button, Card, Chip, Screen } from '@/components/ui';
import { formatearFecha, useFeriados, type Feriado } from '@/lib/feriados';
import { usePendientes } from '@/store/pendientes';
import { colors, radius, shadows, spacing } from '@/theme/tokens';

export default function Feriados() {
  const { data, isLoading, isError, error, refetch, isRefetching } = useFeriados();
  const pendientes = usePendientes((s) => s.items);
  const agregarPendiente = usePendientes((s) => s.agregar);
  const [guardandoFecha, setGuardandoFecha] = useState<string | null>(null);
  const [mensajeExito, setMensajeExito] = useState<string | null>(null);

  const hoy = new Date().toISOString().slice(0, 10);
  const proximo = data?.find((f) => f.date >= hoy);

  function yaGuardado(feriado: Feriado) {
    const fechaFormat = formatearFecha(feriado.date);
    return pendientes.some(
      (p) =>
        p.fecha === fechaFormat ||
        p.titulo.toLowerCase().includes(feriado.localName.toLowerCase())
    );
  }

  async function guardarEnPendientes(feriado: Feriado) {
    setGuardandoFecha(feriado.date);
    setMensajeExito(null);
    try {
      const ok = await agregarPendiente({
        titulo: `Feriado: ${feriado.localName}`,
        materia: 'General',
        fecha: formatearFecha(feriado.date),
        prioridad: 'media',
      });
      if (ok) {
        setMensajeExito(`«${feriado.localName}» se sincronizó y guardó en MySQL.`);
        setTimeout(() => setMensajeExito(null), 4000);
      }
    } finally {
      setGuardandoFecha(null);
    }
  }

  return (
    <Screen>
      {/* Encabezado con status badge */}
      <View style={styles.encabezado}>
        <View style={styles.apiBadge}>
          <Globe2 size={13} color={colors.primary[700]} />
          <AppText variant="micro" color={colors.primary[800]} style={{ fontWeight: '700' }}>
            API NAGER.DATE · BOLIVIA (BO)
          </AppText>
        </View>
        <AppText variant="title">Feriados 2026</AppText>
        <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: 2 }}>
          Calendario oficial de Bolivia · integración con base de datos MySQL
        </AppText>
      </View>

      {/* Banner de Éxito al Guardar */}
      {mensajeExito ? (
        <Card style={styles.mensajeExito}>
          <View style={styles.checkIcon}>
            <Check size={16} color="#FFFFFF" strokeWidth={3} />
          </View>
          <AppText variant="captionStrong" color={colors.primary[900]} style={{ flex: 1 }}>
            {mensajeExito}
          </AppText>
        </Card>
      ) : null}

      {/* Estado de Carga */}
      {isLoading ? (
        <Card style={styles.estado}>
          <ActivityIndicator color={colors.primary[600]} size="large" />
          <AppText variant="bodyStrong" style={{ marginTop: spacing.md }}>
            Consultando API pública Nager.Date…
          </AppText>
          <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: 4 }}>
            Obteniendo calendario oficial de Bolivia en tiempo real
          </AppText>
        </Card>
      ) : null}

      {/* Estado de Error / Modo Avión */}
      {isError ? (
        <Card style={styles.estadoError}>
          <View style={styles.errorIconWrap}>
            <WifiOff size={28} color={colors.semantic.danger} />
          </View>
          <AppText variant="bodyStrong" style={{ marginTop: spacing.md }}>
            Sin conexión a internet (Modo Avión)
          </AppText>
          <AppText
            variant="caption"
            color={colors.text.secondary}
            center
            style={{ marginTop: 4, maxWidth: 300, lineHeight: 18 }}>
            {error instanceof Error
              ? error.message
              : 'No pudimos conectar con https://date.nager.at. Comprueba tu conexión.'}
          </AppText>
          <Button
            label={isRefetching ? 'Reintentando…' : 'Reintentar conexión'}
            variant="primary"
            onPress={() => refetch()}
            loading={isRefetching}
            icon={<RefreshCw size={15} color="#FFFFFF" />}
            style={{ marginTop: spacing.lg }}
          />
        </Card>
      ) : null}

      {data ? (
        <>
          {/* Próximo Feriado Hero Card */}
          {proximo ? (
            <Card style={styles.proximoCard}>
              <View style={styles.proximoHeader}>
                <View style={{ flex: 1 }}>
                  <View style={styles.proximoBadge}>
                    <Sparkles size={12} color="#FFFFFF" />
                    <AppText variant="micro" color="#FFFFFF" style={{ fontWeight: '700' }}>
                      PRÓXIMO FERIADO NACIONAL
                    </AppText>
                  </View>
                  <AppText variant="section" color="#FFFFFF" style={{ marginTop: 6 }}>
                    {proximo.localName}
                  </AppText>
                  <View style={styles.proximoFecha}>
                    <CalendarDays size={14} color="#D1FAE5" />
                    <AppText variant="caption" color="#D1FAE5">
                      {formatearFecha(proximo.date)}
                    </AppText>
                  </View>
                </View>

                {!yaGuardado(proximo) ? (
                  <Pressable
                    onPress={() => guardarEnPendientes(proximo)}
                    disabled={guardandoFecha === proximo.date}
                    style={({ pressed }) => [
                      styles.botonGuardarProximo,
                      pressed && styles.botonPressed,
                    ]}>
                    {guardandoFecha === proximo.date ? (
                      <ActivityIndicator size="small" color={colors.primary[800]} />
                    ) : (
                      <>
                        <BookmarkPlus size={16} color={colors.primary[800]} />
                        <AppText
                          variant="captionStrong"
                          color={colors.primary[800]}
                          style={{ marginLeft: 4 }}>
                          Guardar en BD
                        </AppText>
                      </>
                    )}
                  </Pressable>
                ) : (
                  <View style={styles.enBdBadge}>
                    <BookmarkCheck size={14} color="#065F46" />
                    <AppText variant="micro" color="#065F46" style={{ fontWeight: '700', marginLeft: 3 }}>
                      EN BD
                    </AppText>
                  </View>
                )}
              </View>
            </Card>
          ) : null}

          {/* Lista de Feriados del Año */}
          <View style={{ marginTop: spacing.lg }}>
            <View style={styles.listaHeader}>
              <AppText variant="micro" color={colors.text.tertiary} style={{ letterSpacing: 0.6 }}>
                CALENDARIO COMPLETO (TOCA PARA AGREGAR A PENDIENTES)
              </AppText>
              <AppText variant="micro" color={colors.primary[700]}>
                {data.length} feriados
              </AppText>
            </View>

            {data.map((f) => {
              const guardado = yaGuardado(f);
              const enGuardado = guardandoFecha === f.date;
              const mesNombre = new Date(f.date + 'T12:00:00')
                .toLocaleDateString('es-BO', { month: 'short' })
                .toUpperCase();

              return (
                <Card key={f.date} style={styles.feriadoCard} padded={false}>
                  <View style={styles.fila}>
                    {/* Tile de Fecha */}
                    <View style={styles.fechaTile}>
                      <AppText variant="bodyStrong" color={colors.primary[800]} style={{ fontSize: 16 }}>
                        {f.date.slice(8, 10)}
                      </AppText>
                      <AppText variant="micro" color={colors.primary[700]} style={{ fontSize: 10 }}>
                        {mesNombre}
                      </AppText>
                    </View>

                    {/* Contenido */}
                    <View style={{ flex: 1 }}>
                      <AppText variant="bodyStrong" numberOfLines={1}>
                        {f.localName}
                      </AppText>
                      <AppText variant="caption" color={colors.text.tertiary} style={{ marginTop: 2 }}>
                        {formatearFecha(f.date)}
                      </AppText>
                    </View>

                    {/* Chips o Botón de Guardado */}
                    {f.date === proximo?.date ? (
                      <Chip label="PRÓXIMO" variant="info" small />
                    ) : null}

                    {guardado ? (
                      <View style={styles.guardadoBadge}>
                        <BookmarkCheck size={15} color="#065F46" />
                        <AppText
                          variant="micro"
                          color="#065F46"
                          style={{ marginLeft: 4, fontWeight: '700' }}>
                          Guardado
                        </AppText>
                      </View>
                    ) : (
                      <Pressable
                        onPress={() => guardarEnPendientes(f)}
                        disabled={enGuardado}
                        style={({ pressed }) => [
                          styles.botonGuardarItem,
                          pressed && styles.botonPressed,
                        ]}
                        accessibilityLabel={`Guardar ${f.localName} en base de datos`}>
                        {enGuardado ? (
                          <ActivityIndicator size="small" color={colors.primary[600]} />
                        ) : (
                          <BookmarkPlus size={18} color={colors.primary[700]} />
                        )}
                      </Pressable>
                    )}
                  </View>
                </Card>
              );
            })}
          </View>

          <Button
            label="Actualizar datos desde la API"
            variant="ghost"
            onPress={() => refetch()}
            loading={isRefetching}
            icon={<RefreshCw size={15} color={colors.primary[700]} />}
            style={{ marginTop: spacing.md }}
          />
        </>
      ) : null}
    </Screen>
  );
}

const styles = StyleSheet.create({
  encabezado: {
    marginBottom: spacing.md,
  },
  apiBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: radius.pill,
    backgroundColor: colors.primary[50],
    borderWidth: 1,
    borderColor: colors.primary[200],
    alignSelf: 'flex-start',
    marginBottom: 6,
  },
  mensajeExito: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    backgroundColor: colors.primary[50],
    borderColor: colors.primary[300],
    borderWidth: 1.5,
    borderRadius: radius.lg,
    padding: spacing.md,
    marginBottom: spacing.md,
  },
  checkIcon: {
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: colors.primary[600],
    alignItems: 'center',
    justifyContent: 'center',
  },
  estado: {
    marginTop: spacing.xl,
    alignItems: 'center',
    paddingVertical: spacing['2xl'],
  },
  estadoError: {
    marginTop: spacing.xl,
    alignItems: 'center',
    paddingVertical: spacing['2xl'],
    borderColor: colors.semantic.danger,
    backgroundColor: colors.accent.roseLight,
  },
  errorIconWrap: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: '#FFFFFF',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: '#FECDD3',
  },
  proximoCard: {
    backgroundColor: colors.primary[700],
    borderRadius: radius.xl,
    padding: spacing.xl,
    ...shadows.card,
  },
  proximoHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    gap: spacing.md,
  },
  proximoBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: radius.pill,
    alignSelf: 'flex-start',
  },
  proximoFecha: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    marginTop: 4,
  },
  botonGuardarProximo: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    paddingHorizontal: spacing.md,
    paddingVertical: 8,
    borderRadius: radius.pill,
    ...shadows.sm,
  },
  enBdBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#D1FAE5',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: radius.pill,
  },
  listaHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.xs,
  },
  feriadoCard: {
    marginBottom: spacing.sm,
    borderRadius: radius.lg,
  },
  fila: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    padding: spacing.md,
  },
  fechaTile: {
    width: 48,
    height: 48,
    borderRadius: radius.md,
    backgroundColor: colors.primary[50],
    borderWidth: 1,
    borderColor: colors.primary[100],
    alignItems: 'center',
    justifyContent: 'center',
  },
  guardadoBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ECFDF5',
    borderWidth: 1,
    borderColor: '#A7F3D0',
    paddingHorizontal: 8,
    paddingVertical: 5,
    borderRadius: radius.pill,
  },
  botonGuardarItem: {
    padding: 8,
    borderRadius: radius.md,
    backgroundColor: colors.primary[50],
    borderWidth: 1,
    borderColor: colors.primary[200],
  },
  botonPressed: {
    opacity: 0.8,
    transform: [{ scale: 0.95 }],
  },
});

