  {{-- TABLA PRINCIPAL --}}
  <div class="hidden md:block border border-gray-100 rounded bg-white dark:bg-neutral-800 overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
          <table wire:key="presupuestos-table" class="w-full table-fixed text-sm min-w-[1050px]">
              <thead
                  class="bg-slate-50/50 text-slate-600 dark:bg-neutral-900/50 dark:text-neutral-400 border-b border-gray-100 dark:border-neutral-800">
                  <tr class="text-left text-[11px] uppercase tracking-wider font-semibold">
                      <th class="w-[60px] text-center p-3 select-none whitespace-nowrap">
                          <div x-data="{ allOpen: false }" class="flex items-center justify-center gap-2">
                              <button type="button"
                                  class="w-6 h-6 inline-flex items-center justify-center rounded border border-gray-200 text-gray-500 hover:bg-white hover:border-gray-300 hover:text-gray-700 hover:shadow-sm
                                dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-neutral-200 transition-all cursor-pointer"
                                  title="Desplegar / Ocultar todos"
                                  @click="
                                    allOpen = !allOpen;
                                    $wire.toggleAllPanels(allOpen);
                                ">
                                  <svg x-show="!allOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                      fill="currentColor" class="size-3">
                                      <path fill-rule="evenodd"
                                          d="M3.22 7.595a.75.75 0 0 0 0 1.06l4.25 4.25a.75.75 0 0 0 1.06 0l4.25-4.25a.75.75 0 0 0-1.06-1.06l-2.97 2.97V2.75a.75.75 0 0 0-1.5 0v7.81l-2.97-2.97a.75.75 0 0 0-1.06 0Z"
                                          clip-rule="evenodd" />
                                  </svg>
                                  <svg x-show="allOpen" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                      fill="currentColor" class="size-3">
                                      <path fill-rule="evenodd"
                                          d="M12.78 8.405a.75.75 0 0 0 0-1.06l-4.25-4.25a.75.75 0 0 0-1.06 0l-4.25 4.25a.75.75 0 0 0 1.06 1.06l2.97-2.97v7.81a.75.75 0 0 0 1.5 0v-7.81l2.97 2.97a.75.75 0 0 0 1.06 0Z"
                                          clip-rule="evenodd" />
                                  </svg>
                              </button>
                          </div>
                      </th>

                      <th class="p-3 cursor-pointer select-none whitespace-nowrap hover:text-gray-900 dark:hover:text-white transition-colors"
                          wire:click="sortBy('agente')">
                          <div class="flex items-center gap-1.5">
                              Agente
                              @if ($sortField === 'agente')
                                  <svg class="size-3 {{ $sortDirection === 'asc' ? 'text-indigo-500' : 'rotate-180 text-indigo-500' }}"
                                      fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                  </svg>
                              @endif
                          </div>
                      </th>

                      <th class="p-3 w-[160px] text-right cursor-pointer select-none whitespace-nowrap hover:text-gray-900 dark:hover:text-white transition-colors"
                          wire:click="sortBy('total_presupuesto')">
                          <div class="flex items-center justify-end gap-1.5">
                              Presupuesto
                              @if ($sortField === 'total_presupuesto')
                                  <svg class="size-3 {{ $sortDirection === 'asc' ? 'text-indigo-500' : 'rotate-180 text-indigo-500' }}"
                                      fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                  </svg>
                              @endif
                          </div>
                      </th>

                      <th class="p-3 w-[160px] text-right cursor-pointer select-none whitespace-nowrap hover:text-gray-900 dark:hover:text-white transition-colors"
                          wire:click="sortBy('total_rendido')">
                          <div class="flex items-center justify-end gap-1.5">
                              Rendido
                              @if ($sortField === 'total_rendido')
                                  <svg class="size-3 {{ $sortDirection === 'asc' ? 'text-indigo-500' : 'rotate-180 text-indigo-500' }}"
                                      fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                  </svg>
                              @endif
                          </div>
                      </th>

                      <th class="p-3 w-[180px] text-right cursor-pointer select-none whitespace-nowrap hover:text-gray-900 dark:hover:text-white transition-colors"
                          wire:click="sortBy('total_saldo')">
                          <div class="flex items-center justify-end gap-1.5">
                              Saldo por rendir
                              @if ($sortField === 'total_saldo')
                                  <svg class="size-3 {{ $sortDirection === 'asc' ? 'text-indigo-500' : 'rotate-180 text-indigo-500' }}"
                                      fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                  </svg>
                              @endif
                          </div>
                      </th>

                      <th class="p-3 w-[140px] text-center cursor-pointer select-none whitespace-nowrap hover:text-gray-900 dark:hover:text-white transition-colors"
                          wire:click="sortBy('total_presupuestos')">
                          <div class="flex items-center justify-center gap-1.5">
                              Presupuestos
                              @if ($sortField === 'total_presupuestos')
                                  <svg class="size-3 {{ $sortDirection === 'asc' ? 'text-indigo-500' : 'rotate-180 text-indigo-500' }}"
                                      fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                  </svg>
                              @endif
                          </div>
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

                  <tbody wire:key="agente-block-{{ $rowKey }}" x-data="{ open: @js($open) }"
                      class="divide-y divide-gray-100 dark:divide-neutral-800">

                      {{-- FILA PRINCIPAL --}}
                      <tr class="border-t  border-gray-300 dark:border-neutral-800 hover:bg-slate-50/50 dark:hover:bg-neutral-900/60 transition-colors text-gray-700 dark:text-neutral-200 cursor-pointer"
                          @click="open = !open; $wire.togglePanel({{ $agenteId }}, '{{ $rowMoneda }}')">

                          <td class="p-3 whitespace-nowrap align-middle">
                              <div class="flex items-center justify-center gap-2">
                                  <button type="button"
                                      class="w-6 h-6 inline-flex items-center justify-center rounded border border-gray-200 bg-white text-gray-500 hover:border-gray-300 hover:text-gray-700 hover:shadow-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-400 dark:hover:border-neutral-600 dark:hover:text-neutral-200 transition-all cursor-pointer"
                                      @click.stop="open = !open; $wire.togglePanel({{ $agenteId }}, '{{ $rowMoneda }}')"
                                      :aria-expanded="open">
                                      <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                          fill="currentColor" class="size-3">
                                          <path
                                              d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
                                      </svg>
                                      <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                          fill="currentColor" class="size-3">
                                          <path d="M3.75 7.25a.75.75 0 0 0 0 1.5h8.5a.75.75 0 0 0 0-1.5h-8.5Z" />
                                      </svg>
                                  </button>
                              </div>
                          </td>

                          <td class="p-3 align-top">
                              <div class="flex flex-col gap-1">

                                  <div class="flex items-center gap-2.5">
                                      <svg class="size-4 text-gray-400 dark:text-neutral-500"
                                          xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                          stroke-width="1.8" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                          <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M4.5 20.25a7.5 7.5 0 0115 0" />
                                      </svg>

                                      <span
                                          class="font-semibold text-gray-900 dark:text-neutral-100 uppercase text-xs tracking-wide">
                                          {{ $row->agente_nombre }}
                                      </span>

                                      <span
                                          class="text-[10px] font-bold px-2 py-0.5 rounded-full ml-1
                                      {{ $rowMoneda === 'USD'
                                          ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                          : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                                          {{ $rowMoneda }}
                                      </span>
                                  </div>

                                  <div
                                      class="flex items-center gap-2.5 pl-6 text-[11px] text-gray-500 dark:text-neutral-400">
                                      <svg class="size-3.5 text-gray-400 dark:text-neutral-500"
                                          xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                          stroke-width="1.8" stroke="currentColor">
                                          <rect width="18" height="14" x="3" y="5" rx="2"
                                              stroke-linecap="round" stroke-linejoin="round" />
                                          <path d="M7 15h4M15 15h2M7 11h2M13 11h4" stroke-linecap="round"
                                              stroke-linejoin="round" />
                                      </svg>

                                      <span class="uppercase tracking-wider">
                                          CI: {{ $row->agente_ci ?? '—' }}
                                      </span>
                                  </div>

                              </div>
                          </td>

                          <td class="p-3 text-right tabular-nums font-bold text-gray-800 dark:text-gray-200">
                              {{ number_format((float) $row->total_presupuesto, 2, ',', '.') }}
                          </td>

                          <td class="p-3 text-right tabular-nums font-bold text-emerald-600 dark:text-emerald-400">
                              {{ number_format((float) $row->total_rendido, 2, ',', '.') }}
                          </td>

                          <td
                              class="p-3 text-right tabular-nums font-bold
                          {{ (float) $row->total_saldo <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                              {{ number_format((float) $row->total_saldo, 2, ',', '.') }}
                          </td>

                          <td class="p-3 text-center">
                              <span
                                  class="inline-flex items-center justify-center min-w-[28px] h-6 px-2
                                  rounded text-[11px] font-bold tabular-nums
                                  {{ (int) $row->total_presupuestos > 0
                                      ? 'bg-indigo-50 text-indigo-600 border border-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20'
                                      : 'bg-gray-50 text-gray-500 border border-gray-100 dark:bg-neutral-800 dark:text-neutral-400 dark:border-neutral-700' }}">
                                  {{ (int) $row->total_presupuestos }}
                              </span>
                          </td>


                      </tr>

                      {{-- PANEL DETALLE PRESUPUESTOS --}}
                      @include('livewire.admin.presupuestos._table_panel')
                      {{-- FIN PANEL DETALLE PRESUPUESTOS --}}

                  </tbody>
              @empty
                  <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                      <tr>
                          <td class="p-8 text-center" colspan="6">
                              <div
                                  class="flex flex-col items-center justify-center text-gray-400 dark:text-neutral-500">
                                  <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor"
                                      viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                      </path>
                                  </svg>
                                  <span class="text-sm font-medium">Sin resultados.</span>
                              </div>
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
