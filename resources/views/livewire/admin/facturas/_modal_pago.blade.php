<x-ui.modal wire:key="pago-modal" model="openPagoModal" title="Registrar Pago" maxWidth="sm:max-w-xl md:max-w-2xl"
    onClose="closePago">
    <div class="space-y-4">
        {{-- Tipo / Método / Fecha --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label class="block text-sm mb-1">Tipo <span class="text-red-500">*</span></label>
            <select wire:model="tipo"
                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700">
                <option value="normal">Normal</option>
                <option value="retencion">Retención</option>
            </select>
            @error('tipo')
                <div class="text-red-600 text-xs">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Método de pago <span class="text-red-500">*</span></label>
            <select wire:model="metodo_pago"
                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700">
                <option value="transferencia">Transferencia</option>
                <option value="deposito">Depósito</option>
                <option value="cheque">Cheque</option>
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="qr">QR</option>
                <option value="otro">Otro</option>
            </select>
            @error('metodo_pago')
                <div class="text-red-600 text-xs">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Fecha de pago <span class="text-red-500">*</span></label>
            <input type="datetime-local" wire:model="fecha_pago"
                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700">
            @error('fecha_pago')
                <div class="text-red-600 text-xs">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Banco --}}
    <div>
        <label class="block text-sm mb-1">Banco destino <span class="text-red-500">*</span></label>
        <select wire:model="banco_id"
            class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700">
            <option value="">Seleccione...</option>
            @foreach ($bancos as $b)
                <option value="{{ $b->id }}">
                    {{ $b->nombre }} | {{ $b->numero_cuenta }} | {{ $b->moneda }}
                </option>
            @endforeach
        </select>
        @error('banco_id')
            <div class="text-red-600 text-xs">{{ $message }}</div>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label class="block text-sm mb-1">Monto <span class="text-red-500">*</span></label>
            <input type="text" inputmode="decimal" wire:model.lazy="monto_formatted" placeholder="0,00"
                class="w-full rounded border px-3 py-2
                bg-white dark:bg-neutral-900
                border-gray-300 dark:border-neutral-700
                text-gray-900 dark:text-neutral-100
                placeholder:text-gray-400 dark:placeholder:text-neutral-500
                focus:outline-none focus:ring-2
                focus:ring-gray-300 dark:focus:ring-neutral-700">

            @error('monto')
                <div class="text-red-600 text-xs">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm mb-1">Nro. Operación</label>
            <input wire:model="nro_operacion"
                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700">
            @error('nro_operacion')
                <div class="text-red-600 text-xs">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm mb-1">Observación</label>
        <textarea wire:model="observacion" rows="3"
            class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700"></textarea>
        @error('observacion')
            <div class="text-red-600 text-xs">{{ $message }}</div>
        @enderror
    </div>

    {{-- Comprobante (Imagen o PDF) --}}
    <div>
        <label class="block text-sm mb-1">Respaldo (Imagen/PDF)</label>
        <label
            class="group h-11 flex items-center justify-between w-full rounded border border-dashed
            border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900
            px-4 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-neutral-800 transition">

            <div class="flex items-center gap-3 min-w-0">
                <div
                    class="w-7 h-7 rounded border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800
                    flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4 text-gray-600 dark:text-neutral-200" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" y1="3" x2="12" y2="15" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <div class="text-sm font-medium text-gray-800 dark:text-neutral-100">Adjuntar
                        archivo</div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                        @if ($pago_foto_comprobante)
                            {{ $pago_foto_comprobante->getClientOriginalName() }}
                        @else
                            JPG, PNG o PDF (máx. 5MB)
                        @endif
                    </div>
                </div>
            </div>

            <input type="file" wire:model.live="pago_foto_comprobante" accept=".jpg,.jpeg,.png,.pdf"
                class="hidden" />
        </label>

        <div wire:loading wire:target="pago_foto_comprobante" class="text-xs text-emerald-600 font-medium mt-1">
            Cargando...
        </div>
        @error('pago_foto_comprobante')
            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror

        @if ($pago_foto_comprobante)
            <div class="mt-2 text-xs flex justify-end">
                <button type="button" wire:click="$set('pago_foto_comprobante', null)"
                    class="text-red-500 hover:text-red-600 font-medium">
                    Quitar archivo
                </button>
            </div>
        @endif
    </div>
    </div>

    <x-slot:footer>
        <button type="button" @click="close()"
            class="px-4 py-2 rounded cursor-pointer border border-gray-300 dark:border-neutral-700
                   text-gray-700 dark:text-neutral-200 hover:bg-gray-200 dark:hover:bg-neutral-800">
            Cancelar
        </button>

        <button type="button" wire:click="savePago" wire:loading.attr="disabled" wire:target="savePago"
            class="px-4 py-2 cursor-pointer rounded bg-black text-white hover:bg-gray-800
                   disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="savePago">Guardar pago</span>
            <span wire:loading wire:target="savePago">Guardando…</span>
        </button>
    </x-slot:footer>
</x-ui.modal>
