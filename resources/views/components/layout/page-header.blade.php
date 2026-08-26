@props(['title', 'description' => null])

<div class="mb-6">
    <h1 class="text-xl font-bold text-foreground">{{ $title }}</h1>
    @if($description)
        <p class="text-sm text-muted-foreground mt-1">{{ $description }}</p>
    @endif
</div>
