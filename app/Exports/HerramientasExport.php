<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
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

class HerramientasExport implements FromQuery, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;

    protected $query;
    protected int $rowNumber = 0;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function title(): string
    {
        return 'Herramientas';
    }

    public function query()
    {
        return $this->query->with('empresa');
    }

    public function headings(): array
    {
        return [
            ['CATÁLOGO DE HERRAMIENTAS'],
            ['Generado: ' . now()->format('d/m/Y H:i')],
            [''],
            [
                '#',
                'CÓDIGO',
                'NOMBRE',
                'MARCA',
                'MODELO',
                'ESTADO FÍSICO',
                'UNIDAD',
                'STOCK TOTAL',
                'DISPONIBLE',
                'PRESTADO',
                'P. UNITARIO (Bs)',
                'P. TOTAL (Bs)',
                'SISTEMA',
                'EMPRESA',
            ],
        ];
    }

    public function map($h): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $h->codigo ?? '—',
            $h->nombre,
            $h->marca ?? '—',
            $h->modelo ?? '—',
            $h->estado_fisico_label,
            $h->unidad ?? '—',
            $h->stock_total,
            $h->stock_disponible,
            $h->stock_prestado,
            (float) $h->precio_unitario,
            (float) $h->precio_total,
            $h->active ? 'Activo' : 'Inactivo',
            $h->empresa?->nombre ?? '—',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 14,
            'C' => 40,
            'D' => 16,
            'E' => 16,
            'F' => 14,
            'G' => 10,
            'H' => 12,
            'I' => 12,
            'J' => 12,
            'K' => 16,
            'L' => 16,
            'M' => 12,
            'N' => 24,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');

        return [
            1 => [
                'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '312E81']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '6B7280']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet;
                $lastRow = $sheet->getHighestRow();

                $sheet->getDelegate()->getPageSetup()->setFitToWidth(1);
                $sheet->getDelegate()->getPageSetup()->setFitToHeight(0);
                $sheet->getDelegate()->getPageSetup()->setOrientation(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                );
                $sheet->getDelegate()->getHeaderFooter()->setOddFooter('&L&D &T &R Página &P de &N');
                $sheet->getDelegate()->freezePane('A5');

                $dataRange = "A5:N{$lastRow}";
                $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("A4:N{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);

                for ($row = 5; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:N{$row}")->getFill()->applyFromArray([
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F9FAFB'],
                        ]);
                    }
                }

                // Numeric columns
                foreach (['H', 'I', 'J'] as $col) {
                    $sheet->getStyle("{$col}5:{$col}{$lastRow}")
                        ->getNumberFormat()->setFormatCode('#,##0');
                }
                foreach (['K', 'L'] as $col) {
                    $sheet->getStyle("{$col}5:{$col}{$lastRow}")
                        ->getNumberFormat()->setFormatCode('#,##0.00');
                }
            },
        ];
    }
}
