@section('title', 'Inventario General de Recursos')
<div>

    @include('livewire.admin.herramientas.sections._header')
    @include('livewire.admin.herramientas.sections._alerts')
    @include('livewire.admin.herramientas.sections._stats')
    @include('livewire.admin.herramientas.sections._filters')
    @include('livewire.admin.herramientas.sections._mobile_table')
    @include('livewire.admin.herramientas.sections._desktop_table')
    @include('livewire.admin.herramientas.sections._pagination')

    @include('livewire.admin.herramientas.modals._modal_nueva')
    @include('livewire.admin.herramientas.modals._modal_agregar_stock')
    @include('livewire.admin.herramientas.modals._modal_baja_stock')
    @include('livewire.admin.herramientas.modals._modal_editar')
    @include('livewire.admin.herramientas.modals._modal_detalle')
    @include('livewire.admin.herramientas.modals._modal_zoom_imagen')
    @include('livewire.admin.herramientas.modals._modal_historial_bajas')

</div>
