<?php

namespace App\Exports;

use App\Models\Pendapatan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PendapatanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $kategori;
    protected $bulan_tahun;
    protected int $no = 0;

    public function __construct($kategori = null, $bulan_tahun = null)
    {
        $this->kategori    = $kategori;
        $this->bulan_tahun = $bulan_tahun;
    }

    public function collection()
    {
        $query = Pendapatan::with(['usaha.user']);

        if ($this->kategori) {
            $query->whereHas('usaha', fn($q) => $q->where('kategori', $this->kategori));
        }

        if ($this->bulan_tahun) {
            $query->where('bulan_tahun', $this->bulan_tahun);
        }

        return $query->orderBy('bulan_tahun', 'desc')->get();
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
            'Bulan',
            'Jenis Input',
            'Jumlah (Rp)',
            'Catatan',
            'Tanggal Input',
        ];
    }

    public function map($row): array
    {
        $no = ++$this->no;

        $kategori = $row->usaha->kategori ?? '-';
        $kategoriLabel = match($kategori) {
            'industri'    => 'Industri / Manufaktur',
            'perdagangan' => 'Perdagangan',
            'jasa'        => 'Jasa',
            default       => ucfirst($kategori),
        };

        return [
            $no,
            $row->usaha->nama_usaha ?? '-',
            $row->usaha->kode_kategori ?? '-',
            $kategoriLabel,
            $row->usaha->user->name ?? '-',
            $row->usaha->user->email ?? '-',
            $row->formatted_bulan ?? $row->bulan_tahun,
            ucfirst($row->jenis_input ?? '-'),
            $row->jumlah,
            $row->catatan_laporan ?? '-',
            $row->created_at ? $row->created_at->format('d M Y, H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
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
        return 'Data Pendapatan';
    }
}