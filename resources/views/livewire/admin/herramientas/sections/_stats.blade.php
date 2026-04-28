    {{-- ── STATS CARDS ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 mb-4">

        {{-- Herramientas Activas --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-2.5 sm:p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
            <div class="absolute top-0 right-0 p-2 sm:p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-8 h-8 sm:w-12 sm:h-12 text-indigo-600 dark:text-indigo-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                </svg>
            </div>
            <p class="text-[11px] sm:text-sm font-medium text-gray-500 dark:text-neutral-400 mb-1 leading-tight">Tipos
                activos</p>
            <p class="text-sm sm:text-lg lg:text-xl font-bold text-gray-900 dark:text-white tabular-nums leading-tight">
                {{ $stats->activas ?? 0 }}
            </p>
        </div>

        {{-- Stock Disponible --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-2.5 sm:p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
            <div class="absolute top-0 right-0 p-2 sm:p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-8 h-8 sm:w-12 sm:h-12 text-emerald-600 dark:text-emerald-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-[11px] sm:text-sm font-medium text-gray-500 dark:text-neutral-400 mb-1 leading-tight">
                Disponible</p>
            <p
                class="text-sm sm:text-lg lg:text-xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums leading-tight">
                {{ $stats->disponibles ?? 0 }}
            </p>
        </div>

        {{-- Stock En Préstamo --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-2.5 sm:p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
            <div class="absolute top-0 right-0 p-2 sm:p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-8 h-8 sm:w-12 sm:h-12 text-amber-600 dark:text-amber-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l-3-3m3 3l3-3" />
                </svg>
            </div>
            <p class="text-[11px] sm:text-sm font-medium text-gray-500 dark:text-neutral-400 mb-1 leading-tight">En obra
            </p>
            <p
                class="text-sm sm:text-lg lg:text-xl font-bold text-amber-600 dark:text-amber-400 tabular-nums leading-tight">
                {{ $stats->prestadas ?? 0 }}
            </p>
        </div>

        {{-- Valor Total del Inventario --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-2.5 sm:p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
            <div class="absolute top-0 right-0 p-2 sm:p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-8 h-8 sm:w-12 sm:h-12 text-gray-500 dark:text-neutral-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-[11px] sm:text-sm font-medium text-gray-500 dark:text-neutral-400 mb-1 leading-tight">Valor
                total</p>
            <p class="text-sm sm:text-lg lg:text-xl font-bold text-gray-900 dark:text-white tabular-nums leading-tight">
                Bs. {{ number_format($stats->valor ?? 0, 0, ',', '.') }}
            </p>
        </div>

    </div>
