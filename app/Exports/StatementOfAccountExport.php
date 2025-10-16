<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromArray;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class StatementOfAccountExport implements FromArray, WithStyles, WithEvents
{
    protected $data;
    protected $firstTableData;
    protected $secondTableData;

    public function __construct(array $data,array $secondTableDat)
    {
        $this->data = $data;
        $this->secondTableData = $secondTableDat;
    }

    public function array(): array
    {
        $separatorRow = array_fill(0, 15, ''); // Empty row as a separator

        return array_merge(
            [
                ['LIVEDIN HOLDINGS LIMITED'],
                [],
                ['Address:', 'Prince Turki Bin Abdul Aziz Al Awal Road, Al Raeed Riyadh 12354, Saudi Arabia','', '', '', '', '', '', '', '', '', '','', 'Phone:','+923452424611' ],
                ['', '','', '', '', '', '', '', '', '', '','', '','Contact Person:','Mr. Ali Raza' ],
                ['', '','', '', '', '', '', '', '', '', '', '','','E-mail:','operations@livedin.co' ],
                ['', '','', '', '', '', '', '', '', '', '', '','','','' ],
                ['Statement #: 161102024-KSA-003', '', '', '', '', '', '', '', '', '', '', '', '', 'Host: Faisal AlBadrani'],
                ['Date: November 16, 2024', '', '', '', '', '', '', '','', '', '', '', '',  '',''],
                ['Host ID: KSA-003', '', '', '', '', '', '', '','', '', '', '', '',  '','', ],
                ['Statement Period: 01-11-2024 To 15-11-2024','', '', '', '', '', '', '', '', '', '','', '', '', '', ],
                ['', '','', '', '', '', '', '', '', '', '', '','','','' ],
                [],
                ['Payment Collected by LIVEDIN'],
                ['CheckInDate', 'Check Out Date', 'No. of Nights', 'Apartment No.', 'Booking Status',
                 'Payment Collected by', 'Booking Source', 'Booking ID', 'Guest Name',
                 'Night Rate', 'Booking Amount (SAR)', 'Discounts (SAR)', 'OTA FEE (SAR)',
                 'Livedin Share (SAR)', 'Host Share (SAR)'],
            ],
            $this->data,
            [$separatorRow], // Blank row to separate tables
            [
                [],
                ['Payment Collected by Host'],
                ['CheckInDate', 'Check Out Date', 'No. of Nights', 'Apartment No.', 'Booking Status',
                 'Payment Collected by', 'Booking Source', 'Booking ID', 'Guest Name',
                 'Night Rate', 'Booking Amount (SAR)', 'Discounts (SAR)', 'OTA FEE (SAR)',
                 'Livedin Share (SAR)', 'Host Share (SAR)'],
            ],
            $this->secondTableData
        );
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // 1 => ['font' => ['bold' => true, 'size' => 20]],
            // 7 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4CAF50']]],
            // 14 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2196F3']]],
        ];
    }

    public function registerEvents(): array
    {

        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $data1 = count($this->data)+12;
                $dataStart2 = $data1 + 2;
                $data2 = count($this->secondTableData)+2+$dataStart2;
                // dd($data1, $data2,$dataStart2);
                // Apply borders to the first table
                $sheet->getStyle('A6:O9')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
                $sheet->getStyle("A11:O{$data1}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // Apply borders to the second table
                $sheet->getStyle("A{$dataStart2}:O{$data2}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // Merge cells for the header
                $sheet->mergeCells('A1:O1');
            },
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('LIVEDIN Logo');
        $drawing->setDescription('LIVEDIN Logo');
        $drawing->setPath(public_path('assets/images/livedinlogoexcel.png')); // Update the path
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');

        return [$drawing];
    }
}
