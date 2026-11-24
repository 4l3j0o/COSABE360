import { router } from 'expo-router';
import {
  CheckCircle2,
  ChevronRight,
  KeyRound,
  LogOut,
  MapPin,
  Phone,
  ShieldCheck,
  User,
  X,
} from 'lucide-react-native';
import { useState } from 'react';
import { Modal, Pressable, ScrollView, StyleSheet, Switch, View } from 'react-native';

import { AppText, Avatar, Button, Card, Chip, Screen } from '@/components/ui';
import { estudiante } from '@/data/mock';
import { useSesion } from '@/store/session';
import { colors, radius, spacing } from '@/theme/tokens';

export default function Perfil() {
  const nombre = useSesion((s) => s.nombre) || `${estudiante.nombres} ${estudiante.apellidos}`;
  const curso = useSesion((s) => s.curso) || `${estudiante.curso} · Paralelo ${estudiante.paralelo}`;
  const email = useSesion((s) => s.email) || 'valeria.quispe.8640@cosabe.edu.bo';
  const codigoEstudiante = useSesion((s) => s.codigo) || estudiante.codigo;
  const signOut = useSesion((s) => s.signOut);

  const [modalDatos, setModalDatos] = useState(false);
  const [modalSeguridad, setModalSeguridad] = useState(false);
  const [notiComunicados, setNotiComunicados] = useState(true);
  const [notiNotas, setNotiNotas] = useState(true);
  const [notiAsistencia, setNotiAsistencia] = useState(false);
  const [feedbackPref, setFeedbackPref] = useState<string | null>(null);

  function cambiarPreferencia(setter: (v: boolean) => void, val: boolean, nombrePref: string) {
    setter(val);
    setFeedbackPref(`Preferencia de ${nombrePref} actualizada`);
    setTimeout(() => setFeedbackPref(null), 3000);
  }

  async function salir() {
    await signOut();
    router.replace('/login');
  }

  return (
    <Screen>
      <AppText variant="title">Mi Perfil Estudiantil</AppText>

      {feedbackPref ? (
        <Card style={styles.feedbackCard}>
          <CheckCircle2 size={16} color="#166534" />
          <AppText variant="micro" color="#166534" style={{ marginLeft: 6 }}>
            {feedbackPref}
          </AppText>
        </Card>
      ) : null}

      <View style={styles.encabezado}>
        <Avatar name={nombre} size={76} />
        <AppText variant="section" style={{ marginTop: spacing.md }}>
          {nombre}
        </AppText>
        <AppText variant="caption" color={colors.text.secondary}>
          {curso}
        </AppText>
        <View style={{ marginTop: spacing.sm }}>
          <Chip
            label={`Código: ${codigoEstudiante} · Turno ${estudiante.turno}`}
            background={colors.slate[100]}
            color={colors.slate[600]}
          />
        </View>
      </View>

      <AppText variant="micro" color={colors.text.tertiary} style={styles.tituloSeccion}>
        MI INFORMACIÓN ESCOLAR
      </AppText>
      <Card padded={false}>
        <Pressable style={styles.fila} onPress={() => setModalDatos(true)}>
          <User size={19} color={colors.primary[700]} />
          <View style={{ flex: 1 }}>
            <AppText variant="bodyStrong">Datos personales y académicos</AppText>
            <AppText variant="micro" color={colors.text.tertiary}>
              RUDE, carnet, tutor legal y dirección
            </AppText>
          </View>
          <ChevronRight size={18} color={colors.slate[300]} />
        </Pressable>

        <View style={styles.divisor} />

        <Pressable style={styles.fila} onPress={() => setModalSeguridad(true)}>
          <ShieldCheck size={19} color={colors.primary[700]} />
          <View style={{ flex: 1 }}>
            <AppText variant="bodyStrong">Seguridad y acceso</AppText>
            <AppText variant="micro" color={colors.text.tertiary}>
              Cuenta institucional y contraseña encriptada
            </AppText>
          </View>
          <ChevronRight size={18} color={colors.slate[300]} />
        </Pressable>
      </Card>

      <AppText variant="micro" color={colors.text.tertiary} style={styles.tituloSeccion}>
        NOTIFICACIONES Y ALERTAS
      </AppText>
      <Card padded={false}>
        {[
          {
            titulo: 'Comunicados',
            detalle: 'Avisos oficiales de Dirección y docentes',
            valor: notiComunicados,
            setter: (v: boolean) => cambiarPreferencia(setNotiComunicados, v, 'comunicados'),
          },
          {
            titulo: 'Calificaciones',
            detalle: 'Cuando los profesores publican notas',
            valor: notiNotas,
            setter: (v: boolean) => cambiarPreferencia(setNotiNotas, v, 'calificaciones'),
          },
          {
            titulo: 'Asistencia y retrasos',
            detalle: 'Alertas en caso de faltas o demoras',
            valor: notiAsistencia,
            setter: (v: boolean) => cambiarPreferencia(setNotiAsistencia, v, 'asistencia'),
          },
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
        CSB 360 Estudiante · Versión 1.0.0 Oficial
      </AppText>

      {/* Modal Datos Personales */}
      <Modal
        visible={modalDatos}
        transparent
        animationType="slide"
        onRequestClose={() => setModalDatos(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <AppText variant="bodyStrong">Ficha del Estudiante</AppText>
              <Pressable onPress={() => setModalDatos(false)} style={{ padding: 4 }}>
                <X size={20} color={colors.slate[600]} />
              </Pressable>
            </View>
            <ScrollView showsVerticalScrollIndicator={false} style={{ marginTop: spacing.md }}>
              <Card style={{ marginBottom: spacing.sm }}>
                <AppText variant="micro" color={colors.text.tertiary}>RUDE (REGISTRO ÚNICO)</AppText>
                <AppText variant="bodyStrong">8172004220268640</AppText>
              </Card>
              <Card style={{ marginBottom: spacing.sm }}>
                <AppText variant="micro" color={colors.text.tertiary}>DOCUMENTO DE IDENTIDAD</AppText>
                <AppText variant="bodyStrong">8640192-TJA</AppText>
              </Card>
              <Card style={{ marginBottom: spacing.sm }}>
                <AppText variant="micro" color={colors.text.tertiary}>DIRECCIÓN DE DOMICILIO</AppText>
                <AppText variant="bodyStrong">Barrio El Molino, Calle Sucre Nº 842</AppText>
              </Card>
              <Card style={{ marginBottom: spacing.sm }}>
                <AppText variant="micro" color={colors.text.tertiary}>TUTOR RESPONSABLE</AppText>
                <AppText variant="bodyStrong">Sra. Carmen Mamani de Quispe (Madre)</AppText>
                <AppText variant="caption" color={colors.text.secondary}>Telf. Emergencias: +591 71829304</AppText>
              </Card>
              <Card style={{ marginBottom: spacing.sm }}>
                <AppText variant="micro" color={colors.text.tertiary}>UNIDAD EDUCATIVA</AppText>
                <AppText variant="bodyStrong">Colegio San Bernardo de Tarija</AppText>
                <AppText variant="caption" color={colors.text.secondary}>Nivel Secundario Comunitario Productivo</AppText>
              </Card>
            </ScrollView>
            <Button
              label="Cerrar Ficha"
              variant="secondary"
              onPress={() => setModalDatos(false)}
              style={{ marginTop: spacing.md }}
            />
          </View>
        </View>
      </Modal>

      {/* Modal Seguridad */}
      <Modal
        visible={modalSeguridad}
        transparent
        animationType="slide"
        onRequestClose={() => setModalSeguridad(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <AppText variant="bodyStrong">Seguridad y Cuentas</AppText>
              <Pressable onPress={() => setModalSeguridad(false)} style={{ padding: 4 }}>
                <X size={20} color={colors.slate[600]} />
              </Pressable>
            </View>
            <Card style={{ marginTop: spacing.md }}>
              <AppText variant="micro" color={colors.text.tertiary}>CORREO INSTITUCIONAL</AppText>
              <AppText variant="bodyStrong">{email}</AppText>
              <AppText variant="caption" color={colors.semantic.success} style={{ marginTop: 2 }}>
                ✓ Cuenta verificada por Google Workspace CSB
              </AppText>
            </Card>
            <Card style={{ marginTop: spacing.sm }}>
              <AppText variant="micro" color={colors.text.tertiary}>CONTRASEÑA</AppText>
              <AppText variant="bodyStrong">••••••••••••</AppText>
              <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: 2 }}>
                Encriptada en la base de datos MySQL con algoritmo bcrypt (10 rounds).
              </AppText>
            </Card>
            <Button
              label="Cerrar"
              variant="secondary"
              onPress={() => setModalSeguridad(false)}
              style={{ marginTop: spacing.lg }}
            />
          </View>
        </View>
      </Modal>
    </Screen>
  );
}

const styles = StyleSheet.create({
  encabezado: { alignItems: 'center', marginTop: spacing.lg },
  tituloSeccion: { letterSpacing: 1, marginTop: spacing.xl, marginBottom: spacing.sm },
  fila: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    padding: spacing.lg,
  },
  divisor: { height: 1, backgroundColor: colors.surface.border, marginLeft: 44 },
  feedbackCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F0FDF4',
    borderColor: '#86EFAC',
    borderWidth: 1,
    marginTop: spacing.sm,
    paddingVertical: spacing.xs,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(15, 23, 42, 0.55)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: radius.xl,
    borderTopRightRadius: radius.xl,
    padding: spacing.xl,
    maxHeight: '80%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.xs,
  },
});
