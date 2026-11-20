import { router } from 'expo-router';
import { ChevronRight, LogOut, ShieldCheck, User } from 'lucide-react-native';
import { useState } from 'react';
import { Pressable, StyleSheet, Switch, View } from 'react-native';

import { AppText, Avatar, Button, Card, Chip, Screen } from '@/components/ui';
import { estudiante } from '@/data/mock';
import { useSesion } from '@/store/session';
import { colors, radius, spacing } from '@/theme/tokens';

export default function Perfil() {
  const nombre = useSesion((s) => s.nombre) || `${estudiante.nombres} ${estudiante.apellidos}`;
  const curso = useSesion((s) => s.curso) || `${estudiante.curso} · Paralelo ${estudiante.paralelo}`;
  const signOut = useSesion((s) => s.signOut);

  const [notiComunicados, setNotiComunicados] = useState(true);
  const [notiNotas, setNotiNotas] = useState(true);
  const [notiAsistencia, setNotiAsistencia] = useState(false);

  async function salir() {
    await signOut();
    router.replace('/login');
  }

  return (
    <Screen>
      <AppText variant="title">Mi perfil</AppText>

      <View style={styles.encabezado}>
        <Avatar name={nombre} size={76} />
        <AppText variant="section" style={{ marginTop: spacing.md }}>
          {nombre}
        </AppText>
        <AppText variant="caption" color={colors.text.secondary}>
          {curso}
        </AppText>
        <View style={{ marginTop: spacing.sm }}>
          <Chip label={`Código ${estudiante.codigo} · Turno ${estudiante.turno}`} background={colors.slate[100]} color={colors.slate[600]} />
        </View>
      </View>

      <AppText variant="micro" color={colors.text.tertiary} style={styles.tituloSeccion}>
        MI CUENTA
      </AppText>
      <Card padded={false}>
        <Pressable style={styles.fila}>
          <User size={19} color={colors.primary[700]} />
          <View style={{ flex: 1 }}>
            <AppText variant="bodyStrong">Datos personales</AppText>
            <AppText variant="micro" color={colors.text.tertiary}>
              Teléfono, dirección y fotografía
            </AppText>
          </View>
          <ChevronRight size={18} color={colors.slate[300]} />
        </Pressable>
        <View style={styles.divisor} />
        <Pressable style={styles.fila}>
          <ShieldCheck size={19} color={colors.primary[700]} />
          <View style={{ flex: 1 }}>
            <AppText variant="bodyStrong">Seguridad</AppText>
            <AppText variant="micro" color={colors.text.tertiary}>
              Cuenta de Google institucional
            </AppText>
          </View>
          <ChevronRight size={18} color={colors.slate[300]} />
        </Pressable>
      </Card>

      <AppText variant="micro" color={colors.text.tertiary} style={styles.tituloSeccion}>
        NOTIFICACIONES
      </AppText>
      <Card padded={false}>
        {[
          { titulo: 'Comunicados', detalle: 'Avisos de Dirección y docentes', valor: notiComunicados, setter: setNotiComunicados },
          { titulo: 'Notas', detalle: 'Cuando se publican calificaciones', valor: notiNotas, setter: setNotiNotas },
          { titulo: 'Asistencia', detalle: 'Faltas y retrasos registrados', valor: notiAsistencia, setter: setNotiAsistencia },
        ].map((opcion, i) => (
          <View key={opcion.titulo}>
            {i > 0 ? <View style={styles.divisor} /> : null}
            <View style={styles.fila}>
              <View style={{ flex: 1 }}>
                <AppText variant="bodyStrong">{opcion.titulo}</AppText>
                <AppText variant="micro" color={colors.text.tertiary}>
                  {opcion.detalle}
                </AppText>
              </View>
              <Switch
                value={opcion.valor}
                onValueChange={opcion.setter}
                trackColor={{ true: colors.primary[500], false: colors.slate[200] }}
                thumbColor="#FFFFFF"
              />
            </View>
          </View>
        ))}
      </Card>

      <Button
        label="Cerrar sesión"
        variant="ghost"
        onPress={salir}
        icon={<LogOut size={18} color={colors.semantic.danger} />}
        style={{ marginTop: spacing.xl }}
      />
      <AppText variant="micro" color={colors.text.tertiary} center style={{ marginTop: spacing.md }}>
        CSB 360 Estudiante · Versión 1.0.0
      </AppText>
    </Screen>
  );
}

const styles = StyleSheet.create({
  encabezado: { alignItems: 'center', marginTop: spacing.xl },
  tituloSeccion: { letterSpacing: 1, marginTop: spacing['2xl'], marginBottom: spacing.sm },
  fila: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    padding: spacing.lg,
  },
  divisor: { height: 1, backgroundColor: colors.surface.border, marginLeft: 44 },
});
