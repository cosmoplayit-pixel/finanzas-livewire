@section('title', 'Creditos e Inversiones')
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

    <livewire:admin.inversiones.modals.agregar-comprobante-modal />

    @include('livewire.admin.inversiones.modals._modal_edit_titular')

</div>
