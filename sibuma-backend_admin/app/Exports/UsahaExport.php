<?php

namespace App\Exports;

use App\Models\Usaha;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class UsahaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected int $no = 0;

    public function collection()
    {
        return Usaha::with('user')->orderBy('kategori')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Usaha',
            'Kode',
            'Kategori',
            'Pemilik',
            'Email',
            'Label Pendapatan',
            'Terdaftar Sejak',
        ];
    }

    public function map($row): array
    {
        $no = ++$this->no;

        $kategori = $row->kategori ?? '-';
        $kategoriLabel = match($kategori) {
            'industri'    => 'Industri / Manufaktur',
            'perdagangan' => 'Perdagangan',
            'jasa'        => 'Jasa',
            default       => ucfirst($kategori),
        };

        return [
            $no,
            $row->nama_usaha,
            $row->kode_kategori ?? '-',
            $kategoriLabel,
            $row->user->name ?? '-',
            $row->user->email ?? '-',
            ucfirst($row->label_pendapatan ?? $row->jenis_pendapatan ?? '-'),
            $row->created_at->format('d M Y'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Data Usaha';
    }
}