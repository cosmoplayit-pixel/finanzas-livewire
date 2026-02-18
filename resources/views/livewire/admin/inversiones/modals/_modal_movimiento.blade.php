{{-- resources/views/livewire/admin/inversiones/modals/_modal_movimiento.blade.php --}}

<div>
    <x-ui.modal wire:key="inversion-movimientos-{{ $openMovimientosModal ? 'open' : 'closed' }}"
        model="openMovimientosModal" title="Movimientos de inversión" maxWidth="sm:max-w-2xl md:max-w-7xl"
        onClose="closeMovimientos">
        @php
            $inv = $inversion;
            $isBanco = strtoupper((string) ($inv?->tipo ?? '')) === 'BANCO';
            $mon = strtoupper((string) ($inv?->moneda ?? 'BOB'));

            // Totales
            $sumCapital = 0.0;
            $sumUtilidad = 0.0;

            $sumTotal = 0.0;
            $sumInteres = 0.0;
            $sumMora = 0.0;
            $sumComision = 0.0;
            $sumSeguro = 0.0;

            $fmtMoney = function (float $n) use ($mon) {
                $v = number_format($n, 2, ',', '.');
                return $mon === 'USD' ? '$ ' . $v : $v . ' Bs';
            };
        @endphp

        <div class="space-y-3">

            {{-- HEADER / RESUMEN --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="px-4 py-3 border-b dark:border-neutral-700">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                        {{-- IZQUIERDA --}}
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">
                                {{ $inv?->nombre_completo ?? '—' }}
                            </div>

                            <div
                                class="mt-1 text-xs text-gray-500 dark:text-neutral-400 flex flex-wrap items-center gap-2">
                                <span class="font-mono">{{ $inv?->codigo ?? '—' }}</span>
                                <span class="text-gray-300 dark:text-neutral-600">•</span>
                                <span>{{ $inv?->tipo ?? '—' }}</span>
                                <span class="text-gray-300 dark:text-neutral-600">•</span>

                                <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M12 1v22" />
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                                    </svg>
                                    <span class="font-semibold">{{ $mon }}</span>
                                </span>

                                <span class="text-gray-300 dark:text-neutral-600">•</span>

                                <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M3 10h18" />
                                        <path d="M5 10V20" />
                                        <path d="M19 10V20" />
                                        <path d="M9 10V20" />
                                        <path d="M15 10V20" />
                                        <path d="M2 20h20" />
                                        <path d="M12 2 2 7h20L12 2z" />
                                    </svg>
                                    <span class="truncate max-w-[280px]">
                                        {{ $inv?->banco?->nombre ?? 'Sin banco' }}
                                    </span>
                                </span>
                            </div>
                        </div>

                        {{-- DERECHA: BOTONES --}}
                        <div class="shrink-0 w-full sm:w-auto">
                            <div class="w-full sm:w-auto flex flex-wrap justify-end gap-2">
                                @if (!$isBanco)
                                    {{-- PRIVADO: Pagar utilidad --}}
                                    <button type="button" @disabled(!$inv)
                                        wire:click="$dispatch('openPagarUtilidad', { inversionId: {{ $inv?->id ?? 0 }} })"
                                        class="h-9 px-3 cursor-pointer rounded-lg text-sm font-semibold inline-flex items-center gap-2
                                            bg-emerald-600 text-white hover:opacity-90
                                            disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Pagar utilidad">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M12 1v22" />
                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                                        </svg>
                                        Pagar utilidad
                                    </button>
                                @else
                                    {{-- BANCO: Registrar pago --}}
                                    <button type="button" @disabled(!$inv)
                                        wire:click="$dispatch('openPagarBanco', { inversionId: {{ $inv?->id ?? 0 }} })"
                                        class="h-9 px-3 cursor-pointer rounded-lg text-sm font-semibold inline-flex items-center gap-2
                                            bg-indigo-600 text-white hover:opacity-90
                                            disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Registrar pago banco">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M3 10h18" />
                                            <path d="M5 10V20" />
                                            <path d="M19 10V20" />
                                            <path d="M9 10V20" />
                                            <path d="M15 10V20" />
                                            <path d="M2 20h20" />
                                            <path d="M12 2 2 7h20L12 2z" />
                                        </svg>
                                        Registrar pago banco
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                {{-- CARDS RESUMEN --}}
                <div class="p-4">
                    @if (!$isBanco)
                        {{-- PRIVADO --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Capital actual</div>
                                <div class="text-sm font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                    {{ $fmtMoney((float) ($inv?->capital_actual ?? 0)) }}
                                </div>
                            </div>

                            <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Fecha inicio</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                    {{ $inv?->fecha_inicio?->format('d/m/Y') ?? '—' }}
                                </div>
                            </div>

                            <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Vencimiento</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                    {{ $inv?->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                                </div>
                            </div>

                            <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                                <div class="text-xs text-gray-500 dark:text-neutral-400">% utilidad</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 tabular-nums">
                                    {{ number_format((float) ($inv?->porcentaje_utilidad ?? 0), 2, ',', '.') }}%
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- BANCO --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                            <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Saldo deuda</div>
                                <div class="text-sm font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                    {{ $fmtMoney((float) ($inv?->capital_actual ?? 0)) }}
                                </div>
                            </div>

                            <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Fecha inicio</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                    {{ $inv?->fecha_inicio?->format('d/m/Y') ?? '—' }}
                                </div>
                            </div>

                            <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Vencimiento</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                    {{ $inv?->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                                </div>
                            </div>

                            <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Plazo (meses)</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                    {{ (int) ($inv?->plazo_meses ?? 0) }}
                                </div>
                            </div>

                            <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Día de pago</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100">
                                    {{ (int) ($inv?->dia_pago ?? 0) }}
                                </div>
                            </div>

                            <div class="rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700 p-3">
                                <div class="text-xs text-gray-500 dark:text-neutral-400">Tasa anual / Amortización</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-neutral-100 tabular-nums">
                                    {{ number_format((float) ($inv?->tasa_anual ?? 0), 2, ',', '.') }}%
                                    <span class="text-gray-300 dark:text-neutral-600">•</span>
                                    {{ $inv?->sistema ? ucfirst(strtolower($inv->sistema)) : '—' }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- TABLA MOVIMIENTOS --}}
            <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-[13px] min-w-[1100px]">
                        <thead class="bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-700">
                            <tr class="text-center text-[12px] tracking-wide text-gray-600 dark:text-neutral-300">
                                <th class="p-3 w-[40px]">#</th>
                                <th class="p-3 w-[110px]">Fecha</th>
                                <th class="p-3 min-w-[180px] text-left">Descripción</th>
                                <th class="p-3 w-[110px]">Fecha pago</th>
                                <th class="p-3 w-[140px]">Comprobante</th>

                                @if (!$isBanco)
                                    <th class="p-3 w-[100px]">% utilidad</th>
                                    <th class="p-3 w-[150px]">Capital</th>
                                    <th class="p-3 w-[150px]">Utilidad</th>
                                @else
                                    <th class="p-3 w-[140px]">Concepto</th>
                                    <th class="p-3 w-[140px]">Total</th>
                                    <th class="p-3 w-[140px]">Capital</th>
                                    <th class="p-3 w-[140px]">Interés</th>
                                    <th class="p-3 w-[140px]">Mora</th>
                                    <th class="p-3 w-[140px]">Comisión</th>
                                    <th class="p-3 w-[140px]">Seguro</th>
                                    <th class="p-3 w-[90px]">TC</th>
                                @endif

                                <th class="p-3 w-[70px]">Imagen</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            @forelse($movimientos as $m)
                                @php
                                    $cap = (float) ($m->monto_capital ?? 0);
                                    $ut = (float) ($m->monto_utilidad ?? 0);

                                    $imgPath = $m->comprobante_imagen_path ?? ($m->imagen ?? null);

                                    // BANCO
                                    $concepto = (string) ($m->concepto ?? '');
                                    $total = (float) ($m->monto_total ?? 0);
                                    $int = (float) ($m->monto_interes ?? 0);
                                    $mora = (float) ($m->monto_mora ?? 0);
                                    $com = (float) ($m->monto_comision ?? 0);
                                    $seg = (float) ($m->monto_seguro ?? 0);
                                    $tc = (float) ($m->tipo_cambio ?? 0);

                                    if (!$isBanco) {
                                        $sumCapital += $cap;
                                        $sumUtilidad += $ut;
                                    } else {
                                        $sumTotal += $total;
                                        $sumCapital += $cap;
                                        $sumInteres += $int;
                                        $sumMora += $mora;
                                        $sumComision += $com;
                                        $sumSeguro += $seg;
                                    }
                                @endphp

                                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-900/50 text-center">
                                    <td class="p-2 text-gray-900 dark:text-neutral-100 font-mono">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="p-2 text-gray-900 dark:text-neutral-100 font-mono">
                                        {{ $m->fecha?->format('d/m/Y') ?? '—' }}
                                    </td>

                                    <td class="p-2 text-left">
                                        <div class="text-gray-900 dark:text-neutral-100">
                                            {{ $m->descripcion ?? '—' }}
                                        </div>

                                        {{-- Línea pequeña: banco + cuenta (si hay) --}}
                                        @if (!empty($m->banco))
                                            <div class="mt-1 text-xs text-gray-500 dark:text-neutral-400">
                                                {{ $m->banco->nombre }} • <span
                                                    class="font-mono">{{ $m->banco->numero_cuenta }}</span>
                                            </div>
                                        @endif
                                    </td>

                                    <td class="p-2 text-gray-900 dark:text-neutral-100 font-mono">
                                        {{ $m->fecha_pago?->format('d/m/Y') ?? '—' }}
                                    </td>

                                    <td class="p-2 text-gray-900 dark:text-neutral-100 font-mono">
                                        {{ $m->comprobante ?? '—' }}
                                    </td>

                                    @if (!$isBanco)
                                        <td class="p-2 text-gray-900 dark:text-neutral-100 font-mono">
                                            {{ $m->porcentaje_utilidad !== null ? number_format((float) $m->porcentaje_utilidad, 2, ',', '.') . '%' : '—' }}
                                        </td>

                                        <td
                                            class="p-2 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                            {{ $fmtMoney($cap) }}
                                        </td>

                                        <td class="p-2 text-right tabular-nums text-gray-900 dark:text-neutral-100">
                                            {{ $fmtMoney($ut) }}
                                        </td>
                                    @else
                                        <td class="p-2 text-gray-900 dark:text-neutral-100 font-mono">
                                            {{ $concepto ?: '—' }}
                                        </td>

                                        <td
                                            class="p-2 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                            {{ $fmtMoney($total) }}
                                        </td>

                                        <td
                                            class="p-2 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                            {{ $fmtMoney($cap) }}
                                        </td>

                                        <td class="p-2 text-right tabular-nums text-gray-900 dark:text-neutral-100">
                                            {{ $fmtMoney($int) }}
                                        </td>

                                        <td class="p-2 text-right tabular-nums text-gray-900 dark:text-neutral-100">
                                            {{ $fmtMoney($mora) }}
                                        </td>

                                        <td class="p-2 text-right tabular-nums text-gray-900 dark:text-neutral-100">
                                            {{ $fmtMoney($com) }}
                                        </td>

                                        <td class="p-2 text-right tabular-nums text-gray-900 dark:text-neutral-100">
                                            {{ $fmtMoney($seg) }}
                                        </td>

                                        <td class="p-2 text-gray-900 dark:text-neutral-100 font-mono">
                                            {{ $tc > 0 ? number_format($tc, 2, ',', '.') : '—' }}
                                        </td>
                                    @endif

                                    <td class="p-2 text-center">
                                        @if ($imgPath)
                                            <button type="button"
                                                wire:click="verFotoMovimiento({{ $m->id }})"
                                                class="w-8 h-8 cursor-pointer inline-flex items-center justify-center rounded-lg border
                                                    border-gray-300 text-gray-700 hover:bg-gray-100
                                                    dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-700"
                                                title="Ver imagen">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"
                                                        ry="2" />
                                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                                    <path d="M21 15l-5-5L5 21" />
                                                </svg>
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-neutral-500">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isBanco ? 14 : 9 }}"
                                        class="p-6 text-center text-gray-500 dark:text-neutral-400">
                                        No hay movimientos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        {{-- FOOTER: TOTALES --}}
                        <tfoot class="bg-gray-50 dark:bg-neutral-900 border-t border-gray-200 dark:border-neutral-700">
                            <tr>
                                <td colspan="5"
                                    class="p-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                                    Totales
                                </td>

                                @if (!$isBanco)
                                    <td class="p-3"></td>
                                    <td
                                        class="p-3 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                        {{ $fmtMoney($sumCapital) }}
                                    </td>
                                    <td
                                        class="p-3 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                        {{ $fmtMoney($sumUtilidad) }}
                                    </td>
                                @else
                                    <td class="p-3"></td>
                                    <td
                                        class="p-3 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                        {{ $fmtMoney($sumTotal) }}
                                    </td>
                                    <td
                                        class="p-3 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                        {{ $fmtMoney($sumCapital) }}
                                    </td>
                                    <td
                                        class="p-3 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                        {{ $fmtMoney($sumInteres) }}
                                    </td>
                                    <td
                                        class="p-3 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                        {{ $fmtMoney($sumMora) }}
                                    </td>
                                    <td
                                        class="p-3 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                        {{ $fmtMoney($sumComision) }}
                                    </td>
                                    <td
                                        class="p-3 text-right font-semibold tabular-nums text-gray-900 dark:text-neutral-100">
                                        {{ $fmtMoney($sumSeguro) }}
                                    </td>
                                    <td class="p-3"></td>
                                @endif

                                <td class="p-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>

        @slot('footer')
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="closeMovimientos"
                    class="px-4 py-2 rounded-lg border cursor-pointer
                        border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                        hover:bg-gray-100 dark:hover:bg-neutral-800">
                    Cerrar
                </button>
            </div>
        @endslot
    </x-ui.modal>

    {{-- VISOR FOTO --}}
    <div wire:key="foto-inv-{{ $openFotoModal ? '1' : '0' }}-{{ md5($fotoUrl ?? '') }}">
        <x-ui.foto-zoom-modal :open="$openFotoModal" :url="$fotoUrl" onClose="closeFoto" title="Comprobante adjunto"
            subtitle="Pasa el cursor para ampliar y mover" maxWidth="max-w-5xl" />
    </div>
</div>
