 {{-- FILTROS --}}
 <div x-data="{ openFilters: false }" class="relative">

     <div class="flex flex-col gap-3 md:flex-row md:items-center">
         {{-- Buscar --}}
         <input type="search" wire:model.live.debounce.300ms="search"
             placeholder="Buscar por Factura, Proyecto o Entidad..."
             class="w-full md:w-72 lg:w-md border rounded px-3 py-2" autocomplete="off" />

         {{-- Selects derecha --}}
         <div class="flex flex-col sm:flex-row gap-3 md:ml-auto w-full md:w-auto">
             {{-- PerPage --}}
             <select wire:model.live="perPage"
                 class="w-full sm:w-auto border rounded px-3 py-2
                bg-white text-gray-900 border-gray-300
                dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                focus:outline-none focus:ring-2 focus:ring-offset-0
                focus:ring-gray-300 dark:focus:ring-neutral-600">
                 <option value="5">5</option>
                 <option value="10">10</option>
                 <option value="50">50</option>
             </select>

             {{-- Botón Filtros --}}
             <button type="button" @click.stop="openFilters = !openFilters"
                 class="w-full sm:w-auto border rounded px-3 py-2
                bg-white text-gray-900 border-gray-300 hover:bg-gray-50
                dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800
                focus:outline-none focus:ring-2 focus:ring-offset-0
                focus:ring-gray-300 dark:focus:ring-neutral-600">
                 Filtros
             </button>
         </div>
     </div>

     {{-- ✅ PANEL FLOTANTE --}}
     <div x-show="openFilters" x-cloak @click.outside="openFilters = false" @keydown.escape.window="openFilters = false"
         class="absolute right-0 mt-3 w-full sm:w-[360px] z-50 rounded-xl border border-gray-200 bg-white shadow-xldark:border-neutral-700 dark:bg-neutral-900 overflow-hidden"
         wire:ignore.self wire:key="facturas-panel-filtros">

         {{-- Header --}}
         <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
             <div class="font-semibold text-gray-800 dark:text-neutral-100">Filtros</div>
         </div>

         <div class="px-4 pb-4 space-y-4" x-data="{ secPago: true, secRet: true, secFecha: true, secEstado: true }">

             {{-- PAGO --}}
             <div class="border-t border-gray-200 dark:border-neutral-700 pt-3">
                 <button type="button" class="w-full flex items-center justify-between" @click="secPago = !secPago">
                     <span class="font-semibold text-gray-800 dark:text-neutral-100">Pago</span>
                     <span class="text-gray-400" x-text="secPago ? '▾' : '▸'"></span>
                 </button>

                 <div x-show="secPago" class="mt-3 space-y-2">

                     <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                         <input type="checkbox" value="pendiente" wire:model.live="f_pago"
                             class="rounded border-gray-300 dark:border-neutral-700" />
                         Pendiente
                     </label>

                     <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                         <input type="checkbox" wire:key="chk-pago-parcial" @checked(in_array('parcial', $f_pago ?? [], true))
                             wire:click="toggleFilter('pago','parcial')"
                             class="rounded border-gray-300 dark:border-neutral-700" />
                         Parcial
                     </label>

                     <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                         <input type="checkbox" wire:key="chk-pago-pagada-neto" @checked(in_array('pagada_neto', $f_pago ?? [], true))
                             wire:click="toggleFilter('pago','pagada_neto')"
                             class="rounded border-gray-300 dark:border-neutral-700" />
                         Completado (Neto)
                     </label>

                 </div>
             </div>

             {{-- RETENCIÓN --}}
             <div class="border-t border-gray-200 dark:border-neutral-700 pt-3">
                 <button type="button" class="w-full flex items-center justify-between" @click="secRet = !secRet">
                     <span class="font-semibold text-gray-800 dark:text-neutral-100">Retención</span>
                     <span class="text-gray-400" x-text="secRet ? '▾' : '▸'"></span>
                 </button>

                 <div x-show="secRet" class="mt-3 space-y-2">

                     <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                         <input type="checkbox" wire:key="chk-ret-sin" @checked(in_array('sin_retencion', $f_retencion ?? [], true))
                             wire:click="toggleFilter('retencion','sin_retencion')"
                             class="rounded border-gray-300 dark:border-neutral-700" />
                         Sin retención
                     </label>

                     <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                         <input type="checkbox" wire:key="chk-ret-pendiente" @checked(in_array('retencion_pendiente', $f_retencion ?? [], true))
                             wire:click="toggleFilter('retencion','retencion_pendiente')"
                             class="rounded border-gray-300 dark:border-neutral-700" />
                         Retención pendiente
                     </label>

                     <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                         <input type="checkbox" wire:key="chk-ret-pagada" @checked(in_array('retencion_pagada', $f_retencion ?? [], true))
                             wire:click="toggleFilter('retencion','retencion_pagada')"
                             class="rounded border-gray-300 dark:border-neutral-700" />
                         Retención pagada
                     </label>

                 </div>
             </div>

             {{-- ESTADO GLOBAL --}}
             <div class="border-t border-gray-200 dark:border-neutral-700 pt-3">
                 <button type="button" class="w-full flex items-center justify-between"
                     @click="secEstado = !secEstado">
                     <span class="font-semibold text-gray-800 dark:text-neutral-100">Estado</span>
                     <span class="text-gray-400" x-text="secEstado ? '▾' : '▸'"></span>
                 </button>

                 <div x-show="secEstado" class="mt-3 space-y-2">

                     <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                         <input type="checkbox" wire:key="chk-cerr-abierta" @checked(in_array('abierta', $f_cerrada ?? [], true))
                             wire:click="toggleFilter('cerrada','abierta')"
                             class="rounded border-gray-300 dark:border-neutral-700" />
                         Abiertas
                     </label>

                     <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                         <input type="checkbox" wire:key="chk-cerr-cerrada" @checked(in_array('cerrada', $f_cerrada ?? [], true))
                             wire:click="toggleFilter('cerrada','cerrada')"
                             class="rounded border-gray-300 dark:border-neutral-700" />
                         Cerradas
                     </label>


                 </div>
             </div>

             {{-- FECHA (RANGO) - Emisión --}}
             <div class="border-t border-gray-200 dark:border-neutral-700 pt-3">
                 <button type="button" class="w-full flex items-center justify-between" @click="secFecha = !secFecha">
                     <span class="font-semibold text-gray-800 dark:text-neutral-100">Fecha (Emisión)</span>
                     <span class="text-gray-400" x-text="secFecha ? '▾' : '▸'"></span>
                 </button>

                 <div x-show="secFecha" class="mt-3 space-y-3">

                     <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                         <div>
                             <label class="block text-xs text-gray-600 dark:text-neutral-300 mb-1">Desde</label>
                             <input id="fecha_desde" type="date" wire:model.live="f_fecha_desde"
                                 class="w-full border rounded px-3 py-2 bg-white text-gray-900 border-gray-300 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                                focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-gray-300 dark:focus:ring-neutral-600" />
                         </div>

                         <div>
                             <label class="block text-xs text-gray-600 dark:text-neutral-300 mb-1">Hasta</label>
                             <input id="fecha_hasta" type="date" wire:model.live="f_fecha_hasta"
                                 class="w-full border rounded px-3 py-2 bg-white text-gray-900 border-gray-300 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                                focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-gray-300 dark:focus:ring-neutral-600" />
                         </div>
                     </div>

                     {{-- Opcional: acciones rápidas --}}
                     <div class="flex gap-2">
                         <button type="button" wire:click="setFechaEsteAnio"
                             class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                             Este año
                         </button>

                         <button type="button" wire:click="setFechaAnioPasado"
                             class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                             Año pasado
                         </button>

                         <button type="button" wire:click="clearFecha"
                             class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                             Limpiar fecha
                         </button>
                     </div>

                 </div>
             </div>

         </div>
     </div>
 </div>
