import { StyleSheet, View } from 'react-native';

import { AppText } from './app-text';
import { colors, radius } from '@/theme/tokens';

type Props = {
  name: string;
  size?: number;
  background?: string;
  color?: string;
};

export function Avatar({ name, size = 44, background = colors.primary[600], color = '#FFFFFF' }: Props) {
  const initials = name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase())
    .join('');

  return (
    <View
      style={[
        styles.avatar,
        { width: size, height: size, borderRadius: radius.pill, backgroundColor: background },
      ]}>
      <AppText variant={size > 60 ? 'title' : 'bodyStrong'} color={color}>
        {initials}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  avatar: { alignItems: 'center', justifyContent: 'center' },
});
