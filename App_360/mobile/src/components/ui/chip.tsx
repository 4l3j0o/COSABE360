import { StyleSheet, View } from 'react-native';

import { AppText } from './app-text';
import { colors, radius, spacing } from '@/theme/tokens';

export type ChipVariant = 'default' | 'success' | 'warning' | 'danger' | 'info' | 'neutral' | 'outline';

type Props = {
  label: string;
  variant?: ChipVariant;
  color?: string;
  background?: string;
  borderColor?: string;
  small?: boolean;
  dot?: boolean;
};

export function Chip({
  label,
  variant = 'default',
  color,
  background,
  borderColor,
  small,
  dot,
}: Props) {
  let resolvedBg = background || colors.primary[50];
  let resolvedColor = color || colors.primary[700];
  let resolvedBorder = borderColor || colors.primary[100];
  let dotColor = resolvedColor;

  if (variant === 'success') {
    resolvedBg = colors.accent.emeraldLight;
    resolvedColor = colors.accent.emerald;
    resolvedBorder = '#A7F3D0';
    dotColor = colors.accent.emerald;
  } else if (variant === 'warning') {
    resolvedBg = colors.accent.amberLight;
    resolvedColor = colors.accent.amber;
    resolvedBorder = '#FDE68A';
    dotColor = colors.accent.amber;
  } else if (variant === 'danger') {
    resolvedBg = colors.accent.roseLight;
    resolvedColor = colors.accent.rose;
    resolvedBorder = '#FECDD3';
    dotColor = colors.accent.rose;
  } else if (variant === 'info') {
    resolvedBg = colors.accent.skyLight;
    resolvedColor = colors.accent.sky;
    resolvedBorder = '#BAE6FD';
    dotColor = colors.accent.sky;
  } else if (variant === 'neutral') {
    resolvedBg = colors.neutral[100];
    resolvedColor = colors.neutral[700];
    resolvedBorder = colors.neutral[200];
    dotColor = colors.neutral[500];
  } else if (variant === 'outline') {
    resolvedBg = 'transparent';
    resolvedColor = color || colors.neutral[700];
    resolvedBorder = borderColor || colors.neutral[300];
    dotColor = resolvedColor;
  }

  // override if explicit color / background passed
  if (color) resolvedColor = color;
  if (background) resolvedBg = background;
  if (borderColor) resolvedBorder = borderColor;

  return (
    <View
      style={[
        styles.chip,
        {
          backgroundColor: resolvedBg,
          borderColor: resolvedBorder,
        },
        small && styles.small,
      ]}>
      {dot && <View style={[styles.dot, { backgroundColor: dotColor }, small && styles.dotSmall]} />}
      <AppText variant="micro" color={resolvedColor} numberOfLines={1}>
        {label}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  chip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    borderRadius: radius.pill,
    paddingHorizontal: spacing.md,
    paddingVertical: 5,
    borderWidth: 1,
    alignSelf: 'flex-start',
  },
  small: {
    paddingHorizontal: spacing.sm + 2,
    paddingVertical: 3,
    gap: 4,
  },
  dot: {
    width: 6,
    height: 6,
    borderRadius: 3,
  },
  dotSmall: {
    width: 5,
    height: 5,
    borderRadius: 2.5,
  },
});

