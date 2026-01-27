{{-- resources/views/livewire/admin/facturas.blade.php --}}
@section('title', 'Facturas')

<div class="p-0 md:p-6 space-y-4">

    {{-- Header + acciones --}}
    @include('livewire.admin.facturas._header')

    {{-- Filtros --}}
    @include('livewire.admin.facturas._filters')

    {{-- Alertas --}}
    @include('livewire.admin.facturas._alerts')

    {{-- Vista Mobile --}}
    @include('livewire.admin.facturas._mobile')

    {{-- Resumen Totales --}}
    @include('livewire.admin.facturas._summary')

    {{-- Tabla Desktop --}}
    @include('livewire.admin.facturas._table')

    {{-- Modales --}}
    @includeWhen($openFacturaModal, 'livewire.admin.facturas._modal_factura')
    @includeWhen($openPagoModal, 'livewire.admin.facturas._modal_pago')

</div>
