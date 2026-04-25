<x-ui.modal wire:key="inversion-edit-{{ $open ? 'open' : 'closed' }}" model="open" title="Editar inversión"
    maxWidth="sm:max-w-md" onClose="close">

    <div class="space-y-3 sm:space-y-4">

        {{-- AVISO: tipo bloqueado si tiene más de 1 movimiento --}}
        @if (!$canEditTipo)
            <div
                class="flex items-start gap-2.5 rounded-lg border border-amber-200 bg-amber-50 px-3.5 py-3 text-sm text-amber-800 dark:border-amber-700/50 dark:bg-amber-900/20 dark:text-amber-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <span>
                    El <strong>tipo</strong> no se puede modificar porque esta inversión ya tiene movimientos
                    registrados más allá del capital inicial.
                </span>
            </div>
        @endif

        {{-- DATOS PRINCIPALES --}}
        <div class="rounded-xl border bg-white dark:bg-neutral-900/30 dark:border-neutral-700 overflow-hidden">
            <div class="px-3 sm:px-4 py-2.5 sm:py-3 border-b dark:border-neutral-700">
                <div class="text-sm font-semibold text-gray-800 dark:text-neutral-100">Datos principales</div>
            </div>

            <div class="p-3 sm:p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- Nombre completo --}}
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-sm mb-1">Nombre completo <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.live="nombre_completo"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                   border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                   focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                        @error('nombre_completo')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tipo --}}
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-sm mb-1">Tipo de inversión <span class="text-red-500">*</span></label>
                        @if ($canEditTipo)
                            <div class="relative">
                                <select wire:model.live="tipo"
                                    class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 cursor-pointer">
                                    <option value="PRIVADO">Privado</option>
                                    <option value="BANCO">Banco</option>
                                </select>
                            </div>
                        @else
                            <div
                                class="w-full relative rounded-lg border px-3 py-2 bg-gray-50 dark:bg-neutral-800 border-gray-200 dark:border-neutral-700 text-gray-600 dark:text-neutral-400 cursor-not-allowed select-none">
                                {{ $tipo === 'BANCO' ? 'Banco' : 'Privado' }}
                                <span class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2"
                                            ry="2" />
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                    </svg>
                                </span>
                            </div>
                        @endif
                        @error('tipo')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Día de pago (Solo para nuevas conversiones a BANCO) --}}
                    @if ($tipo === 'BANCO' && $tipo_original === 'PRIVADO')
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm mb-1">Día de pago (1–28) <span
                                    class="text-red-500">*</span></label>
                            <input type="text" inputmode="numeric" wire:model.defer="dia_pago_formatted"
                                wire:blur="formatDiaPago" placeholder="1"
                                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900
                                       border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100
                                       focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                            @error('dia_pago')
                                <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>

    @slot('footer')
        <div class="grid grid-cols-2 gap-2 sm:flex sm:justify-end sm:gap-3">
            <button type="button" wire:click="close"
                class="px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar
            </button>

            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="px-4 py-2 rounded-lg cursor-pointer bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="save">Guardar</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>
    @endslot

</x-ui.modal>
