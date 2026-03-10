<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransaccionesExport implements FromQuery, WithColumnFormatting, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;

    protected $query;

    protected $rowNumber = 0;

    protected $totales;

    public function __construct($query)
    {
        $this->query = $query;

        // Calculate totals for the summary section
        $totalesQuery = clone $query;
        $this->totales = DB::query()->fromSub($totalesQuery, 't')->select(
            DB::raw("SUM(CASE WHEN t.moneda = 'BOB' AND t.tipo_movimiento = 'INGRESO' THEN t.monto ELSE 0 END) as ingresos_bob"),
            DB::raw("SUM(CASE WHEN t.moneda = 'USD' AND t.tipo_movimiento = 'INGRESO' THEN t.monto ELSE 0 END) as ingresos_usd"),
            DB::raw("SUM(CASE WHEN t.moneda = 'BOB' AND t.tipo_movimiento = 'EGRESO' THEN t.monto ELSE 0 END) as egresos_bob"),
            DB::raw("SUM(CASE WHEN t.moneda = 'USD' AND t.tipo_movimiento = 'EGRESO' THEN t.monto ELSE 0 END) as egresos_usd")
        )->first();
    }

    public function title(): string
    {
        return 'Transacciones';
    }

    public function query()
    {
        return $this->query
            ->leftJoin('bancos as b', 't.banco_id', '=', 'b.id')
            ->select('t.*', 'b.nombre as banco_nombre', 'b.numero_cuenta')
            ->orderByDesc('t.fecha')
            ->orderByDesc('t.created_at');
    }

    public function headings(): array
    {
        return [
            ['REPORTE CONSOLIDADO DE TRANSACCIONES'],
            ['Fecha de Generación: '.now()->format('d/m/Y H:i')],
            [''], // Spacer
            [
                '#',
                'FECHA REGISTRO',
                'FECHA PAGO',
                'MÓDULO',
                'BANCO',
                'NRO CUENTA',
                'CONCEPTO',
                'REFERENCIA',
                'MONTO',
                'MONEDA',
            ],
        ];
    }

    public function map($transaccion): array
    {
        $montoFirmado = ($transaccion->tipo_movimiento === 'INGRESO')
            ? $transaccion->monto
            : -$transaccion->monto;

        $this->rowNumber++;

        return [
            $this->rowNumber,
            $transaccion->created_at,
            $transaccion->fecha,
            $transaccion->modulo,
            $transaccion->banco_nombre ?? 'N/A',
            $transaccion->numero_cuenta ?? 'N/A',
            $transaccion->concepto,
            $transaccion->referencia,
            $montoFirmado,
            $transaccion->moneda,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // #
            'B' => 20,  // Fecha Registro
            'C' => 14,  // Fecha Pago
            'D' => 18,  // Módulo
            'E' => 18,  // Banco
            'F' => 18,  // Nro Cuenta
            'G' => 45,  // Concepto
            'H' => 30,  // Referencia
            'I' => 15,  // Monto
            'J' => 10,  // Moneda
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_DATE_DATETIME,
            'C' => NumberFormat::FORMAT_DATE_YYYYMMDD,
            'I' => '#,##0.00',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => '312E81']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['italic' => true, 'size' => 11, 'color' => ['rgb' => '6B7280']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lastRow = $sheet->getHighestRow();
                $lastCol = 'J';
                $startTable = 4;

                // Page Setup
                $sheet->getDelegate()->getPageSetup()->setFitToWidth(1);
                $sheet->getDelegate()->getPageSetup()->setFitToHeight(0);
                $sheet->getDelegate()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                // Footer
                $sheet->getDelegate()->getHeaderFooter()->setOddFooter('&L&D &T &R Página &P de &N');

                // Freeze Panes
                $sheet->getDelegate()->freezePane('A5');

                // Data Range styling
                $dataRange = "A5:{$lastCol}{$lastRow}";
                $sheet->getStyle($dataRange)->getAlignment()->setWrapText(true);
                $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Borders
                $sheet->getStyle("A{$startTable}:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);

                // Zebra striping
                for ($row = 5; $row <= $lastRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()->applyFromArray([
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F9FAFB'],
                        ]);
                    }
                }

                // Summary Section (2 Columns: I and J)
                $currentRow = $lastRow + 2;

                $addSummaryLine = function ($label, $value, $isDark = false) use ($sheet, &$currentRow) {
                    $sheet->setCellValue("I{$currentRow}", $label);
                    $sheet->setCellValue("J{$currentRow}", $value);

                    $style = [
                        'font' => ['bold' => $isDark],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $isDark ? '312E81' : 'F3F4F6'],
                        ],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
                        ],
                    ];

                    if ($isDark) {
                        $style['font']['color'] = ['rgb' => 'FFFFFF'];
                    }

                    $sheet->getStyle("I{$currentRow}:J{$currentRow}")->applyFromArray($style);
                    $sheet->getStyle("I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("J{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');

                    $currentRow++;
                };

                // Labels and Values
                $addSummaryLine('TOTAL INGRESOS (BOB)', $this->totales->ingresos_bob ?? 0);
                $addSummaryLine('TOTAL INGRESOS (USD)', $this->totales->ingresos_usd ?? 0);
                $addSummaryLine('TOTAL EGRESOS (BOB)', $this->totales->egresos_bob ?? 0);
                $addSummaryLine('TOTAL EGRESOS (USD)', $this->totales->egresos_usd ?? 0);

                $netoBob = ($this->totales->ingresos_bob ?? 0) - ($this->totales->egresos_bob ?? 0);
                $netoUsd = ($this->totales->ingresos_usd ?? 0) - ($this->totales->egresos_usd ?? 0);

                $addSummaryLine('FLUJO NETO (BOB)', $netoBob, true);
                $addSummaryLine('FLUJO NETO (USD)', $netoUsd, true);

                // Set widths for summary columns
                $sheet->getDelegate()->getColumnDimension('I')->setWidth(30);
                $sheet->getDelegate()->getColumnDimension('J')->setWidth(15);
            },
        ];
    }
}
