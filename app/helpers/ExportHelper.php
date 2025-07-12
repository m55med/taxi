<?php

namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ExportHelper
{
    /**
     * Exports an array of data to an Excel file.
     *
     * @param array $data The data to export. Must contain a 'headers' key and a 'rows' key.
     * @param string $filename The desired filename for the downloaded file (without extension).
     */
    public static function exportToExcel(array $data, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->fromArray($data['headers'], null, 'A1');

        // Style the header row
        $headerStyle = $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFont()->setSize(12);
        
        // Set data rows
        $sheet->fromArray($data['rows'], null, 'A2');

        // Auto-size columns for better readability
        foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Exports an array of data to a JSON file.
     *
     * @param array $data The data to export.
     * @param string $filename The desired filename for the downloaded file (without extension).
     */
    public static function exportToJson(array $data, string $filename)
    {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment;filename="' . $filename . '_' . date('Y-m-d') . '.json"');
        header('Cache-Control: max-age=0');

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
} 