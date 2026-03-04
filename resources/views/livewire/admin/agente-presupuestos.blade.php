@section('title', 'Presupuestos y Rendiciones')

<div>

    {{-- HEADER --}}
    @include('livewire.admin.presupuestos._header')

    {{-- SUMMARY --}}
    @include('livewire.admin.presupuestos._summary')

    {{-- FILTROS --}}
    @include('livewire.admin.presupuestos._filters')

    {{-- ALERTAS --}}
    <div class="mb-4">
        @include('livewire.admin.presupuestos._alerts')
    </div>

    {{-- TABLA PRINCIPAL + PANEL --}}
    @include('livewire.admin.presupuestos._table')
    @include('livewire.admin.presupuestos._mobile_table')

    {{-- MODALES --}}
    @include('livewire.admin.presupuestos._modal_presupuesto')
    @include('livewire.admin.presupuestos._modal_editor_rendicion')
    @include('livewire.admin.presupuestos._modal_foto')
    @include('livewire.admin.presupuestos._modal_movimiento')

</div>
