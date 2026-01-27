{{-- MODAL EDITOR --}}
<x-ui.modal wire:key="rendicion-editor-{{ $openEditor ? 'open' : 'closed' }}" model="openEditor"
    title="Planilla de Rendición" maxWidth="sm:max-w-2xl md:max-w-5xl" onClose="closeEditor">

    @php
        $hasCompras = !empty($editorCompras) && count($editorCompras) > 0;
        $hasDevoluciones = !empty($editorDevoluciones) && count($editorDevoluciones) > 0;
        $hasMovs = $hasCompras || $hasDevoluciones;
    @endphp

    <div class="space-y-4">
        {{-- CABECERA --}}
        <div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
            <div class="px-4 py-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="text-base font-semibold text-gray-900 dark:text-neutral-100 truncate">
                        Rendición:
                        <span class="font-extrabold tabular-nums">
                            {{ $editorRendicionNro ?? '#' . ($editorRendicionId ?? '—') }}
                        </span>
                    </div>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-neutral-400">
                        <span>
                            Agente:
                            <span class="font-semibold text-gray-800 dark:text-neutral-100">
                                {{ $editorAgenteNombre ?? '—' }}
                            </span>
                        </span>
                        <span class="text-gray-300 dark:text-neutral-700">•</span>
                        <span>
                            Fecha:
                            <span class="font-semibold tabular-nums text-gray-800 dark:text-neutral-100">
                                {{ $editorFecha ? \Carbon\Carbon::parse($editorFecha)->format('d/m/Y') : '—' }}
                            </span>
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    {{-- Tabs --}}
                    <div class="inline-flex rounded-lg border border-gray-200 dark:border-neutral-700 overflow-hidden">
                        <button type="button" wire:click="$set('editorTab','compra')"
                            class="px-3 py-1.5 text-xs font-semibold transition
                            {{ ($editorTab ?? 'compra') === 'compra'
                                ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800' }}">
                            Compra
                        </button>

                        <button type="button" wire:click="$set('editorTab','devolucion')"
                            class="px-3 py-1.5 text-xs font-semibold transition
                            {{ ($editorTab ?? 'compra') === 'devolucion'
                                ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800' }}">
                            Devolución
                        </button>
                    </div>
                </div>
            </div>

            {{-- KPIs --}}
            <div class="border-t dark:border-neutral-700 px-4 py-3">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div
                        class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50/70 dark:bg-neutral-900 p-2">
                        <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400 font-bold">
                            Presupuesto ({{ $editorMonedaBase ?? 'BOB' }})
                        </div>
                        <div class="mt-1 text-lg font-bold tabular-nums text-gray-900 dark:text-neutral-100">
                            {{ number_format((float) ($editorPresupuestoTotal ?? 0), 2, ',', '.') }}
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50/70 dark:bg-neutral-900 p-2">
                        <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400 font-bold">
                            Rendido ({{ $editorMonedaBase ?? 'BOB' }})
                        </div>
                        <div class="mt-1 text-lg font-bold tabular-nums text-green-600 dark:text-green-100">
                            {{ number_format((float) ($editorRendidoTotal ?? 0), 2, ',', '.') }}
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50/70 dark:bg-neutral-900 p-2">
                        <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-neutral-400 font-bold">
                            Saldo ({{ $editorMonedaBase ?? 'BOB' }})
                        </div>
                        <div
                            class="mt-1 text-lg font-extrabold tabular-nums
                            {{ (float) ($editorSaldo ?? 0) <= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-300' }}">
                            {{ number_format((float) ($editorSaldo ?? 0), 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM --}}
        <div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
            <div class="px-4 py-3 border-b dark:border-neutral-700">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                    {{ ($editorTab ?? 'compra') === 'devolucion' ? 'Insertar Devolución' : 'Insertar Compra' }}

                </div>
                <div class="text-xs text-gray-500 dark:text-neutral-400">
                    Si la moneda difiere de la moneda base, registra tipo de cambio (TC = Bs por 1 USD).
                </div>
            </div>

            <div class="p-4 space-y-4">
                {{-- fila 1 --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Fecha</label>
                        <input type="date" wire:model.live="mov_fecha"
                            class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100" />
                        @error('mov_fecha')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Moneda
                            movimiento</label>
                        <select wire:model.live="mov_moneda"
                            class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100">
                            <option value="BOB">BOB</option>
                            <option value="USD">USD</option>
                        </select>
                        @error('mov_moneda')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Tipo cambio
                            (opcional)</label>
                        <input type="text" inputmode="decimal" wire:model.live.debounce.600ms="mov_tipo_cambio"
                            placeholder="Ej: 6,96"
                            class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100" />
                        <div class="mt-1 text-[11px] text-gray-500 dark:text-neutral-400">
                            Solo si {{ $editorMonedaBase ?? 'BOB' }} != {{ $mov_moneda ?? 'BOB' }}.
                        </div>
                        @error('mov_tipo_cambio')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Monto</label>
                        <input type="text" inputmode="decimal" wire:model.live.debounce.600ms="mov_monto"
                            placeholder="0,00"
                            class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100" />
                        @error('mov_monto')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- compra --}}
                @if (($editorTab ?? 'compra') === 'compra')
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Entidad</label>
                            <select wire:model.live="mov_entidad_id"
                                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100">
                                <option value="">Seleccione…</option>
                                @foreach ($editorEntidades ?? [] as $e)
                                    <option value="{{ $e['id'] }}">{{ $e['nombre'] }}</option>
                                @endforeach
                            </select>
                            @error('mov_entidad_id')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Proyecto</label>

                            <select wire:model.live="mov_proyecto_id" @disabled(empty($mov_entidad_id))
                                class="w-full rounded border px-3 py-2 disabled:opacity-50 disabled:cursor-not-allowed">

                                <option value="">
                                    {{ empty($mov_entidad_id) ? 'Seleccione una entidad primero…' : 'Seleccione…' }}
                                </option>

                                @foreach ($editorProyectos as $p)
                                    <option value="{{ $p['id'] }}">{{ $p['nombre'] }}</option>
                                @endforeach
                            </select>

                            @error('mov_proyecto_id')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Tipo
                                comprobante</label>
                            <select wire:model.live="mov_tipo_comprobante"
                                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100">
                                <option value="">Seleccione…</option>
                                <option value="FACTURA">FACTURA</option>
                                <option value="RECIBO">RECIBO</option>
                                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                            </select>
                            @error('mov_tipo_comprobante')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Nro
                                comprobante</label>
                            <input wire:model.live.debounce.500ms="mov_nro_comprobante" placeholder="Ej: F-50"
                                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100" />
                            @error('mov_nro_comprobante')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- devolución --}}
                @if (($editorTab ?? 'compra') === 'devolucion')
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Banco</label>
                            <select wire:model.live="mov_banco_id"
                                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100">
                                <option value="">Seleccione…</option>
                                @foreach ($editorBancos ?? [] as $b)
                                    <option value="{{ $b['id'] }}">{{ $b['nombre'] }} —
                                        {{ $b['numero_cuenta'] }} ({{ $b['moneda'] }})</option>
                                @endforeach
                            </select>
                            @error('mov_banco_id')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Nro
                                transacción</label>
                            <input wire:model.live.debounce.500ms="mov_nro_transaccion" placeholder="Ej: T-82113541"
                                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100" />
                            @error('mov_nro_transaccion')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Observación</label>
                            <input wire:model.live.debounce.500ms="mov_observacion" placeholder="Opcional…"
                                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100" />
                            @error('mov_observacion')
                                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- FOTO + BOTONES --}}
                <div class="flex flex-col md:flex-row md:items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Foto
                            (opcional)</label>
                        <input type="file" wire:model="mov_foto"
                            class="w-full text-sm
                                   file:mr-3 file:px-3 file:py-2 file:rounded file:border
                                   file:border-gray-300 dark:file:border-neutral-700
                                   file:bg-white dark:file:bg-neutral-900
                                   file:text-gray-700 dark:file:text-neutral-200" />
                        @error('mov_foto')
                            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="addMovimiento" wire:loading.attr="disabled"
                            wire:target="addMovimiento, mov_foto"
                            class="px-5 py-2 rounded-lg bg-black text-white hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="addMovimiento, mov_foto">
                                Agregar
                            </span>
                            <span wire:loading wire:target="addMovimiento, mov_foto">
                                Procesando…
                            </span>
                        </button>
                    </div>
                </div>

                {{-- MENSAJE --}}
                @if (!empty($editorCuadreMsg))
                    <div
                        class="text-xs rounded-lg border border-yellow-200 bg-yellow-50 text-yellow-900 p-3 dark:border-yellow-500/30 dark:bg-yellow-500/10 dark:text-yellow-200">
                        {{ $editorCuadreMsg }}
                    </div>
                @endif
            </div>
        </div>

        {{-- TABLAS (SOLO SI HAY DATOS) --}}
        @if ($hasMovs)
            <div class="grid grid-cols-1 gap-4">

                {{-- DEVOLUCIONES --}}
                @if ($hasDevoluciones)
                    <div
                        class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
                        <div class="px-4 py-2 border-b dark:border-neutral-700 flex items-center justify-between">
                            <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Devoluciones
                            </div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                Total devolución (base):
                                <span class="font-extrabold tabular-nums text-gray-800 dark:text-neutral-100">
                                    {{ number_format((float) ($editorTotalDevolucionesBase ?? 0), 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <div class="max-h-[28vh] overflow-auto">
                            <table class="w-full text-sm">
                                <thead
                                    class="sticky top-0 z-10 bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-700">
                                    <tr
                                        class="text-left text-[11px] uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                                        <th class="p-3 w-[60px]">Nro</th>
                                        <th class="p-3 w-[120px]">Fecha</th>
                                        <th class="p-3">Banco</th>
                                        <th class="p-3">Transacción</th>
                                        <th class="p-3 text-right">Monto</th>
                                        <th class="p-3 text-right">Base</th>
                                        <th class="p-3 text-center w-[90px]">Foto</th>
                                        <th class="p-3 text-right w-[140px]">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                    @foreach ($editorDevoluciones ?? [] as $i => $m)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/60 transition">
                                            <td class="p-3 text-gray-700 dark:text-neutral-200">
                                                {{ $i + 1 }}</td>
                                            <td class="p-3 text-gray-700 dark:text-neutral-200 whitespace-nowrap">
                                                {{ $m->fecha?->format('d M Y') ?? '-' }}
                                            </td>
                                            <td class="p-3 text-gray-700 dark:text-neutral-200">
                                                {{ $m->banco?->nombre ?? '—' }}
                                            </td>
                                            <td class="p-3 text-gray-700 dark:text-neutral-200">
                                                {{ $m->nro_transaccion ?? '—' }}
                                            </td>
                                            <td
                                                class="p-3 text-right tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                {{ $m->moneda }}
                                            </td>
                                            <td
                                                class="p-3 text-right tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                                {{ $editorMonedaBase ?? 'BOB' }}
                                            </td>
                                            <td class="p-3 text-center">
                                                @if (!empty($m->foto_path))
                                                    <button type="button" wire:click="verFoto({{ $m->id }})"
                                                        class="text-xs underline text-blue-700 dark:text-blue-300">
                                                        Ver
                                                    </button>
                                                @else
                                                    <span class="text-xs text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="p-3 text-right">
                                                <div class="inline-flex gap-2">

                                                    <button type="button" x-data
                                                        x-on:click="$dispatch('swal:delete-movimiento', { id: {{ $m->id }}, monto: '{{ $m->monto }}' })"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded border border-red-200 text-red-700 hover:bg-red-50
                                                            dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10 transition"
                                                        title="Eliminar">
                                                        {{-- Icono papelera --}}
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

                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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

                        <div class="max-h-[28vh] overflow-auto">
                            <table class="w-full text-sm">
                                <thead
                                    class="sticky top-0 z-10 bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-700">
                                    <tr
                                        class="text-left text-[11px] uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                                        <th class="p-3 w-[60px]">Nro</th>
                                        <th class="p-3 w-[120px]">Fecha</th>
                                        <th class="p-3">Entidad</th>
                                        <th class="p-3">Proyecto</th>
                                        <th class="p-3">Comprobante</th>
                                        <th class="p-3 text-right">Monto</th>
                                        <th class="p-3 text-right">Base</th>
                                        <th class="p-3 text-center w-[90px]">Foto</th>
                                        <th class="p-3 text-right w-[140px]">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                    @foreach ($editorCompras ?? [] as $i => $m)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/60 transition">
                                            <td class="p-3 text-gray-700 dark:text-neutral-200">
                                                {{ $i + 1 }}</td>
                                            <td class="p-3 text-gray-700 dark:text-neutral-200 whitespace-nowrap">
                                                {{ $m->fecha?->format('d M Y') ?? '-' }}
                                            </td>
                                            <td class="p-3 text-gray-700 dark:text-neutral-200">
                                                {{ $m->entidad?->nombre ?? '—' }}
                                            </td>
                                            <td class="p-3 text-gray-700 dark:text-neutral-200">
                                                {{ $m->proyecto?->nombre ?? '—' }}
                                            </td>
                                            <td class="p-3 text-gray-700 dark:text-neutral-200">
                                                {{ $m->tipo_comprobante ?? '—' }}
                                                @if (!empty($m->nro_comprobante))
                                                    <span class="text-gray-400">•</span>
                                                    {{ $m->nro_comprobante }}
                                                @endif
                                            </td>
                                            <td
                                                class="p-3 text-right tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto, 2, ',', '.') }}
                                                {{ $m->moneda }}
                                            </td>
                                            <td
                                                class="p-3 text-right tabular-nums text-gray-900 dark:text-neutral-100">
                                                {{ number_format((float) $m->monto_base, 2, ',', '.') }}
                                                {{ $editorMonedaBase ?? 'BOB' }}
                                            </td>
                                            <td class="p-3 text-center">
                                                @if (!empty($m->foto_path))
                                                    <button type="button" wire:click="verFoto({{ $m->id }})"
                                                        class="text-xs underline text-blue-700 dark:text-blue-300">
                                                        Ver
                                                    </button>
                                                @else
                                                    <span class="text-xs text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="p-3 text-right">
                                                <div class="inline-flex gap-2">
                                                    <button type="button" x-data
                                                        x-on:click="$dispatch('swal:delete-movimiento', { id: {{ $m->id }}, monto: '{{ $m->monto }}' })"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded border border-red-200 text-red-700 hover:bg-red-50
                                                            dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10 transition"
                                                        title="Eliminar">
                                                        {{-- Icono papelera --}}
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
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- FOOTER --}}
                        <div
                            class="px-4 py-2 border-t dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900 flex items-center justify-between">
                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                Total rendido (base):
                                <span class="font-extrabold tabular-nums text-gray-800 dark:text-neutral-100">
                                    {{ number_format((float) ($editorRendidoTotal ?? 0), 2, ',', '.') }}
                                </span>
                            </div>

                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                Saldo (base):
                                <span
                                    class="font-extrabold tabular-nums {{ (float) ($editorSaldo ?? 0) <= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-300' }}">
                                    {{ number_format((float) ($editorSaldo ?? 0), 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        @else
            {{-- Opcional: estado vacío (si quieres ocultar todo, elimina este bloque) --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 p-4">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">
                    Aún no hay movimientos
                </div>
                <div class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                    Cuando registres una compra o devolución, aquí se mostrará el detalle en tablas.
                </div>
            </div>
        @endif
    </div>

    @slot('footer')
        <div class="flex items-center justify-end gap-2">
            <button type="button" wire:click="cerrarRendicion" wire:loading.attr="disabled"
                wire:target="cerrarRendicion" @disabled(((float) ($editorSaldo ?? 0)) > 0)
                class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="cerrarRendicion">Cerrar rendición</span>
                <span wire:loading wire:target="cerrarRendicion">Cerrando…</span>
            </button>

            <button type="button" wire:click="closeEditor"
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 transition">
                Cerrar
            </button>
        </div>
    @endslot
</x-ui.modal>
