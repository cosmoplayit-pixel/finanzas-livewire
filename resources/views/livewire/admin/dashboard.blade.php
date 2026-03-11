@section('title', 'Panel de Control')

@php
    /**
     * Helper: formato numero con separador de miles (.) y decimales (,) estilo boliviano.
     */
    if (!function_exists('fmtBs')) {
        function fmtBs(float $n): string
        {
            return number_format($n, 2, ',', '.');
        }
    }
@endphp

<div class="space-y-3 animate__animated animate__fadeIn">


    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- GRAFICOS (TAMAÑO NORMAL)                                       --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <div
            class="bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 rounded-2xl p-3 shadow-sm">
            <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Balance General Consolidado
                (Bs)</h3>
            <div id="chart-balance" style="min-height: 200px;"></div>
        </div>

        <div
            class="bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 rounded-2xl p-3 shadow-sm">
            <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Distribución de Activos (Bs)
            </h3>
            <div id="chart-activos" style="min-height: 200px;"></div>
        </div>

        <div
            class="bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 rounded-2xl p-3 shadow-sm">
            <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Distribución de Deudas (Bs)
            </h3>
            <div id="chart-deudas" style="min-height: 200px;"></div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- TABLAS EN DOS COLUMNAS                                         --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">

        {{-- COLUMNA ACTIVOS --}}
        <div
            class="bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-800 shadow-sm overflow-hidden flex flex-col h-full">
            <div class="bg-blue-600 px-6 py-2">
                <h3 class="text-[11px] font-black text-white uppercase tracking-widest flex items-center gap-3">
                    <i class="fa-solid fa-arrow-trend-up text-blue-200"></i>
                    Activos
                </h3>
            </div>

            <div class="overflow-x-auto grow">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-neutral-800 border-b border-slate-100 dark:border-neutral-800">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-widest w-12">Nro
                            </th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-widest">Nombre</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">
                                Bs</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">
                                $$</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-neutral-800">
                        {{-- Bancos --}}
                        <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors group">
                            <td class="px-6 py-4 text-sm font-medium text-slate-400">1</td>
                            <td class="px-6 py-1.5">
                                <details class="group/det">
                                    <summary
                                        class="list-none cursor-pointer flex items-center gap-2 text-[13px] font-black text-slate-700 dark:text-neutral-200 uppercase list-none outline-none hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        Efectivo Bancos <i
                                            class="fa-solid fa-chevron-down text-[10px] transition-transform group-open/det:rotate-180"></i>
                                    </summary>
                                    <div
                                        class="mt-3 pl-4 space-y-2 border-l-2 border-amber-200 dark:border-amber-900/40">
                                        @foreach ($efectivoBancos as $b)
                                            <div
                                                class="flex justify-between text-[11px] text-slate-500 dark:text-neutral-400">
                                                <span>{{ $b['nombre'] }}</span>
                                                <span class="font-bold tabular-nums">
                                                    {{ $b['bob'] > 0 ? fmtBs($b['bob']) . ' Bs' : '$ ' . fmtBs($b['usd']) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ fmtBs(collect($efectivoBancos)->sum('bob')) }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ collect($efectivoBancos)->sum('usd') > 0 ? fmtBs(collect($efectivoBancos)->sum('usd')) : '—' }}
                            </td>
                        </tr>
                        {{-- Otros Bancos (Regulares) --}}
                        <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors group">
                            <td class="px-6 py-4 text-sm font-medium text-slate-400">2</td>
                            <td class="px-6 py-1.5">
                                <details class="group/det">
                                    <summary
                                        class="list-none cursor-pointer flex items-center gap-2 text-[13px] font-black text-slate-700 dark:text-neutral-200 uppercase list-none outline-none hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        Bancos <i
                                            class="fa-solid fa-chevron-down text-[10px] transition-transform group-open/det:rotate-180"></i>
                                    </summary>
                                    <div
                                        class="mt-3 pl-4 space-y-2 border-l-2 border-amber-200 dark:border-amber-900/40">
                                        @foreach ($otrosBancos as $b)
                                            <div
                                                class="flex justify-between text-[11px] text-slate-500 dark:text-neutral-400">
                                                <span>{{ $b['nombre'] }}</span>
                                                <span class="font-bold tabular-nums">
                                                    {{ $b['bob'] > 0 ? fmtBs($b['bob']) . ' Bs' : '$ ' . fmtBs($b['usd']) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ fmtBs(collect($otrosBancos)->sum('bob')) }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ collect($otrosBancos)->sum('usd') > 0 ? fmtBs(collect($otrosBancos)->sum('usd')) : '—' }}
                            </td>
                        </tr>
                        {{-- Proyectos --}}
                        <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-400">3</td>
                            <td class="px-6 py-1.5">
                                <span class="text-[13px] font-black text-slate-700 dark:text-neutral-200 uppercase">Ctas
                                    X
                                    Cobrar Proyectos</span>
                                <span
                                    class="block text-[10px] text-slate-400 font-bold uppercase mt-0.5 tracking-tighter opacity-70">(Saldo
                                    + Ret. Pendiente)</span>
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ fmtBs($cuentasProyectosBob) }}</td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                —</td>
                        </tr>
                        {{-- Boletas --}}
                        <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-400">4</td>
                            <td class="px-6 py-1.5">
                                <span class="text-[13px] font-black text-slate-700 dark:text-neutral-200 uppercase">Ctas
                                    X
                                    Cobrar Boletas</span>
                                <span
                                    class="block text-[10px] text-slate-400 font-bold uppercase mt-0.5 tracking-tighter opacity-70">(Saldo
                                    por devolver)</span>
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ $cuentasBoletasBob > 0 ? fmtBs($cuentasBoletasBob) : '—' }}</td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ $cuentasBoletasUsd > 0 ? fmtBs($cuentasBoletasUsd) : '—' }}</td>
                        </tr>
                        {{-- Agentes --}}
                        <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-400">5</td>
                            <td class="px-6 py-1.5">
                                <span class="text-[13px] font-black text-slate-700 dark:text-neutral-200 uppercase">Ctas
                                    X
                                    Rendir Agentes</span>
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ fmtBs($agentesServicioBob) }}</td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ $agentesServicioUsd > 0 ? fmtBs($agentesServicioUsd) : '—' }}</td>
                        </tr>
                    </tbody>
                    <tfoot
                        class="bg-slate-50 dark:bg-neutral-800/50 border-t-2 border-slate-100 dark:border-neutral-800">
                        <tr>
                            <td colspan="2"
                                class="px-6 py-4 text-right text-[11px] font-black text-slate-700 dark:text-neutral-200 uppercase tracking-widest">
                                Total Activos</td>
                            <td
                                class="px-6 py-4 text-right text-sm font-extrabold text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ fmtBs($totalActivosBob) }} Bs</td>
                            <td
                                class="px-6 py-4 text-right text-sm font-extrabold text-slate-700 dark:text-neutral-200 tabular-nums">
                                $ {{ fmtBs($totalActivosUsd) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- COLUMNA DEUDAS --}}
        <div
            class="bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-800 shadow-sm overflow-hidden flex flex-col h-full">
            <div class="bg-red-600 px-6 py-2">
                <h3 class="text-[11px] font-black text-white uppercase tracking-widest flex items-center gap-3">
                    <i class="fa-solid fa-hand-holding-dollar text-red-200"></i>
                    Deudas
                </h3>
            </div>

            <div class="overflow-x-auto grow">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-neutral-800 border-b border-slate-100 dark:border-neutral-800">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-widest w-12">Nro
                            </th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-widest">Nombre</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">
                                Bs</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">
                                $$</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-neutral-800">
                        {{-- Privados --}}
                        <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-400">1</td>
                            <td class="px-6 py-1.5">
                                <span class="text-[13px] font-black text-slate-700 dark:text-neutral-200 uppercase">Inv.
                                    Privados (Capital)</span>
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ fmtBs($invPrivadoCapitalBob) }}</td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ $invPrivadoCapitalUsd > 0 ? fmtBs($invPrivadoCapitalUsd) : '—' }}</td>
                        </tr>
                        <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-400">2</td>
                            <td class="px-6 py-1.5">
                                <span class="text-[13px] font-black text-slate-700 dark:text-neutral-200 uppercase">Inv.
                                    Privados (Utilidad)</span>
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ fmtBs($invPrivadoCuotasBob) }}</td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                —</td>
                        </tr>
                        {{-- Bancos --}}
                        <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-400">3</td>
                            <td class="px-6 py-1.5">
                                <span
                                    class="text-[13px] font-black text-slate-700 dark:text-neutral-200 uppercase">Inv.
                                    Bancos (Capital)</span>
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ fmtBs($invBancoCapitalBob) }}</td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                —</td>
                        </tr>
                        <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-400">4</td>
                            <td class="px-6 py-1.5">
                                <span
                                    class="text-[13px] font-black text-slate-700 dark:text-neutral-200 uppercase">Inv.
                                    Bancos (Interés)</span>
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ fmtBs($invBancoCuotasBob) }}</td>
                            <td
                                class="px-6 py-4 text-right text-sm font-black text-slate-700 dark:text-neutral-200 tabular-nums">
                                —</td>
                        </tr>
                        {{-- Impuestos --}}
                        <tr class="group hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-400">5</td>
                            <td class="px-6 py-1.5">
                                <span
                                    class="text-[13px] font-black text-slate-700 dark:text-neutral-200 uppercase leading-none">Impuestos
                                    Nacionales</span>
                            </td>
                            <td class="px-2 py-2">
                                <input type="text" wire:model.live.debounce.500ms="impuestosNacionalesBobFormatted"
                                    class="w-full bg-slate-50 dark:bg-neutral-800/50 border-slate-200 dark:border-neutral-700 rounded-lg text-right text-[13px] font-black text-slate-700 dark:text-neutral-200 
                                           focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all outline-none py-1 px-2" />
                            </td>
                            <td class="px-2 py-2">
                                <input type="text" wire:model.live.debounce.500ms="impuestosNacionalesUsdFormatted"
                                    class="w-full bg-slate-50 dark:bg-neutral-800/50 border-slate-200 dark:border-neutral-700 rounded-lg text-right text-[13px] font-black text-slate-700 dark:text-neutral-200 
                                           focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all outline-none py-1 px-2" />
                            </td>
                        </tr>
                    </tbody>
                    <tfoot
                        class="bg-slate-50 dark:bg-neutral-800/50 border-t-2 border-slate-100 dark:border-neutral-800">
                        <tr>
                            <td colspan="2"
                                class="px-6 py-4 text-right text-[11px] font-black text-slate-700 dark:text-neutral-200 uppercase tracking-widest">
                                Total Deudas</td>
                            <td
                                class="px-6 py-4 text-right text-sm font-extrabold text-slate-700 dark:text-neutral-200 tabular-nums">
                                {{ fmtBs($totalDeudasBob) }} Bs</td>
                            <td
                                class="px-6 py-4 text-right text-sm font-extrabold text-slate-700 dark:text-neutral-200 tabular-nums">
                                $ {{ fmtBs($totalDeudasUsd) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- SALDO NETO FINAL Y CONSOLIDADO (TAMAÑO RECUPERADO)             --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="space-y-3">

        {{-- Saldo Neto por Moneda --}}
        <div @class([
            'rounded-2xl border-2 p-2 px-4 flex flex-wrap items-center justify-between gap-4 shadow-sm',
            'border-emerald-200 bg-emerald-50/50 dark:border-emerald-500/20 dark:bg-emerald-500/5' =>
                $saldoNetoBob >= 0,
            'border-red-200 bg-red-50/50 dark:border-red-500/20 dark:bg-red-500/5' =>
                $saldoNetoBob < 0,
        ])>
            <div class="flex items-center gap-4">
                <div @class([
                    'w-10 h-10 rounded-xl flex items-center justify-center text-lg shadow-sm',
                    'bg-emerald-500 text-white' => $saldoNetoBob >= 0,
                    'bg-red-500 text-white' => $saldoNetoBob < 0,
                ])>
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <h4 class="text-base font-black text-slate-700 dark:text-white uppercase tracking-tighter">Saldo Neto
                    Final</h4>
            </div>

            <div class="flex items-center gap-12">
                <div class="flex flex-col items-end">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Bolivianos</span>
                    <span @class([
                        'text-2xl font-black tabular-nums',
                        $saldoNetoBob >= 0 ? 'text-emerald-600' : 'text-red-600',
                    ])>
                        {{ fmtBs($saldoNetoBob) }} <small class="text-base opacity-60">Bs</small>
                    </span>
                </div>
                <div class="w-px h-8 bg-slate-200 dark:bg-neutral-800"></div>
                <div class="flex flex-col items-end">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dólares</span>
                    <span @class([
                        'text-2xl font-black tabular-nums',
                        $saldoNetoUsd >= 0 ? 'text-emerald-600' : 'text-red-600',
                    ])>
                        <small class="text-base opacity-60">$</small> {{ fmtBs($saldoNetoUsd) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Footer Consolidado (T/C y Totales Combinados) --}}
        <div
            class="bg-indigo-900 dark:bg-black rounded-2xl p-2 px-6 shadow-2xl border border-white/10 flex flex-wrap items-center justify-between gap-6 animate__animated animate__pulse">

            {{-- T/C Input --}}
            <div class="flex flex-col gap-2">
                <span class="text-[10px] font-black text-indigo-300 uppercase tracking-widest ml-1">Tipo de Cambio
                    (T/C)</span>
                <div
                    class="flex items-center gap-3 bg-white/5 px-4 py-1 rounded-xl border border-white/10 hover:border-indigo-500/50 transition-colors">
                    <i class="fa-solid fa-money-bill-transfer text-indigo-400"></i>
                    <input type="text" wire:model.live.debounce.500ms="tipoCambioFormatted"
                        class="w-16 bg-transparent text-base font-black text-white border-none p-0 focus:ring-0 outline-none text-right" />
                </div>
            </div>

            {{-- Totales Consolidados --}}
            <div class="flex flex-wrap items-center gap-10">
                <div class="flex flex-col items-end">
                    <span class="text-[11px] font-black text-indigo-400 uppercase tracking-widest">Saldo Total
                        Consolidado (Bs)</span>
                    <span class="text-3xl font-black tabular-nums text-white">
                        {{ fmtBs($saldoNetoInBsCombined) }} <small class="text-base opacity-60">Bs</small>
                    </span>
                </div>

                <div class="hidden md:block w-px h-10 bg-white/10"></div>

                <div class="flex flex-col items-end">
                    <span class="text-[11px] font-black text-indigo-400 uppercase tracking-widest">Saldo Total
                        Consolidado ($$)</span>
                    <span class="text-3xl font-black tabular-nums text-indigo-400">
                        <small class="text-base opacity-60">$</small> {{ fmtBs($saldoNetoInUsdCombined) }}
                    </span>
                </div>
            </div>

        </div>

    </div>

</div>

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {

            const chartOptions = {
                chart: {
                    height: 250,
                    type: 'bar',
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'Inter, ui-sans-serif, system-ui'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '55%',
                        distributed: true
                    }
                },
                dataLabels: {
                    enabled: false
                },
                colors: ['#3b82f6', '#ef4444'],
                series: [{
                    name: 'Monto Consolidado (Bs)',
                    data: [{
                            x: 'Activos',
                            y: {{ (float) $totalActivosConsolidatedBob }}
                        },
                        {
                            x: 'Deudas',
                            y: {{ (float) $totalDeudasConsolidatedBob }}
                        }
                    ]
                }],
                xaxis: {
                    categories: ['Activos', 'Deudas'],
                    labels: {
                        style: {
                            colors: '#94a3b8',
                            fontSize: '11px',
                            fontWeight: 600
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#94a3b8'
                        },
                        formatter: (v) => v.toLocaleString()
                    }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: (val) => val.toLocaleString() + ' Bs'
                    }
                }
            };

            const donutOptionsActivos = {
                chart: {
                    height: 260,
                    type: 'donut',
                    fontFamily: 'Inter, ui-sans-serif, system-ui'
                },
                series: [
                    {{ (float) (collect($efectivoBancos)->sum('bob') + collect($efectivoBancos)->sum('usd') * $tipoCambio) }},
                    {{ (float) (collect($otrosBancos)->sum('bob') + collect($otrosBancos)->sum('usd') * $tipoCambio) }},
                    {{ (float) ($cuentasProyectosBob + $cuentasProyectosUsd * $tipoCambio) }},
                    {{ (float) ($cuentasBoletasBob + $cuentasBoletasUsd * $tipoCambio) }},
                    {{ (float) ($agentesServicioBob + $agentesServicioUsd * $tipoCambio) }}
                ],
                labels: ['Efectivo', 'Bancos', 'Proyectos', 'Boletas', 'Agentes'],
                colors: ['#f59e0b', '#3b82f6', '#10b981', '#06b6d4', '#8b5cf6'],
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    fontWeight: 600
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Activos (Bs)',
                                    fontSize: '13px',
                                    formatter: () => '{{ fmtBs($totalActivosConsolidatedBob) }}'
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                }
            };

            const donutOptionsDeudas = {
                chart: {
                    height: 260,
                    type: 'donut',
                    fontFamily: 'Inter, ui-sans-serif, system-ui'
                },
                series: [
                    {{ (float) ($invPrivadoCapitalBob + $invPrivadoCuotasBob + ($invPrivadoCapitalUsd + $invPrivadoCuotasUsd) * $tipoCambio) }},
                    {{ (float) ($invBancoCapitalBob + $invBancoCuotasBob + ($invBancoCapitalUsd + $invBancoCuotasUsd) * $tipoCambio) }},
                    {{ (float) ($impuestosNacionalesBob + $impuestosNacionalesUsd * $tipoCambio) }}
                ],
                labels: ['Privados', 'Bancos', 'Impuestos'],
                colors: ['#f43f5e', '#ef4444', '#6366f1'],
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    fontWeight: 600
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Deudas (Bs)',
                                    fontSize: '13px',
                                    formatter: () => '{{ fmtBs($totalDeudasConsolidatedBob) }}'
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                }
            };

            const chartBalance = new ApexCharts(document.querySelector("#chart-balance"), chartOptions);
            const chartActivos = new ApexCharts(document.querySelector("#chart-activos"), donutOptionsActivos);
            const chartDeudas = new ApexCharts(document.querySelector("#chart-deudas"), donutOptionsDeudas);

            chartBalance.render();
            chartActivos.render();
            chartDeudas.render();
        });
    </script>
@endpush
