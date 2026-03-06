{{-- RESUMEN TOTALES (RESPETA FILTROS) --}}
@php
    $isBoth = $moneda === 'all';
    $valClassBase = $isBoth ? 'text-lg' : 'text-2xl';
@endphp
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    {{-- Capital Privado --}}
    <div
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700 flex flex-col">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <svg class="w-12 h-12 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-neutral-400 mb-3">Capital Privado</p>

        <div class="mt-auto flex flex-col gap-2 relative z-10">
            @if ($isBoth || $moneda === 'BOB')
                <div
                    class="flex items-center justify-between {{ $isBoth ? 'pb-2 border-b border-gray-100 dark:border-neutral-700/50' : '' }}">
                    <span
                        class="text-[11px] font-semibold text-gray-500 dark:text-neutral-400 bg-gray-100 dark:bg-neutral-700/50 px-1.5 py-0.5 rounded">BOB</span>
                    <span class="{{ $valClassBase }} font-bold text-gray-900 dark:text-white tabular-nums">
                        {{ number_format((float) ($totales['privado_bob'] ?? 0), 2, ',', '.') }}
                    </span>
                </div>
            @endif
            @if ($isBoth || $moneda === 'USD')
                <div class="flex items-center justify-between">
                    <span
                        class="text-[11px] font-semibold text-gray-500 dark:text-neutral-400 bg-gray-100 dark:bg-neutral-700/50 px-1.5 py-0.5 rounded">USD</span>
                    <span class="{{ $valClassBase }} font-bold text-gray-900 dark:text-white tabular-nums">
                        {{ number_format((float) ($totales['privado_usd'] ?? 0), 2, ',', '.') }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- Capital Banco --}}
    <div
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700 flex flex-col">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <svg class="w-12 h-12 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-neutral-400 mb-3">Capital Banco</p>

        <div class="mt-auto flex flex-col gap-2 relative z-10">
            @if ($isBoth || $moneda === 'BOB')
                <div
                    class="flex items-center justify-between {{ $isBoth ? 'pb-2 border-b border-gray-100 dark:border-neutral-700/50' : '' }}">
                    <span
                        class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-1.5 py-0.5 rounded">BOB</span>
                    <span class="{{ $valClassBase }} font-bold text-amber-600 dark:text-amber-400 tabular-nums">
                        {{ number_format((float) ($totales['banco_bob'] ?? 0), 2, ',', '.') }}
                    </span>
                </div>
            @endif
            @if ($isBoth || $moneda === 'USD')
                <div class="flex items-center justify-between">
                    <span
                        class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-1.5 py-0.5 rounded">USD</span>
                    <span class="{{ $valClassBase }} font-bold text-amber-600 dark:text-amber-400 tabular-nums">
                        {{ number_format((float) ($totales['banco_usd'] ?? 0), 2, ',', '.') }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- Pagos Pendientes --}}
    <div
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700 flex flex-col">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <svg class="w-12 h-12 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-neutral-400 mb-3"
            title="Pagos que se deben hasta la fecha de hoy">Pagos Pendientes - {{ date('d/M') }}</p>

        <div class="mt-auto flex flex-col gap-2 relative z-10">
            @if ($isBoth || $moneda === 'BOB')
                <div
                    class="flex items-center justify-between {{ $isBoth ? 'pb-2 border-b border-gray-100 dark:border-neutral-700/50' : '' }}">
                    <span
                        class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10 px-1.5 py-0.5 rounded">BOB</span>
                    <span class="{{ $valClassBase }} font-bold text-rose-600 dark:text-rose-400 tabular-nums"
                        title="Pagos que se deben hasta la fecha de hoy">
                        {{ number_format((float) ($totales['pendiente_bob'] ?? 0), 2, ',', '.') }}
                    </span>
                </div>
            @endif
            @if ($isBoth || $moneda === 'USD')
                <div class="flex items-center justify-between">
                    <span
                        class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10 px-1.5 py-0.5 rounded">USD</span>
                    <span class="{{ $valClassBase }} font-bold text-rose-600 dark:text-rose-400 tabular-nums"
                        title="Pagos que se deben hasta la fecha de hoy">
                        {{ number_format((float) ($totales['pendiente_usd'] ?? 0), 2, ',', '.') }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- Total General --}}
    <div
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700 flex flex-col">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <svg class="w-12 h-12 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                </path>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-neutral-400 mb-3">Total General</p>

        <div class="mt-auto flex flex-col gap-2 relative z-10">
            @if ($isBoth || $moneda === 'BOB')
                <div
                    class="flex items-center justify-between {{ $isBoth ? 'pb-2 border-b border-gray-100 dark:border-neutral-700/50' : '' }}">
                    <span
                        class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-1.5 py-0.5 rounded">BOB</span>
                    <span class="{{ $valClassBase }} font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                        {{ number_format((float) ($totales['total_general_bob'] ?? 0), 2, ',', '.') }}
                    </span>
                </div>
            @endif
            @if ($isBoth || $moneda === 'USD')
                <div class="flex items-center justify-between">
                    <span
                        class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-1.5 py-0.5 rounded">USD</span>
                    <span class="{{ $valClassBase }} font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                        {{ number_format((float) ($totales['total_general_usd'] ?? 0), 2, ',', '.') }}
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>
