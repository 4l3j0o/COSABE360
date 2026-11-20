import { StyleSheet, View } from 'react-native';

import { AppText } from './app-text';
import { colors, radius, spacing } from '@/theme/tokens';

type Props = {
  label: string;
  color?: string;
  background?: string;
  small?: boolean;
};

export function Chip({ label, color = colors.primary[700], background = colors.primary[50], small }: Props) {
  return (
    <View style={[styles.chip, { backgroundColor: background }, small && styles.small]}>
      <AppText variant="micro" color={color} numberOfLines={1}>
        {label}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  chip: {
    borderRadius: radius.pill,
    paddingHorizontal: spacing.md,
    paddingVertical: 6,
    alignSelf: 'flex-start',
  },
  small: { paddingHorizontal: spacing.sm, paddingVertical: 3 },
});
