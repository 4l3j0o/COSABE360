import { StyleSheet, View } from 'react-native';

import { AppText } from './app-text';
import { colors, spacing } from '@/theme/tokens';

type Props = {
  title: string;
  action?: string;
};

export function SectionTitle({ title, action }: Props) {
  return (
    <View style={styles.row}>
      <AppText variant="section">{title}</AppText>
      {action ? (
        <AppText variant="caption" color={colors.primary[700]}>
          {action}
        </AppText>
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
