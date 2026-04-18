    {{-- ── STATS CARDS ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">

        {{-- Herramientas Activas --}}
        <div class="rounded-xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900/40 px-4 py-3 flex items-center gap-3 shadow-sm">
            <div class="size-9 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center shrink-0">
                <svg class="size-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-neutral-500">Tipos activos</div>
                <div class="text-xl font-black text-gray-900 dark:text-neutral-100 leading-tight">{{ $stats->activas ?? 0 }}</div>
            </div>
        </div>

        {{-- Stock Disponible --}}
        <div class="rounded-xl border border-emerald-100 dark:border-emerald-800/40 bg-emerald-50/50 dark:bg-emerald-900/10 px-4 py-3 flex items-center gap-3 shadow-sm">
            <div class="size-9 rounded-lg bg-emerald-100 dark:bg-emerald-500/10 flex items-center justify-center shrink-0">
                <svg class="size-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600/70 dark:text-emerald-500">Disponible</div>
                <div class="text-xl font-black text-emerald-700 dark:text-emerald-400 leading-tight">{{ $stats->disponibles ?? 0 }}</div>
            </div>
        </div>

        {{-- Stock En Préstamo --}}
        <div class="rounded-xl border border-amber-100 dark:border-amber-800/40 bg-amber-50/50 dark:bg-amber-900/10 px-4 py-3 flex items-center gap-3 shadow-sm">
            <div class="size-9 rounded-lg bg-amber-100 dark:bg-amber-500/10 flex items-center justify-center shrink-0">
                <svg class="size-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l-3-3m3 3l3-3" />
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold uppercase tracking-wider text-amber-600/70 dark:text-amber-500">En obra</div>
                <div class="text-xl font-black text-amber-700 dark:text-amber-400 leading-tight">{{ $stats->prestadas ?? 0 }}</div>
            </div>
        </div>

        {{-- Valor Total del Inventario --}}
        <div class="rounded-xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900/40 px-4 py-3 flex items-center gap-3 shadow-sm">
            <div class="size-9 rounded-lg bg-gray-100 dark:bg-neutral-800 flex items-center justify-center shrink-0">
                <svg class="size-5 text-gray-500 dark:text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-neutral-500">Valor total</div>
                <div class="text-base font-black text-gray-700 dark:text-neutral-300 leading-tight tabular-nums">
                    Bs. {{ number_format($stats->valor ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

    </div>
