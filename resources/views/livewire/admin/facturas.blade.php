{{-- resources/views/livewire/admin/facturas.blade.php --}}
@section('title', 'Facturas')

<div class="p-0 md:p-6 space-y-4" :title="__('Dashboard')">

    {{-- HEADER (RESPONSIVE) --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold">Facturas</h1>

        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto sm:items-center">


            {{-- Nueva factura --}}
            @can('facturas.create')
                <button wire:click="openCreateFactura" wire:loading.attr="disabled" wire:target="openCreateFactura"
                    class="w-full sm:w-auto px-4 py-2 rounded
                           bg-black text-white
                           hover:bg-gray-800 hover:text-white
                           transition-colors duration-150
                           cursor-pointer
                           disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="openCreateFactura">Nueva factura</span>
                    <span wire:loading wire:target="openCreateFactura">Abriendo…</span>
                </button>
            @endcan
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="flex flex-col gap-3 md:flex-row md:items-center">
        {{-- Buscar --}}
        <input type="search" wire:model.live="search" placeholder="Buscar por Factura, Proyecto o Entidad..."
            class="w-full md:w-72 lg:w-md border rounded px-3 py-2" autocomplete="off" />

        {{-- Selects derecha --}}
        <div class="flex flex-col sm:flex-row gap-3 md:ml-auto w-full md:w-auto">
            {{-- Estado --}}
            <select wire:model.live="status"
                class="w-full sm:w-auto border rounded px-3 py-2
                       bg-white text-gray-900 border-gray-300
                       dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                       focus:outline-none focus:ring-2 focus:ring-offset-0
                       focus:ring-gray-300 dark:focus:ring-neutral-600">
                <option value="all">Todos</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>

            {{-- PerPage --}}
            <select wire:model.live="perPage"
                class="w-full sm:w-auto border rounded px-3 py-2
                       bg-white text-gray-900 border-gray-300
                       dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                       focus:outline-none focus:ring-2 focus:ring-offset-0
                       focus:ring-gray-300 dark:focus:ring-neutral-600">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    {{-- ALERTAS (LIGHT/DARK) --}}
    @if (session('success'))
        <div class="p-3 rounded bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- =========================
        MOBILE: CARDS (md:hidden)
    ========================== --}}
    <div class="space-y-3 md:hidden">
        @forelse ($facturas as $f)
            <div x-data="{ open: false }"
                class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold truncate">
                            {{ $f->numero ?? 'Factura #' . $f->id }}
                        </div>

                        <div class="text-xs text-gray-500 dark:text-neutral-400 truncate mt-1">
                            {{ $f->proyecto?->nombre ?? '—' }}
                        </div>

                        <div class="text-xs text-gray-500 dark:text-neutral-400 truncate mt-1">
                            {{ $f->proyecto?->entidad?->nombre ?? '—' }}
                        </div>
                    </div>

                    <div class="shrink-0 text-right">
                        <div class="text-sm font-semibold">
                            Bs {{ number_format((float) $f->monto_facturado, 2, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-neutral-400">
                            {{ $f->fecha_emision ? $f->fecha_emision->format('Y-m-d') : '—' }}
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="mt-4 flex gap-2">
                    @can('facturas.pay')
                        <button wire:click="openPago({{ $f->id }})"
                            class="w-full px-3 py-1 rounded border border-gray-300 hover:bg-gray-50
                                   dark:border-neutral-700 dark:hover:bg-neutral-800">
                            Registrar pago
                        </button>
                    @endcan

                    <button type="button"
                        class="w-full px-3 py-1 rounded text-sm font-medium
                               border border-gray-300 text-gray-700 hover:bg-gray-50
                               dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800"
                        @click="open = !open">
                        <span x-show="!open">Ver pagos</span>
                        <span x-show="open" x-cloak>Ocultar pagos</span>
                    </button>
                </div>

                {{-- Pagos (cada pago en una fila) --}}
                <div x-show="open" x-cloak class="mt-4 space-y-2">
                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        Pagos realizados: {{ $f->pagos?->count() ?? 0 }}
                    </div>

                    @forelse(($f->pagos ?? collect()) as $pg)
                        <div class="border rounded p-3 bg-gray-50 dark:bg-neutral-800/50 dark:border-neutral-700">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-medium">
                                        {{ $pg->fecha_pago ? $pg->fecha_pago->format('Y-m-d H:i') : '—' }}
                                    </div>

                                    <div class="text-xs text-gray-600 dark:text-neutral-300 truncate mt-1">
                                        Destino:
                                        {{ $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? '—') }}
                                        @php
                                            $cuenta =
                                                $pg->destino_numero_cuenta_snapshot ??
                                                ($pg->banco?->numero_cuenta ?? null);
                                            $moneda = $pg->destino_moneda_snapshot ?? ($pg->banco?->moneda ?? null);
                                        @endphp
                                        @if ($cuenta)
                                            | {{ $cuenta }}{{ $moneda ? ' | ' . $moneda : '' }}
                                        @endif
                                    </div>

                                    <div class="text-xs text-gray-600 dark:text-neutral-300 truncate mt-1">
                                        Método: {{ $pg->metodo_pago ?? '—' }}
                                        | Operación: {{ $pg->nro_operacion ?? '—' }}
                                    </div>

                                    @if ($pg->observacion)
                                        <div class="text-xs text-gray-500 dark:text-neutral-400 truncate mt-1"
                                            title="{{ $pg->observacion }}">
                                            Obs: {{ $pg->observacion }}
                                        </div>
                                    @endif
                                </div>

                                <div class="shrink-0 text-right">
                                    <div class="text-sm font-semibold">
                                        Bs {{ number_format((float) $pg->monto, 2, ',', '.') }}
                                    </div>

                                    @if ($pg->tipo === 'normal')
                                        <span
                                            class="inline-block mt-1 px-2 py-1 rounded text-xs bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200">
                                            Normal
                                        </span>
                                    @else
                                        <span
                                            class="inline-block mt-1 px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-200">
                                            Retención
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div
                            class="border rounded p-3 text-sm text-gray-600 dark:text-neutral-300 dark:border-neutral-800">
                            No hay pagos registrados.
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="border rounded p-4 text-sm text-gray-600 dark:text-neutral-300 dark:border-neutral-800">
                Sin resultados.
            </div>
        @endforelse
    </div>

    {{-- =========================
    TABLET + DESKTOP: TABLA (COMPACTA)
    Agrupa datos como "DESTINO" en 1 columna para PROYECTO y 1 para FACTURA
========================== --}}
    <div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed text-sm min-w-[980px]">
                <thead
                    class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-200">
                    <tr class="text-left">

                        {{-- Toggle + ID --}}
                        <th class="w-[50px] text-center p-2 select-none whitespace-nowrap">
                            ID
                        </th>

                        {{--  PROYECTO (AGRUPADO) --}}
                        <th class="w-[360px] p-2 select-none whitespace-nowrap">
                            Proyecto
                        </th>

                        {{-- FACTURA (AGRUPADO) --}}
                        <th class="w-[420px] p-2 select-none whitespace-nowrap">
                            Factura
                        </th>

                        {{-- Estado --}}
                        <th class="w-[140px] p-2 select-none whitespace-nowrap text-center">
                            Estado
                        </th>

                        {{-- Saldo --}}
                        <th class="w-[110px] p-2 select-none whitespace-nowrap text-center">
                            Saldo
                        </th>

                        @can('facturas.pay')
                            <th class="w-[160px] p-2 whitespace-nowrap text-center">
                                Acciones
                            </th>
                        @endcan
                    </tr>
                </thead>

                @foreach ($facturas as $f)
                    <tbody x-data="{ open: false }" class="divide-y divide-gray-200 dark:divide-neutral-200">
                        <tr class="hover:bg-gray-100 dark:hover:bg-neutral-900 text-gray-700 dark:text-neutral-200">

                            {{-- ID + toggle (+) SOLO para pagos --}}
                            <td class="p-1 whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2 h-full">
                                    <button type="button"
                                        class="w-5 h-5 inline-flex items-center justify-center rounded border border-gray-300 text-gray-600
                                        hover:bg-gray-100 hover:text-gray-800
                                        dark:border-neutral-700 dark:text-neutral-300
                                        dark:hover:bg-neutral-700 dark:hover:text-white
                                        transition cursor-pointer"
                                        @click.stop="open = !open" :aria-expanded="open">
                                        <span x-show="!open">+</span>
                                        <span x-show="open" x-cloak>−</span>
                                    </button>
                                    <span class="text-sm">{{ $f->id }}</span>
                                </div>
                            </td>


                            {{-- =========================
                            PROYECTO (AGRUPADO COMO DESTINO)
                        ========================== --}}
                            <td class="p-2 align-top">
                                <div class="space-y-1">

                                    {{-- Línea principal (Proyecto) --}}
                                    <div class="font-medium truncate" title="{{ $f->proyecto?->nombre ?? '-' }}">
                                        {{ $f->proyecto?->nombre ?? '—' }}
                                    </div>

                                    {{-- Entidad --}}
                                    <div class="text-xs text-gray-500 dark:text-neutral-400 truncate"
                                        title="{{ $f->proyecto?->entidad?->nombre ?? '-' }}">
                                        Entidad: {{ $f->proyecto?->entidad?->nombre ?? '—' }}
                                    </div>

                                    {{-- Retención % + Contrato --}}
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                                        Retención:
                                        <span class="font-semibold text-gray-700 dark:text-neutral-200">
                                            {{ number_format((float) ($f->proyecto?->retencion ?? 0), 2, ',', '.') }}%
                                        </span>

                                    </div>
                                    {{-- Retención % + Contrato --}}
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">

                                        Contrato:
                                        <span class="font-semibold text-gray-700 dark:text-neutral-200">
                                            Bs {{ number_format((float) ($f->proyecto?->monto ?? 0), 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- =========================
                            FACTURA (AGRUPADO COMO DESTINO)
                        ========================== --}}
                            <td class="p-2 align-top">
                                <div class="space-y-1">

                                    {{-- Línea principal (Nro) --}}
                                    <div class="font-medium truncate" title="{{ $f->numero ?? '-' }}">
                                        Nro: {{ $f->numero ?? '—' }}
                                    </div>

                                    {{-- Fecha --}}
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                                        Fecha: {{ $f->fecha_emision ? $f->fecha_emision->format('Y-m-d') : '—' }}
                                    </div>

                                    {{-- Monto + Retención monto --}}
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                                        Monto:
                                        <span class="font-semibold text-gray-700 dark:text-neutral-200">
                                            Bs {{ number_format((float) $f->monto_facturado, 2, ',', '.') }}
                                        </span>
                                        <span class="mx-1">|</span>
                                        Ret. Factura:
                                        <span class="font-semibold text-gray-700 dark:text-neutral-200">
                                            Bs {{ number_format((float) ($f->retencion ?? 0), 2, ',', '.') }}
                                        </span>
                                    </div>

                                    {{-- Detalle / Observación --}}
                                    <div class="text-xs text-gray-500 dark:text-neutral-400 truncate"
                                        title="{{ $f->observacion ?? '—' }}">
                                        Detalle: {{ $f->observacion ?? '—' }}
                                    </div>
                                </div>
                            </td>

                            {{-- ESTADO (2 etiquetas) --}}
                            <td class="p-2 whitespace-nowrap">
                                @php
                                    $cerrada = \App\Services\FacturaFinance::estaCerrada($f);
                                    $estadoPago = \App\Services\FacturaFinance::estadoPagoLabel($f);
                                    $estadoRet = \App\Services\FacturaFinance::estadoRetencionLabel($f);
                                @endphp

                                <div class="flex items-center justify-center gap-2 flex-wrap h-full">
                                    {{-- Estado principal --}}
                                    @if ($cerrada)
                                        <span
                                            class="px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">
                                            Completado
                                        </span>
                                    @else
                                        @if ($estadoPago === 'Pendiente')
                                            <span
                                                class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-neutral-200">
                                                Pagos 0%
                                            </span>
                                        @elseif ($estadoPago === 'Parcial')
                                            @php
                                                $pct = \App\Services\FacturaFinance::porcentajePago($f);
                                            @endphp

                                            <span
                                                class="px-2 py-1 rounded text-xs font-semibold
                                                {{ $pct == 100
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200'
                                                    : ($pct > 0
                                                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200'
                                                        : 'bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-neutral-200') }}">
                                                Pagos {{ $pct }}%
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200">
                                                Pagada (Neto)
                                            </span>
                                        @endif
                                    @endif

                                    {{-- Badge retención (si aplica) --}}
                                    @if ($estadoRet)
                                        @if ($estadoRet === 'Retención pendiente')
                                            <span
                                                class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-200">
                                                {{ $estadoRet }}
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-1 rounded text-xs bg-lime-100 text-lime-800 dark:bg-lime-500/20 dark:text-lime-200">
                                                {{ $estadoRet }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </td>

                            {{-- SALDO (NETO + RETENCIÓN) --}}
                            <td class="p-2 whitespace-nowrap">
                                @php
                                    $saldo = \App\Services\FacturaFinance::saldo($f);
                                    $retPend = \App\Services\FacturaFinance::retencionPendiente($f);
                                @endphp

                                <div class="flex flex-col items-center justify-center gap-0.5 h-full">

                                    {{-- Saldo normal --}}
                                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-200">
                                        Bs {{ number_format((float) $saldo, 2, ',', '.') }}
                                    </div>

                                    {{-- Retención pendiente --}}
                                    @if ($retPend > 0)
                                        <div class="text-xs text-yellow-700 dark:text-yellow-300">
                                            Ret.: Bs {{ number_format((float) $retPend, 2, ',', '.') }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Acciones --}}
                            @can('facturas.pay')
                                <td class="p-2 whitespace-nowrap align-top">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="openPago({{ $f->id }})"
                                            class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-50
                                               dark:border-neutral-700 dark:hover:bg-neutral-800">
                                            Registrar pago
                                        </button>
                                    </div>
                                </td>
                            @endcan
                        </tr>

                        {{--  DETALLE EXPANDIBLE: SOLO pagos (cada pago en una fila) --}}
                        @php
                            // Columnas principales:
                            // ID, Proyecto, Factura, Estado, Pagos = 5
                            // + Acciones si puede pagar
                            $colspan = 5 + (auth()->user()->can('facturas.pay') ? 1 : 0);
                        @endphp

                        <tr x-show="open" x-cloak
                            class="bg-gray-100/60 dark:bg-neutral-900/40 border-b border-gray-200 dark:border-neutral-200">
                            <td class="px-5 py-2" colspan="{{ $colspan }}">
                                <div class="space-y-3 text-sm">

                                    {{-- Pagos en filas --}}
                                    <div
                                        class="border rounded bg-white dark:bg-neutral-900 dark:border-neutral-800 overflow-hidden">
                                        <table class="w-full table-fixed text-sm">
                                            <thead
                                                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-800">
                                                <tr class="text-left">

                                                    {{-- # --}}
                                                    <th class="p-2 w-[5%] text-center whitespace-nowrap">#</th>

                                                    {{-- Destino --}}
                                                    <th class="p-2 w-[30%] whitespace-nowrap">Destino de Banco</th>

                                                    {{-- Fecha --}}
                                                    <th class="p-2 w-[20%] whitespace-nowrap">Fecha de Pago</th>

                                                    {{-- Método --}}
                                                    <th class="p-2 w-[15%] whitespace-nowrap">Método de Pago</th>

                                                    {{-- Monto --}}
                                                    <th class="p-2 w-[15%] whitespace-nowrap text-right">Monto Pagado
                                                    </th>

                                                    {{-- Tipo --}}
                                                    <th class="p-2 w-[15%] whitespace-nowrap text-center">Tipo de Pago
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
                                                @forelse(($f->pagos ?? collect()) as $pg)
                                                    <tr class="text-gray-700 dark:text-neutral-200">

                                                        {{-- Numeración --}}
                                                        <td class="p-2 text-center font-mono text-xs">
                                                            {{ $loop->iteration }}
                                                        </td>

                                                        {{-- Destino --}}
                                                        <td class="p-2 min-w-0">
                                                            <div class="truncate">
                                                                {{ $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? '—') }}

                                                                @php
                                                                    $cuenta =
                                                                        $pg->destino_numero_cuenta_snapshot ??
                                                                        ($pg->banco?->numero_cuenta ?? null);
                                                                    $moneda =
                                                                        $pg->destino_moneda_snapshot ??
                                                                        ($pg->banco?->moneda ?? null);
                                                                @endphp

                                                                @if ($cuenta)
                                                                    <span
                                                                        class="text-xs text-gray-500 dark:text-neutral-400">
                                                                        |
                                                                        {{ $cuenta }}{{ $moneda ? ' | ' . $moneda : '' }}
                                                                    </span>
                                                                @endif
                                                            </div>

                                                            @if ($pg->destino_titular_snapshot)
                                                                <div class="text-xs text-gray-500 dark:text-neutral-400 truncate"
                                                                    title="{{ $pg->destino_titular_snapshot }}">
                                                                    Titular: {{ $pg->destino_titular_snapshot }}
                                                                </div>
                                                            @endif

                                                            @if ($pg->observacion)
                                                                <div class="text-xs text-gray-500 dark:text-neutral-400 truncate"
                                                                    title="{{ $pg->observacion }}">
                                                                    Obs: {{ $pg->observacion }}
                                                                </div>
                                                            @endif
                                                        </td>

                                                        {{-- Fecha --}}
                                                        <td class="p-2 whitespace-nowrap">
                                                            {{ $pg->fecha_pago ? $pg->fecha_pago->format('Y-m-d H:i') : '—' }}
                                                        </td>

                                                        {{-- Método --}}
                                                        <td class="p-2 whitespace-nowrap">
                                                            {{ $pg->metodo_pago ?? '—' }}
                                                        </td>

                                                        {{-- Monto --}}
                                                        <td class="p-2 whitespace-nowrap text-right font-semibold">
                                                            Bs {{ number_format((float) $pg->monto, 2, ',', '.') }}
                                                        </td>

                                                        {{-- Tipo --}}
                                                        <td class="p-2 whitespace-nowrap text-center">
                                                            @if ($pg->tipo === 'normal')
                                                                <span
                                                                    class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200">
                                                                    Pago Normal
                                                                </span>
                                                            @else
                                                                <span
                                                                    class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-200">
                                                                    Pago de Retención
                                                                </span>
                                                            @endif
                                                        </td>

                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="p-3 text-center text-gray-500 dark:text-neutral-400"
                                                            colspan="6">
                                                            No hay pagos registrados para esta factura.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </td>
                        </tr>

                    </tbody>
                @endforeach

                @if ($facturas->count() === 0)
                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                        <tr>
                            <td class="p-4 text-center text-gray-500 dark:text-neutral-400"
                                colspan="{{ 5 + (auth()->user()->can('facturas.pay') ? 1 : 0) }}">
                                Sin resultados.
                            </td>
                        </tr>
                    </tbody>
                @endif
            </table>
        </div>
    </div>





    {{-- PAGINACIÓN --}}
    <div>
        {{ $facturas->links() }}
    </div>

    {{-- =========================================
    MODAL: NUEVA FACTURA (create)
========================================== --}}
    @if ($openFacturaModal)
        <div wire:key="facturas-create-modal" class="fixed inset-0 z-50">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/50 dark:bg-black/70" wire:click="closeFactura"></div>

            {{-- Dialog --}}
            <div class="relative h-full w-full flex items-end sm:items-center justify-center p-0 sm:p-4">
                <div
                    class="w-full
                       h-[100dvh] sm:h-auto
                       sm:max-h-[90vh]
                       sm:max-w-xl md:max-w-2xl
                       bg-white dark:bg-neutral-900
                       text-gray-700 dark:text-neutral-200
                       border border-gray-200 dark:border-neutral-800
                       rounded-none sm:rounded-xl
                       overflow-hidden shadow-xl">

                    {{-- Header (sticky) --}}
                    <div
                        class="sticky top-0 z-10 px-5 py-4 flex justify-between items-center
                           bg-gray-50 dark:bg-neutral-900
                           border-b border-gray-200 dark:border-neutral-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
                            Nueva Factura
                        </h2>

                        <button type="button" wire:click="closeFactura"
                            class="inline-flex items-center justify-center size-9 rounded-md
                               text-gray-500 hover:text-gray-900 hover:bg-gray-100
                               dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800">
                            ✕
                        </button>
                    </div>

                    {{-- Body (scroll) --}}
                    <div
                        class="p-5 space-y-4 overflow-y-auto
                            h-[calc(100dvh-64px-76px)] sm:h-auto sm:max-h-[calc(90vh-64px-76px)]">

                        {{-- Proyecto --}}
                        <div>
                            <label class="block text-sm mb-1">Proyecto</label>
                            <select wire:model.live="proyecto_id"
                                class="w-full rounded border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-300 dark:focus:ring-neutral-700">
                                <option value="">Seleccione...</option>
                                @foreach ($proyectos as $p)
                                    <option value="{{ $p->id }}" title="{{ $p->nombre }}">
                                        {{ \Illuminate\Support\Str::limit($p->nombre, 40) }}
                                        {{ $p->entidad?->nombre ? ' — ' . \Illuminate\Support\Str::limit($p->entidad->nombre, 30) : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proyecto_id')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            {{-- Número --}}
                            <div>
                                <label class="block text-sm mb-1">Nro. Factura</label>
                                <input wire:model="numero" autocomplete="off"
                                    class="w-full rounded border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-300 dark:focus:ring-neutral-700" />
                                @error('numero')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Fecha emisión --}}
                            <div>
                                <label class="block text-sm mb-1">Fecha Emisión</label>
                                <input type="date" wire:model="fecha_emision"
                                    class="w-full rounded border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-300 dark:focus:ring-neutral-700" />
                                @error('fecha_emision')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Monto --}}
                        <div>
                            <label class="block text-sm mb-1">Monto Facturado</label>
                            <input type="number" step="0.01" min="0" wire:model.live="monto_facturado"
                                class="w-full rounded border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-300 dark:focus:ring-neutral-700" />
                            @error('monto_facturado')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ✅ Retención dinámica (desde proyecto) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            {{-- % Retención (bloqueado) --}}
                            <div>
                                <label class="block text-sm mb-1">Retención (%)</label>
                                <input type="number" step="0.01" min="0"
                                    wire:model.live="retencion_porcentaje" disabled
                                    class="w-full rounded border px-3 py-2
                                       bg-gray-100 dark:bg-neutral-800
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       opacity-80 cursor-not-allowed
                                       focus:outline-none" />
                            </div>

                            {{-- Retención (Monto) calculado --}}
                            <div>
                                <label class="block text-sm mb-1">Retención (Monto)</label>
                                <input type="number" step="0.01"
                                    value="{{ number_format((float) ($retencion_monto ?? 0), 2, '.', '') }}" disabled
                                    class="w-full rounded border px-3 py-2
                                       bg-gray-100 dark:bg-neutral-800
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       opacity-80 cursor-not-allowed
                                       focus:outline-none" />
                            </div>
                        </div>

                        {{-- Neto (recomendado) --}}
                        <div>
                            <label class="block text-sm mb-1">Monto Neto (Facturado - Retención)</label>
                            <input type="number" step="0.01"
                                value="{{ number_format((float) ($monto_neto ?? 0), 2, '.', '') }}" disabled
                                class="w-full rounded border px-3 py-2
                                   bg-gray-100 dark:bg-neutral-800
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   opacity-80 cursor-not-allowed
                                   focus:outline-none" />
                        </div>

                        {{-- Observación --}}
                        <div>
                            <label class="block text-sm mb-1">Observación</label>
                            <textarea wire:model="observacion_factura" rows="3"
                                class="w-full rounded border px-3 py-2
                                   bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-300 dark:focus:ring-neutral-700"></textarea>
                            @error('observacion_factura')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer (sticky) --}}
                    <div
                        class="sticky bottom-0 px-5 py-4 flex justify-end gap-2
                            bg-gray-50 dark:bg-neutral-900
                            border-t border-gray-200 dark:border-neutral-800">
                        <button wire:click="closeFactura"
                            class="px-4 py-2 rounded border
                               border-gray-300 dark:border-neutral-700
                               text-gray-700 dark:text-neutral-200
                               hover:bg-gray-100 dark:hover:bg-neutral-800">
                            Cancelar
                        </button>

                        <button wire:click="saveFactura"
                            class="px-4 py-2 rounded bg-black text-white hover:opacity-90">
                            Guardar
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- =========================================
        MODAL: REGISTRAR PAGO
    ========================================== --}}
    @if ($openPagoModal)
        <div wire:key="facturas-pago-modal" class="fixed inset-0 z-50">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/50 dark:bg-black/70" wire:click="closePago"></div>

            {{-- Dialog --}}
            <div class="relative h-full w-full flex items-end sm:items-center justify-center p-0 sm:p-4">
                <div
                    class="w-full
                           h-[100dvh] sm:h-auto
                           sm:max-h-[90vh]
                           sm:max-w-xl md:max-w-2xl
                           bg-white dark:bg-neutral-900
                           text-gray-700 dark:text-neutral-200
                           border border-gray-200 dark:border-neutral-800
                           rounded-none sm:rounded-xl
                           overflow-hidden shadow-xl">

                    {{-- Header (sticky) --}}
                    <div
                        class="sticky top-0 z-10 px-5 py-4 flex justify-between items-center
                               bg-gray-50 dark:bg-neutral-900
                               border-b border-gray-200 dark:border-neutral-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
                            Registrar Pago
                        </h2>

                        <button type="button" wire:click="closePago"
                            class="inline-flex items-center justify-center size-9 rounded-md
                                   text-gray-500 hover:text-gray-900 hover:bg-gray-100
                                   dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800">
                            ✕
                        </button>
                    </div>

                    {{-- Body (scroll) --}}
                    <div
                        class="p-5 space-y-4 overflow-y-auto
                                h-[calc(100dvh-64px-76px)] sm:h-auto sm:max-h-[calc(90vh-64px-76px)]">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            {{-- Tipo --}}
                            <div>
                                <label class="block text-sm mb-1">Tipo</label>
                                <select wire:model="tipo"
                                    class="w-full rounded border px-3 py-2
                                           bg-white dark:bg-neutral-900
                                           border-gray-300 dark:border-neutral-700
                                           text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2
                                           focus:ring-gray-300 dark:focus:ring-neutral-700">
                                    <option value="normal">Normal</option>
                                    <option value="retencion">Retención</option>
                                </select>
                                @error('tipo')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Método --}}
                            <div>
                                <label class="block text-sm mb-1">Método de pago</label>
                                <select wire:model="metodo_pago"
                                    class="w-full rounded border px-3 py-2
                                           bg-white dark:bg-neutral-900
                                           border-gray-300 dark:border-neutral-700
                                           text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2
                                           focus:ring-gray-300 dark:focus:ring-neutral-700">
                                    <option value="transferencia">Transferencia</option>
                                    <option value="deposito">Depósito</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="qr">QR</option>
                                    <option value="otro">Otro</option>
                                </select>
                                @error('metodo_pago')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Banco destino --}}
                        <div>
                            <label class="block text-sm mb-1">Banco destino</label>
                            <select wire:model="banco_id"
                                class="w-full rounded border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-300 dark:focus:ring-neutral-700">
                                <option value="">Seleccione...</option>
                                @foreach ($bancos as $b)
                                    <option value="{{ $b->id }}">
                                        {{ $b->nombre }} | {{ $b->numero_cuenta }} | {{ $b->moneda }}
                                        {{ $b->tipo_cuenta ? '| ' . $b->tipo_cuenta : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('banco_id')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            {{-- Monto --}}
                            <div>
                                <label class="block text-sm mb-1">Monto</label>
                                <input type="number" step="0.01" min="0" wire:model.live="monto"
                                    class="w-full rounded border px-3 py-2
                                           bg-white dark:bg-neutral-900
                                           border-gray-300 dark:border-neutral-700
                                           text-gray-900 dark:text-neutral-100
                                           focus:outline-none focus:ring-2
                                           focus:ring-gray-300 dark:focus:ring-neutral-700" />
                                @error('monto')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Nro operación --}}
                            <div>
                                <label class="block text-sm mb-1">Nro. Operación</label>
                                <input wire:model="nro_operacion" autocomplete="off"
                                    class="w-full rounded border px-3 py-2
                                           bg-white dark:bg-neutral-900
                                           border-gray-300 dark:border-neutral-700
                                           text-gray-900 dark:text-neutral-100
                                           placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                           focus:outline-none focus:ring-2
                                           focus:ring-gray-300 dark:focus:ring-neutral-700" />
                                @error('nro_operacion')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Observación --}}
                        <div>
                            <label class="block text-sm mb-1">Observación</label>
                            <textarea wire:model="observacion" rows="3"
                                class="w-full rounded border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       placeholder:text-gray-400 dark:placeholder:text-neutral-500
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-300 dark:focus:ring-neutral-700"></textarea>
                            @error('observacion')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer (sticky) --}}
                    <div
                        class="sticky bottom-0 px-5 py-4 flex justify-end gap-2
                                bg-gray-50 dark:bg-neutral-900
                                border-t border-gray-200 dark:border-neutral-800">
                        <button wire:click="closePago"
                            class="px-4 py-2 rounded border
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-700 dark:text-neutral-200
                                   hover:bg-gray-100 dark:hover:bg-neutral-800">
                            Cancelar
                        </button>

                        <button wire:click="savePago" class="px-4 py-2 rounded bg-black text-white hover:opacity-90">
                            Guardar pago
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
