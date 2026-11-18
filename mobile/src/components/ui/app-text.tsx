import { Text, type TextProps, type TextStyle } from 'react-native';

import { colors, typography } from '@/theme/tokens';

type Variant = keyof typeof typography;

type Props = TextProps & {
  variant?: Variant;
  color?: string;
  center?: boolean;
};

export function AppText({ variant = 'body', color, center, style, ...rest }: Props) {
  const preset = typography[variant] as TextStyle;
  return (
    <Text
      {...rest}
      style={[
        preset,
        { color: color ?? colors.text.primary },
        center && { textAlign: 'center' },
        style,
      ]}
    />
  );
}
