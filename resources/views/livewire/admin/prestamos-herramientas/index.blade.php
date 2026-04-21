@section('title', 'Salidas y Retornos')
<div>

    @include('livewire.admin.prestamos-herramientas.sections.header')
    @include('livewire.admin.prestamos-herramientas.sections.filters')
    @include('livewire.admin.prestamos-herramientas.sections.table')
    @include('livewire.admin.prestamos-herramientas.modals.prestamo-modal')
    @include('livewire.admin.prestamos-herramientas.modals.devolucion-modal')
    @include('livewire.admin.prestamos-herramientas.modals.baja-modal')
    @include('livewire.admin.prestamos-herramientas.modals.ver-modal')
    @include('livewire.admin.prestamos-herramientas.modals.visor-fotos')

</div>
