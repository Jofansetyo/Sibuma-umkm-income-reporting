<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllDataExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new PendapatanExport(),
            new UsahaExport(),
        ];
    }
}