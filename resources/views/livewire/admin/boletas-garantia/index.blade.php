@section('title', 'Boletas de Garantía')
<div class="p-0 md:p-6 space-y-4">

    @include('livewire.admin.boletas-garantia.sections._header')
    @include('livewire.admin.boletas-garantia.sections._alerts')
    @include('livewire.admin.boletas-garantia.sections._filters')
    @include('livewire.admin.boletas-garantia.sections._table')
    @include('livewire.admin.boletas-garantia.sections._mobile_table')
    @include('livewire.admin.boletas-garantia.sections._pagination')

    {{-- MODALES (Livewire, no Blade) --}}
    <livewire:admin.boletas-garantia.modals.create-modal />
    <livewire:admin.boletas-garantia.modals.devolucion-modal />
    <livewire:admin.boletas-garantia.listeners.delete-devolucion-listener />

    {{-- VISOR FOTO --}}
    <div wire:key="foto-bg-{{ $openFotoModal ? '1' : '0' }}-{{ md5($fotoUrl ?? '') }}">
        <x-ui.foto-zoom-modal :open="$openFotoModal" :url="$fotoUrl" onClose="closeFoto" title="Comprobante adjunto"
            subtitle="Pasa el cursor para ampliar y mover" maxWidth="max-w-5xl" />
    </div>

</div>
