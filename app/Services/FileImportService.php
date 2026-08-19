<?php

namespace App\Services;

use App\Imports\StudentsImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class FileImportService
{
    /**
     * Validate the uploaded file format and structure.
     * Returns ['valid' => bool, 'error' => string|null, 'columns' => array]
     */
    public function validateFile($file): array
    {
        if (!$file instanceof UploadedFile) {
            return ['valid' => false, 'error' => 'Invalid file instance.', 'columns' => []];
        }

        $extension = $file->getClientOriginalExtension();
        if (!in_array(strtolower($extension), ['xlsx', 'xls', 'csv'])) {
            return ['valid' => false, 'error' => 'Invalid file extension. Allowed: xlsx, xls, csv.', 'columns' => []];
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return ['valid' => false, 'error' => 'File size exceeds 10MB limit.', 'columns' => []];
        }

        try {
            $columns = $this->detectColumns($file->getRealPath());
            if (empty($columns)) {
                return ['valid' => false, 'error' => 'File contains no valid headers or is empty.', 'columns' => []];
            }
            
            return ['valid' => true, 'error' => null, 'columns' => $columns];
        } catch (\Exception $e) {
            Log::error('File validation error: ' . $e->getMessage());
            return ['valid' => false, 'error' => 'Could not read file headers.', 'columns' => []];
        }
    }

    /**
     * Detect column headers from the uploaded file.
     */
    public function detectColumns(string $filePath): array
    {
        $headers = [];
        try {
            $studentsImport = new StudentsImport();
            Excel::import($studentsImport, $filePath);
            $headers = $studentsImport->getHeaders();
        } catch (\Exception $e) {
            Log::error('Failed to detect columns: ' . $e->getMessage());
        }
        
        return $headers;
    }

    /**
     * Validate and normalize a phone number.
     * Returns ['valid' => bool, 'number' => string, 'error' => string|null]
     */
    public function validatePhoneNumber(string $number, string $countryCode = '233'): array
    {
        $cleanNumber = preg_replace('/[^\d]/', '', $number);
        
        if (str_starts_with($cleanNumber, '0')) {
            $cleanNumber = substr($cleanNumber, 1);
        }

        if (!str_starts_with($cleanNumber, $countryCode)) {
            $cleanNumber = $countryCode . $cleanNumber;
        }

        $numberWithoutCountryCode = substr($cleanNumber, strlen($countryCode));
        if (strlen($numberWithoutCountryCode) < 9) {
            return [
                'valid' => false,
                'number' => $cleanNumber,
                'error' => 'Phone number is too short.',
            ];
        }

        return [
            'valid' => true,
            'number' => $cleanNumber,
            'error' => null,
        ];
    }

    /**
     * Process and validate all rows from the file.
     * Returns summary with valid/invalid counts and row-level errors.
     */
    public function processRows(array $rows, string $phoneColumn, string $countryCode = '233'): array
    {
        $result = [
            'valid' => [],
            'invalid' => [],
            'summary' => [
                'total' => count($rows),
                'valid_count' => 0,
                'invalid_count' => 0,
            ]
        ];

        foreach ($rows as $index => $row) {
            $phone = $row[$phoneColumn] ?? null;
            if (empty($phone)) {
                $result['invalid'][] = [
                    'phone_number' => $phone ?? '',
                    'data' => $row,
                    'errors' => ["Missing {$phoneColumn} column"]
                ];
                $result['summary']['invalid_count']++;
                continue;
            }

            $validation = $this->validatePhoneNumber((string)$phone, $countryCode);
            if (!$validation['valid']) {
                $result['invalid'][] = [
                    'phone_number' => $phone,
                    'data' => $row,
                    'errors' => [$validation['error']]
                ];
                $result['summary']['invalid_count']++;
                continue;
            }

            $result['valid'][] = [
                'phone_number' => $validation['number'],
                'data' => $row
            ];
            $result['summary']['valid_count']++;
        }

        return $result;
    }
}
