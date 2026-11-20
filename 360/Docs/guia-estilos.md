# Guía de estilos — CSB 360

Fuente de verdad visual: `resources/views/students/show.blade.php` (ficha) y
`resources/views/rude/show.blade.php` (secciones). Todo módulo nuevo debe
seguir estos patrones usando los tokens del tema (`resources/css/app.css`).
No usar colores hex directos.

## Tokens (Tailwind v4, definidos en `@theme`)

| Token | Uso |
|---|---|
| `foreground` / `background` | Texto base / fondo página |
| `card` + `border` | Tarjetas y bordes |
| `muted` / `muted-foreground` | Fondos suaves / texto secundario |
| `primary` (verde institucional) | Acción principal |
| `success` / `warning` / `error` / `info` | Estados y badges |
| `accent` | Hover de filas/botones (`hover:bg-accent`, `hover:bg-muted/20`) |

## Estructura de página

```blade
@extends('layouts.app')
@section('titulo', 'Título')
@section('sidebar')
    @include('dashboard.partials._sidebar-by-rol')
@endsection
@section('contenido')
    <x-layout.page-header title="Título" description="Subtítulo" />
    ...
@endsection
```

Cabecera de ficha (con acciones a la derecha):

```blade
<div class="mb-6">
    @include('layouts._volver', ['href' => route('...'), 'texto' => 'Volver al listado'])
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1">
        <h1 class="text-xl font-bold text-foreground">Nombre</h1>
        <div class="flex items-center gap-2">
            <x-ui.action-button color="success" :href="route('...')">Matricular</x-ui.action-button>
            <x-ui.action-button color="primary" :href="route('...')">Editar</x-ui.action-button>
        </div>
    </div>
</div>
```

Colores de `x-ui.action-button`: `primary` (defecto), `success`, `amber`,
`blue`, `teal`, `slate`, `muted`. Cada módulo secundario usa siempre el
mismo color (RUDE = amber, Boletín = blue, Inclusión = teal,
Expediente = slate).

## Tarjetas y secciones

```blade
<div class="bg-card border border-border rounded-xl p-6">
    <x-ui.section-title>Datos Personales</x-ui.section-title>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
        <x-ui.field label="Código" mono>{{ $codigo }}</x-ui.field>
        <x-ui.field label="Nombres">{{ $nombres }}</x-ui.field>
    </dl>
</div>
```

Para listas tipo RUDE (etiqueta izquierda, valor derecha):

```blade
<div class="bg-card border border-border rounded-xl p-5">
    <x-ui.section-title>Sección I</x-ui.section-title>
    <dl class="space-y-2 text-sm">
        <x-ui.dl-row label="SIE UE" mono>{{ $sie }}</x-ui.dl-row>
    </dl>
</div>
```

## Badges de estado

```blade
<x-ui.badge color="success">activo</x-ui.badge>
```

Colores: `success`, `warning`, `error`, `info`, `neutral` (defecto).
Texto en minúsculas (`mb_strtolower`). Códigos/CI siempre en `font-mono`.

## Tablas

```blade
<div class="bg-card border border-border rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-muted/50 border-b border-border">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-muted-foreground">Col</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <tr class="hover:bg-muted/20">
                    <td class="px-4 py-3">...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

## Formularios

Usar los componentes `x-forms.input`, `x-forms.select`, `x-forms.textarea`,
`x-forms.picker`. Etiqueta:

```blade
<label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Nombre *</label>
```

Botón submit: `px-6 py-2 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90`.
Botón secundario: `border border-input ... hover:bg-muted`.
Botón peligro: `border-red-300 text-red-700 ... hover:bg-red-50`.
Errores: `<p class="text-xs text-error mt-1">` o `@error`.

## Vacíos y avisos

- Sin datos: `<x-ui.empty-state title="..." description="..." />`
- Avisos: `<x-ui.alert type="info">...</x-ui.alert>` (`success|error|warning|info`)
- Toasts vía sesión: `with('mensaje'|'success'|'error', ...)` (ver `layouts/_toasts`)

## Reglas

1. Nada de hex directos: solo tokens del tema.
2. Iconos SVG inline `w-4 h-4` (heroicons, stroke).
3. Responsive: `grid-cols-1 sm:... lg:...`, cabeceras `flex-col sm:flex-row`.
4. `rounded-xl` tarjetas, `rounded-lg` botones/inputs.
