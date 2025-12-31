<div class="flex items-center justify-between w-full">
    <!-- Izquierda -->
    <div class="flex items-center gap-2 min-w-0">
        <div
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </div>

        <div class="grid text-start text-sm min-w-0">
            <span class="mb-0.5 truncate leading-tight font-semibold">
                {{ auth()->user()->empresa?->nombre ?? 'Finanzas' }}
            </span>

        </div>
    </div>

    <!-- Derecha -->
    <div wire:ignore>
        <button type="button" x-data
            @click.prevent.stop="$flux.appearance = ($flux.appearance === 'dark' ? 'light' : 'dark')"
            class="flex items-center justify-center size-9 rounded-md
               border border-gray-300 hover:bg-gray-100
               dark:border-neutral-700 dark:hover:bg-neutral-800
               transition"
            aria-label="Cambiar tema">
            <!-- Sol (cuando estás en oscuro) -->
            <svg x-show="$flux.appearance === 'dark'" xmlns="http://www.w3.org/2000/svg" class="size-5 text-neutral-200"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3
                 m15.364-6.364l-.707.707
                 m-9.9 9.9l-.707.707
                 m0-10.607l.707.707
                 m9.9 9.9l.707.707
                 M12 8a4 4 0 100 8 4 4 0 000-8z" />
            </svg>

            <!-- Luna (cuando estás en claro) -->
            <svg x-show="$flux.appearance !== 'dark'" xmlns="http://www.w3.org/2000/svg" class="size-5 text-gray-800"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3
                 7 7 0 0021 12.79z" />
            </svg>
        </button>
    </div>
</div>
