{{-- RESUMEN TOTALES --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
    {{-- Total Facturado --}}
    <div
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <svg class="w-12 h-12 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-neutral-400 mb-1">Total Facturado ({{ $dateLabel }})
        </p>
        <p class="text-xl font-bold text-gray-900 dark:text-white">
            Bs {{ number_format((float) ($totales['facturado'] ?? 0), 2, ',', '.') }}
        </p>
    </div>

    {{-- Total Pagado --}}
    <div
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <svg class="w-12 h-12 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-neutral-400 mb-1">Total Pagado ({{ $dateLabel }})</p>
        <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
            Bs {{ number_format((float) ($totales['pagado_total'] ?? 0), 2, ',', '.') }}
        </p>
    </div>

    {{-- Saldo --}}
    <div
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <svg class="w-12 h-12 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-neutral-400 mb-1">Saldo Total
            ({{ $historicalLabel }})</p>
        <p class="text-xl font-bold text-rose-600 dark:text-rose-400">
            Bs {{ number_format((float) ($totales['saldo'] ?? 0), 2, ',', '.') }}
        </p>
    </div>

    {{-- Retencion Pendiente --}}
    <div
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <svg class="w-12 h-12 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                </path>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-neutral-400 mb-1">Ret. Pendiente
            ({{ $historicalLabel }})</p>
        <p class="text-xl font-bold text-amber-600 dark:text-amber-400">
            Bs {{ number_format((float) ($totales['retencion_pendiente'] ?? 0), 2, ',', '.') }}
        </p>
    </div>

    {{-- Cantidad --}}
    <div
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 relative overflow-hidden group dark:bg-neutral-800 dark:border-neutral-700">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <svg class="w-12 h-12 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                </path>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-neutral-400 mb-1">Facturas Registradas
            ({{ $historicalLabel }})</p>
        <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
            {{ number_format((int) ($totales['cantidad_total'] ?? 0), 0, ',', '.') }}
        </p>
    </div>
</div>
