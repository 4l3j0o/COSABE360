import type { ReactNode } from 'react';
import { StyleSheet, View } from 'react-native';

import { AppText } from './app-text';
import { colors, radius, spacing } from '@/theme/tokens';

type Props = {
  label: string;
  value: string | number;
  icon?: ReactNode;
  tone?: string;
  background?: string;
};

export function StatCard({ label, value, icon, tone = colors.primary[700], background = colors.surface.card }: Props) {
  return (
    <View style={[styles.card, { backgroundColor: background }]}>
      {icon ? <View style={styles.icon}>{icon}</View> : null}
      <AppText variant="section" color={tone}>
        {value}
      </AppText>
      <AppText variant="micro" color={colors.text.secondary} numberOfLines={1}>
        {label}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    flex: 1,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.surface.border,
    padding: spacing.md,
    gap: 2,
  },
  icon: { marginBottom: 2 },
});
