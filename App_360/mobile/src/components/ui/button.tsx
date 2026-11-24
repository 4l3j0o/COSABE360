import type { ReactNode } from 'react';
import {
  ActivityIndicator,
  Pressable,
  StyleSheet,
  View,
  type StyleProp,
  type ViewStyle,
} from 'react-native';

import { AppText } from './app-text';
import { colors, radius, shadows, spacing } from '@/theme/tokens';

type Variant = 'primary' | 'secondary' | 'ghost' | 'danger' | 'outline';
type Size = 'sm' | 'md' | 'lg';

type Props = {
  label: string;
  onPress?: () => void;
  variant?: Variant;
  size?: Size;
  loading?: boolean;
  disabled?: boolean;
  icon?: ReactNode;
  fullWidth?: boolean;
  style?: StyleProp<ViewStyle>;
};

const backgrounds: Record<Variant, string> = {
  primary: colors.primary[700],
  secondary: colors.surface.card,
  outline: 'transparent',
  ghost: 'transparent',
  danger: colors.semantic.danger,
};

export function Button({
  label,
  onPress,
  variant = 'primary',
  size = 'md',
  loading,
  disabled,
  icon,
  fullWidth = true,
  style,
}: Props) {
  const isDisabled = disabled || loading;

  let textColor: string = colors.text.inverse;
  if (variant === 'secondary') textColor = colors.text.primary;
  if (variant === 'outline') textColor = colors.primary[700];
  if (variant === 'ghost') textColor = colors.text.secondary;

  return (
    <Pressable
      accessibilityRole="button"
      accessibilityState={{ disabled: isDisabled, busy: loading }}
      onPress={isDisabled ? undefined : onPress}
      style={({ pressed }) => [
        styles.base,
        styles[size],
        {
          backgroundColor: backgrounds[variant],
          borderColor:
            variant === 'secondary'
              ? colors.surface.border
              : variant === 'outline'
              ? colors.primary[600]
              : 'transparent',
          borderWidth: variant === 'secondary' || variant === 'outline' ? 1.5 : 0,
          opacity: isDisabled ? 0.45 : 1,
          width: fullWidth ? '100%' : undefined,
          transform: [{ scale: pressed && !isDisabled ? 0.98 : 1 }],
        },
        variant === 'primary' && !isDisabled ? shadows.brandGlow : undefined,
        variant === 'secondary' && !isDisabled ? shadows.sm : undefined,
        style,
      ]}>
      {loading ? (
        <ActivityIndicator color={textColor} size={size === 'sm' ? 'small' : 'small'} />
      ) : (
        <View style={styles.content}>
          {icon}
          <AppText
            variant={size === 'sm' ? 'captionStrong' : 'bodyStrong'}
            color={textColor}>
            {label}
          </AppText>
        </View>
      )}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  base: {
    borderRadius: radius.md,
    alignItems: 'center',
    justifyContent: 'center',
  },
  sm: {
    minHeight: 38,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.xs,
  },
  md: {
    minHeight: 48,
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
  },
  lg: {
    minHeight: 54,
    paddingHorizontal: spacing['2xl'],
    paddingVertical: spacing.lg,
    borderRadius: radius.lg,
  },
  content: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.sm,
  },
});
