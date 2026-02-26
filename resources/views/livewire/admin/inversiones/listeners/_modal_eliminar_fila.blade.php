{{-- MODAL: Eliminar fila PAGADA (requiere contraseña) --}}
<x-ui.modal wire:key="delete-row-{{ $openEliminarFilaModal ? 'open' : 'closed' }}" model="openEliminarFilaModal"
    title="Eliminar registro pagado" maxWidth="sm:max-w-xl" onClose="closeEliminarFilaModal">

    <div class="space-y-3">
        <div
            class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
            Este registro está en estado <b>PAGADO</b>. Para eliminarlo debes confirmar con tu contraseña.
            Se revertirán los saldos según corresponda.
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-neutral-200 mb-1">
                Confirme con su contraseña
            </label>

            <flux:input wire:model.defer="deleteRowPassword" name="deleteRowPassword" type="password" required
                autocomplete="current-password" :placeholder="__('Ingresa tu contraseña')" viewable />
            @error('deleteRowPassword')
                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @slot('footer')
        <div class="flex justify-end gap-2">
            <button type="button" wire:click="closeEliminarFilaModal"
                class="px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar
            </button>

            <button type="button" wire:click="confirmarEliminarFilaConPassword"
                class="px-4 py-2 rounded-lg cursor-pointer
                       bg-red-600 text-white hover:bg-red-700">
                Eliminar
            </button>
        </div>
    @endslot
</x-ui.modal>
