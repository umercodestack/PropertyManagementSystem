<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OtaCombinedSheet implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    public function sheets(): array
    {
        return [
            new AdrExportAirBnb($this->data),
            new AdrExportBookingCom($this->data),
            new AdrExportVrbo($this->data),
            new AdrExportGathern($this->data),
        ];
    }
}
