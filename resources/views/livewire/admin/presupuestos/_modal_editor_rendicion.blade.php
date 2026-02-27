<x-ui.modal wire:key="rendicion-editor-{{ $editorRendicionId ?? 'none' }}" model="openEditor" title="Planilla de Rendición"
    maxWidth="sm:max-w-4xl lg:max-w-7xl" onClose="closeEditor">
    @php
        $hasCompras = !empty($editorCompras) && count($editorCompras) > 0;
        $hasDevoluciones = !empty($editorDevoluciones) && count($editorDevoluciones) > 0;
        $hasMovs = $hasCompras || $hasDevoluciones;
    @endphp

    <div class="space-y-4">
        {{-- CABECERA --}}
        <div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">

            {{-- TOP --}}
            <div class="px-4 py-3">
                {{-- FILA 1 (mobile: título + botón a la derecha) --}}
                <div class="flex items-start gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="text-base font-semibold text-gray-900 dark:text-neutral-100">
                            #
                            <span class="font-extrabold tabular-nums">
                                {{ $editorRendicionNro ?? '#' . ($editorRendicionId ?? '—') }}
                            </span>
                        </div>

                        {{-- META (mobile: chips) --}}
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-neutral-300">
                            <span
                                class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-gray-50 border border-gray-200
                                 dark:bg-neutral-900/30 dark:border-neutral-700">
                                {{-- icon user --}}
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-500" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.5 20.25a7.5 7.5 0 0115 0" />
                                </svg>
                                <span class="text-gray-500 dark:text-neutral-400">Agente:</span>
                                <span class="font-semibold text-gray-900 dark:text-neutral-100 truncate max-w-[180px]">
                                    {{ $editorAgenteNombre ?? '—' }}
                                </span>
                            </span>

                            <span
                                class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-gray-50 border border-gray-200
                                 dark:bg-neutral-900/30 dark:border-neutral-700">
                                {{-- icon calendar --}}
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-3.5 h-3.5 text-gray-400 dark:text-neutral-500" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-gray-500 dark:text-neutral-400">Fecha:</span>
                                <span class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                    {{ $editorFecha ? \Carbon\Carbon::parse($editorFecha)->format('d/m/Y') : '—' }}
                                </span>
                            </span>
                        </div>
                    </div>

                    {{-- BOTÓN (siempre visible arriba a la derecha en mobile) --}}
                    <div class="shrink-0">
                        @can('agente_rendicion.create')
                        <button type="button" wire:click="openMovimientoModal1" wire:loading.attr="disabled"
                            wire:target="openMovimientoModal1" @disabled(($editorSaldo ?? 0) <= 0)
                            title="{{ ($editorSaldo ?? 0) <= 0 ? 'Saldo agotado. No se pueden registrar más movimientos.' : '' }}"
                            class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold transition
                           bg-gray-900 text-white hover:opacity-90
                           disabled:opacity-50 disabled:cursor-not-allowed
                           dark:bg-white dark:text-gray-900">

                            {{-- icon plus --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14M5 12h14" />
                            </svg>

                            <span class="hidden sm:inline" wire:loading.remove wire:target="openMovimientoModal1">
                                Movimiento
                            </span>
                            <span class="sm:hidden" wire:loading.remove wire:target="openMovimientoModal1">
                                Mov.
                            </span>

                            <span wire:loading wire:target="openMovimientoModal1">
                                …
                            </span>
                        </button>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- KPIs --}}
            <div class="border-t dark:border-neutral-700 px-4 py-3">

                {{-- MOBILE: LISTA (como tu imagen) --}}
                <div class="sm:hidden space-y-2">
                    {{-- PRESUP --}}
                    <div class="flex items-baseline justify-between">
                        <span
                            class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-neutral-400 font-semibold">
                            Presup.
                        </span>
                        <span class="text-base font-extrabold tabular-nums text-gray-900 dark:text-neutral-100">
                            {{ number_format((float) ($editorPresupuestoTotal ?? 0), 2, ',', '.') }}
                        </span>
                    </div>

                    {{-- RENDIDO --}}
                    <div class="flex items-baseline justify-between">
                        <span
                            class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-neutral-400 font-semibold">
                            Rendido
                        </span>
                        <span class="text-base font-extrabold tabular-nums text-emerald-600 dark:text-emerald-300">
                            {{ number_format((float) ($editorRendidoTotal ?? 0), 2, ',', '.') }}
                        </span>
                    </div>

                    {{-- SALDO --}}
                    <div class="flex items-baseline justify-between">
                        <span
                            class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-neutral-400 font-semibold">
                            Saldo
                        </span>
                        <span
                            class="text-base font-extrabold tabular-nums
                {{ (float) ($editorSaldo ?? 0) <= 0
                    ? 'text-emerald-700 dark:text-emerald-300'
                    : 'text-rose-600 dark:text-rose-300' }}">
                            {{ number_format((float) ($editorSaldo ?? 0), 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- SM+ : GRID (tu versión actual) --}}
                <div class="hidden sm:grid grid-cols-3 gap-3">
                    {{-- Presupuesto --}}
                    <div
                        class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50/70 dark:bg-neutral-900 p-2.5">
                        <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400 font-bold">
                            Presupuesto ({{ $editorMonedaBase ?? 'BOB' }})
                        </div>
                        <div class="mt-1 text-lg font-extrabold tabular-nums text-gray-900 dark:text-neutral-100">
                            {{ number_format((float) ($editorPresupuestoTotal ?? 0), 2, ',', '.') }}
                        </div>
                    </div>

                    {{-- Rendido --}}
                    <div
                        class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50/70 dark:bg-neutral-900 p-2.5">
                        <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400 font-bold">
                            Rendido ({{ $editorMonedaBase ?? 'BOB' }})
                        </div>
                        <div class="mt-1 text-lg font-extrabold tabular-nums text-emerald-600 dark:text-emerald-300">
                            {{ number_format((float) ($editorRendidoTotal ?? 0), 2, ',', '.') }}
                        </div>
                    </div>

                    {{-- Saldo --}}
                    <div
                        class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50/70 dark:bg-neutral-900 p-2.5">
                        <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400 font-bold">
                            Saldo ({{ $editorMonedaBase ?? 'BOB' }})
                        </div>
                        <div
                            class="mt-1 text-lg font-extrabold tabular-nums
                            {{ (float) ($editorSaldo ?? 0) <= 0
                                ? 'text-emerald-700 dark:text-emerald-300'
                                : 'text-rose-600 dark:text-rose-300' }}">
                            {{ number_format((float) ($editorSaldo ?? 0), 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- TABLAS --}}
        @if ($hasMovs)
            <div class="grid grid-cols-1 gap-4">

                {{-- DEVOLUCIONES --}}
                @if ($hasDevoluciones)
                    <div
                        class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">

                        <div class="px-4 py-2 border-b dark:border-neutral-700 flex items-center justify-between">
                            <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Devoluciones</div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                Total devolución (base):
                                <span class="font-extrabold tabular-nums text-gray-800 dark:text-neutral-100">
                                    {{ number_format((float) ($editorTotalDevolucionesBase ?? 0), 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- DESKTOP: TABLA --}}
                        <div class="hidden md:block overflow-visible">
                            <table class="w-full text-sm">
                                <thead
                                    class="sticky top-0 z-10 bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-700">
                                    <tr
                                        class="text-left text-[11px] uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                                        <th class="p-3 text-center w-[60px]">Nro</th>
                                        <th class="p-3 w-[120px]">Fecha</th>
                                        <th class="p-3">Banco</th>
                                        <th class="p-3">Transacción</th>
                                        <th class="p-3 text-center">Monto</th>
                                        <th class="p-3 text-center">Base</th>
                                        <th class="p-3 text-center w-[90px]">Foto</th>
                                        @can('agente_rendicion.create')
                                        <th class="p-3 text-center w-[140px]">Acc.</th>
                                        @endcan
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                    @foreach ($editorDevoluciones ?? [] as $i => $m)
                                        {{-- FILA PRINCIPAL --}}
                                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/60 transition">
                                            <td class="p-3 text-center text-gray-700 dark:text-neutral-200">
                                                {{ $i + 1 }}
                                            </td>

                                            <td class="p-3 text-gray-700 dark:text-neutral-200 whitespace-nowrap">
                                                {{ $m->fecha?->format('d/m/y') ?? '-' }}
                                            </td>

                                            <td class="p-3 text-gray-700 dark:text-neutral-200">
                                                {{ $m->banco?->nombre ?? '—' }}
                                            </td>

                                            <td class="p-3 text-gray-700 dark:text-neutral-200">
                                                {{ $m->nro_transaccion ?? '—' }}
                                            </td>

                                            <td
                                                class="p-3 text-center tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                {{ $m->moneda }}
                                            </td>

                                            <td
                                                class="p-3 text-center tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                                {{ $editorMonedaBase ?? 'BOB' }}
                                            </td>

                                            <td class="p-3 text-center w-[50px]">
                                                @if (!empty($m->foto_path))
                                                    <button type="button" wire:click="verFoto({{ $m->id }})"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/40 dark:hover:text-blue-400 transition"
                                                        title="Ver foto">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                                            <path d="M21 15l-5-5L5 21" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <span class="text-xs text-gray-400">—</span>
                                                @endif
                                            </td>

                                            @can('agente_rendicion.create')
                                            <td class="p-3 text-center w-[50px]">
                                                <button type="button" x-data
                                                    x-on:click="$dispatch('swal:delete-movimiento', { id: {{ $m->id }}, monto: '{{ $m->monto }}' })"
                                                    class="cursor-pointer inline-flex items-center justify-center w-8 h-8 rounded border border-red-200 text-red-700 hover:bg-red-50
                                                    dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10 transition"
                                                    title="Eliminar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path d="M3 6h18" />
                                                        <path d="M8 6V4h8v2" />
                                                        <path d="M6 6l1 16h10l1-16" />
                                                        <path d="M10 11v6" />
                                                        <path d="M14 11v6" />
                                                    </svg>
                                                </button>
                                            </td>
                                            @endcan
                                        </tr>

                                        {{-- FILA OBSERVACIÓN (ABAJO) --}}
                                        <tr class="bg-gray-50/60 dark:bg-neutral-900/40">
                                            <td colspan="8"
                                                class="px-4 py-2 text-xs text-gray-600 dark:text-neutral-300">
                                                <span
                                                    class="font-medium text-gray-700 dark:text-neutral-200">Obs:</span>
                                                <span
                                                    class="{{ empty($m->observacion) ? 'text-gray-400 dark:text-neutral-500' : '' }}">
                                                    {{ $m->observacion ?: '—' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>

                        {{-- MOBILE: CARDS --}}
                        <div class="md:hidden p-3 space-y-3">
                            @foreach ($editorDevoluciones ?? [] as $i => $m)
                                <div
                                    class="rounded-xl border border-gray-200 dark:border-neutral-700
                                     bg-white dark:bg-neutral-900/20 p-3">

                                    {{-- CABECERA --}}
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                                #{{ $i + 1 }} · {{ $m->banco?->nombre ?? '—' }}
                                            </div>

                                            <div class="mt-0.5 text-xs text-gray-500 dark:text-neutral-400">
                                                {{ $m->fecha?->format('d/m/Y') ?? '-' }}
                                                <span class="mx-1">•</span>
                                                Tx: {{ $m->nro_transaccion ?? '—' }}
                                            </div>
                                        </div>

                                        {{-- ACCIONES --}}
                                        <div class="flex items-center gap-1 shrink-0">
                                            {{-- VER FOTO --}}
                                            @if (!empty($m->foto_path))
                                                <button type="button" wire:click="verFoto({{ $m->id }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg
                                   border border-gray-200 text-gray-700 hover:bg-gray-100
                                   dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 transition"
                                                    title="Ver foto">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="1.8" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                                        <path d="M21 15l-5-5L5 21" />
                                                    </svg>
                                                </button>
                                            @endif

                                            {{-- ELIMINAR --}}
                                            @can('agente_rendicion.create')
                                            <button type="button" x-data
                                                x-on:click="$dispatch('swal:delete-movimiento', { id: {{ $m->id }}, monto: '{{ $m->monto }}' })"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg
                               border border-red-200 text-red-700 hover:bg-red-50
                               dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10 transition"
                                                title="Eliminar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M6 6l1 16h10l1-16" />
                                                    <path d="M10 11v6" />
                                                    <path d="M14 11v6" />
                                                </svg>
                                            </button>
                                            @endcan
                                        </div>
                                    </div>

                                    {{-- OBSERVACIÓN (ABAJO, FULL WIDTH) --}}
                                    <div class="mt-2 text-xs text-gray-600 dark:text-neutral-300">
                                        <span class="font-medium text-gray-700 dark:text-neutral-200">Obs:</span>
                                        <span
                                            class="{{ empty($m->observacion) ? 'text-gray-400 dark:text-neutral-500' : '' }}">
                                            {{ $m->observacion ?: '—' }}
                                        </span>
                                    </div>

                                    {{-- MONTOS --}}
                                    <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                                Monto
                                            </div>
                                            <div
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                {{ $m->moneda }}
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                                Base
                                            </div>
                                            <div
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                                {{ $editorMonedaBase ?? 'BOB' }}
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif


                {{-- COMPRAS --}}
                @if ($hasCompras)
                    <div
                        class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
                        <div class="px-4 py-2 border-b dark:border-neutral-700 flex items-center justify-between">
                            <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Compras</div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                Total compras (base):
                                <span class="font-extrabold tabular-nums text-gray-800 dark:text-neutral-100">
                                    {{ number_format((float) ($editorTotalComprasBase ?? 0), 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- DESKTOP: TABLA --}}
                        <div class="hidden md:block overflow-visible">
                            <table class="w-full text-sm">
                                <thead
                                    class="sticky top-0 z-10 bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-700">
                                    <tr
                                        class="text-left text-[11px] uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                                        <th class="p-3 text-center w-[50px]">Nro</th>
                                        <th class="p-3 text-center w-[70px]">Fecha</th>
                                        <th class="p-3">Entidad</th>
                                        <th class="p-3">Proyecto</th>
                                        <th class="p-3 text-center">Comprobante</th>
                                        <th class="p-3 text-center">Monto</th>
                                        <th class="p-3 text-center">Base</th>
                                        <th class="p-3 text-center w-[50px]">Foto</th>
                                        @can('agente_rendicion.create')
                                        <th class="p-3 text-center w-[50px]">Acc.</th>
                                        @endcan
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                    @foreach ($editorCompras ?? [] as $i => $m)
                                        {{-- FILA PRINCIPAL --}}
                                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/60 transition">
                                            <td class="p-3 text-center text-gray-700 dark:text-neutral-200">
                                                {{ $i + 1 }}
                                            </td>

                                            <td
                                                class="p-3 text-center whitespace-nowrap text-gray-700 dark:text-neutral-200">
                                                {{ $m->fecha?->format('d/m/y') ?? '-' }}
                                            </td>

                                            <td class="p-3 text-gray-700 dark:text-neutral-200">
                                                {{ $m->entidad?->nombre ?? '—' }}
                                            </td>

                                            <td class="p-3 text-gray-700 dark:text-neutral-200">
                                                {{ $m->proyecto?->nombre ?? '—' }}
                                            </td>

                                            <td class="p-3 text-center text-gray-700 dark:text-neutral-200">
                                                {{ $m->tipo_comprobante ?? '—' }}
                                                @if (!empty($m->nro_comprobante))
                                                    <span class="text-gray-400">•</span> {{ $m->nro_comprobante }}
                                                @endif
                                            </td>

                                            <td
                                                class="p-3 text-center tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                {{ $m->moneda }}
                                            </td>

                                            <td
                                                class="p-3 text-center tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                                {{ $editorMonedaBase ?? 'BOB' }}
                                            </td>

                                            <td class="p-3 text-center w-[50px]">
                                                @if (!empty($m->foto_path))
                                                    <button type="button" wire:click="verFoto({{ $m->id }})"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/40 dark:hover:text-blue-400 transition"
                                                        title="Ver foto">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                                            <path d="M21 15l-5-5L5 21" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <span class="text-xs text-gray-400">—</span>
                                                @endif
                                            </td>

                                            @can('agente_rendicion.create')
                                            <td class="p-3 text-center w-[50px]">
                                                <button type="button" x-data
                                                    x-on:click="$dispatch('swal:delete-movimiento', { id: {{ $m->id }}, monto: '{{ $m->monto }}' })"
                                                    class="cursor-pointer inline-flex items-center justify-center w-8 h-8 rounded
                                                    border border-red-200 text-red-700 hover:bg-red-50
                                                    dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10 transition"
                                                    title="Eliminar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path d="M3 6h18" />
                                                        <path d="M8 6V4h8v2" />
                                                        <path d="M6 6l1 16h10l1-16" />
                                                        <path d="M10 11v6" />
                                                        <path d="M14 11v6" />
                                                    </svg>
                                                </button>
                                            </td>
                                            @endcan
                                        </tr>

                                        {{-- FILA OBSERVACIÓN --}}
                                        <tr class="bg-gray-50/60 dark:bg-neutral-900/40">
                                            <td colspan="9"
                                                class="px-4 py-2 text-xs text-gray-600 dark:text-neutral-300">
                                                <span
                                                    class="font-medium text-gray-700 dark:text-neutral-200">Obs:</span>
                                                <span
                                                    class="{{ empty($m->observacion) ? 'text-gray-400 dark:text-neutral-500' : '' }}">
                                                    {{ $m->observacion ?: '—' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>


                        {{--  MOBILE: CARDS --}}
                        <div class="md:hidden p-3 space-y-3">
                            @foreach ($editorCompras ?? [] as $i => $m)
                                <div
                                    class="rounded-xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900/20 p-3">

                                    {{-- Header: # + Proyecto + acciones --}}
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                                #{{ $i + 1 }}
                                                <span class="text-gray-400 dark:text-neutral-600">·</span>
                                                {{ $m->proyecto?->nombre ?? '—' }}
                                            </div>

                                            <div class="mt-0.5 text-xs text-gray-500 dark:text-neutral-400">
                                                {{ $m->entidad?->nombre ?? '—' }}
                                                <span class="mx-1">•</span>
                                                {{ $m->fecha?->format('d/m/Y') ?? '-' }}
                                            </div>

                                            <div class="mt-0.5 text-xs text-gray-500 dark:text-neutral-400">
                                                {{ $m->tipo_comprobante ?? '—' }}
                                                @if (!empty($m->nro_comprobante))
                                                    <span class="text-gray-400">•</span> {{ $m->nro_comprobante }}
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Acciones (foto + eliminar) --}}
                                        <div class="shrink-0 flex items-center gap-2">
                                            @if (!empty($m->foto_path))
                                                <button type="button" wire:click="verFoto({{ $m->id }})"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg
                                   border border-gray-200 text-gray-700 hover:bg-gray-100
                                   dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 transition"
                                                    title="Ver foto">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="1.8" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                                        <path d="M21 15l-5-5L5 21" />
                                                    </svg>
                                                </button>
                                            @endif

                                            @can('agente_rendicion.create')
                                            <button type="button" x-data
                                                x-on:click="$dispatch('swal:delete-movimiento', { id: {{ $m->id }}, monto: '{{ $m->monto }}' })"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg
                               border border-red-200 text-red-700 hover:bg-red-50
                               dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10 transition"
                                                title="Eliminar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M6 6l1 16h10l1-16" />
                                                    <path d="M10 11v6" />
                                                    <path d="M14 11v6" />
                                                </svg>
                                            </button>
                                            @endcan
                                        </div>
                                    </div>

                                    <div class="mt-2 text-xs text-gray-600 dark:text-neutral-300">
                                        <span class="font-medium text-gray-700 dark:text-neutral-200">Obs:</span>
                                        <span
                                            class="{{ empty($m->observacion) ? 'text-gray-400 dark:text-neutral-500' : '' }}">
                                            {{ $m->observacion ?: '—' }}
                                        </span>
                                    </div>

                                    {{-- Montos --}}
                                    <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                                Monto</div>
                                            <div
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                {{ $m->moneda }}
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <div
                                                class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                                Base</div>
                                            <div
                                                class="font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                                {{ $editorMonedaBase ?? 'BOB' }}
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>

                    </div>
                @endif

            </div>
        @else
            <div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 p-4">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Aún no hay movimientos</div>
                <div class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                    Usa el boton <span class="font-semibold">+ Movimientos</span> para agregar compras o devoluciones a
                    esta rendición.
                </div>
            </div>
        @endif
    </div>

    @slot('footer')
        <div class="flex items-center justify-end gap-2">
            <button type="button" wire:click="closeEditor"
                class="cursor-pointer px-4 py-2 rounded-lg border border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 transition">
                Cerrar
            </button>
            @can('agente_rendicion.close')
            <button type="button" wire:click="cerrarRendicion" wire:loading.attr="disabled"
                wire:target="cerrarRendicion" @disabled($editorEstado === 'cerrado' || ((float) ($editorSaldo ?? 0)) > 0)
                class="cursor-pointer px-4 py-2 rounded-lg bg-emerald-600 text-white
           hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="cerrarRendicion">
                    Cerrar rendición
                </span>

                <span wire:loading wire:target="cerrarRendicion">
                    Cerrando…
                </span>
            </button>
            @endcan
        </div>
    @endslot
</x-ui.modal>
