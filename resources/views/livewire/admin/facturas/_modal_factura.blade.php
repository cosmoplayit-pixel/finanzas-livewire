{{-- MODAL: NUEVA FACTURA (create) --}}
<x-ui.modal wire:key="facturas-create-modal" model="openFacturaModal" title="Nueva Factura"
    maxWidth="sm:max-w-xl md:max-w-2xl" onClose="closeFactura">

    <div class="space-y-2 sm:space-y-3">
        {{-- Cliente y Proyecto --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm mb-1">Cliente: <span class="text-red-500">*</span></label>
                <select wire:model.live="entidad_id"
                    class="w-full cursor-pointer rounded-lg border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300/60 dark:border-neutral-700/60
                           text-gray-900 dark:text-neutral-100
                           focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                    <option value="">Seleccione...</option>
                    @foreach ($entidades as $e)
                        <option value="{{ $e->id }}" title="{{ $e->nombre }}">
                            {{ $e->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('entidad_id')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="block text-sm mb-1">Proyecto: <span class="text-red-500">*</span></label>
                <select wire:model.live="proyecto_id" @disabled(!$entidad_id)
                    class="w-full cursor-pointer rounded-lg border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300/60 dark:border-neutral-700/60
                           text-gray-900 dark:text-neutral-100
                           focus:outline-none focus:ring-2 focus:ring-gray-500/40
                           disabled:opacity-60 disabled:bg-gray-100 dark:disabled:bg-neutral-800
                           disabled:cursor-not-allowed">
                    <option value="">
                        {{ $entidad_id ? 'Seleccione...' : 'Seleccione una entidad primero' }}
                    </option>
                    @foreach ($proyectos as $p)
                        <option value="{{ $p->id }}" title="{{ $p->nombre }}">
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('proyecto_id')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Monto --}}
            <div>
                <label class="block text-sm mb-1">Monto Facturado: <span class="text-red-500">*</span></label>
                <input type="text" inputmode="decimal" wire:model.lazy="monto_facturado_formatted" placeholder="0,00"
                    class="w-full rounded-lg border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300/60 dark:border-neutral-700/60
                           text-gray-900 dark:text-neutral-100
                           placeholder:text-gray-400 dark:placeholder:text-neutral-500
                           focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                @error('monto_facturado')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Número --}}
            <div>
                <label class="block text-sm mb-1">Nro. Factura: <span class="text-red-500">*</span></label>
                <input wire:model="numero" autocomplete="off"
                    class="w-full rounded-lg border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300/60 dark:border-neutral-700/60
                           text-gray-900 dark:text-neutral-100
                           focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                @error('numero')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Fecha --}}
            <div>
                <label class="block text-sm mb-1">Fecha Emisión: <span class="text-red-500">*</span></label>
                <input type="datetime-local" wire:model="fecha_emision"
                    class="w-full cursor-pointer rounded-lg border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300/60 dark:border-neutral-700/60
                           text-gray-900 dark:text-neutral-100
                           focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                @error('fecha_emision')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Retención % --}}
            <div>
                <label class="block text-sm mb-1">Retención (%):</label>
                <input type="number" wire:model.live="retencion_porcentaje" disabled
                    class="w-full rounded-lg border px-3 py-2
                           bg-gray-100 dark:bg-neutral-800
                           border-gray-300/60 dark:border-neutral-700/60
                           text-gray-900 dark:text-neutral-100
                           opacity-80 cursor-not-allowed" />
            </div>

            {{-- Retención monto --}}
            <div>
                <label class="block text-sm mb-1">Retención (Monto):</label>
                <input type="number" step="0.01" disabled wire:model="retencion_monto"
                    class="w-full rounded-lg border px-3 py-2
                           bg-gray-100 dark:bg-neutral-800
                           border-gray-300/60 dark:border-neutral-700/60
                           text-gray-900 dark:text-neutral-100
                           opacity-80 cursor-not-allowed" />
            </div>


            {{-- Neto --}}
            <div>
                <label class="block text-sm mb-1">Monto Neto:</label>
                <input type="number" step="0.01" disabled wire:model="monto_neto"
                    class="w-full rounded-lg border px-3 py-2
                           bg-gray-100 dark:bg-neutral-800
                           border-gray-300/60 dark:border-neutral-700/60
                           text-gray-900 dark:text-neutral-100
                           opacity-80 cursor-not-allowed" />
            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Comprobante (Imagen o PDF) --}}
            <div>
                <label class="block text-sm mb-1">Respaldo: <span class="text-red-500">*</span></label>
                <div x-data="{ uploading: false }" x-on:livewire-upload-start="uploading = true"
                    x-on:livewire-upload-finish="uploading = false" x-on:livewire-upload-error="uploading = false">
                    <label
                        class="h-10.5 group flex items-center justify-between w-full rounded-lg border border-dashed
                       border-gray-300/70 dark:border-neutral-700/70
                       bg-white dark:bg-neutral-900 px-4 py-2 cursor-pointer
                       hover:bg-gray-50 dark:hover:bg-neutral-800 transition">

                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-8 h-8 rounded-lg border border-gray-200/70 dark:border-neutral-700/70
                               bg-gray-50 dark:bg-neutral-800 flex items-center justify-center shrink-0">
                                @if (
                                    $foto_comprobante &&
                                        !is_string($foto_comprobante) &&
                                        strtolower($foto_comprobante->getClientOriginalExtension()) === 'pdf')
                                    {{-- Icono PDF --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                        <polyline points="10 9 9 9 8 9" />
                                    </svg>
                                @elseif ($foto_comprobante && !is_string($foto_comprobante))
                                    {{-- Icono Imagen --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <polyline points="21 15 16 10 5 21" />
                                    </svg>
                                @else
                                    {{-- Icono Upload --}}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-4 h-4 text-gray-600 dark:text-neutral-200" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="17 8 12 3 7 8" />
                                        <line x1="12" y1="3" x2="12" y2="15" />
                                    </svg>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-800 dark:text-neutral-100">
                                    Adjuntar archivo
                                </div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                                    @if ($foto_comprobante && !is_string($foto_comprobante))
                                        {{ $foto_comprobante->getClientOriginalName() }}
                                    @else
                                        JPG, PNG o PDF (máx. 5MB)
                                    @endif
                                </div>
                            </div>
                        </div>

                        <input type="file" wire:model.live="foto_comprobante" accept=".jpg,.jpeg,.png,.pdf"
                            class="hidden" />
                    </label>

                    <div x-show="uploading" x-cloak class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                        Subiendo archivo...
                    </div>
                </div>
                @error('foto_comprobante')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror

                @if ($foto_comprobante && !is_string($foto_comprobante))
                    <div class="mt-2 text-right">
                        <button type="button" wire:click="$set('foto_comprobante', null)"
                            class="text-xs text-red-500 hover:text-red-700 underline cursor-pointer">
                            Quitar archivo
                        </button>
                    </div>
                @endif
            </div>

            {{-- Detalle --}}
            <div>
                <label class="block text-sm mb-1">Detalle (Opcional):</label>
                <input type="text" wire:model="observacion_factura"
                    class="w-full rounded-lg border px-3 py-2
                       bg-white dark:bg-neutral-900
                       border-gray-300/60 dark:border-neutral-700/60
                       text-gray-900 dark:text-neutral-100
                       focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                @error('observacion_factura')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- INFORMATIVO PROYECTOS TIPO PROPUESTA --}}
        <div class="px-1 py-1 flex justify-start">
            <div
                class="w-fit flex items-center gap-2 text-blue-700 dark:text-blue-400 bg-blue-50/60 dark:bg-blue-900/20 px-3 py-1.5 rounded-lg border border-blue-100 dark:border-blue-800/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="16" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
                </svg>
                <div class="text-[11px] leading-tight whitespace-nowrap">
                    <span class="font-semibold text-blue-800 dark:text-blue-300">Nota:</span> Solo se pueden emitir
                    facturas para proyectos con estado <span
                        class="font-bold underline decoration-blue-300 dark:decoration-blue-700">Ejecución</span>.
                </div>
            </div>
        </div>


    </div>


    {{-- Footer --}}
    @slot('footer')
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:justify-end sm:gap-3" x-data="{ uploading: false }"
            x-on:livewire-upload-start="uploading = true" x-on:livewire-upload-finish="uploading = false"
            x-on:livewire-upload-error="uploading = false">

            <button type="button" @click="close()"
                class="w-full sm:w-auto px-4 py-2 rounded-lg border cursor-pointer
                       border-gray-300 dark:border-neutral-700
                       text-gray-700 dark:text-neutral-200
                       hover:bg-gray-100 dark:hover:bg-neutral-800">
                Cancelar
            </button>

            <button type="button" wire:click="saveFactura" wire:loading.attr="disabled"
                wire:target="saveFactura,foto_comprobante"
                x-bind:disabled="uploading || !$wire.entidad_id || !$wire.proyecto_id || !$wire.numero || !$wire
                    .monto_facturado_formatted || !$wire
                    .fecha_emision || !$wire.foto_comprobante"
                class="w-full sm:w-auto px-4 py-2 rounded-lg cursor-pointer
                       bg-black text-white hover:opacity-90
                       disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2">
                <span x-show="!uploading" wire:loading.remove wire:target="saveFactura">Guardar</span>
                <span x-show="uploading" x-cloak>Subiendo…</span>
                <span wire:loading wire:target="saveFactura">Guardando…</span>
            </button>

        </div>
    @endslot
</x-ui.modal>
