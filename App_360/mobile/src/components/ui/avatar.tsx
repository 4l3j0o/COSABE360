import { StyleSheet, View } from 'react-native';

import { AppText } from './app-text';
import { colors, radius, shadows } from '@/theme/tokens';

type Props = {
  name: string;
  size?: number;
  background?: string;
  color?: string;
  showRing?: boolean;
};

export function Avatar({
  name,
  size = 44,
  background = colors.primary[600],
  color = '#FFFFFF',
  showRing = true,
}: Props) {
  const initials = name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase())
    .join('');

  return (
    <View
      style={[
        styles.avatarContainer,
        showRing && styles.ring,
        {
          width: size,
          height: size,
          borderRadius: radius.pill,
        },
      ]}>
      <View
        style={[
          styles.avatar,
          {
            width: size - (showRing ? 4 : 0),
            height: size - (showRing ? 4 : 0),
            borderRadius: radius.pill,
            backgroundColor: background,
          },
        ]}>
        <AppText
          variant={size > 60 ? 'title' : size < 36 ? 'micro' : 'bodyStrong'}
          color={color}
          style={styles.initials}>
          {initials}
        </AppText>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  avatarContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    ...shadows.sm,
  },
  ring: {
    borderWidth: 2,
    borderColor: '#FFFFFF',
    shadowColor: colors.primary[900],
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.12,
    shadowRadius: 4,
    elevation: 2,
  },
  avatar: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  initials: {
    letterSpacing: 0.5,
  },
});

