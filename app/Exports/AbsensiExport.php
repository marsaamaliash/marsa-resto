<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AbsensiExport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return array_map(function ($row) {
            return [
                $row['no'] ?? '',
                $row['nama'] ?? '',
                $row['dept'] ?? '',
                $row['tanggal'] ?? '',
                $row['jam_masuk'] ?? '',
                $row['jam_keluar'] ?? '',
            ];
        }, $this->data);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Dept',
            'Tanggal',
            'Jam Masuk',
            'Jam Keluar',
        ];
    }
}
