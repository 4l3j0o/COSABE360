@props([
    'label' => null,
    'name',
    'options' => [],
    'placeholder' => 'Seleccionar...',
    'required' => false,
    'value' => null,
])

<div class="space-y-1.5" {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-semibold uppercase tracking-wider text-neutral-600">
            {{ $label }} @if($required) <span class="text-error">*</span> @endif
        </label>
    @endif
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if($required) required @endif
        class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
        {{ $attributes->except(['class']) }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $key => $label)
            <option value="{{ $key }}" {{ old($name, $value) == $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="text-xs text-error">{{ $message }}</p>
    @enderror
</div>
