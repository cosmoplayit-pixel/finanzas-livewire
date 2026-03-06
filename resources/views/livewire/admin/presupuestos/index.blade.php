@section('title', 'Presupuestos y Rendiciones')

<div class="p-0 md:p-6 space-y-4">

    @include('livewire.admin.presupuestos.sections._header')
    @include('livewire.admin.presupuestos.sections._alerts')
    @include('livewire.admin.presupuestos.sections._summary')
    @include('livewire.admin.presupuestos.sections._filters')
    @include('livewire.admin.presupuestos.sections._table')
    @include('livewire.admin.presupuestos.sections._mobile_table')

    @include('livewire.admin.presupuestos.modals._modal_presupuesto')
    @include('livewire.admin.presupuestos.modals._modal_editor_rendicion')
    @include('livewire.admin.presupuestos.modals._modal_movimiento')
    @include('livewire.admin.presupuestos.modals._modal_eliminar_rendicion')

    @include('livewire.admin.presupuestos.listeners._modal_foto')

</div>
