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
                <option value="5">5</option>
                <option value="10">10</option>
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

    {{-- MOBILE: CARDS (md:hidden) --}}
    <div class="space-y-3 md:hidden">
        @forelse ($facturas as $f)
            @php
                // Finanzas
                $saldo = \App\Services\FacturaFinance::saldo($f);
                $retPend = \App\Services\FacturaFinance::retencionPendiente($f);

                // Estado
                $cerrada = \App\Services\FacturaFinance::estaCerrada($f);
                $estadoPago = \App\Services\FacturaFinance::estadoPagoLabel($f);
                $estadoRet = \App\Services\FacturaFinance::estadoRetencionLabel($f);

                // % pago (si usas esa función)
                $pct = 0;
                if (!$cerrada && $estadoPago === 'Parcial') {
                    $pct = \App\Services\FacturaFinance::porcentajePago($f);
                }

                // Bloqueo acciones si está 100% cerrada (saldo neto 0 y retención pendiente 0)
                $bloqueado = $saldo <= 0 && $retPend <= 0;
            @endphp

            <div x-data="{ open: false }"
                class="border rounded-lg p-4 bg-white dark:bg-neutral-900 dark:border-neutral-800">
                {{-- Header card: Proyecto + Monto --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        {{-- Proyecto --}}
                        <div class="font-semibold truncate" title="{{ $f->proyecto?->nombre ?? '-' }}">
                            {{ $f->proyecto?->nombre ?? '—' }}
                        </div>

                        {{-- Entidad --}}
                        <div class="text-xs text-gray-500 dark:text-neutral-400 truncate mt-1"
                            title="{{ $f->proyecto?->entidad?->nombre ?? '-' }}">
                            Entidad: {{ $f->proyecto?->entidad?->nombre ?? '—' }}
                        </div>

                        {{-- Retención % + Contrato --}}
                        <div class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                            Retención:
                            <span class="font-semibold text-gray-700 dark:text-neutral-200">
                                {{ number_format((float) ($f->proyecto?->retencion ?? 0), 2, ',', '.') }}%
                            </span>
                            <span class="mx-1">|</span>
                            Contrato:
                            <span class="font-semibold text-gray-700 dark:text-neutral-200">
                                Bs {{ number_format((float) ($f->proyecto?->monto ?? 0), 2, ',', '.') }}
                            </span>
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

                {{-- Factura (agrupado) --}}
                <div class="mt-3 border-t border-gray-200 dark:border-neutral-800 pt-3 space-y-1">
                    <div class="text-sm font-medium truncate" title="{{ $f->numero ?? '-' }}">
                        Nro: {{ $f->numero ?? 'Factura #' . $f->id }}
                    </div>

                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        Ret. Factura:
                        <span class="font-semibold text-gray-700 dark:text-neutral-200">
                            Bs {{ number_format((float) ($f->retencion ?? 0), 2, ',', '.') }}
                        </span>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-neutral-400 truncate"
                        title="{{ $f->observacion ?? '—' }}">
                        Detalle: {{ $f->observacion ?? '—' }}
                    </div>
                </div>

                {{-- Estado + Saldo --}}
                <div class="mt-3 flex items-start justify-between gap-3">
                    {{-- Estado (2 etiquetas) --}}
                    <div class="flex flex-wrap gap-2">
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

                    {{-- Saldo --}}
                    <div class="text-right shrink-0">
                        <div class="text-sm font-semibold text-gray-800 dark:text-neutral-200">
                            Bs {{ number_format((float) $saldo, 2, ',', '.') }}
                        </div>

                        @if ($retPend > 0)
                            <div class="text-xs text-yellow-700 dark:text-yellow-300">
                                Ret.: Bs {{ number_format((float) $retPend, 2, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @can('facturas.pay')
                        <button type="button"
                            @if (!$bloqueado) wire:click="openPago({{ $f->id }})" @endif
                            @disabled($bloqueado)
                            class="px-3 py-1 rounded border transition
                            {{ $bloqueado
                                ? 'opacity-50 cursor-not-allowed bg-gray-100 text-gray-500 border-gray-300 dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700'
                                : 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700 hover:border-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 cursor-pointer' }}">
                            {{ $bloqueado ? 'Completo' : 'Registrar pago' }}
                        </button>
                    @endcan

                    <button type="button"
                        class="w-full px-3 py-2 rounded text-sm font-medium
                           border border-gray-300 text-gray-700 hover:bg-gray-50
                           dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800"
                        @click="open = !open">
                        <span x-show="!open">Ver pagos</span>
                        <span x-show="open" x-cloak>Ocultar pagos</span>
                    </button>
                </div>

                {{-- Pagos (detalle) --}}
                <div x-show="open" x-cloak class="mt-4 space-y-2">
                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        Pagos realizados: {{ $f->pagos?->count() ?? 0 }}
                    </div>

                    @forelse(($f->pagos ?? collect()) as $pg)
                        @php
                            $bancoNombre = $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? '—');
                            $cuenta = $pg->destino_numero_cuenta_snapshot ?? ($pg->banco?->numero_cuenta ?? null);
                            $moneda = $pg->destino_moneda_snapshot ?? ($pg->banco?->moneda ?? null);
                            $titular = $pg->destino_titular_snapshot ?? null;

                            $tipoLabel = $pg->tipo === 'normal' ? 'Pago Normal' : 'Pago de Retención';
                            $tipoBadge =
                                $pg->tipo === 'normal'
                                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200'
                                    : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-200';
                        @endphp

                        <div class="border rounded-lg p-3 bg-white dark:bg-neutral-900 dark:border-neutral-800">
                            {{-- Header: Fecha izquierda | Tipo derecha --}}
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-[11px] font-mono text-gray-500 dark:text-neutral-400">
                                        #{{ $loop->iteration }}
                                    </span>
                                    <div class="text-sm font-semibold truncate">
                                        {{ $pg->fecha_pago ? $pg->fecha_pago->format('Y-m-d H:i') : '—' }}
                                    </div>
                                </div>

                                {{-- Tipo --}}
                                @if ($pg->tipo === 'normal')
                                    <span
                                        class="shrink-0 px-2 py-1 rounded text-xs bg-blue-100 text-blue-800
                       dark:bg-blue-500/20 dark:text-blue-200">
                                        Pago Normal
                                    </span>
                                @else
                                    <span
                                        class="shrink-0 px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800
                       dark:bg-yellow-500/20 dark:text-yellow-200">
                                        Pago de Retención
                                    </span>
                                @endif
                            </div>

                            {{-- Detalle: filas label / value --}}
                            <div class="mt-3 grid grid-cols-1 gap-2">

                                {{-- Monto --}}
                                <div class="grid grid-cols-[110px,1fr] gap-2">
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">Monto</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                        Bs {{ number_format((float) $pg->monto, 2, ',', '.') }}
                                    </div>
                                </div>

                                {{-- Método --}}
                                <div class="grid grid-cols-[110px,1fr] gap-2">
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">Método</div>
                                    <div class="text-xs text-gray-800 dark:text-neutral-200">
                                        {{ $pg->metodo_pago ?? '—' }}
                                    </div>
                                </div>

                                {{-- Banco --}}
                                <div class="grid grid-cols-[110px,1fr] gap-2">
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">Banco</div>
                                    <div class="text-xs text-gray-800 dark:text-neutral-200 truncate"
                                        title="{{ $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? '—') }}">
                                        {{ $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? '—') }}
                                    </div>
                                </div>

                                {{-- Cuenta / Moneda --}}
                                <div class="grid grid-cols-[110px,1fr] gap-2">
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">Cuenta</div>
                                    <div class="text-xs text-gray-800 dark:text-neutral-200">
                                        {{ $pg->destino_numero_cuenta_snapshot ?? ($pg->banco?->numero_cuenta ?? '—') }}
                                        @php
                                            $moneda = $pg->destino_moneda_snapshot ?? ($pg->banco?->moneda ?? null);
                                        @endphp
                                        @if ($moneda)
                                            <span class="text-gray-500 dark:text-neutral-400">|
                                                {{ $moneda }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Operación --}}
                                <div class="grid grid-cols-[110px,1fr] gap-2">
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">Operación</div>
                                    <div class="text-xs text-gray-800 dark:text-neutral-200">
                                        {{ $pg->nro_operacion ?? '—' }}
                                    </div>
                                </div>

                                {{-- Titular --}}
                                @if ($pg->destino_titular_snapshot)
                                    <div class="grid grid-cols-[110px,1fr] gap-2">
                                        <div class="text-xs text-gray-500 dark:text-neutral-400">Titular</div>
                                        <div class="text-xs text-gray-800 dark:text-neutral-200 truncate"
                                            title="{{ $pg->destino_titular_snapshot }}">
                                            {{ $pg->destino_titular_snapshot }}
                                        </div>
                                    </div>
                                @endif

                                {{-- Observación --}}
                                @if ($pg->observacion)
                                    <div class="grid grid-cols-[110px,1fr] gap-2">
                                        <div class="text-xs text-gray-500 dark:text-neutral-400">Obs.</div>
                                        <div class="text-xs text-gray-800 dark:text-neutral-200 line-clamp-2"
                                            title="{{ $pg->observacion }}">
                                            {{ $pg->observacion }}
                                        </div>
                                    </div>
                                @endif
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

    {{-- TABLET + DESKTOP: TABLA (COMPACTA) --}}
    <div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed text-sm min-w-[980px]">
                <thead
                    class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-200">
                    <tr class="text-left">

                        <th class="w-[75px] text-center p-2 select-none whitespace-nowrap">
                            <div x-data="{ allOpen: false }" class="flex items-center justify-center gap-2">
                                <button type="button"
                                    class="w-7 h-7 inline-flex items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-100 hover:text-gray-800
                                dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:hover:text-white transition cursor-pointer"
                                    title="Desplegar / Ocultar todos"
                                    @click="allOpen = !allOpen; window.dispatchEvent(new CustomEvent('facturas:toggle-all', { detail: { open: allOpen } }));">
                                    <span x-show="!allOpen">⇵</span>
                                    <span x-show="allOpen" x-cloak>×</span>
                                </button>
                                <span>ID</span>
                            </div>
                        </th>

                        {{-- Proyecto --}}
                        <th class="w-[420px] p-2 select-none whitespace-nowrap">
                            Proyecto
                        </th>

                        {{-- Factura --}}
                        <th class="w-[420px] p-2 select-none whitespace-nowrap">
                            Factura
                        </th>

                        {{-- Estado --}}
                        <th class="w-[120px] p-2 select-none whitespace-nowrap text-center">
                            Estado
                        </th>

                        {{-- Saldo --}}
                        <th class="w-[120px] p-2 select-none whitespace-nowrap text-center">
                            Saldo
                        </th>

                        @can('facturas.pay')
                            <th class="w-[135px] p-2 whitespace-nowrap text-center">
                                Acciones
                            </th>
                        @endcan
                    </tr>
                </thead>

                @foreach ($facturas as $f)
                    <tbody x-data="{ open: false }" x-on:facturas:toggle-all.window="open = $event.detail.open"
                        class="divide-y divide-gray-200 dark:divide-neutral-200">

                        <tr class="hover:bg-gray-100 dark:hover:bg-neutral-900 text-gray-700 dark:text-neutral-200">

                            {{-- ID + toggle --}}
                            <td class="p-2 whitespace-nowrap align-middle">
                                <div class="flex items-center justify-center gap-2">
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
                                    <span
                                        class="text-sm font-medium text-gray-800 dark:text-neutral-200">{{ $f->id }}</span>
                                </div>
                            </td>

                            {{-- Proyecto --}}
                            <td class="p-2 align-top">
                                <div class="min-w-0 space-y-0.5 leading-snug">

                                    {{-- Proyecto (principal) --}}
                                    <div class="flex items-center gap-1 truncate text-sm font-medium text-gray-800 dark:text-neutral-200"
                                        title="{{ $f->proyecto?->nombre ?? '-' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-4 h-4 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
                                        </svg>
                                        <span>{{ $f->proyecto?->nombre ?? '—' }}</span>
                                    </div>

                                    {{-- Entidad --}}
                                    <div class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400"
                                        title="{{ $f->proyecto?->entidad?->nombre ?? '-' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" />
                                            <path
                                                d="M7 7h.01M7 11h.01M7 15h.01M11 7h.01M11 11h.01M11 15h.01M15 7h.01M15 11h.01M15 15h.01" />
                                        </svg>
                                        <span>Entidad: {{ $f->proyecto?->entidad?->nombre ?? '—' }}</span>
                                    </div>

                                    {{-- Retención --}}
                                    <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-neutral-400">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 2l7 4v6c0 5-3 9-7 10C8 21 5 17 5 12V6l7-4z" />
                                        </svg>
                                        <span>Retención:</span>
                                        <span class="font-semibold text-amber-700 dark:text-amber-300">
                                            {{ number_format((float) ($f->proyecto?->retencion ?? 0), 2, ',', '.') }}%
                                        </span>
                                    </div>

                                    {{-- Contrato --}}
                                    <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-neutral-400">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="M12 7v10" />
                                            <path d="M8 11h8" />
                                        </svg>
                                        <span>Contrato:</span>
                                        <span class="font-semibold text-gray-800 dark:text-neutral-200">
                                            Bs {{ number_format((float) ($f->proyecto?->monto ?? 0), 2, ',', '.') }}
                                        </span>
                                    </div>

                                </div>
                            </td>

                            {{-- Factura --}}
                            <td class="p-2 align-top">
                                <div class="min-w-0 space-y-0.5 leading-snug">

                                    {{-- Nro --}}
                                    <div class="flex items-center gap-1 truncate text-sm font-medium text-gray-800 dark:text-neutral-200"
                                        title="{{ $f->numero ?? '-' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-4 h-4 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                            <path d="M14 2v6h6" />
                                            <path d="M16 13H8" />
                                            <path d="M16 17H8" />
                                            <path d="M10 9H8" />
                                        </svg>
                                        <span>Nro: {{ $f->numero ?? '—' }}</span>
                                    </div>

                                    {{-- Fecha --}}
                                    <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-neutral-400">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg>
                                        <span>Fecha:
                                            {{ $f->fecha_emision ? $f->fecha_emision->format('Y-m-d') : '—' }}</span>
                                    </div>

                                    {{-- Monto + Retención --}}
                                    <div
                                        class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-neutral-400">

                                        {{-- Monto --}}
                                        <div class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="9" />
                                                <path d="M12 7v10" />
                                                <path d="M8 11h8" />
                                            </svg>

                                            <span>Monto:</span>
                                            <span class="font-semibold text-gray-800 dark:text-neutral-200">
                                                Bs {{ number_format((float) $f->monto_facturado, 2, ',', '.') }}
                                            </span>
                                        </div>

                                        {{-- Retención --}}
                                        <div class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 2l7 4v6c0 5-3 9-7 10C8 21 5 17 5 12V6l7-4z" />
                                            </svg>

                                            <span>Ret. Factura:</span>
                                            <span class="font-semibold text-amber-700 dark:text-amber-300">
                                                Bs {{ number_format((float) ($f->retencion ?? 0), 2, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Detalle --}}
                                    <div class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400"
                                        title="{{ $f->observacion ?? '—' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 4h16v16H4z" />
                                            <path d="M8 8h8" />
                                            <path d="M8 12h8" />
                                            <path d="M8 16h6" />
                                        </svg>
                                        <span>Detalle: {{ $f->observacion ?? '—' }}</span>
                                    </div>

                                </div>
                            </td>

                            {{-- ESTADO --}}
                            <td class="p-2 whitespace-nowrap align-middle">
                                @php
                                    $cerrada = \App\Services\FacturaFinance::estaCerrada($f);
                                    $estadoPago = \App\Services\FacturaFinance::estadoPagoLabel($f);
                                    $estadoRet = \App\Services\FacturaFinance::estadoRetencionLabel($f);
                                    $pct = \App\Services\FacturaFinance::porcentajePago($f);
                                @endphp

                                <div class="flex items-center justify-center gap-2 flex-wrap">
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

                            {{-- SALDO --}}
                            <td class="p-2 whitespace-nowrap align-middle">
                                @php
                                    $saldo = \App\Services\FacturaFinance::saldo($f);
                                    $retPend = \App\Services\FacturaFinance::retencionPendiente($f);
                                @endphp

                                <div class="flex flex-col items-center justify-center gap-0.5">
                                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-200">
                                        Bs {{ number_format((float) $saldo, 2, ',', '.') }}
                                    </div>

                                    @if ($retPend > 0)
                                        <div class="text-xs text-amber-700 dark:text-amber-300">
                                            Ret.: Bs {{ number_format((float) $retPend, 2, ',', '.') }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Acciones --}}
                            @can('facturas.pay')
                                @php
                                    $saldo = \App\Services\FacturaFinance::saldo($f);
                                    $retPend = \App\Services\FacturaFinance::retencionPendiente($f);
                                    $cerrado = $saldo <= 0 && $retPend <= 0;
                                @endphp

                                <td class="p-2 whitespace-nowrap align-middle">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button"
                                            @if (!$cerrado) wire:click="openPago({{ $f->id }})" @endif
                                            wire:loading.attr="disabled" wire:target="openPago({{ $f->id }})"
                                            wire:loading.class="cursor-not-allowed opacity-50"
                                            wire:loading.class.remove="cursor-pointer hover:bg-blue-700 hover:border-blue-700"
                                            @disabled($cerrado)
                                            class="px-3 py-1 rounded border transition cursor-pointer text-sm
                                            {{ $cerrado
                                                ? 'bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700'
                                                : 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700 hover:border-blue-700 dark:bg-blue-500 dark:border-blue-500 dark:hover:bg-blue-400 dark:hover:border-blue-400' }}">
                                            <span wire:loading.remove wire:target="openPago({{ $f->id }})">
                                                {{ $cerrado ? 'Completo' : 'Registrar pago' }}
                                            </span>

                                            @if (!$cerrado)
                                                <span wire:loading wire:target="openPago({{ $f->id }})">
                                                    Abriendo…
                                                </span>
                                            @endif
                                        </button>
                                    </div>
                                </td>
                            @endcan
                        </tr>

                        @php
                            $colspan = 5 + (auth()->user()->can('facturas.pay') ? 1 : 0);
                        @endphp

                        {{-- FILA: Detalle de pagos --}}
                        <tr x-show="open" x-cloak
                            class="bg-gray-100/60 dark:bg-neutral-900/40 border-b border-gray-200 dark:border-neutral-200">
                            <td class="px-5 py-2" colspan="{{ $colspan }}">
                                <div class="space-y-3 text-sm">

                                    <div
                                        class="border rounded bg-white dark:bg-neutral-900 dark:border-neutral-800 overflow-hidden">
                                        <table class="w-full table-fixed text-sm">
                                            <thead
                                                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-800">
                                                <tr class="text-left">

                                                    <th class="p-2 w-[4%] text-center whitespace-nowrap">#</th>
                                                    <th class="p-2 w-[25%] whitespace-nowrap">Destino de Banco</th>
                                                    <th class="p-2 w-[25%] whitespace-nowrap">Pago</th>
                                                    <th class="p-2 w-[20%] whitespace-nowrap">Observación</th>
                                                    <th class="p-2 w-[10%] whitespace-nowrap text-center">Tipo</th>

                                                    @can('facturas.pay')
                                                        <th class="p-2 w-[4%] whitespace-nowrap text-center">Acc.</th>
                                                    @endcan
                                                </tr>
                                            </thead>

                                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
                                                @forelse(($f->pagos ?? collect()) as $pg)
                                                    <tr class="text-gray-700 dark:text-neutral-200 align-top">

                                                        {{-- # --}}
                                                        <td class="p-2 align-middle">
                                                            <div
                                                                class="flex items-center justify-center text-xs font-medium text-gray-700 dark:text-neutral-200">
                                                                {{ $loop->iteration }}
                                                            </div>
                                                        </td>

                                                        {{-- Destino --}}
                                                        <td class="p-2 align-middle">
                                                            <div class="min-w-0 space-y-0.5 leading-snug">
                                                                <div class="truncate text-sm font-medium text-gray-800 dark:text-neutral-200"
                                                                    title="{{ $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? '—') }}">
                                                                    {{ $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? '—') }}
                                                                </div>

                                                                @php
                                                                    $cuenta =
                                                                        $pg->destino_numero_cuenta_snapshot ??
                                                                        ($pg->banco?->numero_cuenta ?? null);
                                                                    $moneda =
                                                                        $pg->destino_moneda_snapshot ??
                                                                        ($pg->banco?->moneda ?? null);
                                                                @endphp

                                                                <div
                                                                    class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <rect x="2" y="5" width="20"
                                                                            height="14" rx="2" />
                                                                        <line x1="2" y1="10"
                                                                            x2="22" y2="10" />
                                                                    </svg>
                                                                    <span>Nro. Cuenta:
                                                                        {{ $cuenta ?: '—' }}{{ $moneda ? ' | ' . $moneda : '' }}</span>
                                                                </div>

                                                                @if ($pg->destino_titular_snapshot)
                                                                    <div class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400"
                                                                        title="{{ $pg->destino_titular_snapshot }}">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="currentColor" stroke-width="2"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <path
                                                                                d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                                                            <circle cx="12" cy="7"
                                                                                r="4" />
                                                                        </svg>
                                                                        <span>Titular:
                                                                            {{ $pg->destino_titular_snapshot }}</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </td>

                                                        {{-- Pago --}}
                                                        <td class="p-2 align-middle">
                                                            <div class="min-w-0 space-y-0.5 leading-snug">
                                                                {{-- Fecha --}}
                                                                <div
                                                                    class="flex items-center gap-1 truncate text-sm font-medium text-gray-800 dark:text-neutral-200">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="w-4 h-4 text-gray-400 dark:text-neutral-400"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <rect x="3" y="4" width="18"
                                                                            height="18" rx="2" />
                                                                        <line x1="16" y1="2"
                                                                            x2="16" y2="6" />
                                                                        <line x1="8" y1="2"
                                                                            x2="8" y2="6" />
                                                                        <line x1="3" y1="10"
                                                                            x2="21" y2="10" />
                                                                    </svg>
                                                                    <span>{{ $pg->fecha_pago?->format('Y-m-d H:i') ?? '—' }}</span>
                                                                </div>

                                                                {{-- Monto (neutro fuerte, consistente con saldos) --}}
                                                                <div
                                                                    class="flex items-center gap-1 truncate text-sm font-semibold text-gray-800 dark:text-neutral-200">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="w-4 h-4 text-gray-400 dark:text-neutral-400"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <circle cx="12" cy="12" r="9" />
                                                                        <path d="M12 7v10" />
                                                                        <path d="M8 11h8" />
                                                                    </svg>
                                                                    <span>Bs
                                                                        {{ number_format((float) $pg->monto, 2, ',', '.') }}</span>
                                                                </div>

                                                                {{-- Método --}}
                                                                <div
                                                                    class="truncate text-xs text-gray-500 dark:text-neutral-400">
                                                                    Método: {{ $pg->metodo_pago ?? '—' }} | Nro.Op:
                                                                    {{ $pg->nro_operacion ?: '—' }}
                                                                </div>
                                                            </div>
                                                        </td>

                                                        {{-- Observación --}}
                                                        <td class="p-2 align-middle">
                                                            <p
                                                                class="text-sm leading-snug text-gray-700 dark:text-neutral-200 break-words">
                                                                {{ $pg->observacion ?: '—' }}
                                                            </p>
                                                        </td>

                                                        {{-- Tipo --}}
                                                        <td class="p-2 align-middle whitespace-nowrap">
                                                            <div class="flex items-center justify-center">
                                                                @if ($pg->tipo === 'normal')
                                                                    <span
                                                                        class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200">
                                                                        Pago Normal
                                                                    </span>
                                                                @else
                                                                    <span
                                                                        class="inline-flex items-center px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-200">
                                                                        Pago de Retención
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </td>

                                                        {{-- Acciones --}}
                                                        @can('facturas.pay')
                                                            <td class="p-2 align-middle whitespace-nowrap">
                                                                <div class="flex items-center justify-center">
                                                                    <button type="button"
                                                                        class="inline-flex items-center justify-center w-9 h-9 rounded border border-red-300 text-red-700
                                                                        hover:bg-red-200 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-500 transition cursor-pointer"
                                                                        title="Eliminar pago"
                                                                        wire:click="confirmDeletePago({{ $pg->id }})"
                                                                        wire:loading.attr="disabled"
                                                                        wire:target="confirmDeletePago({{ $pg->id }})">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="w-4 h-4" viewBox="0 0 24 24"
                                                                            fill="none" stroke="currentColor"
                                                                            stroke-width="2" stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <path d="M3 6h18" />
                                                                            <path d="M8 6V4h8v2" />
                                                                            <path d="M6 6l1 16h10l1-16" />
                                                                            <path d="M10 11v6" />
                                                                            <path d="M14 11v6" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        @endcan
                                                    </tr>
                                                @empty
                                                    <td class="p-3 text-center text-gray-500 dark:text-neutral-400"
                                                        colspan="{{ $colspan }}">
                                                        No hay pagos registrados para esta factura.
                                                    </td>
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

    {{--  MODAL: NUEVA FACTURA (create) --}}
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
                            class="cursor-pointer inline-flex items-center justify-center size-9 rounded-md
                               text-gray-500 hover:text-gray-900 hover:bg-gray-200
                               dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800">
                            ✕
                        </button>
                    </div>

                    {{-- Body (scroll) --}}
                    <div
                        class="p-5 space-y-4 overflow-y-auto
                            h-[calc(100dvh-64px-76px)] sm:h-auto sm:max-h-[calc(90vh-64px-76px)]">

                        {{-- Entidad + Proyecto --}}

                        {{-- Entidad --}}
                        <div>
                            <label class="block text-sm mb-1">Entidad</label>
                            <select wire:model.live="entidad_id"
                                class="cursor-pointer w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700
                                    text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-neutral-700">
                                <option value="">Seleccione...</option>
                                @foreach ($entidades as $e)
                                    <option value="{{ $e->id }}" title="{{ $e->nombre }}">
                                        {{ \Illuminate\Support\Str::limit($e->nombre, 60) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('entidad_id')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Proyecto (depende de entidad) --}}
                        <div>
                            <label class="block text-sm mb-1">Proyecto</label>

                            <select wire:model.live="proyecto_id" @disabled(!$entidad_id)
                                class="cursor-pointer w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700
                                    text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-neutral-700 
                                    disabled:opacity-60 disabled:cursor-not-allowed">
                                <option value="">
                                    {{ $entidad_id ? 'Seleccione...' : 'Seleccione una entidad primero' }}
                                </option>

                                @foreach ($proyectos as $p)
                                    <option value="{{ $p->id }}" title="{{ $p->nombre }}">
                                        {{ \Illuminate\Support\Str::limit($p->nombre, 75) }}
                                    </option>
                                @endforeach
                            </select>

                            @error('proyecto_id')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
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

                            {{-- Monto --}}
                            <div>
                                <label class="block text-sm mb-1">Monto Facturado</label>
                                <input type="number" step="0.01" min="0"
                                    wire:model.live="monto_facturado"
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

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
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
                            {{-- Neto (recomendado) --}}
                            <div>
                                <label class="block text-sm mb-1">Monto Neto</label>
                                <input type="number" step="0.01"
                                    value="{{ number_format((float) ($monto_neto ?? 0), 2, '.', '') }}" disabled
                                    class="w-full rounded border px-3 py-2
                                   bg-gray-100 dark:bg-neutral-800
                                   border-gray-300 dark:border-neutral-700
                                   text-gray-900 dark:text-neutral-100
                                   opacity-80 cursor-not-allowed
                                   focus:outline-none" />
                            </div>
                        </div>

                        {{-- Detalle --}}
                        <div>
                            <label class="block text-sm mb-1">Detalle</label>
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
                        class="sticky bottom-0 px-5 py-4 flex justify-end gap-2 bg-gray-50 dark:bg-neutral-900 border-t border-gray-200 dark:border-neutral-800">
                        <button wire:click="closeFactura"
                            class="cursor-pointer px-4 py-2 rounded border border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200 hover:bg-gray-200 dark:hover:bg-neutral-800">
                            Cancelar
                        </button>

                        <button wire:click="saveFactura" wire:loading.attr="disabled" wire:target="saveFactura"
                            class="w-full sm:w-auto px-4 py-2 rounded bg-black text-white hover:bg-gray-800 hover:text-white transition-colors duration-150
                            cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">

                            {{-- Texto normal --}}
                            <span wire:loading.remove wire:target="saveFactura">
                                Guardar
                            </span>

                            {{-- Texto loading --}}
                            <span wire:loading wire:target="saveFactura">
                                Guardando…
                            </span>
                        </button>

                    </div>

                </div>
            </div>
        </div>
    @endif

    {{--  MODAL: REGISTRAR PAGO --}}
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
                            class="cursor-pointer inline-flex items-center justify-center size-9 rounded-md
                               text-gray-500 hover:text-gray-900 hover:bg-gray-200
                               dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800">
                            ✕
                        </button>
                    </div>

                    {{-- Body (scroll) --}}
                    <div
                        class="p-5 space-y-4 overflow-y-auto
                            h-[calc(100dvh-64px-76px)] sm:h-auto sm:max-h-[calc(90vh-64px-76px)]">

                        {{-- Tipo / Método / Fecha de pago --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            {{-- Tipo --}}
                            <div>
                                <label class="block text-sm mb-1">Tipo</label>
                                <select wire:model="tipo"
                                    class="cursor-pointer w-full rounded border px-3 py-2
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
                                    class="cursor-pointer w-full rounded border px-3 py-2
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

                            {{-- Fecha de pago (personalizable) --}}
                            <div>
                                <label class="block text-sm mb-1">Fecha de pago</label>
                                <input type="datetime-local" wire:model="fecha_pago"
                                    class="cursor-pointer w-full rounded border px-3 py-2
                                       bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700
                                       text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-300 dark:focus:ring-neutral-700" />
                                @error('fecha_pago')
                                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Banco destino --}}
                        <div>
                            <label class="block text-sm mb-1">Banco destino</label>
                            <select wire:model="banco_id"
                                class="cursor-pointer w-full rounded border px-3 py-2
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
                            class="cursor-pointer px-4 py-2 rounded border
                               border-gray-300 dark:border-neutral-700
                               text-gray-700 dark:text-neutral-200
                               hover:bg-gray-200 dark:hover:bg-neutral-800">
                            Cancelar
                        </button>

                        <button wire:click="savePago" wire:loading.attr="disabled" wire:target="savePago"
                            class="w-full sm:w-auto px-4 py-2 rounded bg-black text-white hover:bg-gray-800 hover:text-white transition-colors duration-150
                            cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">

                            {{-- Texto normal --}}
                            <span wire:loading.remove wire:target="savePago">
                                Guardar pago
                            </span>

                            {{-- Texto loading --}}
                            <span wire:loading wire:target="savePago">
                                Guardando…
                            </span>
                        </button>

                    </div>

                </div>
            </div>
        </div>
    @endif
</div>
