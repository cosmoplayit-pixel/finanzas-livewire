@section('title', 'Presupuestos y Rendiciones')

<div class="p-0 md:p-6 space-y-4">

    {{-- HEADER --}}
    @include('livewire.admin.presupuestos._header')

    {{-- ALERTAS --}}
    @include('livewire.admin.presupuestos._alerts')

    {{-- FILTROS --}}
    @include('livewire.admin.presupuestos._filters')

    {{-- TABLA PRINCIPAL + PANEL --}}
    @include('livewire.admin.presupuestos.._table')

    {{-- MODALES --}}
    @include('livewire.admin.presupuestos._modal_presupuesto')
    @include('livewire.admin.presupuestos._modal_editor_rendicion')
    @include('livewire.admin.presupuestos._modal_foto')
</div>
