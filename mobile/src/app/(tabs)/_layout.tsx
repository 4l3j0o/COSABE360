import { Tabs } from 'expo-router';
import { BookOpen, CalendarDays, CalendarCheck, Home, ListChecks, User } from 'lucide-react-native';

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
        name="pendientes"
        options={{
          title: 'Pendientes',
          tabBarIcon: ({ color, size }) => <ListChecks color={color} size={size - 2} />,
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
        name="feriados"
        options={{
          title: 'Feriados',
          tabBarIcon: ({ color, size }) => <CalendarDays color={color} size={size - 2} />,
        }}
      />
      <Tabs.Screen
        name="perfil"
        options={{
          title: 'Perfil',
          tabBarIcon: ({ color, size }) => <User color={color} size={size - 2} />,
        }}
      />
      {/* Pantallas opcionales fuera de la barra */}
      <Tabs.Screen name="asistencia" options={{ href: null }} />
      <Tabs.Screen name="comunicados" options={{ href: null }} />
    </Tabs>
  );
}
