{{-- ===================== FILTERS (DESKTOP + MOBILE) ===================== --}}
<div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">

    {{-- MOBILE (<= md): FILTROS COLAPSABLES (MISMO TAMAÑO DE LETRA) --}}
    <div class="md:hidden" x-data="{ openFilters: false }">

        {{-- Header / botón MOBILE --}}
        <div class="px-4 h-11 flex items-center justify-between">
            {{-- Izquierda --}}
            <div class="text-[13px] font-semibold text-gray-700 dark:text-neutral-200">
                Filtros
            </div>

            {{-- Derecha --}}
            <button type="button" @click="openFilters = !openFilters"
                class="inline-flex items-center gap-1.5
                   px-3 h-8
                   rounded-lg
                   text-[13px] font-semibold
                   border border-gray-200
                   bg-white text-gray-700
                   hover:bg-gray-50
                   dark:border-neutral-700
                   dark:bg-neutral-900
                   dark:text-neutral-100
                   dark:hover:bg-neutral-800/60
                   transition">

                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 4h-7" />
                    <path d="M10 4H3" />
                    <path d="M21 12h-9" />
                    <path d="M8 12H3" />
                    <path d="M21 20h-5" />
                    <path d="M12 20H3" />
                    <path d="M14 2v4" />
                    <path d="M12 10v4" />
                    <path d="M16 18v4" />
                </svg>

                <span x-text="openFilters ? 'Ocultar' : 'Mostrar'"></span>
            </button>
        </div>

        {{-- Contenido (oculto al inicio) --}}
        <div class="mt-2 space-y-3 px-4 pb-3 text-[13px]" x-show="openFilters" x-collapse x-cloak>

            <div>
                <label class="block mb-1 text-gray-600 dark:text-neutral-300 text-[13px]">
                    Búsqueda
                </label>

                <input type="text" wire:model.live="search" placeholder="Código o titular…" autocomplete="off"
                    name="inv_search"
                    class="w-full rounded-lg border px-3 py-2
                       bg-white dark:bg-neutral-900
                       border-gray-300 dark:border-neutral-700
                       text-gray-900 dark:text-neutral-100
                       text-[13px]
                       focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block mb-1 text-gray-600 dark:text-neutral-300 text-[13px]">
                        Tipo
                    </label>

                    <select wire:model.live="fTipo"
                        class="w-full rounded-lg border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           text-[13px]
                           focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                        <option value="">Todos</option>
                        <option value="PRIVADO">Privado</option>
                        <option value="BANCO">Banco</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-gray-600 dark:text-neutral-300 text-[13px]">
                        Estado
                    </label>

                    <select wire:model.live="fEstado"
                        class="w-full rounded-lg border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700
                           text-gray-900 dark:text-neutral-100
                           text-[13px]
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

    {{-- DESKTOP (>= md): tu layout original --}}
    <div class="hidden md:block p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="sm:col-span-3 lg:col-span-3">
                <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Búsqueda</label>
                <input type="text" wire:model.live="search" placeholder="Código o titular…" name="inv_search"
                    autocomplete="new-password"
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
