{{-- resources/views/livewire/admin/facturas.blade.php --}}
@section('title', 'Facturas')

<div>

    {{-- Header + acciones --}}
    @include('livewire.admin.facturas._header')

    {{-- Resumen Totales --}}
    @include('livewire.admin.facturas._summary')

    {{-- Filtros --}}
    @include('livewire.admin.facturas._filters')

    {{-- Alertas --}}
    <div class="mb-4">
        @include('livewire.admin.facturas._alerts')
    </div>

    {{-- Vista Mobile --}}
    @include('livewire.admin.facturas._mobile')

    {{-- Tabla Desktop --}}
    @include('livewire.admin.facturas._table')

    {{-- Modales --}}
    @includeWhen($openFacturaModal, 'livewire.admin.facturas._modal_factura')
    @includeWhen($openPagoModal, 'livewire.admin.facturas._modal_pago')
    @includeWhen($openEliminarFacturaModal, 'livewire.admin.facturas._modal_eliminar_factura')

    {{-- VISOR FOTO --}}
    <div wire:key="foto-bg-{{ $openFotoModal ? '1' : '0' }}-{{ md5($fotoUrl ?? '') }}">
        <x-ui.foto-zoom-modal :open="$openFotoModal" :url="$fotoUrl" onClose="closeFoto" title="Comprobante adjunto"
            subtitle="Pasa el cursor para ampliar y mover" maxWidth="max-w-5xl" />
    </div>
</div>
