<div>
    <x-ui.modal wire:key="resumen-proyecto-modal" model="isOpen" title="Detalle Financiero del Proyecto"
        maxWidth="sm:max-w-xl md:max-w-5xl" onClose="closeModal">
        @if ($proyecto)

            {{-- CABECERA --}}
            <div class="mb-6">
                <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1.5 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ $proyecto->entidad->nombre ?? 'Sin entidad vinculada' }}
                </p>
                <h2 class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-1.5">
                    <span class="text-gray-400 dark:text-neutral-500">#</span>
                    {{ $proyecto->nombre }}
                </h2>
            </div>

            {{-- TABLA --}}
            <div class="rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead
                            class="bg-gray-50 dark:bg-neutral-800/60 border-b border-gray-200 dark:border-neutral-700">
                            <tr>
                                <th
                                    class="px-4 py-2.5 text-left text-[10px] font-medium text-gray-400 dark:text-neutral-500 uppercase tracking-wider w-10">
                                    #</th>
                                <th
                                    class="px-3 py-2.5 text-left text-[10px] font-medium text-gray-400 dark:text-neutral-500 uppercase tracking-wider">
                                    Fecha</th>
                                <th
                                    class="px-3 py-2.5 text-left text-[10px] font-medium text-gray-400 dark:text-neutral-500 uppercase tracking-wider">
                                    Tipo</th>
                                <th
                                    class="px-3 py-2.5 text-left text-[10px] font-medium text-gray-400 dark:text-neutral-500 uppercase tracking-wider">
                                    Concepto</th>
                                <th
                                    class="px-3 py-2.5 text-left text-[10px] font-medium text-gray-400 dark:text-neutral-500 uppercase tracking-wider">
                                    Agente</th>
                                <th
                                    class="px-3 py-2.5 text-right text-[10px] font-medium text-gray-400 dark:text-neutral-500 uppercase tracking-wider">
                                    Monto</th>
                                <th
                                    class="px-3 py-2.5 text-center text-[10px] font-medium text-gray-400 dark:text-neutral-500 uppercase tracking-wider w-[60px]">
                                    Imagen</th>
                                <th
                                    class="px-4 py-2.5 text-center text-[10px] font-medium text-gray-400 dark:text-neutral-500 uppercase tracking-wider w-[60px]">
                                    Doc</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-neutral-800 bg-white dark:bg-neutral-900">
                            @forelse ($movimientos as $mov)
                                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800/50 transition-colors">

                                    <td class="px-4 py-3 text-[11px] font-medium text-gray-400 dark:text-neutral-600">
                                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </td>

                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-neutral-400">
                                        {{ \Carbon\Carbon::parse($mov['fecha'])->format('d/m/Y') }}
                                    </td>

                                    <td class="px-3 py-3 whitespace-nowrap">
                                        @php
                                            $badgeClass = match ($mov['tipo']) {
                                                'egreso'
                                                    => 'bg-amber-50 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                                'ingreso_real'
                                                    => 'bg-blue-50 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                                default
                                                    => 'bg-gray-100 text-gray-600 dark:bg-neutral-800 dark:text-neutral-400',
                                            };
                                            $dotClass = match ($mov['tipo']) {
                                                'egreso' => 'bg-amber-500',
                                                'ingreso_real' => 'bg-blue-500',
                                                default => 'bg-gray-400',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-[10px] font-medium {{ $badgeClass }}">
                                            <span
                                                class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $dotClass }}"></span>
                                            {{ $mov['origen'] }} — {{ $mov['documento'] }}
                                        </span>
                                    </td>

                                    <td class="px-3 py-3 text-xs text-gray-800 dark:text-neutral-200 max-w-[220px]">
                                        {{ $mov['concepto'] }}
                                    </td>

                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-neutral-500">
                                        {{ $mov['agente'] ?? '—' }}
                                    </td>

                                    <td class="px-3 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        @if ($mov['tipo'] === 'egreso')
                                            <span class="text-amber-700 dark:text-amber-400">
                                                −{{ number_format((float) $mov['monto'], 2, ',', '.') }}
                                            </span>
                                        @elseif ($mov['tipo'] === 'ingreso_real')
                                            <span class="text-blue-700 dark:text-blue-400">
                                                +{{ number_format((float) $mov['monto'], 2, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-gray-600 dark:text-neutral-400">
                                                {{ number_format((float) $mov['monto'], 2, ',', '.') }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="p-2 text-center">
                                        @php
                                            $foto = $mov['foto_path'] ?? null;
                                            $ext = $foto ? strtolower(pathinfo($foto, PATHINFO_EXTENSION)) : '';
                                            $isPdf = $ext === 'pdf';
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'bmp']);
                                        @endphp

                                        @if ($foto)
                                            @if ($isPdf)
                                                <a href="{{ asset('storage/' . $foto) }}" target="_blank"
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-rose-600 border-rose-300 hover:bg-rose-50 hover:border-rose-400 dark:bg-neutral-900 dark:text-rose-400 dark:border-rose-700 dark:hover:bg-rose-900/20 shadow-sm"
                                                    title="Ver PDF">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path
                                                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                        <polyline points="14 2 14 8 20 8" />
                                                        <line x1="9" y1="13" x2="15"
                                                            y2="13" />
                                                        <line x1="9" y1="17" x2="15"
                                                            y2="17" />
                                                        <line x1="9" y1="9" x2="11"
                                                            y2="9" />
                                                    </svg>
                                                </a>
                                            @elseif($isImage)
                                                <button type="button"
                                                    @click.stop="$wire.openFotoComprobante('{{ asset('storage/' . $foto) }}')"
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-indigo-600 border-indigo-300 hover:bg-indigo-50 hover:border-indigo-400 dark:bg-neutral-900 dark:text-indigo-400 dark:border-indigo-700 dark:hover:bg-indigo-900/20 shadow-sm"
                                                    title="Ver imagen">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <rect x="3" y="3" width="18" height="18" rx="2"
                                                            ry="2" />
                                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                                        <polyline points="21 15 16 10 5 21" />
                                                    </svg>
                                                </button>
                                            @else
                                                <a href="{{ asset('storage/' . $foto) }}" target="_blank"
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg border transition-all cursor-pointer bg-white text-gray-600 border-gray-300 hover:bg-gray-50 hover:border-gray-400 dark:bg-neutral-900 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-900/20 shadow-sm"
                                                    title="Ver archivo">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path
                                                            d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z">
                                                        </path>
                                                        <polyline points="13 2 13 9 20 9"></polyline>
                                                    </svg>
                                                </a>
                                            @endif
                                        @else
                                            <div class="w-8 h-8 mx-auto inline-flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-300 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-700 shadow-sm"
                                                title="Sin respaldo">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"
                                                        ry="2" />
                                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                                    <polyline points="21 15 16 10 5 21" />
                                                </svg>
                                            </div>
                                        @endif
                                    </td>

                                    <td class="p-2 text-center">
                                        @if ($mov['url'])
                                            <a href="{{ $mov['url'] }}" target="_blank"
                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-neutral-700 text-gray-400 hover:text-gray-700 hover:border-gray-400 dark:hover:text-neutral-200 dark:hover:border-neutral-500 transition-colors shadow-sm bg-white dark:bg-neutral-900"
                                                title="Ver documento original">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 02 2h10a2 2 0 0 02-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        @else
                                            <span
                                                class="w-8 h-8 rounded-lg border border-transparent inline-flex items-center justify-center text-gray-300 dark:text-neutral-700 text-xs">—</span>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8"
                                        class="px-4 py-10 text-center text-sm text-gray-400 dark:text-neutral-600">
                                        No hay movimientos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        <tfoot
                            class="bg-gray-50 dark:bg-neutral-800/60 border-t border-gray-200 dark:border-neutral-700">
                            <tr>
                                <td colspan="6"
                                    class="px-3 py-3 text-right text-[10px] font-medium text-gray-400 dark:text-neutral-500 uppercase tracking-wider">
                                    Total egresos
                                </td>
                                <td
                                    class="px-3 py-3 text-right text-[15px] font-medium text-red-700 dark:text-red-400 tabular-nums whitespace-nowrap">
                                    −{{ number_format((float) $total_compras, 2, ',', '.') }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        @endif

        @slot('footer')
            <div class="flex justify-end w-full">
                <button type="button" wire:click="closeModal"
                    class="px-4 py-2 rounded-lg border border-gray-300 dark:border-neutral-700 text-sm font-medium text-gray-600 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800 transition-colors">
                    Cerrar
                </button>
            </div>
        @endslot
    </x-ui.modal>

    {{-- Visor de fotos --}}
    @include('livewire.admin.presupuestos.listeners._modal_foto')
</div>
