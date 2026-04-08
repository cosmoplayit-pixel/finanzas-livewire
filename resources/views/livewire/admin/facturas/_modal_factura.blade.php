{{-- MODAL: FACTURA (create / edit) --}}
<x-ui.modal wire:key="facturas-create-modal" model="openFacturaModal" :title="$facturaEditId ? 'Editar Factura' : 'Nueva Factura'" maxWidth="sm:max-w-xl md:max-w-2xl"
    onClose="closeFactura">

    <div class="space-y-2 sm:space-y-3">
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">

            {{-- Cliente --}}
            <div class="col-span-1 lg:col-span-1">
                <label class="block text-sm mb-1">Cliente: <span class="text-red-500">*</span></label>
                <select wire:model.live="entidad_id"
                    class="w-full cursor-pointer rounded-lg border px-3 py-2
                           bg-white dark:bg-neutral-900
                           border-gray-300/60 dark:border-neutral-700/60
                           text-gray-900 dark:text-neutral-100
                           focus:outline-none focus:ring-2 focus:ring-gray-500/40">
                    <option value="">Seleccione...</option>
                    @foreach ($entidades as $e)
                        <option value="{{ $e->id }}" title="{{ $e->nombre }}">{{ $e->nombre }}</option>
                    @endforeach
                </select>
                @error('entidad_id')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Proyecto --}}
            <div class="col-span-1 lg:col-span-1">
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
                        <option value="{{ $p->id }}" title="{{ $p->nombre }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
                @error('proyecto_id')
                    <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Monto --}}
            <div class="col-span-1 lg:col-span-1">
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
            <div class="col-span-1 lg:col-span-1">
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
            <div class="col-span-1 lg:col-span-1">
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

            {{-- Retención % --}}
            <div class="col-span-1 lg:col-span-1">
                <label class="block text-sm mb-1">Retención (%):</label>
                <input type="number" wire:model.live="retencion_porcentaje" disabled
                    class="w-full rounded-lg border px-3 py-2
                           bg-gray-100 dark:bg-neutral-800
                           border-gray-300/60 dark:border-neutral-700/60
                           text-gray-900 dark:text-neutral-100
                           opacity-80 cursor-not-allowed" />
            </div>

            {{-- Retención monto --}}
            <div class="col-span-1 lg:col-span-1">
                <label class="block text-sm mb-1">Retención (Monto):</label>
                <input type="number" step="0.01" disabled wire:model="retencion_monto"
                    class="w-full rounded-lg border px-3 py-2
                           bg-gray-100 dark:bg-neutral-800
                           border-gray-300/60 dark:border-neutral-700/60
                           text-gray-900 dark:text-neutral-100
                           opacity-80 cursor-not-allowed" />
            </div>

            {{-- Neto --}}
            <div class="col-span-1 lg:col-span-1">
                <label class="block text-sm mb-1">Monto Neto:</label>
                <input type="number" step="0.01" disabled wire:model="monto_neto"
                    class="w-full rounded-lg border px-3 py-2
                           bg-gray-100 dark:bg-neutral-800
                           border-gray-300/60 dark:border-neutral-700/60
                           text-gray-900 dark:text-neutral-100
                           opacity-80 cursor-not-allowed" />
            </div>

            {{-- Comprobante (Imagen o PDF) --}}
            <div class="col-span-2 lg:col-span-1">
                <x-ui.scanner model="foto_comprobante" :label="$facturaEditId ? 'Respaldo (opcional: reemplaza el actual)' : 'Respaldo'" :file="$foto_comprobante" />
            </div>

            {{-- Detalle --}}
            <div class="col-span-2 lg:col-span-3">
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

        {{-- Nota --}}
        <div class="flex justify-start mt-3">
            <div
                class="w-fit flex items-center gap-2 text-blue-700 dark:text-blue-400 bg-blue-50/60 dark:bg-blue-900/20 px-3 py-1.5 rounded-lg border border-blue-100 dark:border-blue-800/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="16" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
                </svg>
                <div class="text-[11px] leading-tight">
                    <span class="font-semibold text-blue-800 dark:text-blue-300">Nota:</span> Solo se pueden emitir
                    facturas para proyectos con estado <span
                        class="font-bold underline decoration-blue-300 dark:decoration-blue-700">Ejecución</span>.
                </div>
            </div>
        </div>
    </div>

    @slot('footer')
        <div class="grid grid-cols-2 gap-2 w-full sm:flex sm:justify-end sm:gap-3" x-data="{ uploading: false }"
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
                    .monto_facturado_formatted || !$wire.fecha_emision ||
                    (!$wire.facturaEditId && !$wire.foto_comprobante)"
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
