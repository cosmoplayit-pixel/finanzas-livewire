{{-- TABLET + DESKTOP: TABLA (COMPACTA) --}}
<div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table wire:key="facturas-table" class="w-full table-fixed text-sm min-w-[980px]">
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
                    class="divide-y divide-gray-200 dark:divide-neutral-200" wire:key="factura-row-{{ $f->id }}">

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
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path
                                            d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
                                    </svg>
                                    <span>{{ $f->proyecto?->nombre ?? '—' }}</span>
                                </div>

                                {{-- Entidad --}}
                                <div class="flex items-center gap-1 truncate text-xs text-gray-500 dark:text-neutral-400"
                                    title="{{ $f->proyecto?->entidad?->nombre ?? '-' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                        <path
                                            d="M7 7h.01M7 11h.01M7 15h.01M11 7h.01M11 11h.01M11 15h.01M15 7h.01M15 11h.01M15 15h.01" />
                                    </svg>
                                    <span>Entidad: {{ $f->proyecto?->entidad?->nombre ?? '—' }}</span>
                                </div>

                                {{-- Retención --}}
                                <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-neutral-400">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
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
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
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
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
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
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
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
                                        class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-400" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
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
                                                <tr wire:key="factura-{{ $f->id }}-pago-{{ $pg->id }}"
                                                    class="text-gray-700 dark:text-neutral-200 align-top">

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
                                                                {{ $pg->destino_banco_nombre_snapshot ?? ($pg->banco?->nombre ?? 'Caja Chica') }}
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
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <rect x="2" y="5" width="20" height="14"
                                                                        rx="2" />
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
                                                                        <circle cx="12" cy="7" r="4" />
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
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <rect x="3" y="4" width="18" height="18"
                                                                        rx="2" />
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
                                                                    stroke-linecap="round" stroke-linejoin="round">
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
                                                @php
                                                    $colspanPagos = 5 + (auth()->user()->can('facturas.pay') ? 1 : 0);
                                                @endphp

                                                <tr>
                                                    <td class="p-3 text-center text-gray-500 dark:text-neutral-400"
                                                        colspan="{{ $colspanPagos }}">
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
