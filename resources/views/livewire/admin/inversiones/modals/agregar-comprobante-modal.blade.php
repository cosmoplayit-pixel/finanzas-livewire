<div>
    <x-ui.modal wire:key="agregar-comprobante-inv-{{ $open ? '1' : '0' }}"
        model="open" title="Agregar comprobante" maxWidth="sm:max-w-sm"
        onClose="cerrar">

        <x-ui.scanner model="file" label="Foto (JPG, PNG o PDF, máx. 5 MB)" :file="$file" />

        @error('file')
            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror

        <x-slot:footer>
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="cerrar"
                    class="px-4 py-2 cursor-pointer rounded-lg border border-gray-300 dark:border-neutral-700
                           text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-800">
                    Cancelar
                </button>
                <button type="button" wire:click="guardar"
                    wire:loading.attr="disabled" wire:target="guardar,file"
                    class="px-4 py-2 cursor-pointer rounded-lg text-white bg-emerald-600 hover:bg-emerald-700
                           disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="guardar">Guardar</span>
                    <span wire:loading wire:target="guardar">Guardando…</span>
                </button>
            </div>
        </x-slot:footer>

    </x-ui.modal>
</div>
