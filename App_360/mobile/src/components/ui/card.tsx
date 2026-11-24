import type { ReactNode } from 'react';
import { Pressable, StyleSheet, View, type StyleProp, type ViewStyle } from 'react-native';

import { colors, radius, shadows, spacing } from '@/theme/tokens';

type Variant = 'default' | 'flat' | 'elevated' | 'highlight';

type Props = {
  children: ReactNode;
  style?: StyleProp<ViewStyle>;
  padded?: boolean;
  variant?: Variant;
  onPress?: () => void;
};

export function Card({
  children,
  style,
  padded = true,
  variant = 'default',
  onPress,
}: Props) {
  const containerStyle = [
    styles.base,
    styles[variant],
    padded && styles.padded,
    style,
  ];

  if (onPress) {
    return (
      <Pressable
        onPress={onPress}
        style={({ pressed }) => [
          containerStyle,
          pressed && { opacity: 0.92, transform: [{ scale: 0.99 }] },
        ]}>
        {children}
      </Pressable>
    );
  }

  return <View style={containerStyle}>{children}</View>;
}

const styles = StyleSheet.create({
  base: {
    backgroundColor: colors.surface.card,
    borderRadius: radius.lg,
    overflow: 'hidden',
  },
  default: {
    borderWidth: 1,
    borderColor: colors.surface.border,
    ...shadows.card,
  },
  flat: {
    borderWidth: 1,
    borderColor: colors.surface.borderSubtle,
    backgroundColor: colors.surface.subtle,
  },
  elevated: {
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.8)',
    ...shadows.floating,
  },
  highlight: {
    borderWidth: 1,
    borderColor: colors.primary[200],
    backgroundColor: colors.primary[50],
    ...shadows.sm,
  },
  padded: {
    padding: spacing.lg,
  },
});
