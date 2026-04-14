    {{-- ===================== CARDS MOBILE ===================== --}}
    <div class="space-y-3 md:hidden">
        @forelse ($herramientas as $h)
            <div class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-sm overflow-hidden"
                wire:key="mobile-card-{{ $h->id }}">

                {{-- Cabecera: imagen + info --}}
                <div class="flex items-start gap-3 p-3">
                    {{-- Imagen --}}
                    @if ($h->imagen)
                        <img src="{{ Storage::url($h->imagen) }}" alt="{{ $h->nombre }}"
                            class="w-16 h-16 rounded-xl object-cover border border-gray-200 dark:border-neutral-700 shrink-0 cursor-pointer hover:opacity-80 transition"
                            onclick="window.dispatchEvent(new CustomEvent('open-image-modal', { detail: '{{ Storage::url($h->imagen) }}' }))">
                    @else
                        <div
                            class="w-16 h-16 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center border border-indigo-100 dark:border-indigo-800 shrink-0">
                            <svg class="w-7 h-7 text-indigo-300 dark:text-indigo-600" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    @endif

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                @if ($h->codigo)
                                    <span
                                        class="inline-block font-mono text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 px-1.5 py-0.5 rounded mb-1">{{ $h->codigo }}</span>
                                @endif
                                <div
                                    class="font-bold text-gray-900 dark:text-neutral-100 text-sm leading-tight truncate">
                                    {{ $h->nombre }}</div>
                                @if ($h->marca || $h->modelo)
                                    <div class="text-xs text-gray-500 dark:text-neutral-400 truncate mt-0.5">
                                        {{ implode(' — ', array_filter([$h->marca, $h->modelo])) }}
                                    </div>
                                @endif
                            </div>
                            <div class="shrink-0 flex flex-col items-end gap-1">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $h->estado_fisico_badge }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $h->estado_fisico_dot }}"></span>
                                    {{ $h->estado_fisico_label }}
                                </span>
                                @if (!$h->active)
                                    <span
                                        class="text-[10px] font-medium text-red-500 dark:text-red-400">Inactivo</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stock bar --}}
                <div class="px-3 pb-2">
                    <div class="grid grid-cols-3 gap-2">
                        <div
                            class="rounded-xl bg-emerald-50 dark:bg-emerald-900/15 border border-emerald-100 dark:border-emerald-800/40 p-2 text-center">
                            <div
                                class="text-[9px] uppercase font-bold text-emerald-500 dark:text-emerald-400 tracking-wide">
                                Disp.</div>
                            <div
                                class="text-base font-black {{ $h->stock_disponible > 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-400' }}">
                                {{ $h->stock_disponible }}</div>
                        </div>
                        <div
                            class="rounded-xl bg-gray-50 dark:bg-neutral-800 border border-gray-100 dark:border-neutral-700 p-2 text-center">
                            <div
                                class="text-[9px] uppercase font-bold text-gray-400 dark:text-neutral-500 tracking-wide">
                                Total</div>
                            <div class="text-base font-black text-gray-700 dark:text-neutral-200">
                                {{ $h->stock_total }}</div>
                        </div>
                        <div
                            class="rounded-xl bg-amber-50 dark:bg-amber-900/15 border border-amber-100 dark:border-amber-800/40 p-2 text-center">
                            <div
                                class="text-[9px] uppercase font-bold text-amber-500 dark:text-amber-400 tracking-wide">
                                Prest.</div>
                            <div
                                class="text-base font-black {{ $h->stock_prestado > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-gray-400 dark:text-neutral-600' }}">
                                {{ $h->stock_prestado }}</div>
                        </div>
                    </div>
                </div>

                {{-- Acciones (solo íconos) --}}
                @canany(['herramientas.update', 'herramientas.toggle', 'herramientas.delete'])
                    <div
                        class="flex items-center gap-1.5 px-3 py-2.5 border-t border-gray-100 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-900/30">

                        {{-- Ver detalle --}}
                        <button wire:click="openDetail({{ $h->id }})" title="Ver detalle"
                            class="flex-1 inline-flex items-center justify-center h-9 rounded-xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-gray-500 dark:text-neutral-400 hover:text-indigo-600 hover:border-indigo-300 dark:hover:text-indigo-400 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>

                        @can('herramientas.update')
                            {{-- Editar --}}
                            <button wire:click="openEdit({{ $h->id }})" title="Editar"
                                class="flex-1 inline-flex items-center justify-center h-9 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>

                            {{-- Stock Controls --}}
                            <div class="flex-1 flex gap-1">
                                {{-- Agregar stock --}}
                                <button wire:click="openAddStock({{ $h->id }})" wire:loading.attr="disabled"
                                    wire:target="openAddStock({{ $h->id }})" title="Agregar stock"
                                    class="flex-1 inline-flex items-center justify-center h-9 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition cursor-pointer disabled:opacity-50">
                                    <svg wire:loading.remove wire:target="openAddStock({{ $h->id }})" class="w-4 h-4"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    <svg wire:loading wire:target="openAddStock({{ $h->id }})"
                                        class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                        </path>
                                    </svg>
                                </button>
                                {{-- Baja stock --}}
                                <button wire:click="openBajaStock({{ $h->id }})" wire:loading.attr="disabled"
                                    wire:target="openBajaStock({{ $h->id }})" title="Dar de baja stock"
                                    class="flex-1 inline-flex items-center justify-center h-9 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition cursor-pointer disabled:opacity-50">
                                    <svg wire:loading.remove wire:target="openBajaStock({{ $h->id }})" class="w-4 h-4"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                    <svg wire:loading wire:target="openBajaStock({{ $h->id }})"
                                        class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        @endcan

                        @can('herramientas.toggle')
                            {{-- Activar/Desactivar --}}
                            <button type="button" x-data="{ loading: false }"
                                x-on:click="loading = true; $dispatch('swal:toggle-active-herramienta', { id: {{ $h->id }}, active: @js($h->active), name: @js($h->nombre) })"
                                x-on:swal:done.window="loading = false" x-bind:disabled="loading"
                                title="{{ $h->active ? 'Desactivar' : 'Activar' }}"
                                class="flex-1 inline-flex items-center justify-center h-9 rounded-xl border transition cursor-pointer disabled:opacity-50 {{ $h->active ? 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40' : 'border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100' }}">
                                <span x-show="!loading">
                                    @if ($h->active)
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    @endif
                                </span>
                                <span x-show="loading" x-cloak>
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                        </path>
                                    </svg>
                                </span>
                            </button>
                        @endcan

                        @can('herramientas.delete')
                            {{-- Eliminar --}}
                            <button type="button" x-data
                                x-on:click="$dispatch('swal:delete-herramienta', { id: {{ $h->id }}, name: @js($h->nombre) })"
                                title="Eliminar"
                                class="flex-1 inline-flex items-center justify-center h-9 rounded-xl border border-red-200 dark:border-red-800 bg-white dark:bg-neutral-900 text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        @endcan
                    </div>
                @endcanany
            </div>
        @empty
            <div
                class="rounded-2xl border border-gray-200 dark:border-neutral-800 p-8 text-center text-sm text-gray-400 dark:text-neutral-500">
                <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                Sin resultados.
            </div>
        @endforelse
    </div>
