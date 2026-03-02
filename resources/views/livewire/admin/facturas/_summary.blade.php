{{-- RESUMEN TOTALES (RESPETA FILTROS) --}}
<div class="hidden md:block border rounded bg-white dark:bg-neutral-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead
                class="bg-gray-50 text-gray-700 dark:bg-neutral-900 dark:text-neutral-200 border-b border-gray-200 dark:border-neutral-200">
                <tr class="text-left">
                    <th class="p-3 font-medium tracking-wider whitespace-nowrap">Total facturado</th>
                    <th class="p-3 font-medium tracking-wider whitespace-nowrap">Total pagado</th>
                    <th class="p-3 font-medium tracking-wider text-right whitespace-nowrap">Saldo total</th>
                    <th class="p-3 font-medium tracking-wider text-right whitespace-nowrap">Ret. pendiente</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-200">
                <tr class="text-gray-700 dark:text-neutral-200">
                    <td class="p-3 font-semibold">
                        Bs {{ number_format((float) ($totales['facturado'] ?? 0), 2, ',', '.') }}
                    </td>

                    <td class="p-3 font-semibold">
                        Bs {{ number_format((float) ($totales['pagado_total'] ?? 0), 2, ',', '.') }}
                    </td>

                    <td class="p-3 text-right font-semibold">
                        Bs {{ number_format((float) ($totales['saldo'] ?? 0), 2, ',', '.') }}
                    </td>

                    <td class="p-3 text-right font-semibold">
                        Bs {{ number_format((float) ($totales['retencion_pendiente'] ?? 0), 2, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
