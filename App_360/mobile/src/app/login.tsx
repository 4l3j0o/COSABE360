import { Image } from 'expo-image';
import { router } from 'expo-router';
import { ArrowRight, Database, ShieldCheck, Sparkles, UserCheck } from 'lucide-react-native';
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

import { AppText, Button, Card, Chip } from '@/components/ui';
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
    if (await signInDemo()) router.replace('/(tabs)');
  }

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      style={styles.flex}>
      <ScrollView
        contentContainerStyle={styles.contenido}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}>
        {/* Encabezado Institucional */}
        <View style={styles.marca}>
          <View style={styles.escudoContainer}>
            <Image
              source={require('@/assets/images/escudo.png')}
              style={styles.escudo}
              contentFit="contain"
            />
          </View>
          <View style={styles.badgeColegio}>
            <Sparkles size={13} color={colors.primary[700]} />
            <AppText variant="micro" color={colors.primary[800]} style={styles.etiqueta}>
              COLEGIO SAN BERNARDO · TARIJA
            </AppText>
          </View>
          <AppText variant="display" style={styles.titulo}>
            CSB 360 Móvil
          </AppText>
          <AppText variant="body" color={colors.text.secondary} style={styles.bajada}>
            Portal estudiantil para seguimiento académico, calificaciones y tareas en tiempo real.
          </AppText>
        </View>

        {/* Tarjeta de Acceso Rápido Demo (Recomendado para evaluación) */}
        <Pressable
          style={({ pressed }) => [styles.demoCard, pressed && styles.cardPressed]}
          onPress={entrarDemo}
          accessibilityRole="button">
          <View style={styles.demoHeader}>
            <View style={styles.demoIcon}>
              <UserCheck size={20} color={colors.primary[700]} />
            </View>
            <View style={{ flex: 1 }}>
              <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                <AppText variant="bodyStrong" color={colors.primary[900]}>
                  Acceso Rápido con Cuenta Demo
                </AppText>
                <Chip label="ACTIVO" variant="success" small dot />
              </View>
              <AppText variant="caption" color={colors.text.secondary} style={{ marginTop: 2 }}>
                Valeria Quispe · 6to Secundaria (MySQL Local)
              </AppText>
            </View>
            <ArrowRight size={18} color={colors.primary[600]} />
          </View>
        </Pressable>

        {/* Formulario de Login tradicional */}
        <Card style={styles.tarjeta}>
          <View style={styles.formHeader}>
            <Database size={16} color={colors.text.tertiary} />
            <AppText variant="micro" color={colors.text.tertiary} style={{ letterSpacing: 0.8 }}>
              AUTENTICACIÓN CON BASE DE DATOS
            </AppText>
          </View>

          <View style={styles.fieldGroup}>
            <AppText variant="caption" color={colors.text.secondary} style={styles.label}>
              Correo institucional (@cosabe.edu.bo)
            </AppText>
            <TextInput
              value={email}
              onChangeText={setEmail}
              placeholder="valeria.quispe.8640@cosabe.edu.bo"
              placeholderTextColor={colors.text.tertiary}
              autoCapitalize="none"
              keyboardType="email-address"
              style={styles.input}
            />
          </View>

          <View style={styles.fieldGroup}>
            <AppText variant="caption" color={colors.text.secondary} style={styles.label}>
              Contraseña
            </AppText>
            <TextInput
              value={password}
              onChangeText={setPassword}
              placeholder="Contraseña del portal"
              placeholderTextColor={colors.text.tertiary}
              secureTextEntry
              style={styles.input}
            />
          </View>

          {error ? (
            <View style={styles.errorBox}>
              <AppText variant="caption" color={colors.semantic.danger}>
                {error}
              </AppText>
            </View>
          ) : null}

          <Button
            label="Iniciar Sesión"
            onPress={entrar}
            loading={loading}
            icon={<ArrowRight size={16} color="#FFFFFF" />}
            style={{ marginTop: spacing.xs }}
          />

          <View style={styles.credencialesHint}>
            <ShieldCheck size={14} color={colors.slate[500]} />
            <AppText variant="micro" color={colors.slate[600]}>
              Credenciales de prueba: CSB2025
            </AppText>
          </View>
        </Card>

        <AppText variant="micro" color={colors.text.tertiary} center style={styles.ayuda}>
          UPDS Tarija · Aplicaciones Móviles I · Grupo 7
        </AppText>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1, backgroundColor: colors.surface.background },
  contenido: {
    padding: spacing.xl,
    paddingTop: 48,
    paddingBottom: 40,
    flexGrow: 1,
    maxWidth: 500,
    width: '100%',
    alignSelf: 'center',
  },
  marca: { alignItems: 'center', marginBottom: spacing.xl },
  escudoContainer: {
    width: 92,
    height: 92,
    borderRadius: radius['2xl'],
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: colors.primary[100],
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.md,
    ...shadows.card,
  },
  escudo: { width: 72, height: 72 },
  badgeColegio: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: spacing.md,
    paddingVertical: 5,
    borderRadius: radius.pill,
    backgroundColor: colors.primary[50],
    borderWidth: 1,
    borderColor: colors.primary[200],
    marginBottom: spacing.sm,
  },
  etiqueta: { letterSpacing: 0.8, fontWeight: '700' },
  titulo: { textAlign: 'center', fontSize: 32, lineHeight: 38 },
  bajada: {
    textAlign: 'center',
    marginTop: spacing.xs,
    maxWidth: 320,
    lineHeight: 20,
  },
  demoCard: {
    backgroundColor: colors.primary[50],
    borderWidth: 1.5,
    borderColor: colors.primary[300],
    borderRadius: radius.xl,
    padding: spacing.md + 2,
    marginBottom: spacing.lg,
    ...shadows.sm,
  },
  cardPressed: {
    opacity: 0.9,
    transform: [{ scale: 0.99 }],
  },
  demoHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  demoIcon: {
    width: 40,
    height: 40,
    borderRadius: radius.pill,
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: colors.primary[200],
    alignItems: 'center',
    justifyContent: 'center',
  },
  tarjeta: {
    backgroundColor: colors.surface.card,
    borderRadius: radius.xl,
    padding: spacing.xl,
    borderWidth: 1,
    borderColor: colors.surface.border,
    ...shadows.card,
  },
  formHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginBottom: spacing.md,
  },
  fieldGroup: {
    marginBottom: spacing.md,
  },
  label: {
    marginBottom: 6,
    fontWeight: '500',
  },
  input: {
    borderWidth: 1,
    borderColor: colors.surface.border,
    backgroundColor: colors.slate[50],
    borderRadius: radius.md,
    paddingHorizontal: spacing.lg,
    paddingVertical: 13,
    fontFamily: fonts.regular,
    fontSize: 14,
    color: colors.text.primary,
  },
  errorBox: {
    backgroundColor: colors.accent.roseLight,
    borderWidth: 1,
    borderColor: '#FECDD3',
    borderRadius: radius.sm,
    padding: spacing.sm,
    marginBottom: spacing.sm,
  },
  credencialesHint: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    marginTop: spacing.md,
    paddingTop: spacing.sm,
    borderTopWidth: 1,
    borderTopColor: colors.neutral[100],
  },
  ayuda: { marginTop: spacing['2xl'] },
});

