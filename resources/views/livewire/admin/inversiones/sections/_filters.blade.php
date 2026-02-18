{{-- ===================== FILTERS (DESKTOP + MOBILE) ===================== --}}
<div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
        <div class="flex items-center justify-between gap-3">
            <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Filtros</div>

            {{-- Badges (solo mobile) --}}
            <div class="md:hidden flex items-center gap-2 text-[11px]">
                @if (!empty($search))
                    <span
                        class="inline-flex items-center gap-1 px-2 py-1 rounded bg-gray-100 text-gray-700 dark:bg-neutral-800 dark:text-neutral-200">
                        {{-- icon search --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7" />
                            <path d="M21 21l-4.3-4.3" />
                        </svg>
                        {{ \Illuminate\Support\Str::limit($search, 12) }}
                    </span>
                @endif

                @if (!empty($fTipo))
                    <span
                        class="inline-flex items-center gap-1 px-2 py-1 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200">
                        {{-- icon tag --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M20.59 13.41 11 3.83V2h-2v2.59l9.59 9.58a2 2 0 0 1 0 2.83l-2.34 2.34a2 2 0 0 1-2.83 0L3.83 13.41a2 2 0 0 1 0-2.83l2.34-2.34" />
                            <path d="M7 7h.01" />
                        </svg>
                        {{ $fTipo }}
                    </span>
                @endif

                @if (!empty($fEstado))
                    <span
                        class="inline-flex items-center gap-1 px-2 py-1 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">
                        {{-- icon check/flag --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 22V4" />
                            <path d="M4 4h14l-2 5 2 5H4" />
                        </svg>
                        {{ $fEstado }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="p-4 space-y-3">

        {{-- MOBILE (<= md): buscador arriba + 2 selects en grid --}}
        <div class="md:hidden space-y-3">
            <div>
                <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Búsqueda</label>
                <input type="text" wire:model.live="search" placeholder="Código o titular…"
                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                           focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Tipo</label>
                    <select wire:model.live="fTipo"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                               border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                        <option value="">Todos</option>
                        <option value="PRIVADO">Privado</option>
                        <option value="BANCO">Banco</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Estado</label>
                    <select wire:model.live="fEstado"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                               border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                        <option value="">Todos</option>
                        <option value="ACTIVA">Activa</option>
                        <option value="PENDIENTE">Pendiente</option>
                        <option value="CERRADA">Cerrada</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- DESKTOP (>= md): tu layout original --}}
        <div class="hidden md:block">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div class="sm:col-span-3 lg:col-span-3">
                    <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Búsqueda</label>
                    <input type="text" wire:model.live="search" placeholder="Código o titular…"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                               border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                </div>

                <div></div>

                <div>
                    <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Tipo</label>
                    <select wire:model.live="fTipo"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                               border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                        <option value="">Todos</option>
                        <option value="PRIVADO">Privado</option>
                        <option value="BANCO">Banco</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Estado</label>
                    <select wire:model.live="fEstado"
                        class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                               border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                        <option value="">Todos</option>
                        <option value="ACTIVA">Activa</option>
                        <option value="PENDIENTE">Pendiente</option>
                        <option value="CERRADA">Cerrada</option>
                    </select>
                </div>
            </div>
        </div>


    </div>
</div>
