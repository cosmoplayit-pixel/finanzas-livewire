    {{-- ===================== TABLA DESKTOP ===================== --}}
    <div
        class="hidden md:block border border-gray-200 rounded-xl bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden shadow-sm mt-4">
        <table class="w-full table-fixed text-[13px] text-left">
            <thead
                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-700">
                <tr class="text-left text-xs uppercase tracking-wider">
                    <th class="p-2 w-[5%] text-center">Img</th>
                    <th class="p-2 w-[5%] cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('codigo')">
                        Código
                        @if ($sortField === 'codigo')
                            <span class="text-gray-900 dark:text-white">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif

                    </th>
                    <th class="p-2 w-[22%] cursor-pointer select-none" wire:click="sortBy('nombre')">
                        Herramienta
                        @if ($sortField === 'nombre')
                            <span
                                class="text-gray-900 dark:text-white">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="p-2 w-[12%] cursor-pointer select-none whitespace-nowrap"
                        wire:click="sortBy('estado_fisico')">
                        Est. Físico
                        @if ($sortField === 'estado_fisico')
                            <span
                                class="text-gray-900 dark:text-white">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif

                    </th>
                    <th class="p-2 w-[8%] text-center cursor-pointer select-none"
                        wire:click="sortBy('stock_disponible')">
                        Disp.
                        @if ($sortField === 'stock_disponible')
                            <span
                                class="text-gray-900 dark:text-white">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif

                    </th>
                    <th class="p-2 w-[8%] text-center cursor-pointer select-none hidden xl:table-cell"
                        wire:click="sortBy('stock_total')">
                        Total
                    </th>
                    <th class="p-2 w-[8%] text-center cursor-pointer select-none hidden xl:table-cell"
                        wire:click="sortBy('stock_prestado')">
                        Prest.
                    </th>
                    <th class="p-2 w-[10%] text-right cursor-pointer select-none hidden xl:table-cell"
                        wire:click="sortBy('precio_unitario')">
                        P. Unit.
                    </th>
                    <th class="p-2 w-[6%] text-center cursor-pointer select-none" wire:click="sortBy('active')">
                        Sistema</th>
                    <th class="p-2 w-[18%] whitespace-nowrap text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                @forelse ($herramientas as $h)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-neutral-900/40 transition-colors"
                        wire:key="herr-{{ $h->id }}">

                        {{-- Imagen --}}
                        <td class="p-2 text-center">
                            @if ($h->imagen)
                                <img src="{{ Storage::url($h->imagen) }}"
                                    class="w-10 h-10 object-cover rounded-lg shadow-sm border border-gray-200 dark:border-neutral-700 inline-block cursor-pointer hover:opacity-80 transition"
                                    alt="Img"
                                    onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ Storage::url($h->imagen) }}' }))">
                            @else
                                <div
                                    class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-neutral-800 inline-flex items-center justify-center text-gray-400 dark:text-neutral-600 border border-gray-200 dark:border-neutral-700">
                                    <svg class="w-5 h-5 opacity-50" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                            @endif
                        </td>

                        {{-- Código --}}
                        <td class="p-2 whitespace-nowrap">
                            @if ($h->codigo)
                                <span class="font-mono text-xs font-semibold text-gray-500 dark:text-neutral-400">
                                    {{ $h->codigo }}
                                </span>
                            @else
                                <span class="text-gray-400 dark:text-neutral-600">—</span>
                            @endif
                        </td>

                        {{-- Nombre + Marca/Modelo --}}
                        <td class="p-2">
                            <div class="font-semibold text-gray-900 dark:text-neutral-100 truncate"
                                title="{{ $h->nombre }}">
                                {{ $h->nombre }}
                            </div>

                            @if ($h->marca || $h->modelo)
                                <div class="text-xs text-gray-500 dark:text-neutral-500 truncate flex items-center gap-1 mt-0.5"
                                    title="{{ $h->marca }} {{ $h->modelo }}">
                                    <svg class="w-3 h-3 shrink-0 text-gray-400 dark:text-neutral-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span
                                        class="truncate">{{ implode(' — ', array_filter([$h->marca, $h->modelo])) }}</span>
                                </div>
                            @endif
                        </td>

                        {{-- Estado Físico --}}
                        <td class="p-2 whitespace-nowrap">
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-medium {{ $h->estado_fisico_badge }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $h->estado_fisico_dot }}"></span>
                                {{ $h->estado_fisico_label }}
                            </span>
                        </td>


                        {{-- Stock Disponible --}}
                        <td class="p-2 text-center">
                            @if ($h->stock_disponible > 0)
                                <span class="text-xs font-bold text-gray-700 dark:text-neutral-300">
                                    {{ $h->stock_disponible }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-red-50 text-red-600 border border-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20 text-xs font-bold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    0
                                </span>
                            @endif
                        </td>


                        {{-- Stock Total --}}
                        <td
                            class="p-2 text-center text-gray-600 dark:text-neutral-400 font-medium hidden xl:table-cell">
                            {{ $h->stock_total }}
                        </td>

                        {{-- Stock Prestado --}}
                        <td class="p-2 text-center hidden xl:table-cell">
                            @if ($h->stock_prestado > 0)
                                <span class="text-xs font-semibold text-gray-500 dark:text-neutral-400">
                                    {{ $h->stock_prestado }}
                                </span>
                            @else
                                <span class="text-gray-400 dark:text-neutral-600">—</span>
                            @endif
                        </td>


                        {{-- Precio Unitario --}}
                        <td
                            class="p-2 text-right tabular-nums text-gray-700 dark:text-neutral-300 hidden xl:table-cell">
                            {{ number_format($h->precio_unitario, 2, ',', '.') }}
                        </td>

                        {{-- Estado Sistema --}}
                        <td class="p-2 text-center whitespace-nowrap">
                            @if ($h->active)
                                <span
                                    class="px-1.5 py-0.5 rounded text-[11px] font-medium bg-gray-50 text-gray-500 dark:bg-neutral-800 dark:text-neutral-400 border border-gray-200 dark:border-neutral-700">Activo</span>
                            @else
                                <span
                                    class="px-1.5 py-0.5 rounded text-[11px] font-medium bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 border border-red-100 dark:border-red-500/20">Inactivo</span>
                            @endif
                        </td>


                        {{-- Acciones --}}
                        @canany(['herramientas.update', 'herramientas.toggle', 'herramientas.delete',
                            'herramientas.stock_add', 'herramientas.stock_baja'])
                            <td class="p-2 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5">

                                    {{-- Ver detalle --}}
                                    <button wire:click="openDetail({{ $h->id }})" wire:loading.attr="disabled"
                                        wire:target="openDetail({{ $h->id }})" title="Ver detalle"
                                        class="cursor-pointer w-7 h-7 flex-none inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 dark:text-neutral-500 dark:hover:text-indigo-400 transition disabled:opacity-50">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    @can('herramientas.update')
                                        {{-- Editar --}}
                                        <button wire:click="openEdit({{ $h->id }})" wire:loading.attr="disabled"
                                            wire:target="openEdit({{ $h->id }})" title="Editar"
                                            class="cursor-pointer w-7 h-7 flex-none inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 dark:text-neutral-500 dark:hover:text-amber-400 transition disabled:opacity-50">
                                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                    @endcan

                                    {{-- Stock Controls --}}
                                    <div class="flex items-center gap-1.5">
                                        {{-- Agregar stock --}}
                                        @can('herramientas.stock_add')
                                            <button wire:click="openAddStock({{ $h->id }})"
                                                wire:loading.attr="disabled" wire:target="openAddStock({{ $h->id }})"
                                                title="Agregar stock"
                                                class="cursor-pointer w-7 h-7 flex-none inline-flex items-center justify-center rounded-lg border border-indigo-200 dark:border-indigo-800 bg-white dark:bg-neutral-900 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 transition disabled:opacity-50">
                                                <svg wire:loading.remove wire:target="openAddStock({{ $h->id }})"
                                                    class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                                <svg wire:loading wire:target="openAddStock({{ $h->id }})"
                                                    class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                </svg>
                                            </button>
                                        @endcan
                                        {{-- Baja stock --}}
                                        @can('herramientas.stock_baja')
                                            <button wire:click="openBajaStock({{ $h->id }})"
                                                wire:loading.attr="disabled" wire:target="openBajaStock({{ $h->id }})"
                                                title="Dar de baja stock"
                                                class="cursor-pointer w-7 h-7 flex-none inline-flex items-center justify-center rounded-lg border border-red-200 dark:border-red-800 bg-white dark:bg-neutral-900 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/40 transition disabled:opacity-50">
                                                <svg wire:loading.remove wire:target="openBajaStock({{ $h->id }})"
                                                    class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                        d="M20 12H4" />
                                                </svg>
                                                <svg wire:loading wire:target="openBajaStock({{ $h->id }})"
                                                    class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>


                                    @can('herramientas.toggle')
                                        <button type="button" x-data="{ loading: false }"
                                            x-on:click="loading = true; $dispatch('swal:toggle-active-herramienta', { id: {{ $h->id }}, active: @js($h->active), name: @js($h->nombre) })"
                                            x-on:swal:done.window="loading = false" x-bind:disabled="loading"
                                            title="{{ $h->active ? 'Desactivar' : 'Activar' }}"
                                            class="cursor-pointer w-7 h-7 flex-none inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 transition disabled:opacity-50 {{ $h->active ? 'text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10' : 'text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10' }}">

                                            <span x-show="!loading">
                                                @if ($h->active)
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                @else
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @endif
                                            </span>
                                            <span x-show="loading" x-cloak>
                                                <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                </svg>
                                            </span>
                                        </button>
                                    @endcan

                                    @can('herramientas.delete')
                                        <button type="button" x-data
                                            x-on:click="$dispatch('swal:delete-herramienta', { id: {{ $h->id }}, name: @js($h->nombre) })"
                                            title="Eliminar"
                                            class="cursor-pointer w-7 h-7 flex-none inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/20 dark:text-neutral-500 dark:hover:text-red-400 transition">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endcan

                                </div>
                            </td>
                        @endcanany
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="p-8 text-center text-gray-400 dark:text-neutral-500">
                            <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            Sin resultados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
