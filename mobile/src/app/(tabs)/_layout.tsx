import { Tabs } from 'expo-router';
import { BookOpen, CalendarCheck, Home, MessageSquare, User } from 'lucide-react-native';

import { colors, fonts } from '@/theme/tokens';

export default function TabsLayout() {
  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: colors.primary[700],
        tabBarInactiveTintColor: colors.slate[400],
        tabBarStyle: {
          height: 68,
          paddingBottom: 10,
          paddingTop: 6,
          borderTopColor: colors.surface.border,
          backgroundColor: '#FFFFFF',
        },
        tabBarLabelStyle: { fontFamily: fonts.medium, fontSize: 11 },
      }}>
      <Tabs.Screen
        name="index"
        options={{
          title: 'Inicio',
          tabBarIcon: ({ color, size }) => <Home color={color} size={size - 2} />,
        }}
      />
      <Tabs.Screen
        name="notas"
        options={{
          title: 'Notas',
          tabBarIcon: ({ color, size }) => <BookOpen color={color} size={size - 2} />,
        }}
      />
      <Tabs.Screen
        name="asistencia"
        options={{
          title: 'Asistencia',
          tabBarIcon: ({ color, size }) => <CalendarCheck color={color} size={size - 2} />,
        }}
      />
      <Tabs.Screen
        name="comunicados"
        options={{
          title: 'Comunicados',
          tabBarIcon: ({ color, size }) => <MessageSquare color={color} size={size - 2} />,
        }}
      />
      <Tabs.Screen
        name="perfil"
        options={{
          title: 'Perfil',
          tabBarIcon: ({ color, size }) => <User color={color} size={size - 2} />,
        }}
      />
    </Tabs>
  );
}
