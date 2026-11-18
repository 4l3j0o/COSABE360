/**
 * Tokens de diseño CSB 360 — alineados al sistema web (verde #1A8C1A + Outfit).
 */

export const colors = {
  primary: {
    50: '#E6F4E6',
    100: '#C8E8C9',
    200: '#A5D7A8',
    300: '#7CC483',
    400: '#4FAD58',
    500: '#2E9E3A',
    600: '#1A8C1A',
    700: '#0E710E',
    800: '#0A5A0A',
    900: '#084508',
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
  },
  semantic: {
    success: '#16A34A',
    warning: '#D97706',
    danger: '#DC2626',
    info: '#2563EB',
  },
  surface: {
    background: '#F8FAFC',
    card: '#FFFFFF',
    border: '#E2E8F0',
    overlay: 'rgba(15,23,42,0.45)',
  },
  text: {
    primary: '#0F172A',
    secondary: '#475569',
    tertiary: '#94A3B8',
    inverse: '#FFFFFF',
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
} as const;

export const radius = {
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  pill: 999,
} as const;

export const fonts = {
  regular: 'Outfit_400Regular',
  medium: 'Outfit_500Medium',
  semibold: 'Outfit_600SemiBold',
  bold: 'Outfit_700Bold',
} as const;

export const typography = {
  display: { fontFamily: fonts.bold, fontSize: 28, lineHeight: 34 },
  title: { fontFamily: fonts.semibold, fontSize: 22, lineHeight: 28 },
  section: { fontFamily: fonts.semibold, fontSize: 18, lineHeight: 24 },
  body: { fontFamily: fonts.regular, fontSize: 15, lineHeight: 22 },
  bodyStrong: { fontFamily: fonts.semibold, fontSize: 15, lineHeight: 22 },
  caption: { fontFamily: fonts.regular, fontSize: 13, lineHeight: 18 },
  micro: { fontFamily: fonts.medium, fontSize: 11, lineHeight: 14 },
} as const;

export const shadows = {
  card: {
    shadowColor: '#0F172A',
    shadowOpacity: 0.08,
    shadowRadius: 16,
    shadowOffset: { width: 0, height: 4 },
    elevation: 3,
  },
  floating: {
    shadowColor: '#0F172A',
    shadowOpacity: 0.14,
    shadowRadius: 32,
    shadowOffset: { width: 0, height: 12 },
    elevation: 8,
  },
} as const;

/** Escala oficial boliviana: ED (0-50), DA (51-68), DO (69-84), DP (85-100). */
export function escalaNota(nota: number) {
  if (nota >= 85) return { sigla: 'DP', label: 'Dominio pleno', color: colors.semantic.success };
  if (nota >= 69) return { sigla: 'DO', label: 'Dominio', color: colors.semantic.info };
  if (nota >= 51) return { sigla: 'DA', label: 'En desarrollo', color: colors.semantic.warning };
  return { sigla: 'ED', label: 'En riesgo', color: colors.semantic.danger };
}

export const estadoAsistencia: Record<string, { label: string; color: string; bg: string }> = {
  PRESENTE: { label: 'Presente', color: colors.semantic.success, bg: colors.primary[50] },
  AUSENTE: { label: 'Ausente', color: colors.semantic.danger, bg: '#FEF2F2' },
  RETRASO: { label: 'Retraso', color: colors.semantic.warning, bg: '#FFFBEB' },
  LICENCIA: { label: 'Licencia', color: colors.semantic.info, bg: '#EFF6FF' },
  RETIRO_JUSTIFICADO: { label: 'Retiro', color: colors.slate[600], bg: colors.slate[100] },
};
