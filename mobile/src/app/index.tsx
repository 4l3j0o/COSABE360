import { Image } from 'expo-image';
import { router } from 'expo-router';
import { useEffect } from 'react';
import { ActivityIndicator, StyleSheet, Text, View } from 'react-native';

import { useSesion } from '@/store/session';
import { colors, fonts, spacing } from '@/theme/tokens';

export default function Splash() {
  const token = useSesion((s) => s.token);
  const hydrated = useSesion((s) => s.hydrated);

  useEffect(() => {
    if (!hydrated) return;
    const t = setTimeout(() => router.replace(token ? '/(tabs)' : '/login'), 900);
    return () => clearTimeout(t);
  }, [hydrated, token]);

  return (
    <View style={styles.contenedor}>
      <View style={styles.logo}>
        <Image source={require('@/assets/images/escudo.png')} style={styles.escudo} contentFit="contain" />
      </View>
      <Text style={styles.nombre}>CSB 360</Text>
      <Text style={styles.subtitulo}>Colegio San Bernardo</Text>
      <ActivityIndicator color="#FFFFFF" style={{ marginTop: spacing['2xl'] }} />
      <Text style={styles.cargando}>Verificando sesión…</Text>
      <Text style={styles.version}>Versión 1.0.0</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  contenedor: {
    flex: 1,
    backgroundColor: colors.primary[700],
    alignItems: 'center',
    justifyContent: 'center',
    padding: spacing['2xl'],
  },
  logo: {
    width: 132,
    height: 132,
    borderRadius: 66,
    backgroundColor: '#FFFFFF',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.xl,
  },
  escudo: { width: 96, height: 96 },
  nombre: { fontFamily: fonts.bold, fontSize: 32, color: '#FFFFFF' },
  subtitulo: { fontFamily: fonts.regular, fontSize: 15, color: '#D8F0D8', marginTop: 2 },
  cargando: {
    fontFamily: fonts.regular,
    fontSize: 13,
    color: '#D8F0D8',
    marginTop: spacing.md,
  },
  version: {
    position: 'absolute',
    bottom: 40,
    fontFamily: fonts.regular,
    fontSize: 11,
    color: 'rgba(255,255,255,0.6)',
  },
});
