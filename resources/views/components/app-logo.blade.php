<div class="flex items-center justify-between w-full">
    <!-- Izquierda -->
    <div class="flex items-center gap-2 min-w-0">
        <div
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            {{-- Icono profesional (edificio/empresa) --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 21h18" />
                <path d="M5 21V7a2 2 0 0 1 2-2h3v16" />
                <path d="M14 21V5a2 2 0 0 1 2-2h3v18" />
                <path d="M8 9h.01" />
                <path d="M8 12h.01" />
                <path d="M8 15h.01" />
                <path d="M17 9h.01" />
                <path d="M17 12h.01" />
                <path d="M17 15h.01" />
            </svg>
        </div>

        <div class="grid text-start text-sm min-w-0">
            <span class="mb-0.5 truncate leading-tight font-semibold">
                {{ auth()->user()->empresa?->nombre ?? 'Finanzas' }}
            </span>
        </div>
    </div>
</div>
