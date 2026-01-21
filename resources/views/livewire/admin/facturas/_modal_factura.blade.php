{{-- MODAL: NUEVA FACTURA (create) --}}
<x-ui.modal wire:key="facturas-create-modal" model="openFacturaModal" title="Nueva Factura" maxWidth="md:max-w-2xl"
    onClose="closeFactura">
    {{-- Entidad --}}
    <div>
        <label class="block text-sm mb-1">Entidad</label>
        <select wire:model.live="entidad_id"
            class="cursor-pointer w-full rounded border px-3 py-2
                   bg-white dark:bg-neutral-900
                   border-gray-300 dark:border-neutral-700
                   text-gray-900 dark:text-neutral-100
                   focus:outline-none focus:ring-2
                   focus:ring-gray-300 dark:focus:ring-neutral-700">
            <option value="">Seleccione...</option>
            @foreach ($entidades as $e)
                <option value="{{ $e->id }}" title="{{ $e->nombre }}">
                    {{ \Illuminate\Support\Str::limit($e->nombre, 60) }}
                </option>
            @endforeach
        </select>
        @error('entidad_id')
            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Proyecto --}}
    <div>
        <label class="block text-sm mb-1">Proyecto</label>
        <select wire:model.live="proyecto_id" @disabled(!$entidad_id)
            class="cursor-pointer w-full rounded border px-3 py-2
                   bg-white dark:bg-neutral-900
                   border-gray-300 dark:border-neutral-700
                   text-gray-900 dark:text-neutral-100
                   focus:outline-none focus:ring-2
                   focus:ring-gray-300 dark:focus:ring-neutral-700
                   disabled:opacity-60 disabled:cursor-not-allowed">
            <option value="">
                {{ $entidad_id ? 'Seleccione...' : 'Seleccione una entidad primero' }}
            </option>
            @foreach ($proyectos as $p)
                <option value="{{ $p->id }}" title="{{ $p->nombre }}">
                    {{ \Illuminate\Support\Str::limit($p->nombre, 75) }}
                </option>
            @endforeach
        </select>
        @error('proyecto_id')
            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        {{-- Número --}}
        <div>
            <label class="block text-sm mb-1">Nro. Factura</label>
            <input wire:model="numero" autocomplete="off"
                class="w-full rounded border px-3 py-2
                       bg-white dark:bg-neutral-900
                       border-gray-300 dark:border-neutral-700
                       text-gray-900 dark:text-neutral-100
                       focus:outline-none focus:ring-2
                       focus:ring-gray-300 dark:focus:ring-neutral-700" />
            @error('numero')
                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>

        {{-- Monto --}}
        <div>
            <label class="block text-sm mb-1">Monto Facturado</label>
            <input type="text" inputmode="decimal" wire:model.lazy="monto_facturado_formatted" placeholder="0,00"
                class="w-full rounded border px-3 py-2
               bg-white dark:bg-neutral-900
               border-gray-300 dark:border-neutral-700
               text-gray-900 dark:text-neutral-100
               placeholder:text-gray-400 dark:placeholder:text-neutral-500
               focus:outline-none focus:ring-2
               focus:ring-gray-300 dark:focus:ring-neutral-700" />
            @error('monto_facturado')
                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>

        {{-- Fecha --}}
        <div>
            <label class="block text-sm mb-1">Fecha Emisión</label>
            <input type="datetime-local" wire:model="fecha_emision"
                class="w-full rounded border px-3 py-2
                       bg-white dark:bg-neutral-900
                       border-gray-300 dark:border-neutral-700
                       text-gray-900 dark:text-neutral-100
                       focus:outline-none focus:ring-2
                       focus:ring-gray-300 dark:focus:ring-neutral-700" />
            @error('fecha_emision')
                <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        {{-- Retención % --}}
        <div>
            <label class="block text-sm mb-1">Retención (%)</label>
            <input type="number" wire:model.live="retencion_porcentaje" disabled
                class="w-full rounded border px-3 py-2
                       bg-gray-100 dark:bg-neutral-800
                       border-gray-300 dark:border-neutral-700
                       text-gray-900 dark:text-neutral-100
                       opacity-80 cursor-not-allowed" />
        </div>

        {{-- Retención monto --}}
        <div>
            <label class="block text-sm mb-1">Retención (Monto)</label>
            <input type="number" step="0.01" disabled
                value="{{ number_format((float) ($retencion_monto ?? 0), 2, '.', '') }}"
                class="w-full rounded border px-3 py-2
                       bg-gray-100 dark:bg-neutral-800
                       border-gray-300 dark:border-neutral-700
                       text-gray-900 dark:text-neutral-100
                       opacity-80 cursor-not-allowed" />
        </div>

        {{-- Neto --}}
        <div>
            <label class="block text-sm mb-1">Monto Neto</label>
            <input type="number" step="0.01" disabled
                value="{{ number_format((float) ($monto_neto ?? 0), 2, '.', '') }}"
                class="w-full rounded border px-3 py-2
                       bg-gray-100 dark:bg-neutral-800
                       border-gray-300 dark:border-neutral-700
                       text-gray-900 dark:text-neutral-100
                       opacity-80 cursor-not-allowed" />
        </div>
    </div>

    {{-- Detalle --}}
    <div>
        <label class="block text-sm mb-1">Detalle</label>
        <textarea wire:model="observacion_factura" rows="3"
            class="w-full rounded border px-3 py-2
                   bg-white dark:bg-neutral-900
                   border-gray-300 dark:border-neutral-700
                   text-gray-900 dark:text-neutral-100
                   focus:outline-none focus:ring-2
                   focus:ring-gray-300 dark:focus:ring-neutral-700"></textarea>
        @error('observacion_factura')
            <div class="text-red-600 dark:text-red-400 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Footer --}}
    <x-slot:footer>
        <button type="button" @click="close()"
            class="cursor-pointer px-4 py-2 rounded border
                   border-gray-300 dark:border-neutral-700
                   text-gray-700 dark:text-neutral-200
                   hover:bg-gray-200 dark:hover:bg-neutral-800
                   transition-colors duration-150">
            Cancelar
        </button>

        <button type="button" wire:click="saveFactura" wire:loading.attr="disabled" wire:target="saveFactura"
            class="w-full sm:w-auto px-4 py-2 rounded bg-black text-white cursor-pointer
                   hover:bg-gray-800 transition
                   disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="saveFactura">Guardar</span>
            <span wire:loading wire:target="saveFactura">Guardando…</span>
        </button>
    </x-slot:footer>
</x-ui.modal>
