import { Image } from 'expo-image';
import { router } from 'expo-router';
import { useState } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  TextInput,
  View,
} from 'react-native';

import { AppText, Button } from '@/components/ui';
import { useSesion } from '@/store/session';
import { colors, fonts, radius, shadows, spacing } from '@/theme/tokens';

export default function Login() {
  const signIn = useSesion((s) => s.signIn);
  const signInDemo = useSesion((s) => s.signInDemo);
  const loading = useSesion((s) => s.loading);
  const error = useSesion((s) => s.error);

  const [email, setEmail] = useState('valeria.quispe.8640@cosabe.edu.bo');
  const [password, setPassword] = useState('CSB2025');

  async function entrar() {
    if (await signIn(email, password)) router.replace('/(tabs)');
  }

  async function entrarDemo() {
    await signInDemo();
    router.replace('/(tabs)');
  }

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      style={styles.flex}>
      <ScrollView
        contentContainerStyle={styles.contenido}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}>
        <View style={styles.marca}>
          <Image source={require('@/assets/images/escudo.png')} style={styles.escudo} contentFit="contain" />
          <AppText variant="micro" color={colors.primary[700]} style={styles.etiqueta}>
            PORTAL DEL ESTUDIANTE
          </AppText>
          <AppText variant="display" style={styles.titulo}>
            Bienvenida a tu{'\n'}espacio escolar
          </AppText>
          <AppText variant="body" color={colors.text.secondary} style={styles.bajada}>
            Consulta tus avances, horarios y novedades del colegio en un solo lugar.
          </AppText>
        </View>

        <View style={styles.tarjeta}>
          <Pressable style={styles.google} onPress={entrarDemo} accessibilityRole="button">
            <View style={styles.g}>
              <AppText variant="bodyStrong" color="#4285F4">
                G
              </AppText>
            </View>
            <AppText variant="bodyStrong">Continuar con Google</AppText>
          </Pressable>
          <AppText variant="caption" color={colors.text.tertiary} center style={{ marginTop: spacing.sm }}>
            Solo cuentas @cosabe.edu.bo
          </AppText>

          <View style={styles.separador} />

          <AppText variant="micro" color={colors.text.secondary} style={{ marginBottom: spacing.sm }}>
            ACCESO DE PRUEBA
          </AppText>
          <TextInput
            value={email}
            onChangeText={setEmail}
            placeholder="Correo institucional"
            placeholderTextColor={colors.text.tertiary}
            autoCapitalize="none"
            keyboardType="email-address"
            style={styles.input}
          />
          <TextInput
            value={password}
            onChangeText={setPassword}
            placeholder="Contraseña"
            placeholderTextColor={colors.text.tertiary}
            secureTextEntry
            style={styles.input}
          />
          {error ? (
            <AppText variant="caption" color={colors.semantic.danger} style={{ marginBottom: spacing.sm }}>
              {error}
            </AppText>
          ) : null}
          <Button label="Ingresar a la demo" onPress={entrar} loading={loading} />
        </View>

        <AppText variant="caption" color={colors.primary[700]} center style={styles.ayuda}>
          ¿Problemas para ingresar?
        </AppText>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1, backgroundColor: colors.surface.background },
  contenido: { padding: spacing.xl, paddingTop: 56, paddingBottom: 40, flexGrow: 1 },
  marca: { alignItems: 'center', marginBottom: spacing['2xl'] },
  escudo: { width: 88, height: 88, marginBottom: spacing.lg },
  etiqueta: { letterSpacing: 1.2, marginBottom: spacing.sm },
  titulo: { textAlign: 'center', fontSize: 30, lineHeight: 36 },
  bajada: { textAlign: 'center', marginTop: spacing.sm, maxWidth: 320 },
  tarjeta: {
    backgroundColor: colors.surface.card,
    borderRadius: radius.xl,
    padding: spacing.xl,
    borderWidth: 1,
    borderColor: colors.surface.border,
    ...shadows.card,
  },
  google: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.md,
    borderWidth: 1,
    borderColor: colors.surface.border,
    borderRadius: radius.md,
    paddingVertical: spacing.md,
    backgroundColor: '#FFFFFF',
  },
  g: {
    width: 24,
    height: 24,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  separador: {
    height: 1,
    backgroundColor: colors.surface.border,
    marginVertical: spacing.lg,
  },
  input: {
    borderWidth: 1,
    borderColor: colors.surface.border,
    backgroundColor: colors.slate[50],
    borderRadius: radius.md,
    paddingHorizontal: spacing.lg,
    paddingVertical: 12,
    fontFamily: fonts.regular,
    fontSize: 15,
    color: colors.text.primary,
    marginBottom: spacing.sm,
  },
  ayuda: { marginTop: spacing.xl },
});
