@section('title', 'Inversiones')
<div>
    @include('livewire.admin.inversiones.sections._header')
    @include('livewire.admin.inversiones.sections._alerts')
    @include('livewire.admin.inversiones.sections._summary')
    @include('livewire.admin.inversiones.sections._filters')
    @include('livewire.admin.inversiones.sections._table')
    @include('livewire.admin.inversiones.sections._mobile_table')
    @include('livewire.admin.inversiones.sections._pagination')

    <livewire:admin.inversiones.modals.create-modal />
    <livewire:admin.inversiones.modals.movimiento-modal />
    <livewire:admin.inversiones.modals.pagar-utilidad-modal />
    <livewire:admin.inversiones.modals.pagar-banco-modal />
    <livewire:admin.inversiones.listeners.delete-movimiento-listener />

    {{-- MODAL AGREGAR COMPROBANTE INVERSIÓN --}}
    <x-ui.modal wire:key="agregar-comprobante-inv-{{ $openAgregarComprobante ? '1' : '0' }}"
        model="openAgregarComprobante" title="Agregar comprobante" maxWidth="sm:max-w-sm"
        onClose="cerrarAgregarComprobante">

        <div class="space-y-3">
            <x-ui.scanner model="agregarComprobanteFile" label="Foto (JPG, PNG o PDF, máx. 5 MB)"
                :file="$agregarComprobanteFile" />

            @error('agregarComprobanteFile')
                <div class="text-red-600 text-xs">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <button type="button" wire:click="cerrarAgregarComprobante"
                class="px-4 py-2 cursor-pointer rounded-lg border border-gray-300 dark:border-neutral-700
                       text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-800">
                Cancelar
            </button>
            <button type="button" wire:click="guardarComprobanteInversion"
                wire:loading.attr="disabled" wire:target="guardarComprobanteInversion,agregarComprobanteFile"
                class="px-4 py-2 cursor-pointer rounded-lg text-white bg-emerald-600 hover:bg-emerald-700
                       disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="guardarComprobanteInversion">Guardar</span>
                <span wire:loading wire:target="guardarComprobanteInversion">Guardando…</span>
            </button>
        </div>

    </x-ui.modal>

</div>
