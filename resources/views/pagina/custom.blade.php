@if ($paginator->hasPages())
    <div class="flex items-center justify-between">
        <p class="text-xs text-muted-foreground">
            Mostrando {{ $paginator->firstItem() }} al {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
        </p>

        <nav class="flex items-center gap-1" role="navigation" aria-label="Paginacion">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-medium text-muted-foreground/40 bg-muted/30 cursor-not-allowed select-none">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-medium text-muted-foreground bg-card border border-border hover:bg-muted hover:text-foreground hover:border-primary/30 transition-all duration-200">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-medium text-muted-foreground/40 select-none">...</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-semibold bg-primary text-primary-foreground shadow-sm">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-medium text-muted-foreground bg-card border border-transparent hover:bg-muted hover:text-foreground hover:border-border transition-all duration-200">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-medium text-muted-foreground bg-card border border-border hover:bg-muted hover:text-foreground hover:border-primary/30 transition-all duration-200">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-medium text-muted-foreground/40 bg-muted/30 cursor-not-allowed select-none">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </nav>
    </div>
@endif
