import { Pressable, StyleSheet, View } from 'react-native';

import { AppText } from './app-text';
import { colors, spacing } from '@/theme/tokens';

type Props = {
  title: string;
  action?: string;
  onAction?: () => void;
};

export function SectionTitle({ title, action, onAction }: Props) {
  return (
    <View style={styles.row}>
      <AppText variant="section">{title}</AppText>
      {action ? (
        onAction ? (
          <Pressable onPress={onAction} hitSlop={8}>
            <AppText variant="caption" color={colors.primary[700]}>
              {action}
            </AppText>
          </Pressable>
        ) : (
          <AppText variant="caption" color={colors.primary[700]}>
            {action}
          </AppText>
        )
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: spacing.md,
  },
});
