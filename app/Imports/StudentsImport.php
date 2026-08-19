<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToArray, WithHeadingRow
{
    private array $rows = [];
    private array $headers = [];

    public function array(array $rows): void
    {
        $this->rows = $rows;
        if (!empty($rows)) {
            $this->headers = array_keys($rows[0]);
        }
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
