{{-- FILTROS --}}
<div class="rounded-lg border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 p-4 md:p-5">
    <div class="flex flex-col gap-3 md:flex-row md:items-center">

        {{-- Buscar (izquierda) --}}
        <div class="w-full md:w-80">
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-200">
                Buscar agente
            </label>
            <input type="search" wire:model.live.debounce.500ms="search" placeholder="Nombre o CI del agente…"
                class="w-full rounded-lg border px-3 py-2.5
                       bg-white text-gray-900 border-gray-300
                       dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                       focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                autocomplete="off" />
        </div>

        {{-- Controles (derecha) --}}
        <div class="flex flex-col sm:flex-row gap-3 md:ml-auto w-full md:w-auto sm:items-end">

            {{-- Estado (switch Cerrados/Abiertos) --}}
            <div class="w-full sm:w-56">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-neutral-200">
                    Estado
                </label>

                <div
                    class="w-full rounded-lg border px-3 py-2.5
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

            {{-- Moneda --}}
            <div class="w-full sm:w-44">
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
            <div class="w-full sm:w-32">
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
    </div>
</div>
