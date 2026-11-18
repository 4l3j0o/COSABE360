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
import { colors, radius, spacing } from '@/theme/tokens';

type Variant = 'primary' | 'secondary' | 'ghost' | 'danger';

type Props = {
  label: string;
  onPress?: () => void;
  variant?: Variant;
  loading?: boolean;
  disabled?: boolean;
  icon?: ReactNode;
  fullWidth?: boolean;
  style?: StyleProp<ViewStyle>;
};

const backgrounds: Record<Variant, string> = {
  primary: colors.primary[600],
  secondary: colors.surface.card,
  ghost: 'transparent',
  danger: colors.semantic.danger,
};

export function Button({
  label,
  onPress,
  variant = 'primary',
  loading,
  disabled,
  icon,
  fullWidth = true,
  style,
}: Props) {
  const isDisabled = disabled || loading;
  const textColor =
    variant === 'primary' || variant === 'danger' ? colors.text.inverse : colors.primary[700];

  return (
    <Pressable
      accessibilityRole="button"
      accessibilityState={{ disabled: isDisabled, busy: loading }}
      onPress={isDisabled ? undefined : onPress}
      style={({ pressed }) => [
        styles.base,
        {
          backgroundColor: backgrounds[variant],
          borderColor: variant === 'secondary' ? colors.surface.border : 'transparent',
          opacity: isDisabled ? 0.5 : pressed ? 0.85 : 1,
          width: fullWidth ? '100%' : undefined,
        },
        style,
      ]}>
      {loading ? (
        <ActivityIndicator color={textColor} />
      ) : (
        <View style={styles.content}>
          {icon}
          <AppText variant="bodyStrong" color={textColor}>
            {label}
          </AppText>
        </View>
      )}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  base: {
    minHeight: 48,
    borderRadius: radius.md,
    borderWidth: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
  },
  content: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
});
