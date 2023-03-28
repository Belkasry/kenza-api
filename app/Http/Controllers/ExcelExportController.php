<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelExportController extends Controller implements FromCollection, WithMapping, WithHeadings, WithStyles
{
    private $data;
    private $mapping;
    private $template;

    public function __construct($data, $mapping, $template)
    {
        $this->data = $data;
        $this->mapping = $mapping;
        $this->template = $template;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function map($row): array
    {
        $result = [];

        foreach ($this->mapping as $mappingItem) {
            if ($mappingItem['headerKey'] !== null && array_key_exists($mappingItem['headerKey'], $row)) {
                $result[$mappingItem['templateKey']] = $row[$mappingItem['headerKey']];
            } else {
                $result[$mappingItem['templateKey']] = null; // or provide a default value here
            }
        }

        return $result;
    }

    public function headings(): array
    {
        $labels = [];
        foreach ($this->mapping as $index => $mappingItem) {
            foreach ($this->template as $templateItem) {
                if ($templateItem['key'] == $mappingItem['templateKey']) {
                    $labels[] = $templateItem['label'];
                    break;
                }
            }
        }
        return $labels;
    }


    public function export()
    {
        $filename = 'export.xlsx';
        return Excel::download(new ExcelExportController($this->data, $this->mapping, $this->template), $filename);
    }


    public function styles(Worksheet $sheet)
    {
        // Find the columns that have fige property set to true
        $columnsToColor = [];
        foreach ($this->template as $index => $templateItem) {
            if ($templateItem['fige']) {
                $columnsToColor[] = $index + 1;
            }
        }
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setSize(14);
        foreach(range('A',$sheet->getHighestColumn()) as $columnID){
            $sheet->getColumnDimension($columnID)->setWidth(30);
        }

        foreach ($columnsToColor as $columnIndex) {
            $sheet->getStyleByColumnAndRow($columnIndex, 1, $sheet->getHighestRow())
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FFA07A');
        }


    }

}
