<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\DB;

class PenawaranHargaExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $barangs;
    protected $perusahaan;
    protected $catalogTitle;
    protected $showStock;

    public function __construct($barangs, $perusahaan, $catalogTitle, $showStock)
    {
        $this->barangs = $barangs;
        $this->perusahaan = $perusahaan;
        $this->catalogTitle = $catalogTitle;
        $this->showStock = $showStock;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = collect();
        
        foreach ($this->barangs as $index => $barang) {
            $row = [
                $index + 1,
                $barang->kode_barang,
                $barang->name,
                $barang->merek ?? '-',
                $barang->ukuran ?? '-',
                $barang->unit_dasar ?? '-',
                $barang->harga_jual,
            ];

            if ($this->showStock) {
                $stock = DB::table('stocks')
                    ->where('kode_barang', $barang->kode_barang)
                    ->sum('good_stock');
                $row[] = $stock;
            }

            $data->push($row);
        }

        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headings = [
            'No',
            'Kode Barang',
            'Nama Barang',
            'Merek',
            'Ukuran/Type',
            'Satuan',
            'Harga Jual',
        ];

        if ($this->showStock) {
            $headings[] = 'Stok';
        }

        return $headings;
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row (after title rows)
            5 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2C3E50'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,   // No
            'B' => 18,  // Kode Barang
            'C' => 35,  // Nama Barang
            'D' => 15,  // Merek
            'E' => 15,  // Ukuran
            'F' => 10,  // Satuan
            'G' => 18,  // Harga Jual
        ];

        if ($this->showStock) {
            $widths['H'] = 12; // Stok
        }

        return $widths;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Penawaran Harga';
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Insert company info and title at the top
                $sheet->insertNewRowBefore(1, 4);
                
                // Company name (row 1)
                $sheet->setCellValue('A1', $this->perusahaan->nama_perusahaan ?? 'NAMA PERUSAHAAN');
                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '2C3E50'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                
                // Company address (row 2)
                if ($this->perusahaan && $this->perusahaan->alamat) {
                    $sheet->setCellValue('A2', $this->perusahaan->alamat);
                    $sheet->mergeCells('A2:G2');
                    $sheet->getStyle('A2')->applyFromArray([
                        'font' => ['size' => 10],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }
                
                // Catalog title (row 3)
                $sheet->setCellValue('A3', $this->catalogTitle);
                $sheet->mergeCells('A3:G3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => '2C3E50'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                
                // Date (row 4)
                $sheet->setCellValue('A4', 'Tanggal: ' . date('d/m/Y'));
                $sheet->mergeCells('A4:G4');
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['size' => 9],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                // Get the highest row
                $highestRow = $sheet->getHighestRow();
                $lastColumn = $this->showStock ? 'H' : 'G';
                
                // Apply borders to data area
                $sheet->getStyle('A5:' . $lastColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);
                
                // Center align specific columns
                $sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F6:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Right align price column
                $sheet->getStyle('G6:G' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('G6:G' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
                
                // Center align stock column if exists
                if ($this->showStock) {
                    $sheet->getStyle('H6:H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('H6:H' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
                }
                
                // Zebra striping for rows
                for ($i = 6; $i <= $highestRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle('A' . $i . ':' . $lastColumn . $i)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F9F9F9'],
                            ],
                        ]);
                    }
                }
                
                // Auto-size rows
                foreach (range(1, $highestRow) as $row) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }
            },
        ];
    }
}

