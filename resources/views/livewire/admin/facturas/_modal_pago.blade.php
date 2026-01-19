<x-ui.modal wire:key="pago-modal" model="openPagoModal" title="Registrar Pago" maxWidth="sm:max-w-xl md:max-w-2xl"
    onClose="closePago">
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
            <input type="number" step="0.01" wire:model.live="monto"
                class="w-full rounded border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700">
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

    <x-slot:footer>
        <button type="button" @click="close()"
            class="px-4 py-2 rounded border border-gray-300 dark:border-neutral-700
                   text-gray-700 dark:text-neutral-200 hover:bg-gray-200 dark:hover:bg-neutral-800">
            Cancelar
        </button>

        <button type="button" wire:click="savePago" wire:loading.attr="disabled" wire:target="savePago"
            class="px-4 py-2 rounded bg-black text-white hover:bg-gray-800
                   disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="savePago">Guardar pago</span>
            <span wire:loading wire:target="savePago">Guardando…</span>
        </button>
    </x-slot:footer>
</x-ui.modal>
