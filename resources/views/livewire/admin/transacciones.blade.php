<div>
    {{-- Header & Title --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Transacciones
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Consulta y consolidación de todos los movimientos de ingresos y egresos de los módulos.
            </p>
        </div>

        <div class="flex gap-2">
            <button wire:click="exportBrowser"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Exportar CSV
            </button>
        </div>
    </div>

    {{-- Resumen Cards --}}
    @php
        $isBoth = empty($moneda);
        $valClassBase = $isBoth ? 'text-lg' : 'text-2xl';
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Ingresos --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group flex flex-col">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-3">Total Ingresos</p>
            <div class="mt-auto flex flex-col gap-2 relative z-10">
                @if ($isBoth || $moneda === 'BOB')
                    <div class="flex items-center justify-between {{ $isBoth ? 'pb-2 border-b border-gray-100' : '' }}">
                        <span
                            class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">BOB</span>
                        <span class="{{ $valClassBase }} font-bold text-gray-900 tabular-nums">
                            {{ number_format((float) ($totales->ingresos_bob ?? 0), 2, ',', '.') }}
                        </span>
                    </div>
                @endif
                @if ($isBoth || $moneda === 'USD')
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">USD</span>
                        <span class="{{ $valClassBase }} font-bold text-gray-900 tabular-nums">
                            {{ number_format((float) ($totales->ingresos_usd ?? 0), 2, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Egresos --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group flex flex-col">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-3">Total Egresos</p>
            <div class="mt-auto flex flex-col gap-2 relative z-10">
                @if ($isBoth || $moneda === 'BOB')
                    <div class="flex items-center justify-between {{ $isBoth ? 'pb-2 border-b border-gray-100' : '' }}">
                        <span
                            class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">BOB</span>
                        <span class="{{ $valClassBase }} font-bold text-gray-900 tabular-nums">
                            {{ number_format((float) ($totales->egresos_bob ?? 0), 2, ',', '.') }}
                        </span>
                    </div>
                @endif
                @if ($isBoth || $moneda === 'USD')
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">USD</span>
                        <span class="{{ $valClassBase }} font-bold text-gray-900 tabular-nums">
                            {{ number_format((float) ($totales->egresos_usd ?? 0), 2, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Neto --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group flex flex-col">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3">
                    </path>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-3">Flujo Neto</p>
            @php
                $neto_bob = ($totales->ingresos_bob ?? 0) - ($totales->egresos_bob ?? 0);
                $neto_usd = ($totales->ingresos_usd ?? 0) - ($totales->egresos_usd ?? 0);
            @endphp
            <div class="mt-auto flex flex-col gap-2 relative z-10">
                @if ($isBoth || $moneda === 'BOB')
                    <div
                        class="flex items-center justify-between {{ $isBoth ? 'pb-2 border-b border-gray-100' : '' }}">
                        <span
                            class="text-[11px] font-semibold {{ $neto_bob >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50' }} px-1.5 py-0.5 rounded">BOB</span>
                        <span
                            class="{{ $valClassBase }} font-bold {{ $neto_bob >= 0 ? 'text-emerald-600' : 'text-rose-600' }} tabular-nums">
                            {{ number_format($neto_bob, 2, ',', '.') }}
                        </span>
                    </div>
                @endif
                @if ($isBoth || $moneda === 'USD')
                    <div class="flex items-center justify-between">
                        <span
                            class="text-[11px] font-semibold {{ $neto_usd >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50' }} px-1.5 py-0.5 rounded">USD</span>
                        <span
                            class="{{ $valClassBase }} font-bold {{ $neto_usd >= 0 ? 'text-emerald-600' : 'text-rose-600' }} tabular-nums">
                            {{ number_format($neto_usd, 2, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Cantidad --}}
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group flex flex-col">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                    </path>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-2">Transacciones</p>
            <p class="mt-auto text-2xl font-bold text-gray-900 relative z-10">
                {{ number_format((int) ($totales->total_transacciones ?? 0), 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Filtros Toolbar --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="p-4 border-b border-gray-200 bg-gray-50 rounded-t-xl flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                    </path>
                </svg>
                Filtros Avanzados
            </h3>
            <button wire:click="limpiarFiltros" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                Limpiar Filtros
            </button>
        </div>
        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

            {{-- Búsqueda --}}
            <div class="col-span-1 sm:col-span-2 lg:col-span-1">
                <label class="block text-xs font-medium text-gray-700 mb-1">Buscar</label>
                <div class="relative">
                    <input wire:model.live.debounce.500ms="search" type="text"
                        placeholder="Concepto, ref, notas..."
                        class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            {{-- Banco --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Banco / Cuenta</label>
                <select wire:model.live="banco_id"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Todos los bancos --</option>
                    @foreach ($bancos as $b)
                        <option value="{{ $b->id }}">{{ $b->nombre }} - {{ $b->numero_cuenta }}
                            ({{ $b->moneda }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tipo --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Tipo de Mov.</label>
                <select wire:model.live="tipo"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Todos --</option>
                    <option value="INGRESO">Ingresos solo</option>
                    <option value="EGRESO">Egresos solo</option>
                </select>
            </div>

            {{-- Módulo --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Módulo Origen</label>
                <select wire:model.live="modulo"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Todos --</option>
                    @foreach ($modulos_disponibles as $mod)
                        <option value="{{ $mod }}">{{ $mod }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Rango de Fechas --}}
            <div class="col-span-1 sm:col-span-2 flex gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Desde</label>
                    <input wire:model.live="date_from" type="date"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Hasta</label>
                    <input wire:model.live="date_to" type="date"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            {{-- Con/Sin Comprobante --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Comprobante</label>
                <select wire:model.live="has_attachment"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Todos --</option>
                    <option value="yes">Con adjunto</option>
                    <option value="no">Sin adjunto</option>
                </select>
            </div>

            {{-- Estado --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Estado</label>
                <input wire:model.live.debounce.300ms="estado" type="text" placeholder="Ej: PAGADO, PENDIENTE..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

        </div>
    </div>

    {{-- Tabla de Transacciones --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Módulo / Banco</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Concepto y Ref.</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Monto</th>
                        <th scope="col"
                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado / Adjunto</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($transacciones as $t)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">Creación:
                                    {{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') }}</span>
                                <div class="text-xs text-gray-500">Pago:
                                    {{ \Carbon\Carbon::parse($t->fecha)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mb-1 border border-gray-200">
                                    {{ $t->modulo }}
                                </span>
                                <div class="text-xs text-gray-500 truncate max-w-xs"
                                    title="{{ collect($bancos)->firstWhere('id', $t->banco_id)?->nombre ?? 'N/A' }}">
                                    🏦 {{ collect($bancos)->firstWhere('id', $t->banco_id)?->nombre ?? 'N/A' }}
                                    @if (collect($bancos)->firstWhere('id', $t->banco_id)?->numero_cuenta)
                                        - {{ collect($bancos)->firstWhere('id', $t->banco_id)->numero_cuenta }}
                                    @endif
                                    ({{ collect($bancos)->firstWhere('id', $t->banco_id)?->moneda ?? $t->moneda }})
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 mb-0.5">{{ $t->concepto }}</div>
                                <div class="text-xs text-gray-500 flex gap-2">
                                    @if ($t->referencia)
                                        <span class="text-indigo-600 font-medium">Ref: {{ $t->referencia }}</span>
                                    @endif
                                    @if ($t->notas)
                                        <span class="truncate block max-w-xs text-gray-400"
                                            title="{{ $t->notas }}">| Nota: {{ $t->notas }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span
                                    class="text-sm font-bold {{ $t->tipo_movimiento === 'INGRESO' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $t->tipo_movimiento === 'INGRESO' ? '+' : '-' }}
                                    {{ number_format($t->monto, 2) }}
                                </span>
                                <div class="text-xs font-medium text-gray-500">{{ $t->moneda }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="mb-1">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ strtoupper($t->estado) === 'PAGADO' || strtoupper($t->estado) === 'COMPLETADO'
                                            ? 'bg-emerald-100 text-emerald-800'
                                            : (strtoupper($t->estado) === 'PENDIENTE'
                                                ? 'bg-amber-100 text-amber-800'
                                                : 'bg-gray-100 text-gray-800') }}">
                                        {{ $t->estado ?: 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex justify-center">
                                    @if ($t->comprobante)
                                        <a href="{{ Storage::url($t->comprobante) }}" target="_blank"
                                            class="text-gray-400 hover:text-indigo-600 transition-colors"
                                            title="Ver comprobante">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                </path>
                                            </svg>
                                        </a>
                                    @else
                                        <span class="text-gray-300" title="Sin comprobante">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                                </path>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if ($t->url_origen)
                                    <a href="{{ $t->url_origen }}"
                                        class="text-indigo-600 hover:text-indigo-900 inline-flex items-center gap-1 group">
                                        Origen
                                        <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                        </svg>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <p class="text-sm font-medium">No se encontraron transacciones con los filtros
                                    actuales.</p>
                                <button wire:click="limpiarFiltros"
                                    class="mt-2 text-sm text-indigo-600 font-medium hover:text-indigo-500">Limpiar los
                                    filtros</button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transacciones->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $transacciones->links() }}
            </div>
        @endif
    </div>
</div>
