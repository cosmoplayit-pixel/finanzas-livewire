 {{-- FOTO --}}
 <x-ui.modal wire:key="foto-modal-{{ $openFotoModal ? 'open' : 'closed' }}" model="openFotoModal"
     title="Foto del comprobante" maxWidth="sm:max-w-2xl" onClose="closeFoto">

     <div class="p-3">
         @if ($fotoUrl)
             <img src="{{ $fotoUrl }}" alt="Foto" class="w-full rounded border dark:border-neutral-700" />
         @else
             <div class="text-sm text-gray-500 dark:text-neutral-400">No hay foto.</div>
         @endif
     </div>

     @slot('footer')
         <button type="button" wire:click="closeFoto"
             class="px-4 py-2 rounded-lg border border-gray-300 dark:border-neutral-700 text-gray-700 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 transition">
             Cerrar
         </button>
     @endslot
 </x-ui.modal>
