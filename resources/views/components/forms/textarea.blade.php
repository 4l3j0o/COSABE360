@props([
    'label' => null,
    'name',
    'placeholder' => '',
    'required' => false,
    'value' => null,
    'rows' => 4,
])

<div class="space-y-1.5" {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-semibold uppercase tracking-wider text-neutral-600">
            {{ $label }} @if($required) <span class="text-error">*</span> @endif
        </label>
    @endif
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        rows="{{ $rows }}"
        @if($required) required @endif
        class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all resize-none"
        {{ $attributes->except(['class']) }}
    >{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="text-xs text-error">{{ $message }}</p>
    @enderror
</div>
