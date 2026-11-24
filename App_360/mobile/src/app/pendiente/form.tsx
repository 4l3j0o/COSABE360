import { router, useLocalSearchParams } from 'expo-router';
import { ArrowLeft, Check, Database, Sparkles } from 'lucide-react-native';
import { useEffect, useRef, useState } from 'react';
import { Pressable, StyleSheet, TextInput, View } from 'react-native';

import { AppText, Button, Card, Screen } from '@/components/ui';
import { materias } from '@/data/mock';
import { usePendientes, type Prioridad } from '@/store/pendientes';
import { colors, fonts, radius, shadows, spacing } from '@/theme/tokens';

const prioridades: { id: Prioridad; label: string; color: string; bg: string; border: string }[] = [
  { id: 'baja', label: 'Baja', color: colors.accent.emerald, bg: colors.accent.emeraldLight, border: colors.accent.emerald },
  { id: 'media', label: 'Media', color: colors.semantic.warning, bg: colors.accent.amberLight, border: colors.semantic.warning },
  { id: 'alta', label: 'Alta', color: colors.semantic.danger, bg: colors.accent.roseLight, border: colors.semantic.danger },
];

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
  const cargar = usePendientes((s) => s.cargar);
  const agregar = usePendientes((s) => s.agregar);
  const actualizar = usePendientes((s) => s.actualizar);
  const guardando = usePendientes((s) => s.guardando);

  const existente = items.find((i) => String(i.id) === id);

  const [titulo, setTitulo] = useState('');
  const [materia, setMateria] = useState('');
  const [fecha, setFecha] = useState('');
  const [prioridad, setPrioridad] = useState<Prioridad>('media');
  const [errores, setErrores] = useState<Record<string, string>>({});
  const [errorGuardado, setErrorGuardado] = useState<string | null>(null);
  const cargado = useRef(false);

  // Si se entra directo a editar (por ejemplo desde un enlace), se lee la tabla.
  useEffect(() => {
    if (id) cargar();
  }, [id, cargar]);

  // Copia los datos del registro una sola vez, sin pisar lo que escribe el usuario.
  useEffect(() => {
    if (!existente || cargado.current) return;
    setTitulo(existente.titulo);
    setMateria(existente.materia);
    setFecha(existente.fecha);
    setPrioridad(existente.prioridad);
    cargado.current = true;
  }, [existente]);

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

  async function guardar() {
    if (!validar()) return;
    setErrorGuardado(null);

    const datos = { titulo: titulo.trim(), materia, fecha: fecha.trim(), prioridad };
    const ok = existente
      ? await actualizar(existente.id, { ...datos, hecho: existente.hecho })
      : await agregar(datos);

    if (!ok) {
      setErrorGuardado(usePendientes.getState().error ?? 'No se pudo guardar en MySQL.');
      return;
    }
    router.back();
  }

  return (
    <Screen>
      {/* Barra de navegación superior */}
      <View style={styles.barra}>
        <Pressable onPress={() => router.back()} style={styles.volver}>
          <ArrowLeft size={18} color={colors.primary[700]} />
          <AppText variant="captionStrong" color={colors.primary[700]}>
            Volver
          </AppText>
        </Pressable>
        <AppText variant="bodyStrong">{existente ? 'Editar pendiente' : 'Nuevo pendiente'}</AppText>
        <View style={{ width: 60 }} />
      </View>

      {errorGuardado ? (
        <Card style={styles.errorBox}>
          <AppText variant="caption" color={colors.semantic.danger}>
            {errorGuardado}
          </AppText>
        </Card>
      ) : null}

      {/* Formulario */}
      <Card style={styles.formCard}>
        {/* Campo Título */}
        <View style={styles.fieldGroup}>
          <AppText variant="micro" color={colors.text.secondary} style={styles.etiqueta}>
            TÍTULO DE LA TAREA *
          </AppText>
          <TextInput
            value={titulo}
            onChangeText={setTitulo}
            placeholder="Ej. Resolver ejercicios de la página 40"
            placeholderTextColor={colors.text.tertiary}
            style={[styles.input, errores.titulo && styles.inputError]}
          />
          {errores.titulo ? (
            <AppText variant="micro" color={colors.semantic.danger} style={styles.errorText}>
              {errores.titulo}
            </AppText>
          ) : null}
        </View>

        {/* Campo Materia */}
        <View style={styles.fieldGroup}>
          <AppText variant="micro" color={colors.text.secondary} style={styles.etiqueta}>
            ASIGNATURA *
          </AppText>
          <View style={styles.chips}>
            {materias.slice(0, 8).map((m) => {
              const activa = materia === m.nombre;
              return (
                <Pressable
                  key={m.id}
                  onPress={() => setMateria(m.nombre)}
                  style={[styles.chip, activa && styles.chipActivo]}>
                  <AppText
                    variant="caption"
                    color={activa ? '#FFFFFF' : colors.text.secondary}
                    style={{ fontWeight: activa ? '700' : '500' }}>
                    {m.nombre}
                  </AppText>
                </Pressable>
              );
            })}
          </View>
          {errores.materia ? (
            <AppText variant="micro" color={colors.semantic.danger} style={styles.errorText}>
              {errores.materia}
            </AppText>
          ) : null}
        </View>

        {/* Campo Fecha */}
        <View style={styles.fieldGroup}>
          <AppText variant="micro" color={colors.text.secondary} style={styles.etiqueta}>
            FECHA LÍMITE (DD/MM/AAAA) *
          </AppText>
          <TextInput
            value={fecha}
            onChangeText={setFecha}
            placeholder="25/11/2026"
            placeholderTextColor={colors.text.tertiary}
            keyboardType="numbers-and-punctuation"
            style={[styles.input, errores.fecha && styles.inputError]}
          />
          {errores.fecha ? (
            <AppText variant="micro" color={colors.semantic.danger} style={styles.errorText}>
              {errores.fecha}
            </AppText>
          ) : null}
        </View>

        {/* Campo Prioridad */}
        <View style={styles.fieldGroup}>
          <AppText variant="micro" color={colors.text.secondary} style={styles.etiqueta}>
            NIVEL DE PRIORIDAD
          </AppText>
          <View style={styles.chipsPrioridad}>
            {prioridades.map((p) => {
              const activa = prioridad === p.id;
              return (
                <Pressable
                  key={p.id}
                  onPress={() => setPrioridad(p.id)}
                  style={[
                    styles.chipPrioridad,
                    { borderColor: activa ? p.border : colors.neutral[200] },
                    activa && { backgroundColor: p.bg },
                  ]}>
                  <View style={[styles.dotPrioridad, { backgroundColor: p.border }]} />
                  <AppText
                    variant="caption"
                    color={activa ? p.color : colors.text.secondary}
                    style={{ fontWeight: activa ? '700' : '500' }}>
                    {p.label}
                  </AppText>
                </Pressable>
              );
            })}
          </View>
        </View>
      </Card>

      {/* SQL Feedback Card */}
      <Card style={styles.sqlCard}>
        <View style={styles.sqlHeader}>
          <Database size={15} color={colors.primary[700]} />
          <AppText variant="micro" color={colors.primary[800]} style={{ fontWeight: '700' }}>
            OPERACIÓN EN BASE DE DATOS (MYSQL LOCAL)
          </AppText>
        </View>
        <AppText variant="micro" color={colors.text.secondary} style={{ marginTop: 4 }}>
          {existente
            ? 'Al guardar se ejecuta UPDATE pendientes SET titulo=?, materia=?, fecha=?, prioridad=? WHERE id=? AND usuario_id=?'
            : 'Al guardar se ejecuta INSERT INTO pendientes (usuario_id, titulo, materia, fecha, prioridad, hecho) VALUES (?, ?, ?, ?, ?, ?)'}
        </AppText>
      </Card>

      {/* Botón de Guardar */}
      <Button
        label={existente ? 'Guardar cambios' : 'Crear pendiente'}
        onPress={guardar}
        loading={guardando}
        icon={<Check size={18} color="#FFFFFF" strokeWidth={2.5} />}
        style={{ marginTop: spacing.lg }}
      />
    </Screen>
  );
}

const styles = StyleSheet.create({
  barra: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: spacing.lg,
  },
  volver: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingVertical: 6,
    paddingHorizontal: 8,
    borderRadius: radius.md,
    backgroundColor: colors.primary[50],
  },
  formCard: {
    borderRadius: radius.xl,
    padding: spacing.lg,
  },
  fieldGroup: {
    marginBottom: spacing.md + 2,
  },
  etiqueta: {
    marginBottom: 6,
    letterSpacing: 0.6,
    fontWeight: '700',
  },
  input: {
    borderWidth: 1,
    borderColor: colors.surface.border,
    backgroundColor: colors.slate[50],
    borderRadius: radius.md,
    paddingHorizontal: spacing.lg,
    paddingVertical: 12,
    fontFamily: fonts.regular,
    fontSize: 14,
    color: colors.text.primary,
  },
  inputError: {
    borderColor: colors.semantic.danger,
    borderWidth: 1.5,
  },
  errorText: {
    marginTop: 4,
  },
  chips: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.xs,
  },
  chip: {
    paddingHorizontal: spacing.md,
    paddingVertical: 7,
    borderRadius: radius.pill,
    backgroundColor: colors.neutral[100],
    borderWidth: 1,
    borderColor: colors.neutral[200],
  },
  chipActivo: {
    backgroundColor: colors.primary[600],
    borderColor: colors.primary[600],
  },
  chipsPrioridad: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  chipPrioridad: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 9,
    borderRadius: radius.lg,
    borderWidth: 1.5,
    backgroundColor: '#FFFFFF',
  },
  dotPrioridad: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
  errorBox: {
    borderColor: colors.semantic.danger,
    backgroundColor: colors.accent.roseLight,
    marginBottom: spacing.md,
  },
  sqlCard: {
    marginTop: spacing.md,
    backgroundColor: colors.primary[50],
    borderColor: colors.primary[200],
    borderWidth: 1,
  },
  sqlHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
});

