<section class="section">
    @section('title', 'Préstamos y Devoluciones')

    {{-- HEADER --}}
    @include('livewire.admin.prestamos-herramientas.sections.header')

    {{-- FILTROS --}}
    @include('livewire.admin.prestamos-herramientas.sections.filters')

    {{-- TABLA --}}
    @include('livewire.admin.prestamos-herramientas.sections.table')

    {{-- MODALES --}}
    @include('livewire.admin.prestamos-herramientas.modals.prestamo-modal')
    @include('livewire.admin.prestamos-herramientas.modals.devolucion-modal')
    @include('livewire.admin.prestamos-herramientas.modals.baja-modal')
    @include('livewire.admin.prestamos-herramientas.modals.ver-modal')
    @include('livewire.admin.prestamos-herramientas.modals.visor-fotos')

</section>
