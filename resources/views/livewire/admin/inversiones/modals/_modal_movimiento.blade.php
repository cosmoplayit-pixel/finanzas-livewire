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

</div>
