@section('title', 'Panel de Control')

<div class="space-y-3 animate__animated animate__fadeIn">

    {{-- GRAFICOS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 rounded-2xl p-3 shadow-sm"
            id="data-balance" data-activos="{{ (float) $totalActivosConsolidatedBob }}"
            data-deudas="{{ (float) $totalDeudasConsolidatedBob }}"
            data-patrimonio="{{ (float) $totalPatrimonioConsolidatedBob }}">
            <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Balance General Consolidado
                (Bs)</h3>
            <div id="chart-balance" wire:ignore style="min-height: 200px;"></div>
        </div>

        <div class="bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 rounded-2xl p-3 shadow-sm"
            id="data-activos"
            data-vals='[
                {{ (float) (collect($efectivoBancos)->sum('bob') + collect($efectivoBancos)->sum('usd') * $tipoCambio) }},
                {{ (float) (collect($otrosBancos)->sum('bob') + collect($otrosBancos)->sum('usd') * $tipoCambio) }},
                {{ (float) ($cuentasProyectosBob + $cuentasProyectosUsd * $tipoCambio) }},
                {{ (float) ($cuentasBoletasBob + $cuentasBoletasUsd * $tipoCambio) }},
                {{ (float) ($agentesServicioBob + $agentesServicioUsd * $tipoCambio) }}
            ]'
            data-total="{{ $this->fmtBs($totalActivosConsolidatedBob) }}">
            <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Distribución de Activos (Bs)
            </h3>
            <div id="chart-activos" wire:ignore style="min-height: 200px;"></div>
        </div>

        <div class="bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 rounded-2xl p-3 shadow-sm"
            id="data-deudas"
            data-vals='[
                {{ (float) ($invPrivadoCapitalBob + $invPrivadoCuotasBob + ($invPrivadoCapitalUsd + $invPrivadoCuotasUsd) * $tipoCambio) }},
                {{ (float) ($invBancoCapitalBob + $invBancoCuotasBob + ($invBancoCapitalUsd + $invBancoCuotasUsd) * $tipoCambio) }},
                {{ (float) ($impuestosNacionalesBob + $impuestosNacionalesUsd * $tipoCambio) }}
            ]'
            data-total="{{ $this->fmtBs($totalDeudasConsolidatedBob) }}">
            <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Distribución de Deudas (Bs)
            </h3>
            <div id="chart-deudas" wire:ignore style="min-height: 200px;"></div>
        </div>

        <div class="bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 rounded-2xl p-3 shadow-sm"
            id="data-patrimonio"
            data-vals='[
                {{ (float) ($patrimonioHerramientasBob + $patrimonioHerramientasUsd * $tipoCambio) }},
                {{ (float) ($patrimonioMaterialesBob + $patrimonioMaterialesUsd * $tipoCambio) }},
                {{ (float) ($patrimonioMobiliarioBob + $patrimonioMobiliarioUsd * $tipoCambio) }},
                {{ (float) ($patrimonioVehiculosBob + $patrimonioVehiculosUsd * $tipoCambio) }},
                {{ (float) ($patrimonioInmueblesBob + $patrimonioInmueblesUsd * $tipoCambio) }}
            ]'
            data-total="{{ $this->fmtBs($totalPatrimonioConsolidatedBob) }}">
            <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Distribución de Patrimonio
                (Bs)
            </h3>
            <div id="chart-patrimonio" wire:ignore style="min-height: 200px;"></div>
        </div>
    </div>

    {{-- TABLAS --}}
    <div x-data="{ tab: 'activos' }" class="space-y-0">

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 items-start">

            {{-- ── TAB BAR: ocupa todo el ancho, oculto en xl ── --}}
            <div
                class="col-span-full xl:hidden sticky top-0 z-10 bg-white dark:bg-neutral-950 border border-slate-200 dark:border-neutral-800 shadow-sm rounded-xl overflow-hidden">
                <div class="flex">
                    <button @click="tab = 'activos'"
                        :class="tab === 'activos' ?
                            'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-indigo-50/60 dark:bg-indigo-900/20' :
                            'text-slate-400 dark:text-neutral-500'"
                        class="flex-1 py-3 text-[10px] font-black uppercase tracking-widest flex flex-col items-center gap-1 transition-all cursor-pointer">
                        <i class="fa-solid fa-arrow-trend-up text-[13px]"></i>Activos
                    </button>
                    <button @click="tab = 'deudas'"
                        :class="tab === 'deudas' ?
                            'border-b-2 border-rose-600 text-rose-600 dark:text-rose-400 bg-rose-50/60 dark:bg-rose-900/20' :
                            'text-slate-400 dark:text-neutral-500'"
                        class="flex-1 py-3 text-[10px] font-black uppercase tracking-widest flex flex-col items-center gap-1 transition-all cursor-pointer">
                        <i class="fa-solid fa-hand-holding-dollar text-[13px]"></i>Deudas
                    </button>
                    <button @click="tab = 'patrimonio'"
                        :class="tab === 'patrimonio' ?
                            'border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 bg-emerald-50/60 dark:bg-emerald-900/20' :
                            'text-slate-400 dark:text-neutral-500'"
                        class="flex-1 py-3 text-[10px] font-black uppercase tracking-widest flex flex-col items-center gap-1 transition-all cursor-pointer">
                        <i class="fa-solid fa-gem text-[13px]"></i>Patrimonio
                    </button>
                </div>
            </div>

            {{-- ══════════════ ACTIVOS ══════════════ --}}
            <div x-show="tab === 'activos'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                class="xl:!block col-span-full xl:col-span-1 bg-white dark:bg-neutral-900
                   rounded-none xl:rounded-2xl
                   border-0 xl:border border-slate-200 dark:border-neutral-800
                   shadow-none xl:shadow-sm
                   overflow-hidden flex flex-col">

                <div class="bg-indigo-600 px-4 py-2">
                    <h3 class="text-[11px] font-black text-white uppercase tracking-widest flex items-center gap-3">
                        <i class="fa-solid fa-arrow-trend-up text-indigo-200"></i>Cuentas Corrientes
                    </h3>
                </div>

                <div class="overflow-x-auto grow">
                    <table class="w-full text-left border-collapse table-fixed">
                        <colgroup>
                            <col style="width:8%">
                            <col style="width:42%">
                            <col style="width:25%">
                            <col style="width:25%">
                        </colgroup>
                        <thead
                            class="bg-slate-50 dark:bg-neutral-800 border-b border-slate-100 dark:border-neutral-800">
                            <tr>
                                <th class="px-2 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nro
                                </th>
                                <th class="px-3 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                    Nombre</th>
                                <th
                                    class="px-2 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">
                                    Bs</th>
                                <th
                                    class="px-2 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">
                                    $$</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-neutral-800">
                            <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                                <td class="px-2 py-4 text-[11px] font-medium text-slate-400">1</td>
                                <td class="px-3 py-4">
                                    <details class="group/det">
                                        <summary
                                            class="list-none cursor-pointer flex items-center gap-1.5 text-[11px] font-black text-slate-700 dark:text-neutral-200 uppercase outline-none hover:text-blue-600 dark:hover:text-blue-400 transition-colors whitespace-nowrap">
                                            Efectivo Bancos <i
                                                class="fa-solid fa-chevron-down text-[10px] transition-transform group-open/det:rotate-180 shrink-0"></i>
                                        </summary>
                                        <div
                                            class="mt-2 pl-3 space-y-2 border-l-2 border-amber-200 dark:border-amber-900/40">
                                            @foreach ($efectivoBancos as $b)
                                                <div
                                                    class="flex justify-between text-[11px] text-slate-500 dark:text-neutral-400">
                                                    <span>{{ $b['nombre'] }}</span>
                                                    <span
                                                        class="font-bold tabular-nums">{{ $b['bob'] > 0 ? $this->fmtBs($b['bob']) . ' Bs' : '$ ' . $this->fmtBs($b['usd']) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                </td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ $this->fmtBs(collect($efectivoBancos)->sum('bob')) }}</td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ collect($efectivoBancos)->sum('usd') > 0 ? $this->fmtBs(collect($efectivoBancos)->sum('usd')) : '—' }}
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                                <td class="px-2 py-4 text-[11px] font-medium text-slate-400">2</td>
                                <td class="px-3 py-4">
                                    <details class="group/det">
                                        <summary
                                            class="list-none cursor-pointer flex items-center gap-1.5 text-[11px] font-black text-slate-700 dark:text-neutral-200 uppercase outline-none hover:text-blue-600 dark:hover:text-blue-400 transition-colors whitespace-nowrap">
                                            Bancos <i
                                                class="fa-solid fa-chevron-down text-[10px] transition-transform group-open/det:rotate-180 shrink-0"></i>
                                        </summary>
                                        <div
                                            class="mt-2 pl-3 space-y-2 border-l-2 border-amber-200 dark:border-amber-900/40">
                                            @foreach ($otrosBancos as $b)
                                                <div
                                                    class="flex justify-between text-[11px] text-slate-500 dark:text-neutral-400">
                                                    <span>{{ $b['nombre'] }}</span>
                                                    <span
                                                        class="font-bold tabular-nums">{{ $b['bob'] > 0 ? $this->fmtBs($b['bob']) . ' Bs' : '$ ' . $this->fmtBs($b['usd']) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                </td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ $this->fmtBs(collect($otrosBancos)->sum('bob')) }}</td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ collect($otrosBancos)->sum('usd') > 0 ? $this->fmtBs(collect($otrosBancos)->sum('usd')) : '—' }}
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                                <td class="px-2 py-4 text-[11px] font-medium text-slate-400">3</td>
                                <td class="px-3 py-4"><span
                                        class="text-[11px] font-black text-slate-700 dark:text-neutral-200 uppercase whitespace-nowrap">Ctas
                                        X Cobrar Proyectos</span></td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ $this->fmtBs($cuentasProyectosBob) }}</td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    —</td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                                <td class="px-2 py-4 text-[11px] font-medium text-slate-400">4</td>
                                <td class="px-3 py-4"><span
                                        class="text-[11px] font-black text-slate-700 dark:text-neutral-200 uppercase whitespace-nowrap">Ctas
                                        X Cobrar Boletas</span></td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ $cuentasBoletasBob > 0 ? $this->fmtBs($cuentasBoletasBob) : '—' }}</td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ $cuentasBoletasUsd > 0 ? $this->fmtBs($cuentasBoletasUsd) : '—' }}</td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                                <td class="px-2 py-4 text-[11px] font-medium text-slate-400">5</td>
                                <td class="px-3 py-4"><span
                                        class="text-[11px] font-black text-slate-700 dark:text-neutral-200 uppercase whitespace-nowrap">Ctas
                                        X Rendir Agentes</span></td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ $this->fmtBs($agentesServicioBob) }}</td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ $agentesServicioUsd > 0 ? $this->fmtBs($agentesServicioUsd) : '—' }}</td>
                            </tr>
                        </tbody>
                        <tfoot
                            class="bg-slate-50 dark:bg-neutral-800/50 border-t-2 border-slate-100 dark:border-neutral-800">
                            <tr>
                                <td colspan="2"
                                    class="px-2 py-4 text-right text-[10px] font-black text-slate-700 dark:text-neutral-200 uppercase tracking-widest whitespace-nowrap">
                                    Total Activos</td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-indigo-600 dark:text-indigo-400 tabular-nums whitespace-nowrap">
                                    {{ $this->fmtBs($totalActivosBob) }} <span class="text-[9px] opacity-60">Bs</span>
                                </td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-indigo-600 dark:text-indigo-400 tabular-nums whitespace-nowrap">
                                    <span class="text-[9px] opacity-60">$</span> {{ $this->fmtBs($totalActivosUsd) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- ══════════════ DEUDAS ══════════════ --}}
            <div x-show="tab === 'deudas'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                class="xl:!block col-span-full xl:col-span-1 bg-white dark:bg-neutral-900
                   rounded-none xl:rounded-2xl
                   border-0 xl:border border-slate-200 dark:border-neutral-800
                   shadow-none xl:shadow-sm
                   overflow-hidden flex flex-col">

                <div class="bg-rose-600 px-4 py-2">
                    <h3 class="text-[11px] font-black text-white uppercase tracking-widest flex items-center gap-3">
                        <i class="fa-solid fa-hand-holding-dollar text-rose-200"></i>Deudas
                    </h3>
                </div>

                <div class="overflow-x-auto grow">
                    <table class="w-full text-left border-collapse table-fixed">
                        <colgroup>
                            <col style="width:8%">
                            <col style="width:42%">
                            <col style="width:25%">
                            <col style="width:25%">
                        </colgroup>
                        <thead
                            class="bg-slate-50 dark:bg-neutral-800 border-b border-slate-100 dark:border-neutral-800">
                            <tr>
                                <th class="px-2 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                    Nro</th>
                                <th class="px-3 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                    Nombre</th>
                                <th
                                    class="px-2 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">
                                    Bs</th>
                                <th
                                    class="px-2 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">
                                    $$</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-neutral-800">
                            <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                                <td class="px-2 py-4 text-[11px] font-medium text-slate-400">1</td>
                                <td class="px-3 py-4"><span
                                        class="text-[11px] font-black text-slate-700 dark:text-neutral-200 uppercase whitespace-nowrap">Inv.
                                        Privados (Capital)</span></td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ $this->fmtBs($invPrivadoCapitalBob) }}</td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ $invPrivadoCapitalUsd > 0 ? $this->fmtBs($invPrivadoCapitalUsd) : '—' }}</td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                                <td class="px-2 py-4 text-[11px] font-medium text-slate-400">2</td>
                                <td class="px-3 py-4"><span
                                        class="text-[11px] font-black text-slate-700 dark:text-neutral-200 uppercase whitespace-nowrap">Inv.
                                        Privados (Interés)</span></td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ $this->fmtBs($invPrivadoCuotasBob) }}</td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    —</td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                                <td class="px-2 py-4 text-[11px] font-medium text-slate-400">3</td>
                                <td class="px-3 py-4"><span
                                        class="text-[11px] font-black text-slate-700 dark:text-neutral-200 uppercase whitespace-nowrap">Inv.
                                        Bancos (Capital)</span></td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ $this->fmtBs($invBancoCapitalBob) }}</td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    —</td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                                <td class="px-2 py-4 text-[11px] font-medium text-slate-400">4</td>
                                <td class="px-3 py-4"><span
                                        class="text-[11px] font-black text-slate-700 dark:text-neutral-200 uppercase whitespace-nowrap">Inv.
                                        Bancos (Interés)</span></td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    {{ $this->fmtBs($invBancoCuotasBob) }}</td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 tabular-nums whitespace-nowrap">
                                    —</td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/20 transition-colors">
                                <td class="px-2 py-4 text-[11px] font-medium text-slate-400">5</td>
                                <td class="px-3 py-4"><span
                                        class="text-[11px] font-black text-slate-700 dark:text-neutral-200 uppercase whitespace-nowrap">Impuestos
                                        Nacionales</span></td>
                                <td class="p-0">
                                    <input type="text" wire:model.blur="impuestosNacionalesBobFormatted"
                                        class="w-full bg-transparent border-0 border-b border-slate-200 dark:border-neutral-700 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 focus:ring-0 focus:border-red-400 transition-all outline-none py-1 px-2" />
                                </td>
                                <td class="p-0">
                                    <input type="text" wire:model.blur="impuestosNacionalesUsdFormatted"
                                        class="w-full bg-transparent border-0 border-b border-slate-200 dark:border-neutral-800 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 focus:ring-0 focus:border-red-400 transition-all outline-none py-1 px-2" />
                                </td>
                            </tr>
                        </tbody>
                        <tfoot
                            class="bg-slate-50 dark:bg-neutral-800/50 border-t-2 border-slate-100 dark:border-neutral-800">
                            <tr>
                                <td colspan="2"
                                    class="px-2 py-4 text-right text-[10px] font-black text-slate-700 dark:text-neutral-200 uppercase tracking-widest whitespace-nowrap">
                                    Total Deudas</td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-rose-600 dark:text-rose-400 tabular-nums whitespace-nowrap">
                                    {{ $this->fmtBs($totalDeudasBob) }} <span class="text-[9px]">Bs</span></td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-rose-600 dark:text-rose-400 tabular-nums whitespace-nowrap">
                                    <span class="text-[9px]">$</span> {{ $this->fmtBs($totalDeudasUsd) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- ══════════════ PATRIMONIO ══════════════
             Tablet: ocupa las 2 columnas (md:col-span-2)
             PC:     ocupa 1 columna (lg:col-span-1)
        ══════════════════════════════════════════ --}}
            <div x-show="tab === 'patrimonio'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                class="xl:!block md:col-span-2 xl:col-span-1
                   bg-white dark:bg-neutral-900
                   rounded-none xl:rounded-2xl
                   border-0 xl:border border-slate-200 dark:border-neutral-800
                   shadow-none xl:shadow-sm
                   overflow-hidden flex flex-col">

                <div class="bg-emerald-600 px-4 py-2">
                    <h3 class="text-[11px] font-black text-white uppercase tracking-widest flex items-center gap-3">
                        <i class="fa-solid fa-gem text-emerald-200"></i> Patrimonio
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    {{--
                    Tablet: tabla ocupa el doble de ancho → más espacio para Nombre.
                    Usamos las mismas proporciones % — se escalan solas.
                --}}
                    <table class="w-full text-left border-collapse table-fixed">
                        <colgroup>
                            <col style="width:8%">
                            <col style="width:42%">
                            <col style="width:25%">
                            <col style="width:25%">
                        </colgroup>
                        <thead
                            class="bg-slate-50 dark:bg-neutral-800 border-b border-slate-100 dark:border-neutral-800">
                            <tr>
                                <th class="px-2 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                    Nro</th>
                                <th class="px-3 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                    Nombre</th>
                                <th
                                    class="px-2 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">
                                    BS</th>
                                <th
                                    class="px-2 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">
                                    $$</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-neutral-800">
                            @foreach ([['num' => 1, 'label' => 'Herramientas', 'bob' => 'patrimonioHerramientasBobFormatted', 'usd' => 'patrimonioHerramientasUsdFormatted'], ['num' => 2, 'label' => 'Materiales', 'bob' => 'patrimonioMaterialesBobFormatted', 'usd' => 'patrimonioMaterialesUsdFormatted'], ['num' => 3, 'label' => 'Mobiliario', 'bob' => 'patrimonioMobiliarioBobFormatted', 'usd' => 'patrimonioMobiliarioUsdFormatted'], ['num' => 4, 'label' => 'Vehículos', 'bob' => 'patrimonioVehiculosBobFormatted', 'usd' => 'patrimonioVehiculosUsdFormatted'], ['num' => 5, 'label' => 'Inmuebles', 'bob' => 'patrimonioInmueblesBobFormatted', 'usd' => 'patrimonioInmueblesUsdFormatted']] as $row)
                                <tr class="hover:bg-emerald-50/10 transition-colors">
                                    <td class="px-2 py-4 text-[11px] font-medium text-slate-400">{{ $row['num'] }}
                                    </td>
                                    <td class="px-3 py-4"><span
                                            class="text-[11px] font-black text-slate-700 dark:text-neutral-200 uppercase whitespace-nowrap">{{ $row['label'] }}</span>
                                    </td>
                                    <td class="p-0">
                                        <input type="text" wire:model.blur="{{ $row['bob'] }}"
                                            class="w-full bg-transparent border-0 border-b border-slate-200 dark:border-neutral-700 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 focus:ring-0 focus:border-emerald-400 transition-all outline-none py-1 px-2" />
                                    </td>
                                    <td class="p-0">
                                        <input type="text" wire:model.blur="{{ $row['usd'] }}"
                                            class="w-full bg-transparent border-0 border-b border-slate-200 dark:border-neutral-800 text-right text-[12px] font-black text-slate-700 dark:text-neutral-200 focus:ring-0 focus:border-emerald-400 transition-all outline-none py-1 px-2" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot
                            class="bg-slate-50 dark:bg-neutral-800/50 border-t-2 border-slate-100 dark:border-neutral-800">
                            <tr>
                                <td colspan="2"
                                    class="px-2 py-4 text-right text-[10px] font-black text-slate-700 dark:text-neutral-200 uppercase tracking-widest whitespace-nowrap">
                                    Total Patrimonio</td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-emerald-600 dark:text-emerald-400 tabular-nums whitespace-nowrap">
                                    {{ $this->fmtBs($totalPatrimonioBob) }} <span class="text-[9px]">Bs</span></td>
                                <td
                                    class="px-2 py-4 text-right text-[12px] font-black text-emerald-600 dark:text-emerald-400 tabular-nums whitespace-nowrap">
                                    <span class="text-[9px]">$</span> {{ $this->fmtBs($totalPatrimonioUsd) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>{{-- /grid --}}
    </div>

    {{-- SALDO NETO FINAL Y CONSOLIDADO --}}
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
                    'w-10 h-10 rounded-xl flex items-center justify-center text-lg shadow-sm shrink-0',
                    'bg-emerald-500 text-white' => $saldoNetoBob >= 0,
                    'bg-red-500 text-white' => $saldoNetoBob < 0,
                ])>
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <h4 class="text-base font-black text-slate-700 dark:text-white uppercase tracking-tighter">Saldo Neto
                    Final</h4>
            </div>

            {{-- Montos: en mobile se apilan, en PC van en fila --}}
            <div class="flex items-center gap-6 sm:gap-12 w-full sm:w-auto justify-around sm:justify-end">
                <div class="flex flex-col items-end">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Bolivianos</span>
                    <span @class([
                        'text-xl sm:text-2xl font-black tabular-nums',
                        $saldoNetoBob >= 0 ? 'text-emerald-600' : 'text-red-600',
                    ])>
                        {{ $this->fmtBs($saldoNetoBob) }} <small class="text-sm sm:text-base opacity-60">Bs</small>
                    </span>
                </div>
                <div class="w-px h-8 bg-slate-200 dark:bg-neutral-800"></div>
                <div class="flex flex-col items-end">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dólares</span>
                    <span @class([
                        'text-xl sm:text-2xl font-black tabular-nums',
                        $saldoNetoUsd >= 0 ? 'text-emerald-600' : 'text-red-600',
                    ])>
                        <small class="text-sm sm:text-base opacity-60">$</small> {{ $this->fmtBs($saldoNetoUsd) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Footer Consolidado --}}
        <div class="bg-indigo-900 dark:bg-black rounded-2xl shadow-2xl border border-white/10 overflow-hidden">

            {{-- Mobile: apilado. PC: fila con justify-between --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between sm:p-2 sm:px-6 sm:gap-6">

                {{-- T/C Input --}}
                <div class="flex flex-col gap-1.5 p-3 sm:p-0 border-b border-white/10 sm:border-0">
                    <span class="text-[10px] font-black text-indigo-300 uppercase tracking-widest">Tipo de Cambio
                        (T/C)</span>
                    <div
                        class="flex items-center gap-3 bg-white/5 px-4 py-1 rounded-xl border border-white/10 hover:border-indigo-500/50 transition-colors">
                        <i class="fa-solid fa-money-bill-transfer text-indigo-400"></i>
                        <input type="text" wire:model.blur="tipoCambioFormatted"
                            class="w-16 bg-transparent text-base font-black text-white border-none p-0 focus:ring-0 outline-none text-right" />
                    </div>
                </div>

                {{-- Totales: en mobile dos columnas iguales, en PC fila --}}
                <div class="flex divide-x divide-white/10 sm:divide-x-0 sm:flex-nowrap sm:items-center sm:gap-10">
                    <div class="flex-1 flex flex-col items-center sm:items-end py-3 px-3 sm:p-0">
                        <span
                            class="text-[9px] sm:text-[11px] font-black text-indigo-400 uppercase tracking-widest text-center sm:text-right whitespace-nowrap">Saldo
                            Total Consolidado</span>
                        <span
                            class="text-lg sm:text-3xl font-black tabular-nums text-white leading-tight whitespace-nowrap">
                            {{ $this->fmtBs($saldoNetoInBsCombined) }} <small
                                class="text-xs sm:text-base opacity-60">Bs</small>
                        </span>
                    </div>

                    <div class="hidden sm:block w-px h-10 bg-white/10"></div>

                    <div class="flex-1 flex flex-col items-center sm:items-end py-3 px-3 sm:p-0">
                        <span
                            class="text-[9px] sm:text-[11px] font-black text-indigo-400 uppercase tracking-widest text-center sm:text-right whitespace-nowrap">Saldo
                            Total Consolidado</span>
                        <span
                            class="text-lg sm:text-3xl font-black tabular-nums text-indigo-400 leading-tight whitespace-nowrap">
                            <small class="text-xs sm:text-base opacity-60">$</small>
                            {{ $this->fmtBs($saldoNetoInUsdCombined) }}
                        </span>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>
