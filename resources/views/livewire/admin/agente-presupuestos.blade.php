@section('title', 'Presupuestos de Agente')

<div class="p-0 md:p-6 space-y-4" :title="__('Dashboard')">

    {{-- HEADER --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold">Presupuestos</h1>

        <button wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
            class="w-full sm:w-auto px-4 py-2 rounded
                       bg-black text-white
                       hover:bg-gray-800 hover:text-white
                       transition-colors duration-150
                       cursor-pointer
                       disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="openCreate">Nuevo Presupuesto</span>
            <span wire:loading wire:target="openCreate">Abriendo…</span>
        </button>
    </div>

    {{-- ALERTAS --}}
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

    {{-- FILTROS --}}
    <div class="flex flex-col gap-3 md:flex-row md:items-center">
        <input type="search" wire:model.live="search" placeholder="Buscar por banco, agente o nro transacción…"
            class="w-full md:w-80 border rounded px-3 py-2" autocomplete="off" />

        <div class="flex flex-col sm:flex-row gap-3 md:ml-auto w-full md:w-auto">

            {{-- Empresa (solo admin) --}}
            @if (auth()->user()?->hasRole('Administrador'))
                <select wire:model.live="empresaFilter"
                    class="w-full sm:w-auto md:w-56 border rounded px-3 py-2
                           bg-white text-gray-900 border-gray-300
                           dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700">
                    <option value="all">Todas las Empresas</option>
                    @foreach ($empresas as $e)
                        <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                    @endforeach
                </select>
            @endif

            <select wire:model.live="monedaFilter"
                class="w-full sm:w-auto md:w-44 border rounded px-3 py-2
                       bg-white text-gray-900 border-gray-300
                       dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700">
                <option value="all">Todas Monedas</option>
                <option value="BOB">BOB</option>
                <option value="USD">USD</option>
            </select>

            <select wire:model.live="estadoFilter"
                class="w-full sm:w-auto md:w-44 border rounded px-3 py-2
                       bg-white text-gray-900 border-gray-300
                       dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700">
                <option value="all">Todos Estados</option>
                <option value="abierto">Abierto</option>
                <option value="cerrado">Cerrado</option>
            </select>

            <select wire:model.live="status"
                class="w-full sm:w-auto md:w-36 border rounded px-3 py-2
                       bg-white text-gray-900 border-gray-300
                       dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700">
                <option value="all">Todos</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>

            <select wire:model.live="perPage"
                class="w-full sm:w-auto md:w-24 border rounded px-3 py-2
                       bg-white text-gray-900 border-gray-300
                       dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    {{-- TABLET + DESKTOP: TABLA (expandible como Proyectos) --}}
    <div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
        <table class="w-full table-auto text-sm">

            <thead
                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-200">
                <tr class="text-left">

                    {{-- ID + toggle visible cuando NO es 2xl --}}
                    <th class="w-[90px] text-center p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('id')">
                        ID
                        @if ($sortField === 'id')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="p-2 cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('banco_id')">
                        Banco
                    </th>

                    <th class="p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('agente_servicio_id')">
                        Agente
                    </th>

                    <th class="w-[110px] p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('moneda')">
                        Moneda
                        @if ($sortField === 'moneda')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    <th class="w-[150px] p-2 cursor-pointer select-none whitespace-nowrap text-right"
                        wire:click="sortBy('monto')">
                        Monto
                        @if ($sortField === 'monto')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>

                    {{-- Solo en 2xl: transacción + fecha + saldo por rendir --}}
                    <th class="w-[150px] p-2 select-none whitespace-nowrap hidden 2xl:table-cell">
                        Nro. Transacción
                    </th>

                    <th class="w-[120px] p-2 select-none whitespace-nowrap hidden 2xl:table-cell">
                        Fecha
                    </th>

                    <th class="w-[160px] p-2 select-none whitespace-nowrap text-right hidden 2xl:table-cell">
                        Saldo por rendir
                    </th>

                    <th class="w-[120px] p-2 cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('estado')">
                        Estado
                        @if ($sortField === 'estado')
                            {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                        @endif
                    </th>
                </tr>
            </thead>

            @foreach ($presupuestos as $p)
                <tbody wire:key="pres-{{ $p->id }}" x-data="{ open: false }"
                    class="divide-y divide-gray-200 dark:divide-neutral-200">

                    <tr class="hover:bg-gray-100 dark:hover:bg-neutral-900">

                        {{-- ID + botón expandible (solo cuando NO es 2xl) --}}
                        <td class="p-1 whitespace-nowrap text-center" x-data="{ showToggle: !window.matchMedia('(min-width: 1536px)').matches }" x-init="const mq = window.matchMedia('(min-width: 1536px)');
                        const handler = e => showToggle = !e.matches;
                        mq.addEventListener('change', handler);">
                            <button type="button" x-show="showToggle" x-cloak
                                class="w-5 h-5 inline-flex items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-100 hover:text-gray-800
                                       dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:hover:text-white transition cursor-pointer"
                                @click.stop="open = !open" :aria-expanded="open">
                                <span x-show="!open">+</span>
                                <span x-show="open">−</span>
                            </button>
                            <span class="ml-1">{{ $p->id }}</span>
                        </td>

                        <td class="p-2 min-w-0">
                            <span class="block truncate max-w-full" title="{{ $p->banco?->nombre ?? '-' }}">
                                {{ $p->banco?->nombre ?? '-' }}
                            </span>
                            <span class="block text-xs text-gray-500 dark:text-neutral-400 truncate">
                                {{ $p->banco?->numero_cuenta ?? '' }}
                            </span>
                        </td>

                        <td class="p-2 min-w-0">
                            <span class="block truncate max-w-full" title="{{ $p->agente?->nombre ?? '-' }}">
                                {{ $p->agente?->nombre ?? '-' }}
                            </span>
                            <span class="block text-xs text-gray-500 dark:text-neutral-400 truncate">
                                CI: {{ $p->agente?->ci ?? '—' }}
                            </span>
                        </td>

                        <td class="p-2 whitespace-nowrap">
                            {{ $p->moneda }}
                        </td>

                        <td class="p-2 whitespace-nowrap text-right tabular-nums">
                            {{ number_format((float) $p->monto, 2, ',', '.') }}
                        </td>

                        <td class="p-2 whitespace-nowrap hidden 2xl:table-cell">
                            {{ $p->nro_transaccion }}
                        </td>

                        <td class="p-2 whitespace-nowrap hidden 2xl:table-cell">
                            {{ optional($p->fecha_presupuesto)->format('Y-m-d') ?? '-' }}
                        </td>

                        <td class="p-2 whitespace-nowrap text-right tabular-nums hidden 2xl:table-cell">
                            {{ number_format((float) ($p->saldo_por_rendir ?? 0), 2, ',', '.') }}
                        </td>

                        <td class="p-2 whitespace-nowrap">
                            @if ($p->estado === 'cerrado')
                                <span
                                    class="px-2 py-1 rounded text-xs bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200">
                                    Cerrado
                                </span>
                            @else
                                <span
                                    class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-200">
                                    Abierto
                                </span>
                            @endif
                        </td>
                    </tr>

                    {{-- Detalle expandible SOLO cuando NO es 2xl --}}
                    @php
                        $colspan = 6; // ID, Banco, Agente, Moneda, Monto, Estado
                    @endphp

                    <tr x-show="open" x-cloak
                        class="2xl:hidden bg-gray-100/60 dark:bg-neutral-900/40 border-b border-gray-200 dark:border-neutral-200">
                        <td class="pl-20 py-2" colspan="{{ $colspan }}">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">

                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">
                                        Nro. Transacción
                                    </span>
                                    <span class="block truncate">
                                        {{ $p->nro_transaccion }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">
                                        Fecha
                                    </span>
                                    <span class="block truncate">
                                        {{ optional($p->fecha_presupuesto)->format('Y-m-d') ?? '-' }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">
                                        Saldo banco antes
                                    </span>
                                    <span class="block truncate tabular-nums">
                                        {{ number_format((float) ($p->saldo_banco_antes ?? 0), 2, ',', '.') }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">
                                        Saldo banco después
                                    </span>
                                    <span class="block truncate tabular-nums">
                                        {{ number_format((float) ($p->saldo_banco_despues ?? 0), 2, ',', '.') }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">
                                        Rendido total
                                    </span>
                                    <span class="block truncate tabular-nums">
                                        {{ number_format((float) ($p->rendido_total ?? 0), 2, ',', '.') }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400">
                                        Saldo por rendir
                                    </span>
                                    <span class="block truncate tabular-nums">
                                        {{ number_format((float) ($p->saldo_por_rendir ?? 0), 2, ',', '.') }}
                                    </span>
                                </div>

                            </div>
                        </td>
                    </tr>
                </tbody>
            @endforeach

            @if ($presupuestos->count() === 0)
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                    <tr>
                        <td class="p-4 text-center text-gray-500 dark:text-neutral-400" colspan="10">
                            Sin resultados.
                        </td>
                    </tr>
                </tbody>
            @endif
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div>
        {{ $presupuestos->links() }}
    </div>

    {{-- MODAL: CREAR PRESUPUESTO --}}
    <x-ui.modal wire:key="presupuesto-modal-{{ $openModal ? 'open' : 'closed' }}" model="openModal"
        title="Registrar Presupuesto" maxWidth="sm:max-w-xl md:max-w-2xl" onClose="closeModal">
        <div class="space-y-2">

            {{-- ORIGEN Y DESTINO  --}}
            <div class="rounded border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">

                {{-- HEADER --}}
                <div class="px-4 py-3 border-b dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                        Origen y destino de fondos
                    </div>
                </div>

                {{-- CONTENT --}}
                <div class="p-4 space-y-4">

                    {{-- BANCO --}}
                    <div>
                        <label class="block text-sm mb-1">
                            Banco <span class="text-red-500">*</span>
                        </label>

                        <select wire:model.live="banco_id"
                            class="w-full rounded border px-3 py-2
                       bg-white dark:bg-neutral-900
                       border-gray-300 dark:border-neutral-700
                       text-gray-900 dark:text-neutral-100">
                            <option value="">Seleccione…</option>
                            @foreach ($bancos as $b)
                                <option value="{{ $b->id }}">
                                    {{ $b->nombre }} — {{ $b->numero_cuenta }} ({{ $b->moneda }})
                                </option>
                            @endforeach
                        </select>

                        @error('banco_id')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- AGENTE --}}
                    <div>
                        <label class="block text-sm mb-1">
                            Agente <span class="text-red-500">*</span>
                        </label>

                        <select wire:model.live="agente_servicio_id"
                            class="w-full rounded border px-3 py-2
                       bg-white dark:bg-neutral-900
                       border-gray-300 dark:border-neutral-700
                       text-gray-900 dark:text-neutral-100">
                            <option value="">Seleccione…</option>
                            @foreach ($agentes as $a)
                                <option value="{{ $a->id }}">
                                    {{ $a->nombre }} — CI: {{ $a->ci ?? '—' }}
                                </option>
                            @endforeach
                        </select>

                        @error('agente_servicio_id')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- DATOS DE LA OPERACIÓN --}}
            <div class="rounded border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="px-4 py-3 border-b dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Datos de la operación</div>
                </div>

                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm mb-1">
                                Monto <span class="text-red-500">*</span>
                            </label>
                            <input type="text" inputmode="decimal" wire:model.live="monto_formatted"
                                placeholder="0,00"
                                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100" />

                            @error('monto')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">
                                Fecha <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" wire:model="fecha_presupuesto"
                                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100" />
                            @error('fecha_presupuesto')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">
                                Nro. Transacción <span class="text-red-500">*</span>
                            </label>
                            <input wire:model.live="nro_transaccion" placeholder="Ej: 156285"
                                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100" />
                            @error('nro_transaccion')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if ($monto_excede_saldo)
                        <div class="mt-3 text-xs text-red-600 dark:text-red-400">
                            El monto no puede ser mayor al saldo actual del banco.
                        </div>
                    @endif

                    <p class="mt-3 text-xs text-gray-500 dark:text-neutral-400">
                        <span class="text-red-500">*</span> Campos obligatorios.
                    </p>
                </div>
            </div>

            {{-- IMPACTO FINANCIERO (RESUMEN SIMPLE) --}}
            <div class="rounded border bg-gray-50 dark:bg-neutral-900/40 dark:border-neutral-700">
                <div class="px-4 py-2 border-b dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                        Impacto financiero
                    </div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        Vista previa del movimiento.
                    </div>
                </div>

                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">

                        {{-- Banco --}}
                        <div class="rounded bg-white dark:bg-neutral-900/30 border dark:border-neutral-700 p-3">
                            <div class="text-xs font-semibold text-gray-700 dark:text-neutral-200 mb-2">
                                Banco
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-neutral-400">
                                    Saldo actual
                                </span>
                                <span class="font-medium tabular-nums">
                                    {{ number_format((float) $saldo_banco_actual_preview, 2, ',', '.') }}
                                    <span class="text-xs">
                                        {{ $moneda === 'USD' ? '$' : 'Bs' }}
                                    </span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-500 dark:text-neutral-400">
                                    Saldo después
                                </span>
                                <span class="font-medium tabular-nums">
                                    {{ number_format((float) $saldo_banco_despues_preview, 2, ',', '.') }}
                                    <span class="text-xs">
                                        {{ $moneda === 'USD' ? '$' : 'Bs' }}
                                    </span>
                                </span>
                            </div>
                        </div>

                        {{-- Agente --}}
                        <div class="rounded bg-white dark:bg-neutral-900/30 border dark:border-neutral-700 p-3">
                            <div class="text-xs font-semibold text-gray-700 dark:text-neutral-200 mb-2">
                                Agente
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-neutral-400">
                                    Saldo actual
                                </span>
                                <span class="font-medium tabular-nums">
                                    {{ number_format((float) $saldo_agente_actual_preview, 2, ',', '.') }}
                                    <span class="text-xs">
                                        {{ $moneda === 'USD' ? '$' : 'Bs' }}
                                    </span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-500 dark:text-neutral-400">
                                    Saldo después
                                </span>
                                <span class="font-medium tabular-nums">
                                    {{ number_format((float) $saldo_agente_despues_preview, 2, ',', '.') }}
                                    <span class="text-xs">
                                        {{ $moneda === 'USD' ? '$' : 'Bs' }}
                                    </span>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        @slot('footer')
            <button type="button" wire:click="closeModal"
                class="px-4 py-2 rounded border cursor-pointer
                   border-gray-300 dark:border-neutral-700
                   text-gray-700 dark:text-neutral-200
                   hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar
            </button>

            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                @disabled(!$this->puedeGuardar)
                class="px-4 py-2 cursor-pointer rounded bg-black text-white hover:opacity-90
               disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="save">Agregar</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        @endslot
    </x-ui.modal>

    {{-- MODAL: PANEL DE PRESUPUESTOS (segunda ventana) --}}
    <x-ui.modal wire:key="panel-presupuestos-{{ $openPanel ? 'open' : 'closed' }}" model="openPanel"
        title="Rendición - Presupuestos" maxWidth="sm:max-w-2xl md:max-w-4xl" onClose="closePanel">

        <div class="space-y-4">

            {{-- TOP BAR (compacta) --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
                <div class="px-4 py-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                    <div class="min-w-0">
                        <div class="text-base font-semibold text-gray-900 dark:text-neutral-100 truncate">
                            {{ $panelAgente?->nombre ?? 'Presupuestos del agente' }}
                        </div>

                        <div
                            class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-neutral-400">
                            <span>
                                CI:
                                <span class="font-semibold tabular-nums text-gray-800 dark:text-neutral-100">
                                    {{ $panelAgente?->ci ?? '—' }}
                                </span>
                            </span>

                            <span class="text-gray-300 dark:text-neutral-700">•</span>

                            {{-- Indicador del último creado --}}
                            <span>
                                Último resultado:
                                <span class="font-semibold">amarillo</span>
                            </span>

                            {{-- Badge moneda actual --}}
                            <span
                                class="text-[11px] px-2 py-0.5 rounded-full
                                   bg-gray-100 text-gray-700
                                   dark:bg-neutral-800 dark:text-neutral-200">
                                Mostrando: {{ ($panelMoneda ?? 'BOB') === 'USD' ? 'USD ($)' : 'Bolivianos (Bs)' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">

                        {{-- Selector MONEDA (Bs / USD) --}}
                        <div
                            class="inline-flex rounded-lg border border-gray-200 dark:border-neutral-700 overflow-hidden">
                            <button type="button" wire:click="setPanelMoneda('BOB')"
                                class="px-3 py-1.5 text-xs font-semibold transition
                            {{ ($panelMoneda ?? 'BOB') === 'BOB'
                                ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800' }}">
                                Bs
                            </button>

                            <button type="button" wire:click="setPanelMoneda('USD')"
                                class="px-3 py-1.5 text-xs font-semibold transition
                            {{ ($panelMoneda ?? 'BOB') === 'USD'
                                ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800' }}">
                                USD
                            </button>
                        </div>

                        {{-- Filtro ACTIVOS/TODOS --}}
                        <div
                            class="inline-flex rounded-lg border border-gray-200 dark:border-neutral-700 overflow-hidden">
                            <button type="button" wire:click="$set('soloActivos', false)"
                                class="px-3 py-1.5 text-xs font-semibold transition
                            {{ !$soloActivos
                                ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800' }}">
                                Todos
                            </button>

                            <button type="button" wire:click="$set('soloActivos', true)"
                                class="px-3 py-1.5 text-xs font-semibold transition
                            {{ $soloActivos
                                ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800' }}">
                                Activos
                            </button>
                        </div>

                    </div>
                </div>

                {{-- KPIs (según moneda seleccionada) --}}
                <div class="border-t dark:border-neutral-700 px-4 py-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                        {{-- Saldo a rendir (según panelMoneda) --}}
                        <div
                            class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50/70 dark:bg-neutral-900 p-3">
                            <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                {{ ($panelMoneda ?? 'BOB') === 'USD' ? 'USD a rendir' : 'Bs a rendir' }}
                            </div>

                            <div class="mt-1 text-lg font-bold tabular-nums text-gray-900 dark:text-neutral-100">
                                {{ number_format(
                                    (float) (($panelMoneda ?? 'BOB') === 'USD' ? $panelAgente->saldo_usd ?? 0 : $panelAgente->saldo_bob ?? 0),
                                    2,
                                    ',',
                                    '.',
                                ) }}
                            </div>
                        </div>

                        {{-- TOTAL FALTA (según panelMoneda) --}}
                        <div
                            class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50/70 dark:bg-neutral-900 p-3">
                            <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                                Falta por rendir ({{ $panelMoneda ?? 'BOB' }})
                            </div>

                            <div class="mt-1 text-lg font-extrabold tabular-nums text-red-600 dark:text-red-300">
                                {{ number_format((float) $panelTotalFalta, 2, ',', '.') }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- TABLA (scroll interno + thead sticky) --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
                <div class="max-h-[52vh] overflow-auto">
                    <table class="w-full text-sm">
                        <thead
                            class="sticky top-0 z-10 bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-700">
                            <tr
                                class="text-left text-[11px] uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                                <th class="p-3 w-[70px]">Nro</th>
                                <th class="p-3 w-[180px]">Fecha</th>
                                <th class="p-3 text-right">Presupuesto</th>
                                <th class="p-3 text-right">Rendido</th>
                                <th class="p-3 text-right">Saldo</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            @forelse($panelPresupuestos as $i => $p)
                                @php
                                    $isLast = (int) $lastCreatedId === (int) $p->id;
                                    $saldo = (float) $p->saldo_por_rendir;
                                @endphp

                                <tr
                                    class="{{ $isLast ? 'bg-yellow-50 dark:bg-yellow-500/10' : '' }} hover:bg-gray-50 dark:hover:bg-neutral-800/60 transition">
                                    <td class="p-3 text-gray-700 dark:text-neutral-200">
                                        {{ $i + 1 }}
                                    </td>

                                    <td class="p-3 whitespace-nowrap text-gray-700 dark:text-neutral-200">
                                        <div class="font-medium">
                                            {{ optional($p->fecha_presupuesto)->format('Y-m-d H:i') ?? '-' }}
                                        </div>

                                    </td>

                                    <td class="p-3 text-right tabular-nums text-gray-900 dark:text-neutral-100">
                                        {{ number_format((float) $p->monto, 2, ',', '.') }}
                                    </td>

                                    <td class="p-3 text-right tabular-nums text-gray-900 dark:text-neutral-100">
                                        {{ number_format((float) $p->rendido_total, 2, ',', '.') }}
                                    </td>

                                    <td
                                        class="p-3 text-right tabular-nums font-semibold
                                    {{ $saldo <= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-300' }}">
                                        {{ number_format($saldo, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-6 text-center text-gray-500 dark:text-neutral-400" colspan="5">
                                        Sin registros.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Footer tabla --}}
                <div
                    class="px-4 py-2 border-t dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900 flex items-center justify-between">
                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        Registros:
                        <span class="font-semibold text-gray-800 dark:text-neutral-100">
                            {{ count($panelPresupuestos) }}
                        </span>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                        Saldo total ({{ $panelMoneda ?? 'BOB' }}):
                        <span class="font-extrabold tabular-nums text-red-600 dark:text-red-300">
                            {{ number_format((float) $panelTotalFalta, 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

        @slot('footer')
            <div class="flex items-center justify-end gap-2">
                <button type="button" wire:click="closePanel"
                    class="px-4 py-2 rounded-lg border
                       border-gray-300 dark:border-neutral-700
                       text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800 transition">
                    Cerrar
                </button>
            </div>
        @endslot
    </x-ui.modal>



</div>
