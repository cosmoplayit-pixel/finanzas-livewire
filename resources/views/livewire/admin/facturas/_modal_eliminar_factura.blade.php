{{-- MODAL: Eliminar factura (requiere contraseña) --}}
<x-ui.modal wire:key="delete-factura-{{ $openEliminarFacturaModal ? 'open' : 'closed' }}" model="openEliminarFacturaModal"
    title="Eliminar factura" maxWidth="sm:max-w-xl" onClose="closeEliminarFacturaModal">

    <div class="space-y-3">
        <div
            class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
            <p class="font-semibold mb-1">⚠️ Esta acción es irreversible.</p>
            <p>Se eliminará la factura y su comprobante adjunto (si tiene). Solo se puede eliminar si <strong>no tiene
                    pagos registrados</strong>.</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-neutral-200 mb-1">
                Confirme con su contraseña
            </label>

            <flux:input wire:model.defer="deleteFacturaPassword" name="deleteFacturaPassword" type="password" required
                autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly')"
                :placeholder="__('Ingresa tu contraseña')" viewable />
            @error('deleteFacturaPassword')
                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @slot('footer')
        <div class="flex justify-end gap-2">
            <button type="button" wire:click="closeEliminarFacturaModal"
                class="px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar
            </button>

            <button type="button" wire:click="confirmarEliminarFactura" wire:loading.attr="disabled"
                wire:target="confirmarEliminarFactura"
                class="px-4 py-2 rounded-lg cursor-pointer bg-red-600 text-white hover:bg-red-700 disabled:opacity-60">
                <span wire:loading.remove wire:target="confirmarEliminarFactura">Eliminar</span>
                <span wire:loading wire:target="confirmarEliminarFactura">Eliminando...</span>
            </button>
        </div>
    @endslot
</x-ui.modal>
