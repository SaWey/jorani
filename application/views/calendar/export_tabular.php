<?php
/**
 * This view exports a tabular calendar of the leave taken by a group of users
 * It builds a Spreadsheet file downloaded by the browser.
 * @copyright  Copyright (c) 2014-2016 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.4.3
 */

$sheet = $this->excel->setActiveSheetIndex(0);  

//Print the header with the values of the export parameters
$sheet->setTitle(mb_strimwidth(lang('calendar_tabular_export_title'), 0, 28, "..."));  //Maximum 31 characters allowed in sheet title.
$sheet->setCellValue('A1', lang('calendar_tabular_export_param_entity'));
$sheet->setCellValue('A2', lang('calendar_tabular_export_param_month'));
$sheet->setCellValue('A3', lang('calendar_tabular_export_param_year'));
$sheet->setCellValue('A4', lang('calendar_tabular_export_param_children'));
$sheet->getStyle('A1:A4')->getFont()->setBold(true);
$sheet->setCellValue('B1', $this->organization_model->getName($id));
$sheet->setCellValue('B2', $month);
$sheet->setCellValue('B3', $year);
if ($children == TRUE) {
    $sheet->setCellValue('B4', lang('global_true'));
} else {
    $sheet->setCellValue('B4', lang('global_false'));
}

//Print two lines : the short name of all days for the selected month (horizontally aligned)
$start = $year . '-' . $month . '-' . '1';    //first date of selected month
$lastDay = date("t", strtotime($start));    //last day of selected month
for ($ii = 1; $ii <=$lastDay; $ii++) {
    $dayNum = date("N", strtotime($year . '-' . $month . '-' . $ii));
    $col = $this->excel->column_name(3 + $ii);
    //Print day number
    $sheet->setCellValue($col . '9', $ii);
    //Print short name of the day
    switch ($dayNum)
    {
        case 1: $sheet->setCellValue($col . '8', lang('calendar_monday_short')); break;
        case 2: $sheet->setCellValue($col . '8', lang('calendar_tuesday_short')); break;
        case 3: $sheet->setCellValue($col . '8', lang('calendar_wednesday_short')); break;
        case 4: $sheet->setCellValue($col . '8', lang('calendar_thursday_short')); break;
        case 5: $sheet->setCellValue($col . '8', lang('calendar_friday_short')); break;
        case 6: $sheet->setCellValue($col . '8', lang('calendar_saturday_short')); break;
        case 7: $sheet->setCellValue($col . '8', lang('calendar_sunday_short')); break;
    }
}
//Label for employee name
$sheet->setCellValue('C8', lang('calendar_tabular_export_thead_employee'));
$sheet->mergeCells('C8:C9');
//The header is horizontally aligned
$col = $this->excel->column_name(3 + $lastDay);
$sheet->getStyle('C8:' . $col . '9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//Get the tabular data
$tabular = $this->leaves_model->tabular($id, $month, $year, $children);

//Box around the lines for each employee
$styleBox = array(
    'borders' => array(
        'top' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        ),
        'bottom' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    )
  );

$dayBox =  array(
    'borders' => array(
        'left' => array(
            'style' => PHPExcel_Style_Border::BORDER_DASHDOT,
            'rgb' => '808080'
        ),
        'right' => array(
            'style' => PHPExcel_Style_Border::BORDER_DASHDOT,
            'rgb' => '808080'
        )
    )
 );

//Background colors for the calendar according to the leave status
function createExcelCellStyle($color){
    return [
        'fill' => [
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => ['rgb' => str_replace('#', '', $color)]
        ]
    ];
}
$status_styles = [
    1 => createExcelCellStyle(getStatusColor(1)),
    2 => createExcelCellStyle(getStatusColor(2)),
    3 => createExcelCellStyle(getStatusColor(3)),
    4 => createExcelCellStyle(getStatusColor(4)),
    5 => createExcelCellStyle('000000'),
    6 => createExcelCellStyle('000000')
];

$line = 10;
//Iterate on all employees of the selected entity
foreach ($tabular as $employee) {
    //Merge the two line containing the name of the employee and apply a border around it
    $sheet->setCellValue('C' . $line, $employee->name);
    $sheet->mergeCells('C' . $line . ':C' . ($line + 1));
    $col = $this->excel->column_name($lastDay + 3);
    $sheet->getStyle('C' . $line . ':' . $col . ($line + 1))->applyFromArray($styleBox);

    //Iterate on all days of the selected month
    $dayNum = 0;
    foreach ($employee->days as $day) {
        $dayNum++;
        $col = $this->excel->column_name(3 + $dayNum);

        if($day->display == 4){
            $sheet->getStyle($col . $line . ':' . $col . ($line + 1))->applyFromArray($status_styles[5]);
            $sheet->getComment($col . $line)->getText()->createTextRun($day->type);
            $sheet->getComment($col . ($line + 1))->getText()->createTextRun($day->type);
        }

        if($use_status_colors){
            if($day->am->status > 0 && $day->am->status < 7){
                $sheet->getStyle($col . $line)->applyFromArray($status_styles[$day->am->status]);
                $sheet->getComment($col . $line)->getText()->createTextRun($day->am->type);
            }
            if($day->pm->status > 0 && $day->pm->status < 7){
                $sheet->getStyle($col . ($line + 1))->applyFromArray($status_styles[$day->pm->status]);
                $sheet->getComment($col . ($line + 1))->getText()->createTextRun($day->pm->type);
            }
        }else{
            if($day->am->status > 0 && $day->am->status < 7){
                $sheet->getStyle($col . $line)->applyFromArray(createExcelCellStyle($types[$day->am->type_id]['color']));
                $sheet->getComment($col . $line)->getText()->createTextRun($day->am->type);
            }
            if($day->pm->status > 0 && $day->pm->status < 7){
                $sheet->getStyle($col . ($line + 1))->applyFromArray(createExcelCellStyle($types[$day->pm->type_id]['color']));
                $sheet->getComment($col . ($line + 1))->getText()->createTextRun($day->pm->type);
            }
        }

    }//day
    $line += 2;
}//Employee

//Autofit for all column containing the days
for ($ii = 1; $ii <=$lastDay; $ii++) {
    $col = $this->excel->column_name($ii + 3);
    $sheet->getStyle($col . '8:' . $col . ($line - 1))->applyFromArray($dayBox);
    $sheet->getColumnDimension($col)->setAutoSize(TRUE);
}
$sheet->getColumnDimension('A')->setAutoSize(TRUE);
$sheet->getColumnDimension('B')->setAutoSize(TRUE);
$sheet->getColumnDimension('C')->setWidth(40);

//Set layout to landscape and make the Excel sheet fit to the page
$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setFitToPage(true);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);

exportSpreadsheet($this, 'tabular');