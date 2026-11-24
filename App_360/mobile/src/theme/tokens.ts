/**
 * Tokens de diseño CSB 360 — Sistema de diseño moderno (Vercel-inspired UI/UX).
 *
 * Paleta Verde Esmeralda Institucional + Neutros Zinc/Slate de alto contraste,
 * elevaciones sutiles, micro-interacciones táctiles y jerarquía tipográfica rigurosa.
 */

export const colors = {
  primary: {
    50: '#ECFDF5',
    100: '#D1FAE5',
    200: '#A7F3D0',
    300: '#6EE7B7',
    400: '#34D399',
    500: '#10B981',
    600: '#059669',
    700: '#047857',
    800: '#065F46',
    900: '#064E3B',
    950: '#022C22',
  },
  slate: {
    50: '#F8FAFC',
    100: '#F1F5F9',
    200: '#E2E8F0',
    300: '#CBD5E1',
    400: '#94A3B8',
    500: '#64748B',
    600: '#475569',
    700: '#334155',
    800: '#1E293B',
    900: '#0F172A',
    950: '#090D16',
  },
  zinc: {
    50: '#FAFAFA',
    100: '#F4F4F5',
    200: '#E4E4E7',
    300: '#D4D4D8',
    400: '#A1A1AA',
    500: '#71717A',
    600: '#52525B',
    700: '#3F3F46',
    800: '#27272A',
    900: '#18181B',
    950: '#09090B',
  },
  neutral: {
    50: '#F8FAFC',
    100: '#F1F5F9',
    200: '#E2E8F0',
    300: '#CBD5E1',
    400: '#94A3B8',
    500: '#64748B',
    600: '#475569',
    700: '#334155',
    800: '#1E293B',
    900: '#0F172A',
    950: '#090D16',
  },
  accent: {
    emerald: '#059669',
    emeraldLight: '#ECFDF5',
    amber: '#D97706',
    amberLight: '#FFFBEB',
    rose: '#DC2626',
    roseLight: '#FEF2F2',
    sky: '#0284C7',
    skyLight: '#F0F9FF',
    purple: '#7C3AED',
    purpleLight: '#F5F3FF',
  },
  semantic: {
    success: '#059669',
    successLight: '#ECFDF5',
    warning: '#D97706',
    warningLight: '#FFFBEB',
    danger: '#DC2626',
    dangerLight: '#FEF2F2',
    info: '#2563EB',
    infoLight: '#EFF6FF',
    purple: '#7C3AED',
    purpleLight: '#F5F3FF',
  },
  surface: {
    background: '#F8FAFC',
    card: '#FFFFFF',
    subtle: '#F1F5F9',
    border: '#E2E8F0',
    borderSubtle: '#F1F5F9',
    overlay: 'rgba(15, 23, 42, 0.60)',
    elevated: '#FFFFFF',
  },
  text: {
    primary: '#0F172A',
    secondary: '#475569',
    tertiary: '#94A3B8',
    muted: '#CBD5E1',
    inverse: '#FFFFFF',
    brand: '#047857',
  },
} as const;

export const spacing = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 20,
  '2xl': 24,
  '3xl': 32,
  '4xl': 40,
} as const;

export const radius = {
  xs: 6,
  sm: 10,
  md: 14,
  lg: 18,
  xl: 24,
  '2xl': 32,
  pill: 9999,
} as const;

export const fonts = {
  regular: 'Outfit_400Regular',
  medium: 'Outfit_500Medium',
  semibold: 'Outfit_600SemiBold',
  bold: 'Outfit_700Bold',
} as const;

export const typography = {
  display: { fontFamily: fonts.bold, fontSize: 30, lineHeight: 36, letterSpacing: -0.5 },
  headline: { fontFamily: fonts.bold, fontSize: 24, lineHeight: 30, letterSpacing: -0.3 },
  title: { fontFamily: fonts.semibold, fontSize: 20, lineHeight: 26, letterSpacing: -0.2 },
  section: { fontFamily: fonts.semibold, fontSize: 17, lineHeight: 22, letterSpacing: -0.1 },
  body: { fontFamily: fonts.regular, fontSize: 15, lineHeight: 22 },
  bodyStrong: { fontFamily: fonts.semibold, fontSize: 15, lineHeight: 22 },
  caption: { fontFamily: fonts.regular, fontSize: 13, lineHeight: 18 },
  captionStrong: { fontFamily: fonts.medium, fontSize: 13, lineHeight: 18 },
  micro: { fontFamily: fonts.medium, fontSize: 11, lineHeight: 14, letterSpacing: 0.2 },
  microBold: { fontFamily: fonts.bold, fontSize: 11, lineHeight: 14, letterSpacing: 0.4 },
} as const;

export const shadows = {
  sm: {
    shadowColor: '#0F172A',
    shadowOpacity: 0.04,
    shadowRadius: 6,
    shadowOffset: { width: 0, height: 2 },
    elevation: 1,
  },
  card: {
    shadowColor: '#0F172A',
    shadowOpacity: 0.06,
    shadowRadius: 14,
    shadowOffset: { width: 0, height: 4 },
    elevation: 2,
  },
  floating: {
    shadowColor: '#0F172A',
    shadowOpacity: 0.12,
    shadowRadius: 28,
    shadowOffset: { width: 0, height: 10 },
    elevation: 6,
  },
  brandGlow: {
    shadowColor: '#059669',
    shadowOpacity: 0.25,
    shadowRadius: 16,
    shadowOffset: { width: 0, height: 6 },
    elevation: 4,
  },
} as const;

/** Escala oficial boliviana del Ministerio de Educación: ED (0-50), DA (51-68), DO (69-84), DP (85-100). */
export function escalaNota(nota: number) {
  if (nota >= 85) {
    return {
      sigla: 'DP',
      label: 'Dominio Pleno',
      color: colors.semantic.success,
      bg: colors.semantic.successLight,
    };
  }
  if (nota >= 69) {
    return {
      sigla: 'DO',
      label: 'Dominio Óptimo',
      color: colors.semantic.info,
      bg: colors.semantic.infoLight,
    };
  }
  if (nota >= 51) {
    return {
      sigla: 'DA',
      label: 'Desarrollo Aceptable',
      color: colors.semantic.warning,
      bg: colors.semantic.warningLight,
    };
  }
  return {
    sigla: 'ED',
    label: 'En Desarrollo',
    color: colors.semantic.danger,
    bg: colors.semantic.dangerLight,
  };
}

export const estadoAsistencia: Record<string, { label: string; color: string; bg: string }> = {
  PRESENTE: { label: 'Presente', color: colors.semantic.success, bg: colors.semantic.successLight },
  AUSENTE: { label: 'Ausente', color: colors.semantic.danger, bg: colors.semantic.dangerLight },
  RETRASO: { label: 'Retraso', color: colors.semantic.warning, bg: colors.semantic.warningLight },
  LICENCIA: { label: 'Licencia', color: colors.semantic.info, bg: colors.semantic.infoLight },
  RETIRO_JUSTIFICADO: { label: 'Retiro', color: colors.slate[600], bg: colors.slate[100] },
};
