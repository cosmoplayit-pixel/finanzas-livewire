{{-- resources/views/livewire/admin/inversiones/modals/_modal_movimiento.blade.php --}}

<div>
    <x-ui.modal wire:key="inversion-movimientos-{{ $openMovimientosModal ? 'open' : 'closed' }}"
        model="openMovimientosModal" title="Movimientos de inversión" maxWidth="sm:max-w-2xl md:max-w-7xl"
        maxHeight="sm:max-h-[95vh]" onClose="closeMovimientos">
        <div class="space-y-3">

            {{-- DESKTOP --}}
            <div class="hidden md:block">
                @if (!$isBanco)
                    @include('livewire.admin.inversiones.modals.movimiento._movimientos_privado_desktop')
                @else
                    @include('livewire.admin.inversiones.modals.movimiento._movimientos_banco_desktop')
                @endif
            </div>

            {{-- MOBILE --}}
            <div class="md:hidden">
                @if (!$isBanco)
                    @include('livewire.admin.inversiones.modals.movimiento._movimientos_privado_mobile')
                @else
                    @include('livewire.admin.inversiones.modals.movimiento._movimientos_banco_mobile')
                @endif
            </div>

        </div>
    </x-ui.modal>


    @include('livewire.admin.inversiones.listeners._modal_eliminar_fila')
    @include('livewire.admin.inversiones.listeners._modal_eliminar_todo')
    @include('livewire.admin.inversiones.listeners._modal_foto')

    {{-- MODAL AGREGAR FOTO --}}
    <x-ui.modal wire:key="agregar-foto-{{ $openAgregarFoto ? '1' : '0' }}" model="openAgregarFoto"
        title="Agregar imagen comprobante" maxWidth="sm:max-w-sm" onClose="cerrarAgregarFoto">

        <x-ui.scanner model="agregarFotoFile" label="Foto (JPG, PNG o PDF, máx. 5 MB)"
            :file="$agregarFotoFile" />

        @error('agregarFotoFile')
            <div class="text-red-600 text-xs">{{ $message }}</div>
        @enderror

        <x-slot:footer>
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="cerrarAgregarFoto"
                    class="px-4 py-2 cursor-pointer rounded-lg border border-gray-300 dark:border-neutral-700
                           text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-800">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarFotoMovimiento"
                    wire:loading.attr="disabled" wire:target="guardarFotoMovimiento,agregarFotoFile"
                    class="px-4 py-2 cursor-pointer rounded-lg text-white bg-emerald-600 hover:bg-emerald-700
                           disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="guardarFotoMovimiento">Guardar</span>
                    <span wire:loading wire:target="guardarFotoMovimiento">Guardando…</span>
                </button>
            </div>
        </x-slot:footer>

    </x-ui.modal>

</div>
