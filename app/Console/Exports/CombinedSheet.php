<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CombinedSheet implements WithMultipleSheets
{

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    public function sheets(): array
    {
        return [
             new AdrExport($this->data),
             new AdrExportNet($this->data),
             new OccupancyExport($this->data),
        ];
    }
}
