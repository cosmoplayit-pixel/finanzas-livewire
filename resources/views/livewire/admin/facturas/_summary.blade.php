{{-- RESUMEN TOTALES (RESPETA FILTROS) --}}
<div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full table-fixed text-sm min-w-[980px]">
            <thead
                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-200">
                <tr class="text-left">
                    <th class="p-2 w-[25%] whitespace-nowrap">Monto total facturado</th>
                    <th class="p-2 w-[25%] whitespace-nowrap">Monto total pagado</th>
                    <th class="p-2 w-[25%] whitespace-nowrap text-center">Saldo total</th>
                    <th class="p-2 w-[25%] whitespace-nowrap text-center">Retención pendiente total</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                <tr class="text-gray-700 dark:text-neutral-200">
                    <td class="p-2 font-semibold">
                        Bs {{ number_format((float) ($totales['facturado'] ?? 0), 2, ',', '.') }}
                    </td>

                    <td class="p-2 font-semibold">
                        Bs {{ number_format((float) ($totales['pagado_total'] ?? 0), 2, ',', '.') }}
                    </td>

                    <td class="p-2 text-center font-semibold">
                        Bs {{ number_format((float) ($totales['saldo'] ?? 0), 2, ',', '.') }}
                    </td>

                    <td class="p-2 text-center font-semibold">
                        Bs {{ number_format((float) ($totales['retencion_pendiente'] ?? 0), 2, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
