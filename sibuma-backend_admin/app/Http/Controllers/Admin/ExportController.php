<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usaha;
use App\Models\Pendapatan;
use Illuminate\Http\Request;
use App\Exports\PendapatanExport;
use App\Exports\UsahaExport;
use App\Exports\AllDataExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * Export pendapatan ke Excel (dengan filter kategori & bulan)
     */
    public function exportPendapatan(Request $request)
    {
        $kategori   = $request->get('kategori');
        $bulan      = $request->get('bulan'); // format: Y-m dari input type="month"

        $filename = 'pendapatan_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new PendapatanExport($kategori, $bulan), $filename);
    }

    /**
     * Export data usaha ke Excel
     */
    public function exportUsaha()
    {
        $filename = 'data_usaha_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new UsahaExport(), $filename);
    }

    /**
     * Export semua data (pendapatan + usaha) - untuk tombol Export di dashboard
     */
    public function exportAll()
    {
        $filename = 'sibuma_semua_data_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new AllDataExport(), $filename);
    }
}