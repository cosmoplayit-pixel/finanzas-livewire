{{-- ========================= --}}
{{-- MOBILE: CARDS PRO (NO +)  --}}
{{-- ========================= --}}
<div class="md:hidden space-y-3">

    @forelse($agentesResumen as $row)
        @php
            $agenteId = (int) $row->agente_servicio_id;
            $rowMoneda = (string) $row->moneda;
            $rowKey = $agenteId . '|' . $rowMoneda;
            $open = (bool) ($panelsOpen[$rowKey] ?? false);
        @endphp

        <div wire:key="agente-card-{{ $rowKey }}"
            class="rounded-2xl border border-gray-200 dark:border-neutral-700
                   bg-white dark:bg-neutral-800/70 shadow-sm overflow-hidden">

            {{-- HEADER (TOGGLE COMPLETO) --}}
            <button type="button" wire:click="togglePanel({{ $agenteId }}, '{{ $rowMoneda }}')"
                wire:loading.attr="disabled" wire:target="togglePanel({{ $agenteId }}, '{{ $rowMoneda }}')"
                class="w-full text-left">

                <div
                    class="grid grid-cols-[1fr_auto] gap-3 p-4  hover:bg-gray-50/70 dark:hover:bg-neutral-900/40 transition">

                    {{-- DATOS AGENTE --}}
                    <div class="min-w-0">
                        <div class="flex  gap-2">
                            <svg class="size-4 text-gray-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 20.25a7.5 7.5 0 0115 0" />
                            </svg>

                            <span class="font-semibold text-[12px] text-gray-900 dark:text-neutral-100 truncate">
                                {{ $row->agente_nombre }}
                            </span>

                            {{-- MONEDA A LA DERECHA --}}
                            <span
                                class="ml-auto text-[10px] font-bold px-2 py-1 rounded-sm inline-flex
                                {{ $rowMoneda === 'USD'
                                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-200'
                                    : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200' }}">
                                {{ $rowMoneda }}
                            </span>
                        </div>

                        <div class="mt-1 flex  gap-2 text-xs text-gray-500 dark:text-neutral-400">
                            <svg class="size-4 text-gray-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 10.5V6.75A2.25 2.25 0 0014.25 4.5h-4.5A2.25 2.25 0 007.5 6.75v3.75" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 10.5h18M5.25 10.5v6.75A2.25 2.25 0 007.5 19.5h9a2.25 2.25 0 002.25-2.25V10.5" />
                            </svg>
                            <span>CI: {{ $row->agente_ci ?? '—' }}</span>
                        </div>
                    </div>

                    {{-- CHEVRON / ESTADO --}}
                    <div class="flex py-1 text-xs text-gray-400 dark:text-neutral-500">
                        <span>{{ $open ? 'Ocultar' : 'Ver' }}</span>
                        <svg class="size-5 transition-transform {{ $open ? 'rotate-180' : 'rotate-0' }}"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </button>

            {{-- KPIs --}}
            <div class="px-4 pb-2">
                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-xl border border-gray-200 dark:border-neutral-700 p-2">
                        <div class="text-[10px] uppercase text-gray-500 dark:text-neutral-400">
                            Presupuesto
                        </div>
                        <div class="mt-1 font-extrabold tabular-nums">
                            {{ number_format((float) $row->total_presupuesto, 2, ',', '.') }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-neutral-700 p-2">
                        <div class="text-[10px] uppercase text-gray-500 dark:text-neutral-400">
                            Rendido
                        </div>
                        <div class="mt-1 font-extrabold text-green-600 dark:text-green-300 tabular-nums">
                            {{ number_format((float) $row->total_rendido, 2, ',', '.') }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-neutral-700 p-2">
                        <div class="text-[10px] uppercase text-gray-500 dark:text-neutral-400">
                            Saldo por rendir
                        </div>
                        <div
                            class="mt-1 font-extrabold tabular-nums
                            {{ (float) $row->total_saldo <= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ number_format((float) $row->total_saldo, 2, ',', '.') }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-neutral-700 p-2">
                        <div class="text-[10px] uppercase text-gray-500 dark:text-neutral-400">
                            Presupuestos
                        </div>
                        <span
                            class="inline-flex mt-1 px-3 py-1 rounded-full text-xs font-bold
                            {{ (int) $row->total_presupuestos > 0
                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200'
                                : 'bg-gray-100 text-gray-500 dark:bg-neutral-700 dark:text-neutral-400' }}">
                            {{ (int) $row->total_presupuestos }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- PANEL DETALLE --}}
            <div class="{{ $open ? 'block' : 'hidden' }} border-t border-gray-200 dark:border-neutral-700">
                <div class="p-3 overflow-x-auto">
                    @include('livewire.admin.presupuestos._mobile_table_panel')
                </div>
            </div>

        </div>
    @empty
        <div class="rounded-2xl border p-6 text-center text-gray-500">
            Sin resultados.
        </div>
    @endforelse

    <div class="pt-2">
        {{ $agentesResumen->links() }}
    </div>
</div>
