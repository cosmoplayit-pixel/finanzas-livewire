    {{-- ===================== MODAL VER DETALLE PRÉSTAMO ===================== --}}
    <x-ui.modal wire:key="prestamo-ver-{{ $openModalVer ? 'open' : 'closed' }}" model="openModalVer"
        title="Detalle del Préstamo — {{ $verNroPrestamo }}" maxWidth="sm:max-w-5xl" onClose="closeVer">

        @if ($verPrestamos && $verPrestamos->isNotEmpty())
            @php
                $verFirst = $verPrestamos->first();
                $totalPrestadas = $verPrestamos->sum('cantidad_prestada');
                $totalDevueltas = $verPrestamos->sum('cantidad_devuelta');
                $totalPendientes = $totalPrestadas - $totalDevueltas;
                $isVencido = $verPrestamos->contains(
                    fn($i) => $i->estado !== 'finalizado' && $i->fecha_vencimiento && $i->fecha_vencimiento->isPast(),
                );
                $estadoGlobal = $totalPendientes == 0 ? 'finalizado' : ($isVencido ? 'vencido' : 'activo');
                $progreso = $totalPrestadas > 0 ? round(($totalDevueltas / $totalPrestadas) * 100) : 0;

                // Identificar sesiones de retorno. Según el Trait WithDevoluciones, solo el primer registro
                // de cada lote de retorno recibe el array de fotos, lo que nos sirve de "bandera" de inicio.
                $allDevolutions = $verPrestamos
                    ->flatMap(function ($vp) {
                        return $vp->devoluciones->map(function ($d) use ($vp) {
                            $d->_herramienta_nombre = $vp->herramienta?->nombre ?? 'Herramienta Eliminada';
                            $d->_herramienta_imagen = $vp->herramienta?->imagen ?? null;
                            $d->_herramienta_id = $vp->herramienta_id;
                            return $d;
                        });
                    })
                    ->sortBy('id'); // El ID garantiza el orden real de inserción

                $receptores = [];
                $sessionTemp = null;
                $sessionIdx = 1;

                foreach ($allDevolutions as $dev) {
                    // Si el item tiene fotos o es el primero, iniciamos un subgrupo (sesión)
                    // Nota: fotos_entrada es un array, se considera inicio si no está vacío
                    $esInicioSesion = !empty($dev->fotos_entrada);

                    if (!$sessionTemp || $esInicioSesion) {
                        if ($sessionTemp) {
                            $receptores[] = $sessionTemp;
                        }
                        $sessionTemp = [
                            'nro' => $sessionIdx++,
                            'fecha_devolucion' => $dev->fecha_devolucion,
                            'observaciones' => $dev->observaciones,
                            'fotos' => $dev->fotos_entrada ?? [],
                            'firma' => $dev->firma_entrada,
                            'items' => collect([]),
                            'created_at' => $dev->created_at,
                        ];
                    }
                    $sessionTemp['items']->push($dev);
                }
                if ($sessionTemp) {
                    $receptores[] = $sessionTemp;
                }

                $receptores = array_reverse($receptores); // Mostrar el más reciente arriba

                $fotosSalidaUrls = collect($verFirst->fotos_salida ?? [])
                    ->map(fn($f) => Storage::url($f))
                    ->values()
                    ->all();
            @endphp

            <div class=" overflow-y-auto space-y-4" x-data="{}" x-init="$nextTick(() => { const el = $el.querySelector('[data-destacado]'); if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); })">

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- SECCIÓN 1 — CABECERA: Estado + Stats + Datos      --}}
                {{-- ══════════════════════════════════════════════════ --}}
                <div
                    class="rounded-xl border border-gray-200 dark:border-neutral-700 bg-gray-50/50 dark:bg-neutral-800/30 overflow-hidden">

                    {{-- Barra de estado + progreso --}}
                    <div
                        class="flex items-center justify-between gap-4 px-4 py-3 border-b border-gray-100 dark:border-neutral-700/50">
                        <div class="flex items-center gap-2.5">
                            @if ($estadoGlobal === 'finalizado')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400 text-[11px] font-black uppercase ring-1 ring-emerald-200 dark:ring-emerald-700/50">
                                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Devuelto
                                </span>
                            @elseif ($estadoGlobal === 'vencido')
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400 text-[11px] font-black uppercase ring-1 ring-red-200 dark:ring-red-700/50 animate-pulse">
                                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                    </svg>
                                    Vencido
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400 text-[11px] font-black uppercase ring-1 ring-blue-200 dark:ring-blue-700/50">
                                    <span class="size-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    En Obra
                                </span>
                            @endif

                            {{-- Stats rápidos --}}
                            <div class="flex items-center gap-1.5 text-[11px]">
                                <span class="text-gray-400 dark:text-neutral-600">·</span>
                                <span class="text-gray-600 dark:text-neutral-300 font-bold">{{ $totalPrestadas }}
                                    prest.</span>
                                <span class="text-gray-300 dark:text-neutral-700">/</span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $totalDevueltas }}
                                    dev.</span>
                                @if ($totalPendientes > 0)
                                    <span class="text-gray-300 dark:text-neutral-700">/</span>
                                    <span class="text-amber-600 dark:text-amber-400 font-bold">{{ $totalPendientes }}
                                        pend.</span>
                                @endif
                            </div>
                        </div>

                        {{-- Porcentaje --}}
                        <span
                            class="text-[11px] font-black {{ $estadoGlobal === 'finalizado' ? 'text-emerald-600 dark:text-emerald-400' : ($isVencido ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400') }}">
                            {{ $progreso }}%
                        </span>
                    </div>

                    {{-- Barra de progreso --}}
                    <div class="h-1 w-full bg-gray-100 dark:bg-neutral-700">
                        <div class="h-full transition-all duration-500 {{ $estadoGlobal === 'finalizado' ? 'bg-emerald-500' : ($isVencido ? 'bg-red-500' : 'bg-blue-500') }}"
                            style="width: {{ $progreso }}%"></div>
                    </div>

                    {{-- Grid de datos --}}
                    <div
                        class="grid grid-cols-2 sm:grid-cols-3 gap-0 divide-x divide-y divide-gray-100 dark:divide-neutral-700/50">
                        <div class="px-4 py-2">
                            <div
                                class="text-[9px] uppercase font-black text-gray-400 dark:text-neutral-500 tracking-wider mb-0.5">
                                Cliente</div>
                            <div class="font-bold text-gray-900 dark:text-neutral-100 text-[13px] truncate">
                                {{ $verFirst->entidad?->nombre ?? '—' }}</div>
                        </div>
                        <div class="px-4 py-2">
                            <div
                                class="text-[9px] uppercase font-black text-gray-400 dark:text-neutral-500 tracking-wider mb-0.5">
                                Proyecto</div>
                            <div class="font-bold text-gray-900 dark:text-neutral-100 text-[13px] truncate">
                                {{ $verFirst->proyecto?->nombre ?? '—' }}</div>
                        </div>
                        <div class="px-4 py-2 col-span-2 sm:col-span-1">
                            <div
                                class="text-[9px] uppercase font-black text-gray-400 dark:text-neutral-500 tracking-wider mb-0.5">
                                Responsable</div>
                            <div class="font-bold text-gray-900 dark:text-neutral-100 text-[13px] truncate">
                                {{ $verFirst->agente?->nombre ?? ($verFirst->receptor_manual ?: '—') }}</div>
                        </div>
                        <div class="px-4 py-2">
                            <div
                                class="text-[9px] uppercase font-black text-gray-400 dark:text-neutral-500 tracking-wider mb-0.5">
                                Fecha Salida</div>
                            <div class="font-bold text-gray-900 dark:text-neutral-100 text-[13px]">
                                {{ $verFirst->fecha_prestamo?->format('d/m/Y') ?? '—' }}</div>
                        </div>
                        <div class="px-4 py-2">
                            <div
                                class="text-[9px] uppercase font-black text-gray-400 dark:text-neutral-500 tracking-wider mb-0.5">
                                Retorno Estimado</div>
                            <div
                                class="font-bold text-[13px] {{ $isVencido ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-neutral-100' }}">
                                {{ $verFirst->fecha_vencimiento ? $verFirst->fecha_vencimiento->format('d/m/Y') : 'Abierto' }}
                            </div>
                        </div>

                        {{-- Fotos de salida (si existen) --}}
                        @if (!empty($fotosSalidaUrls))
                            <div class="px-4 py-2 col-span-2 sm:col-span-1">
                                <div
                                    class="text-[9px] uppercase font-black text-gray-400 dark:text-neutral-500 tracking-wider mb-1.5">
                                    Fotos de Salida ({{ count($fotosSalidaUrls) }})
                                </div>
                                <div class="flex gap-1.5 flex-wrap">
                                    @foreach ($verFirst->fotos_salida as $foto)
                                        @php
                                            $url = Storage::url($foto);
                                            $isPdf = str_ends_with(strtolower($foto), '.pdf');
                                        @endphp
                                        @if ($isPdf)
                                            <a href="{{ $url }}" target="_blank" title="Ver PDF"
                                                class="size-10 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 flex flex-col items-center justify-center text-red-500 hover:bg-red-100 transition cursor-pointer shrink-0">
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                                <span class="text-[7px] font-black mt-0.5">PDF</span>
                                            </a>
                                        @else
                                            <img src="{{ $url }}" alt="Salida"
                                                class="size-10 rounded-lg object-cover border border-indigo-200 dark:border-indigo-800 shadow-sm cursor-pointer hover:opacity-80 transition shrink-0"
                                                @click="$dispatch('open-viewer', { title: '{{ $verNroPrestamo }} — Salida', photos: {{ json_encode($fotosSalidaUrls) }} })">
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Firma de Salida --}}
                        @if ($verFirst->firma_salida)
                            <div class="px-4 py-2 col-span-2 sm:col-span-1">
                                <div
                                    class="text-[9px] uppercase font-black text-gray-400 dark:text-neutral-500 tracking-wider mb-1">
                                    Firma de Salida
                                </div>
                                <div
                                    class="bg-white dark:bg-neutral-900 rounded-lg border border-gray-100 dark:border-neutral-700/50 p-1 w-fit">
                                    <img src="{{ $verFirst->firma_salida }}"
                                        class="h-12 object-contain dark:invert transition-all" alt="Firma Salida">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- SECCIÓN 2 — HERRAMIENTAS PRESTADAS                --}}
                {{-- ══════════════════════════════════════════════════ --}}
                <div>
                    {{-- Encabezado de sección --}}
                    <div class="flex items-center gap-2 mb-2">
                        <div
                            class="size-5 rounded-md bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                            <svg class="size-3 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                            </svg>
                        </div>
                        <span
                            class="text-[11px] uppercase font-black text-gray-500 dark:text-neutral-400 tracking-wider">Herramientas
                            Prestadas</span>
                        <span class="text-[10px] text-gray-400 dark:text-neutral-600">{{ $verPrestamos->count() }}
                            ítem(s) · {{ $totalPrestadas }} u.</span>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
                        <table class="w-full text-sm">
                            <thead
                                class="bg-gray-50 dark:bg-neutral-800/80 text-[9px] uppercase tracking-wider text-gray-400 dark:text-neutral-500">
                                <tr>
                                    <th class="px-4 py-2.5 text-left font-black">Herramienta</th>
                                    <th class="px-3 py-2.5 text-center font-black w-20">Prestado</th>
                                    <th class="px-3 py-2.5 text-center font-black w-20">Devuelto</th>
                                    <th class="px-3 py-2.5 text-center font-black w-20">Pendiente</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                                @foreach ($verPrestamos as $vp)
                                    @php
                                        $pendiente = $vp->cantidad_prestada - $vp->cantidad_devuelta;
                                        $isDestacado =
                                            $verDestacadoHerramientaId &&
                                            $vp->herramienta_id == $verDestacadoHerramientaId;
                                        $pct =
                                            $vp->cantidad_prestada > 0
                                                ? round(($vp->cantidad_devuelta / $vp->cantidad_prestada) * 100)
                                                : 0;
                                    @endphp
                                    <tr @if ($isDestacado) data-destacado @endif
                                        class="{{ $isDestacado ? 'bg-amber-50 dark:bg-amber-900/20 ring-2 ring-inset ring-amber-300 dark:ring-amber-700' : 'hover:bg-gray-50/60 dark:hover:bg-neutral-800/30' }} transition">
                                        <td class="px-4 py-1.5">
                                            <div class="flex items-center gap-3">
                                                {{-- Imagen --}}
                                                @if ($vp->herramienta?->imagen)
                                                    <img src="{{ Storage::url($vp->herramienta->imagen) }}"
                                                        class="size-9 rounded-lg object-cover border border-gray-200 dark:border-neutral-700 shrink-0">
                                                @else
                                                    <div
                                                        class="size-9 rounded-lg bg-gray-100 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 flex items-center justify-center shrink-0">
                                                        <svg class="size-4 text-gray-300 dark:text-neutral-600"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                                                        </svg>
                                                    </div>
                                                @endif

                                                {{-- Info --}}
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span
                                                            class="font-bold text-gray-900 dark:text-neutral-100 text-[13px]">
                                                            {{ $vp->herramienta?->nombre ?? 'Herramienta Eliminada' }}
                                                        </span>
                                                        @if ($isDestacado)
                                                            <span
                                                                class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-amber-200 dark:bg-amber-700/50 text-amber-800 dark:text-amber-300 text-[9px] font-black uppercase ring-1 ring-amber-300 dark:ring-amber-600 shrink-0">
                                                                Dada de baja
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div
                                                        class="font-mono text-[10px] text-indigo-500 dark:text-indigo-400 mt-0.5">
                                                        {{ $vp->herramienta?->codigo ?? '—' }}
                                                    </div>
                                                    {{-- Mini barra de progreso por fila --}}
                                                    <div
                                                        class="mt-1.5 h-1 w-20 rounded-full bg-gray-100 dark:bg-neutral-700 overflow-hidden">
                                                        <div class="h-full rounded-full {{ $pendiente == 0 ? 'bg-emerald-500' : ($isVencido ? 'bg-red-400' : 'bg-blue-400') }}"
                                                            style="width: {{ $pct }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-1.5 text-center">
                                            <span
                                                class="inline-flex items-center justify-center w-8 h-7 rounded-lg bg-gray-100 dark:bg-neutral-800 font-black text-gray-700 dark:text-neutral-300 text-sm">
                                                {{ $vp->cantidad_prestada }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-1.5 text-center">
                                            <span
                                                class="inline-flex items-center justify-center w-8 h-7 rounded-lg font-black text-sm {{ $vp->cantidad_devuelta > 0 ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400' : 'bg-gray-50 dark:bg-neutral-800/50 text-gray-300 dark:text-neutral-700' }}">
                                                {{ $vp->cantidad_devuelta }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-1.5 text-center">
                                            @if ($pendiente > 0)
                                                <span
                                                    class="inline-flex items-center justify-center w-8 h-7 rounded-lg bg-amber-50 dark:bg-amber-900/20 font-black text-amber-700 dark:text-amber-400 text-sm">
                                                    {{ $pendiente }}
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center justify-center size-7 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="3" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- SECCIÓN 3 — RECEPCIONES REGISTRADAS               --}}
                {{-- ══════════════════════════════════════════════════ --}}
                @if (!empty($receptores))
                    <div>
                        {{-- Encabezado de sección --}}
                        <div class="flex items-center gap-2 mb-3">
                            <div
                                class="size-5 rounded-md bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                <svg class="size-3 text-emerald-600 dark:text-emerald-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
                                </svg>
                            </div>
                            <span
                                class="text-[11px] uppercase font-black text-gray-500 dark:text-neutral-400 tracking-wider">Recepciones
                                Registradas</span>
                            <span class="text-[10px] text-gray-400 dark:text-neutral-600">{{ count($receptores) }}
                                sesión(es) de retorno</span>
                        </div>

                        <div class="space-y-4">
                            @foreach ($receptores as $recepcion)
                                <div
                                    class="overflow-hidden rounded-2xl border border-gray-100 dark:border-neutral-700/50 bg-white/50 dark:bg-neutral-800/20 shadow-sm transition hover:shadow-md">
                                    {{-- Cabecera del Retorno --}}
                                    <div
                                        class="flex items-center justify-between gap-4 border-b border-gray-100 dark:border-neutral-700/50 bg-gray-50/50 dark:bg-neutral-800/40 px-4 py-2.5">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="flex h-5 items-center rounded-full bg-emerald-500 px-2.5 text-[9px] font-black uppercase tracking-wider text-white">
                                                Retorno {{ $recepcion['nro'] }}
                                            </span>
                                            <div
                                                class="flex items-center gap-1.5 text-[11px] font-bold text-gray-600 dark:text-neutral-300">
                                                <svg class="size-3 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ $recepcion['fecha_devolucion']->format('d/m/Y') }}
                                            </div>
                                            @if ($recepcion['observaciones'])
                                                <span class="text-gray-300 dark:text-neutral-700">·</span>
                                                <div class="flex items-center gap-1 max-w-[200px] sm:max-w-xs">
                                                    <svg class="size-3 text-gray-400 shrink-0" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                    </svg>
                                                    <span
                                                        class="truncate text-[11px] italic text-gray-500 dark:text-neutral-400">
                                                        "{{ $recepcion['observaciones'] }}"
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Fotos de la Sesión --}}
                                        @if (!empty($recepcion['fotos']))
                                            @php
                                                $urls = array_map(fn($f) => Storage::url($f), $recepcion['fotos']);
                                                $fotosCount = count($urls);
                                            @endphp
                                            <div class="flex items-center space-x-3">
                                                @foreach (array_slice($urls, 0, 1) as $idx => $url)
                                                    <div class="relative transition-transform hover:z-10 hover:scale-110"
                                                        style="z-index: {{ 10 - $idx }};">
                                                        <img src="{{ $url }}"
                                                            class="size-8 rounded-full border-2 border-white object-cover shadow-sm dark:border-neutral-800 cursor-pointer"
                                                            @click="$dispatch('open-viewer', { title: 'Fotos Retorno {{ $recepcion['nro'] }}', photos: {{ json_encode($urls) }} })">
                                                    </div>
                                                @endforeach
                                                @if ($fotosCount > 3)
                                                    <div
                                                        class="relative z-0 flex size-8 items-center justify-center rounded-full border-2 border-white bg-emerald-100 text-[10px] font-black text-emerald-700 dark:border-neutral-800 dark:bg-emerald-900/50 dark:text-emerald-400">
                                                        +{{ $fotosCount - 3 }}
                                                    </div>
                                                @endif
                                        @endif
                                    </div>

                                    {{-- Firma de la Recepción --}}
                                    @if (!empty($recepcion['firma']))
                                        <div
                                            class="shrink-0 bg-white dark:bg-neutral-900 rounded-lg border border-gray-100 dark:border-neutral-700/50 p-1 ml-auto mr-2">
                                            <img src="{{ $recepcion['firma'] }}"
                                                class="h-8 object-contain dark:invert transition-all"
                                                alt="Firma Recepción">
                                        </div>
                                    @endif
                                </div>

                                {{-- Items del Retorno --}}
                                <div class="divide-y divide-gray-50 dark:divide-neutral-700/30">
                                    @foreach ($recepcion['items'] as $dev)
                                        <div
                                            class="flex items-center gap-3 px-4 py-1.5 transition hover:bg-gray-50/30 dark:hover:bg-neutral-800/10">
                                            {{-- Imagen Herramienta --}}
                                            @if ($dev->_herramienta_imagen)
                                                <img src="{{ Storage::url($dev->_herramienta_imagen) }}"
                                                    class="size-10 rounded-xl border border-gray-100 object-cover dark:border-neutral-700">
                                            @else
                                                <div
                                                    class="flex size-10 items-center justify-center rounded-xl bg-gray-50 dark:bg-neutral-800">
                                                    <svg class="size-4 text-gray-300" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                                                    </svg>
                                                </div>
                                            @endif

                                            <div class="flex-1 min-w-0">
                                                <div class="text-[13px] font-bold text-gray-900 dark:text-neutral-100">
                                                    {{ $dev->_herramienta_nombre }}
                                                </div>
                                                @if ($dev->estado_fisico)
                                                    @php
                                                        $efBadge = match($dev->estado_fisico) {
                                                            'bueno'   => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
                                                            'regular' => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                                                            'malo'    => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
                                                            default   => 'bg-gray-50 text-gray-500 border-gray-200',
                                                        };
                                                        $efLabel = match($dev->estado_fisico) {
                                                            'bueno'   => 'Bueno',
                                                            'regular' => 'Regular',
                                                            'malo'    => 'Malo',
                                                            default   => ucfirst($dev->estado_fisico),
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded border text-[9px] font-black uppercase {{ $efBadge }} mt-0.5">
                                                        {{ $efLabel }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div
                                                class="flex h-7 items-center rounded-lg bg-emerald-50 px-2.5 text-[11px] font-black text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                                +{{ $dev->cantidad_devuelta }} u.
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                        </div>
                @endforeach
            </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════ --}}
        {{-- SECCIÓN 4 — BAJAS / PÉRDIDAS                      --}}
        {{-- ══════════════════════════════════════════════════ --}}
        @if ($verBajas && $verBajas->isNotEmpty())
            <div>
                {{-- Encabezado de sección --}}
                <div class="flex items-center gap-2 mb-2">
                    <div
                        class="size-5 rounded-md bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                        <svg class="size-3 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <span class="text-[11px] uppercase font-black text-red-500 dark:text-red-400 tracking-wider">Bajas
                        / Pérdidas</span>
                    <span class="text-[10px] text-gray-400 dark:text-neutral-600">{{ $verBajas->count() }}
                        registro(s)</span>
                </div>

                <div class="space-y-2">
                    @foreach ($verBajas as $baja)
                        <div
                            class="flex items-start gap-3 rounded-xl border border-red-100 dark:border-red-900/30 bg-red-50/40 dark:bg-red-900/10 px-4 py-2">
                            {{-- Icono baja --}}
                            <div
                                class="size-9 rounded-lg bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800/50 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="size-4 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>

                            {{-- Datos --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-gray-900 dark:text-neutral-100 text-[13px]">
                                        {{ $baja->herramienta?->nombre ?? 'Herramienta Eliminada' }}
                                    </span>
                                    <span
                                        class="inline-flex items-center px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400 text-[10px] font-black">
                                        -{{ $baja->cantidad }} u.
                                    </span>
                                </div>
                                @if ($baja->observaciones)
                                    <div class="text-[11px] text-gray-600 dark:text-neutral-400 mt-0.5">
                                        {{ $baja->observaciones }}
                                    </div>
                                @endif
                                <div class="text-[10px] text-gray-400 dark:text-neutral-600 mt-0.5">
                                    {{ $baja->created_at->format('d/m/Y H:i') }} ·
                                    {{ $baja->user?->name ?? 'Sistema' }}
                                </div>
                            </div>

                            {{-- Evidencia foto --}}
                            @if ($baja->imagen)
                                <img src="{{ Storage::url($baja->imagen) }}" alt="Evidencia baja"
                                    class="size-12 rounded-lg object-cover border-2 border-red-200 dark:border-red-800 cursor-pointer hover:opacity-80 transition shrink-0"
                                    @click="$dispatch('open-viewer', { title: 'Evidencia de Baja', photos: ['{{ Storage::url($baja->imagen) }}'] })">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Espaciado inferior --}}
        <div class="h-1"></div>

        </div>
    @else
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="w-14 h-14 bg-gray-50 dark:bg-neutral-800 rounded-full flex items-center justify-center mb-3">
                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </div>
            <p class="text-sm text-gray-400">No se encontraron datos para este préstamo.</p>
        </div>
        @endif

        @slot('footer')
            <div class="w-full flex justify-between items-center gap-3">
                @if ($verNroPrestamo)
                    <button type="button" wire:click="exportPdf('{{ $verNroPrestamo }}')" wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-lg border border-gray-200 dark:border-neutral-700 text-gray-500 dark:text-neutral-400 hover:text-red-500 hover:border-red-200 dark:hover:border-red-800 transition text-sm font-bold flex items-center gap-2 cursor-pointer">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span wire:loading.remove wire:target="exportPdf">Exportar PDF</span>
                        <span wire:loading wire:target="exportPdf">Generando...</span>
                    </button>
                @else
                    <div></div>
                @endif
                <button type="button" wire:click="closeVer"
                    class="px-5 py-2 rounded-lg border cursor-pointer border-gray-300 dark:border-neutral-700 text-gray-600 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-neutral-800 text-sm font-bold transition">
                    Cerrar
                </button>
            </div>
        @endslot
    </x-ui.modal>
