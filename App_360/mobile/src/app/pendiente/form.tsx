import { router, useLocalSearchParams } from 'expo-router';
import { ArrowLeft, Check } from 'lucide-react-native';
import { useState } from 'react';
import { Pressable, StyleSheet, TextInput, View } from 'react-native';

import { AppText, Button, Card, Screen } from '@/components/ui';
import { materias } from '@/data/mock';
import { usePendientes, type Prioridad } from '@/store/pendientes';
import { colors, fonts, radius, spacing } from '@/theme/tokens';

const prioridades: Prioridad[] = ['alta', 'media', 'baja'];

function fechaValida(texto: string) {
  const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(texto.trim());
  if (!m) return false;
  const [dia, mes, anio] = [Number(m[1]), Number(m[2]), Number(m[3])];
  if (mes < 1 || mes > 12 || anio < 2020 || anio > 2100) return false;
  const fecha = new Date(anio, mes - 1, dia);
  return fecha.getDate() === dia && fecha.getMonth() === mes - 1;
}

export default function FormularioPendiente() {
  const { id } = useLocalSearchParams<{ id?: string }>();
  const items = usePendientes((s) => s.items);
  const agregar = usePendientes((s) => s.agregar);
  const actualizar = usePendientes((s) => s.actualizar);
  const existente = items.find((i) => i.id === id);

  const [titulo, setTitulo] = useState(existente?.titulo ?? '');
  const [materia, setMateria] = useState(existente?.materia ?? '');
  const [fecha, setFecha] = useState(existente?.fecha ?? '');
  const [prioridad, setPrioridad] = useState<Prioridad>(existente?.prioridad ?? 'media');
  const [errores, setErrores] = useState<Record<string, string>>({});

  function validar() {
    const nuevos: Record<string, string> = {};
    if (titulo.trim().length < 3) {
      nuevos.titulo = 'Escribí al menos 3 caracteres.';
    }
    if (!materia) {
      nuevos.materia = 'Elegí una materia.';
    }
    if (!fecha.trim()) {
      nuevos.fecha = 'La fecha es obligatoria.';
    } else if (!fechaValida(fecha)) {
      nuevos.fecha = 'Usá el formato dd/mm/aaaa con una fecha real.';
    }
    setErrores(nuevos);
    return Object.keys(nuevos).length === 0;
  }

  function guardar() {
    if (!validar()) return;
    const datos = { titulo: titulo.trim(), materia, fecha: fecha.trim(), prioridad };
    if (existente) {
      actualizar(existente.id, { ...datos, hecho: existente.hecho });
    } else {
      agregar(datos);
    }
    router.back();
  }

  return (
    <Screen>
      <View style={styles.barra}>
        <Pressable onPress={() => router.back()} style={styles.volver}>
          <ArrowLeft size={20} color={colors.slate[700]} />
          <AppText variant="caption" color={colors.text.secondary}>
            Volver
          </AppText>
        </Pressable>
        <AppText variant="bodyStrong">{existente ? 'Editar pendiente' : 'Nuevo pendiente'}</AppText>
      </View>

      <AppText variant="micro" color={colors.text.tertiary} style={styles.etiqueta}>
        TÍTULO
      </AppText>
      <TextInput
        value={titulo}
        onChangeText={setTitulo}
        placeholder="Ej. Resolver ejercicios de la página 40"
        placeholderTextColor={colors.text.tertiary}
        style={[styles.input, errores.titulo && styles.inputError]}
      />
      {errores.titulo ? (
        <AppText variant="micro" color={colors.semantic.danger}>
          {errores.titulo}
        </AppText>
      ) : null}

      <AppText variant="micro" color={colors.text.tertiary} style={styles.etiqueta}>
        MATERIA
      </AppText>
      <View style={styles.chips}>
        {materias.slice(0, 6).map((m) => (
          <Pressable
            key={m.id}
            onPress={() => setMateria(m.nombre)}
            style={[styles.chip, materia === m.nombre && styles.chipActivo]}>
            <AppText variant="caption" color={materia === m.nombre ? '#FFFFFF' : colors.text.secondary}>
              {m.nombre}
            </AppText>
          </Pressable>
        ))}
      </View>
      {errores.materia ? (
        <AppText variant="micro" color={colors.semantic.danger} style={{ marginTop: 4 }}>
          {errores.materia}
        </AppText>
      ) : null}

      <AppText variant="micro" color={colors.text.tertiary} style={styles.etiqueta}>
        FECHA LÍMITE (DD/MM/AAAA)
      </AppText>
      <TextInput
        value={fecha}
        onChangeText={setFecha}
        placeholder="20/11/2026"
        placeholderTextColor={colors.text.tertiary}
        keyboardType="numbers-and-punctuation"
        style={[styles.input, errores.fecha && styles.inputError]}
      />
      {errores.fecha ? (
        <AppText variant="micro" color={colors.semantic.danger}>
          {errores.fecha}
        </AppText>
      ) : null}

      <AppText variant="micro" color={colors.text.tertiary} style={styles.etiqueta}>
        PRIORIDAD
      </AppText>
      <View style={styles.chips}>
        {prioridades.map((p) => (
          <Pressable
            key={p}
            onPress={() => setPrioridad(p)}
            style={[styles.chip, prioridad === p && styles.chipActivo]}>
            <AppText variant="caption" color={prioridad === p ? '#FFFFFF' : colors.text.secondary}>
              {p[0].toUpperCase() + p.slice(1)}
            </AppText>
          </Pressable>
        ))}
      </View>

      <Card style={{ marginTop: spacing.xl }}>
        <AppText variant="micro" color={colors.text.tertiary}>
          Los datos se guardarán en la base SQLite del celular (a partir de la Jornada 3).
        </AppText>
      </Card>

      <Button
        label={existente ? 'Guardar cambios' : 'Crear pendiente'}
        onPress={guardar}
        icon={<Check size={18} color="#FFFFFF" />}
        style={{ marginTop: spacing.xl }}
      />
    </Screen>
  );
}

const styles = StyleSheet.create({
  barra: { flexDirection: 'row', alignItems: 'center', gap: spacing.md, marginBottom: spacing.lg },
  volver: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  etiqueta: { marginTop: spacing.lg, marginBottom: spacing.sm, letterSpacing: 1 },
  input: {
    borderWidth: 1,
    borderColor: colors.surface.border,
    backgroundColor: '#FFFFFF',
    borderRadius: radius.md,
    paddingHorizontal: spacing.lg,
    paddingVertical: 12,
    fontFamily: fonts.regular,
    fontSize: 15,
    color: colors.text.primary,
  },
  inputError: { borderColor: colors.semantic.danger, borderWidth: 1.5 },
  chips: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
  chip: {
    paddingHorizontal: spacing.lg,
    paddingVertical: 8,
    borderRadius: radius.pill,
    backgroundColor: colors.slate[100],
  },
  chipActivo: { backgroundColor: colors.primary[600] },
});
