{{-- FILTROS (Desktop full-width sin huecos + Mobile colapsable) --}}
<div x-data="{
    open: false,
    init() {
        const mq = window.matchMedia('(min-width: 768px)'); // md
        const sync = () => {
            // En desktop siempre abierto
            this.open = mq.matches ? true : this.open;
        };
        sync();
        mq.addEventListener?.('change', sync);
    }
}" class="rounded-lg border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 p-4 md:p-5">

    {{-- HEADER MOBILE: botón filtros --}}
    <div class="flex items-center justify-between md:hidden">
        <div class="min-w-0">
            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                Filtros
            </div>
            <div class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                Busqueda · Estado · Moenda · Paginación
            </div>
        </div>

        <button type="button" @click="open = !open"
            class="shrink-0 inline-flex items-center gap-2 rounded-lg border px-3 py-1
                   bg-white text-gray-700 border-gray-200
                   dark:bg-neutral-900 dark:text-neutral-200 dark:border-neutral-700
                   hover:bg-gray-50 dark:hover:bg-neutral-800">
            <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
            <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    {{-- CONTENIDO FILTROS (en desktop siempre visible, en mobile colapsable) --}}
    <div x-show="open" x-transition.opacity.duration.150ms class="mt-4 md:mt-0">

        {{-- DESKTOP: GRID 12 --}}
        <div class="hidden md:grid md:grid-cols-12 md:gap-4 md:items-end">
            {{-- Buscar: ocupa la mayor parte --}}
            <div class="md:col-span-6 lg:col-span-7">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-200">
                    Buscar agente
                </label>
                <input type="search" wire:model.live="search" placeholder="Nombre o CI del agente…"
                    class="w-full rounded-lg border px-3 py-2.5
                           bg-white text-gray-900 border-gray-300
                           dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                           focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                    autocomplete="off" />
            </div>

            {{-- Estado --}}
            <div class="md:col-span-2 lg:col-span-2">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-200">
                    Estado
                </label>
                <div
                    class="w-full rounded-lg border px-2 py-2.5
                            bg-gray-50/60 border-gray-200
                            dark:bg-neutral-900/40 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-neutral-400">Cerrados</span>

                        <button type="button" wire:click="$toggle('soloPendientes')"
                            class="cursor-pointer relative inline-flex mx-2 shrink-0 h-6 w-11 items-center rounded-full transition-colors
                                   {{ $soloPendientes ? 'bg-emerald-600' : 'bg-gray-300 dark:bg-neutral-700' }}
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            <span
                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform
                                         {{ $soloPendientes ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>

                        <span
                            class="text-sm font-medium {{ $soloPendientes ? 'text-emerald-600' : 'text-gray-600 dark:text-neutral-400' }}">
                            Abiertos
                        </span>
                    </div>
                </div>
            </div>

            {{-- Moneda --}}
            <div class="md:col-span-2 lg:col-span-2">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-200">
                    Moneda
                </label>
                <select wire:model.live="moneda"
                    class="cursor-pointer w-full rounded-lg border px-3 py-2.5
                           bg-white text-gray-900 border-gray-300
                           dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                           focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                    <option value="all">Todas</option>
                    <option value="BOB">BOB</option>
                    <option value="USD">USD</option>
                </select>
            </div>

            {{-- Por página --}}
            <div class="md:col-span-1 lg:col-span-1">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-200">
                    Por página
                </label>
                <select wire:model.live="perPage"
                    class="cursor-pointer w-full rounded-lg border px-3 py-2.5
                           bg-white text-gray-900 border-gray-300
                           dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                           focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        {{-- MOBILE: STACK/GRID ) --}}
        <div class="md:hidden space-y-3">
            {{-- Buscar --}}
            <div class="w-full">
                <label class="block text-xs font-medium mb-1 text-gray-700 dark:text-neutral-200">
                    Buscar agente
                </label>
                <input type="search" wire:model.live="search" placeholder="Nombre o CI del agente…"
                    class="w-full rounded-lg border px-3 py-1.5
                           bg-white text-gray-900 border-gray-300
                           dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                           focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                    autocomplete="off" />
            </div>

            {{-- Grid 2 columnas: Moneda + Por página --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-700 dark:text-neutral-200">
                        Moneda
                    </label>
                    <select wire:model.live="moneda"
                        class="cursor-pointer w-full rounded-lg border px-3 py-1.5
                               bg-white text-gray-900 border-gray-300
                               dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                               focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                        <option value="all">Todas</option>
                        <option value="BOB">BOB</option>
                        <option value="USD">USD</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-700 dark:text-neutral-200">
                        Por página
                    </label>
                    <select wire:model.live="perPage"
                        class="cursor-pointer w-full rounded-lg border px-3 py-1.5
                               bg-white text-gray-900 border-gray-300
                               dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                               focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            {{-- Estado --}}
            <div class="w-full">
                <label class="block text-xs font-medium mb-1 text-gray-700 dark:text-neutral-200">
                    Estado
                </label>
                <div
                    class="w-full rounded-lg border px-3 py-1.5
                            bg-gray-50/60 border-gray-200
                            dark:bg-neutral-900/40 dark:border-neutral-700">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm text-gray-600 dark:text-neutral-400">
                            Cerrados
                        </span>

                        <button type="button" wire:click="$toggle('soloPendientes')"
                            class="cursor-pointer relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                                   {{ $soloPendientes ? 'bg-emerald-600' : 'bg-gray-300 dark:bg-neutral-700' }}
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                            <span
                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform
                                         {{ $soloPendientes ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>

                        <span
                            class="text-sm font-medium
                                   {{ $soloPendientes ? 'text-emerald-600' : 'text-gray-600 dark:text-neutral-400' }}">
                            Abiertos
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
