<?php

namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use League\Csv\Writer;
use TCPDF;

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
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $filename . '_' . date('Y-m-d') . '.json"');
        header('Cache-Control: max-age=0');

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Exports an array of data to a CSV file.
     *
     * @param array $data The data to export, with 'headers' and 'rows'.
     * @param string $filename The desired filename for the downloaded file.
     */
    public static function exportToCsv(array $data, string $filename)
    {
        $csv = Writer::createFromString('');
        // Add UTF-8 BOM to prevent issues with special characters in some Excel versions
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->insertOne($data['headers']);
        $csv->insertAll($data['rows']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $filename . '_' . date('Y-m-d') . '.csv"');
        header('Cache-Control: max-age=0');

        $csv->output();
        exit;
    }

    /**
     * Exports an array of data to a PDF file.
     *
     * @param array $data The data to export, with 'headers' and 'rows'.
     * @param string $filename The desired filename for the downloaded file.
     */
    public static function exportToPdf(array $data, string $filename)
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Your Application Name');
        $pdf->SetTitle($filename);
        $pdf->SetSubject('Report Data');

        // Set default font
        $pdf->SetFont('dejavusans', '', 10); // Use a font that supports Arabic characters
        
        $pdf->AddPage();
        
        // Create an HTML table
        $html = '<table border="1" cellpadding="4">';
        // Header
        $html .= '<thead><tr style="background-color:#cccccc; font-weight:bold;">';
        foreach ($data['headers'] as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead>';
        
        // Body
        $html .= '<tbody>';
        foreach ($data['rows'] as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $pdf->Output($filename . '_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }
    public static function exportToTxt(array $data, string $filename)
    {
        ob_start();

        // Headers
        foreach ($data['headers'] as $header) {
            echo str_pad($header, 25);
        }
        echo "\n" . str_repeat('=', count($data['headers']) * 25) . "\n";

        // Rows
        foreach ($data['rows'] as $row) {
            foreach ($row as $cell) {
                echo str_pad($cell, 25);
            }
            echo "\n";
        }

        $content = ob_get_clean();

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $filename . '_' . date('Y-m-d') . '.txt"');
        header('Cache-Control: max-age=0');
        
        echo $content;
        exit;
    }
}
