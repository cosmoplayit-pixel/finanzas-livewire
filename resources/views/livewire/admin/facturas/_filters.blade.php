 {{-- FILTROS --}}
 @php
     $filtrosActivos = 0;
     if (!empty($f_pago)) {
         $filtrosActivos += is_array($f_pago) ? count($f_pago) : 1;
     }
     if (!empty($f_retencion)) {
         $filtrosActivos += is_array($f_retencion) ? count($f_retencion) : 1;
     }
     if (!empty($f_cerrada)) {
         $filtrosActivos += is_array($f_cerrada) ? count($f_cerrada) : 1;
     }
     if (!empty($f_fecha_desde) || !empty($f_fecha_hasta)) {
         $filtrosActivos++;
     }
 @endphp
 <div x-data="{ openFilters: false }" class="relative mb-6">

     <div class="rounded-xl border bg-white dark:bg-neutral-900/40 dark:border-neutral-700 overflow-hidden">
         {{-- MOBILE (<= md): FILTROS COLAPSABLES --}}
         <div class="md:hidden" x-data="{ openMobile: false }">
             <div class="px-4 h-11 flex items-center justify-between">
                 <div class="text-[13px] font-semibold text-gray-700 dark:text-neutral-200">
                     Filtros
                 </div>
                 <button type="button" @click="openMobile = !openMobile"
                     class="inline-flex items-center gap-1.5 px-3 h-8 rounded-lg text-[13px] font-semibold border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:hover:bg-neutral-800/60 transition cursor-pointer">
                     <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                         <path d="M21 4h-7" />
                         <path d="M10 4H3" />
                         <path d="M21 12h-9" />
                         <path d="M8 12H3" />
                         <path d="M21 20h-5" />
                         <path d="M12 20H3" />
                         <path d="M14 2v4" />
                         <path d="M12 10v4" />
                         <path d="M16 18v4" />
                     </svg>
                     <span x-text="openMobile ? 'Ocultar' : 'Mostrar'"></span>
                 </button>
             </div>

             <div class="mt-2 space-y-3 px-4 pb-3 text-[13px]" x-show="openMobile" x-collapse x-cloak>
                 <div>
                     <label class="block mb-1 text-gray-600 dark:text-neutral-300 text-[13px]">Búsqueda</label>
                     <input type="text" wire:model.live.debounce.300ms="search"
                         placeholder="Nro Factura, Proyecto o Entidad..." autocomplete="new-password" readonly
                         onfocus="this.removeAttribute('readonly')"
                         class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 text-[13px] focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                 </div>
                 <div class="grid grid-cols-2 gap-3">
                     <div>
                         <label class="block mb-1 text-gray-600 dark:text-neutral-300 text-[13px]">Mostrar</label>
                         <select wire:model.live="perPage"
                             class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 text-[13px] focus:outline-none focus:ring-2 focus:ring-gray-500/40 cursor-pointer">
                             <option value="5">5</option>
                             <option value="10">10</option>
                             <option value="50">50</option>
                         </select>
                     </div>
                     <div>
                         <label class="block mb-1 text-transparent select-none text-[13px]">&nbsp;</label>
                         <button type="button" @click.stop="openFilters = !openFilters"
                             class="relative w-full flex items-center justify-center gap-2 rounded-lg border px-3 py-2 bg-white text-gray-900 border-gray-300 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px] font-medium transition cursor-pointer
                                    {{ $filtrosActivos > 0 ? 'border-blue-500 dark:border-blue-500' : '' }}">
                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                     d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                             </svg>
                             Avanzados
                             @if ($filtrosActivos > 0)
                                 <span
                                     class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white text-[10px] font-bold leading-none">
                                     {{ $filtrosActivos }}
                                 </span>
                             @endif
                         </button>
                     </div>
                 </div>
             </div>
         </div>

         {{-- DESKTOP (>= md): Layout extendido --}}
         <div class="hidden md:block p-4">
             <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                 <div class="md:col-span-6 lg:col-span-8">
                     <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Búsqueda</label>
                     <input type="text" wire:model.live.debounce.300ms="search"
                         placeholder="Nro Factura, Proyecto o Entidad..." autocomplete="new-password" readonly
                         onfocus="this.removeAttribute('readonly')"
                         class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40" />
                 </div>

                 <div class="md:col-span-3 lg:col-span-2">
                     <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Mostrar</label>
                     <select wire:model.live="perPage"
                         class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-700 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-gray-500/40 cursor-pointer">
                         <option value="5">5</option>
                         <option value="10">10</option>
                         <option value="50">50</option>
                     </select>
                 </div>

                 <div class="md:col-span-3 lg:col-span-2">
                     <label class="block text-xs mb-1 text-gray-600 dark:text-neutral-300">Filtros</label>
                     <button type="button" @click.stop="openFilters = !openFilters"
                         class="relative w-full flex items-center justify-center gap-2 rounded-lg border px-3 py-2 bg-white text-gray-900 border-gray-300 hover:bg-gray-50 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-gray-500/40 text-[13px] font-medium transition cursor-pointer
                                {{ $filtrosActivos > 0 ? 'border-blue-500 dark:border-blue-500' : '' }}">
                         <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                         </svg>
                         Opciones
                         @if ($filtrosActivos > 0)
                             <span
                                 class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white text-[10px] font-bold leading-none">
                                 {{ $filtrosActivos }}
                             </span>
                         @endif
                     </button>
                 </div>
             </div>
         </div>
     </div>

     {{-- ✅ PANEL FLOTANTE --}}
     <div x-show="openFilters" x-cloak @click.outside="openFilters = false" @keydown.escape.window="openFilters = false"
         class="absolute right-0 top-full mt-2 w-full sm:w-[360px] z-50 rounded-xl border border-gray-200 bg-white shadow-xl dark:border-neutral-700 dark:bg-neutral-900 overflow-hidden"
         wire:ignore.self wire:key="facturas-panel-filtros">

         {{-- Header --}}
         <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-700">
             <div class="flex items-center gap-2">
                 <div class="font-semibold text-gray-800 dark:text-neutral-100">Filtros</div>
                 @if ($filtrosActivos > 0)
                     <span
                         class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full bg-blue-500 text-white text-[10px] font-bold leading-none">
                         {{ $filtrosActivos }} activo{{ $filtrosActivos > 1 ? 's' : '' }}
                     </span>
                 @endif
             </div>
         </div>

         <div class="px-4 pb-4 space-y-4" x-data="{ secPago: true, secRet: true, secFecha: true, secEstado: true }">

             {{-- PAGO --}}
             <div class="border-t border-gray-200 dark:border-neutral-700 pt-3">
                 <button type="button" class="w-full flex items-center justify-between cursor-pointer"
                     @click="secPago = !secPago">
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
                 <button type="button" class="w-full flex items-center justify-between cursor-pointer"
                     @click="secRet = !secRet">
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
                 <button type="button" class="w-full flex items-center justify-between cursor-pointer"
                     @click="secEstado = !secEstado">
                     <span class="font-semibold text-gray-800 dark:text-neutral-100">Estado</span>
                     <span class="text-gray-400" x-text="secEstado ? '▾' : '▸'"></span>
                 </button>

                 <div x-show="secEstado" class="mt-3 space-y-2">

                     <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                         <input type="checkbox" wire:key="chk-cerr-abierta" @checked(in_array('abierta', $f_cerrada ?? [], true))
                             wire:click="toggleFilter('cerrada','abierta')"
                             class="rounded border-gray-300 dark:border-neutral-700" />
                         Por cobrar
                     </label>

                     <label class="flex items-center gap-2 text-sm text-gray-800 dark:text-neutral-200">
                         <input type="checkbox" wire:key="chk-cerr-cerrada" @checked(in_array('cerrada', $f_cerrada ?? [], true))
                             wire:click="toggleFilter('cerrada','cerrada')"
                             class="rounded border-gray-300 dark:border-neutral-700" />
                         Pagadas
                     </label>


                 </div>
             </div>

             {{-- FECHA (RANGO) - Emisión --}}
             <div class="border-t border-gray-200 dark:border-neutral-700 pt-3">
                 <button type="button" class="w-full flex items-center justify-between cursor-pointer"
                     @click="secFecha = !secFecha">
                     <span class="font-semibold text-gray-800 dark:text-neutral-100">Fecha (Emisión)</span>
                     <span class="text-gray-400" x-text="secFecha ? '▾' : '▸'"></span>
                 </button>

                 <div x-show="secFecha" class="mt-3 space-y-3">

                     <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                         <div>
                             <label class="block text-xs text-gray-600 dark:text-neutral-300 mb-1">Desde</label>
                             <input id="fecha_desde" type="date" wire:model.live="f_fecha_desde"
                                 class="w-full cursor-pointer border rounded px-3 py-2 bg-white text-gray-900 border-gray-300 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                                focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-gray-300 dark:focus:ring-neutral-600" />
                         </div>

                         <div>
                             <label class="block text-xs text-gray-600 dark:text-neutral-300 mb-1">Hasta</label>
                             <input id="fecha_hasta" type="date" wire:model.live="f_fecha_hasta"
                                 class="w-full cursor-pointer border rounded px-3 py-2 bg-white text-gray-900 border-gray-300 dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700
                                focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-gray-300 dark:focus:ring-neutral-600" />
                         </div>
                     </div>

                     {{-- Opcional: acciones rápidas --}}
                     <div class="flex gap-2">
                         <button type="button" wire:click="setFechaEsteAnio"
                             class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 cursor-pointer">
                             Este año
                         </button>

                         <button type="button" wire:click="setFechaAnioPasado"
                             class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 cursor-pointer">
                             Año pasado
                         </button>

                         <button type="button" wire:click="clearFecha"
                             class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 cursor-pointer">
                             Limpiar fecha
                         </button>
                     </div>

                 </div>
             </div>

         </div>
     </div>
 </div>
