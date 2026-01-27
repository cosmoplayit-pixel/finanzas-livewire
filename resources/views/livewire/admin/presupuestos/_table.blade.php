  {{-- TABLA PRINCIPAL --}}
  <div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
      <div class="overflow-x-auto">
          <table class="w-full table-fixed text-sm min-w-[1050px]">
              <thead
                  class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-200">
                  <tr class="text-left">
                      <th class="w-[75px] text-center p-2 select-none whitespace-nowrap">Ag.</th>

                      <th class="p-2 cursor-pointer select-none" wire:click="sortBy('agente')">
                          Agente
                          @if ($sortField === 'agente')
                              {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                          @endif
                      </th>

                      <th class="p-2 w-[110px] text-center cursor-pointer select-none" wire:click="sortBy('moneda')">
                          Moneda
                          @if ($sortField === 'moneda')
                              {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                          @endif
                      </th>

                      <th class="p-2 w-[160px] text-right cursor-pointer select-none"
                          wire:click="sortBy('total_presupuesto')">
                          Presupuesto
                          @if ($sortField === 'total_presupuesto')
                              {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                          @endif
                      </th>

                      <th class="p-2 w-[160px] text-right cursor-pointer select-none"
                          wire:click="sortBy('total_rendido')">
                          Rendido
                          @if ($sortField === 'total_rendido')
                              {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                          @endif
                      </th>

                      <th class="p-2 w-[180px] text-right cursor-pointer select-none"
                          wire:click="sortBy('total_saldo')">
                          Saldo por rendir
                          @if ($sortField === 'total_saldo')
                              {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                          @endif
                      </th>

                      <th class="p-2 w-[140px] text-center cursor-pointer select-none"
                          wire:click="sortBy('total_presupuestos')">
                          Presupuestos
                          @if ($sortField === 'total_presupuestos')
                              {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                          @endif
                      </th>
                  </tr>
              </thead>

              @forelse($agentesResumen as $row)
                  @php
                      $agenteId = (int) $row->agente_servicio_id;
                      $rowMoneda = (string) $row->moneda; //viene del query (BOB/USD)
                      $rowKey = $agenteId . '|' . $rowMoneda; //clave compuesta

                      $open = (bool) ($panelsOpen[$rowKey] ?? false);
                  @endphp

                  <tbody wire:key="agente-block-{{ $rowKey }}"
                      class="cursor-pointer divide-y divide-gray-200 dark:divide-neutral-200">

                      {{-- FILA PRINCIPAL --}}
                      <tr class="hover:bg-gray-50 dark:hover:bg-neutral-900">
                          <td class="p-2 text-center align-middle">
                              <button type="button"
                                  wire:click="togglePanel({{ $agenteId }}, '{{ $rowMoneda }}')"
                                  wire:loading.attr="disabled"
                                  wire:target="togglePanel({{ $agenteId }}, '{{ $rowMoneda }}')"
                                  class="cursor-pointer w-7 h-7 inline-flex items-center justify-center rounded border
                                       border-gray-300 text-gray-700 hover:bg-gray-100
                                       dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                                  {{ $open ? '−' : '+' }}
                              </button>
                          </td>

                          <td class="p-2 align-top">
                              <div class="flex flex-col gap-0.5">

                                  <div class="flex items-center gap-2">
                                      <svg class="size-4 text-gray-400 dark:text-neutral-500"
                                          xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                          stroke-width="1.5" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                          <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M4.5 20.25a7.5 7.5 0 0115 0" />
                                      </svg>

                                      <span class="font-medium text-gray-900 dark:text-neutral-100">
                                          {{ $row->agente_nombre }}
                                      </span>
                                  </div>

                                  <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-neutral-400">
                                      <svg class="size-4 text-gray-400 dark:text-neutral-500"
                                          xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                          stroke-width="1.5" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M16.5 10.5V6.75A2.25 2.25 0 0014.25 4.5h-4.5A2.25 2.25 0 007.5 6.75v3.75" />
                                          <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 10.5h18M5.25 10.5v6.75A2.25 2.25 0 007.5 19.5h9a2.25 2.25 0 002.25-2.25V10.5" />
                                      </svg>

                                      <span>
                                          CI: {{ $row->agente_ci ?? '—' }}
                                      </span>
                                  </div>

                              </div>
                          </td>

                          {{-- Moneda visible (ya no hay filtro global) --}}
                          <td class="p-2 text-center">
                              <span
                                  class="text-xs font-semibold px-2 py-1 rounded-full
                                {{ $rowMoneda === 'USD'
                                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-200'
                                    : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200' }}">
                                  {{ $rowMoneda }}
                              </span>
                          </td>

                          <td class="p-2 text-right tabular-nums font-semibold">
                              {{ number_format((float) $row->total_presupuesto, 2, ',', '.') }}
                          </td>

                          <td class="p-2 text-right tabular-nums font-semibold text-green-500 dark:text-green-300">
                              {{ number_format((float) $row->total_rendido, 2, ',', '.') }}
                          </td>



                          <td
                              class="p-2 text-right tabular-nums font-semibold
                            {{ (float) $row->total_saldo <= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-300' }}">
                              {{ number_format((float) $row->total_saldo, 2, ',', '.') }}
                          </td>

                          <td class="p-2 text-center">
                              <span
                                  class="inline-flex items-center justify-center min-w-[32px] px-2 py-0.5
                                    rounded-full text-xs font-bold tabular-nums
                                    {{ (int) $row->total_presupuestos > 0
                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200'
                                        : 'bg-gray-100 text-gray-500 dark:bg-neutral-700 dark:text-neutral-400' }}">
                                  {{ (int) $row->total_presupuestos }}
                              </span>
                          </td>


                      </tr>

                      {{-- PANEL DETALLE PRESUPUESTOS --}}
                      @include('livewire.admin.presupuestos._table_panel')
                      {{-- FIN PANEL DETALLE PRESUPUESTOS --}}

                  </tbody>
              @empty
                  <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                      <tr>
                          <td class="p-6 text-center text-gray-500 dark:text-neutral-400" colspan="7">
                              Sin resultados.
                          </td>
                      </tr>
                  </tbody>
              @endforelse
          </table>
      </div>
  </div>

  {{-- PAGINACIÓN --}}
  <div>
      {{ $agentesResumen->links() }}
  </div>
