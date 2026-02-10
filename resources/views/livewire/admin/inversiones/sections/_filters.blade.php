{{-- ===================== FILTERS ===================== --}}
<div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
        <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Filtros</div>
    </div>

    <div class="p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="sm:col-span-2 lg:col-span-2">
                <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Búsqueda</label>
                <input type="text" wire:model.live="search" placeholder="Código o titular…"
                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                           border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                           focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
            </div>
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
                    <option value="CERRADA">Cerrada</option>
                </select>
            </div>
        </div>
    </div>
</div>
