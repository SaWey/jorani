<?php
/**
 * This view exports a yearly calendar of the leave taken by a user (can be displayed by HR or manager)
 * It builds an Excel 2007 file downloaded by the browser.
 * @copyright  Copyright (c) 2014-2016 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.4.3
 */

//Either self access, Manager or HR
if ($employee == 0) {
    $employee = $this->user_id;
} else {
    if (!$this->is_hr) {
        if ($this->manager != $this->user_id) {
            $employee = $this->user_id;
        }
    }
}

$employee_name = $this->users_model->getName($employee);
//Load the leaves for all the months of the selected year

$months = array(
    lang('January') => $this->leaves_model->linear($employee, 1, $year, TRUE, TRUE, TRUE, TRUE),
    lang('February') => $this->leaves_model->linear($employee, 2, $year, TRUE, TRUE, TRUE, TRUE),
    lang('March') => $this->leaves_model->linear($employee, 3, $year, TRUE, TRUE, TRUE, TRUE),
    lang('April') => $this->leaves_model->linear($employee, 4, $year, TRUE, TRUE, TRUE, TRUE),
    lang('May') => $this->leaves_model->linear($employee, 5, $year, TRUE, TRUE, TRUE, TRUE),
    lang('June') => $this->leaves_model->linear($employee, 6, $year, TRUE, TRUE, TRUE, TRUE),
    lang('July') => $this->leaves_model->linear($employee, 7, $year, TRUE, TRUE, TRUE, TRUE),
    lang('August') => $this->leaves_model->linear($employee, 8, $year, TRUE, TRUE, TRUE, TRUE),
    lang('September') => $this->leaves_model->linear($employee, 9, $year, TRUE, TRUE, TRUE, TRUE),
    lang('October') => $this->leaves_model->linear($employee, 10, $year, TRUE, TRUE, TRUE, TRUE),
    lang('November') => $this->leaves_model->linear($employee, 11, $year, TRUE, TRUE, TRUE, TRUE),
    lang('December') => $this->leaves_model->linear($employee, 12, $year, TRUE, TRUE, TRUE, TRUE),
);


$sheet = $this->excel->setActiveSheetIndex(0);

//Print the header with the values of the export parameters
$sheet->setTitle(mb_strimwidth(lang('calendar_year_title'), 0, 28, "..."));  //Maximum 31 characters allowed in sheet title.
$sheet->setCellValue('A1', lang('calendar_year_title') . ' ' . $year . ' (' . $employee_name . ') ');
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->mergeCells('A1:C1');

//Print a line with all possible day numbers (1 to 31)
for ($ii = 1; $ii <= 31; $ii++) {
    $col = $this->excel->column_name(3 + $ii);
    $sheet->setCellValue($col . '3', $ii);
}

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
//Box around a day
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
//To fill at the left of months having less than 31 days
 $styleMonthPad = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => '00FFFF')
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

$line = 4;
//Iterate on all employees of the selected entity
foreach ($months as $month_name => $month) {
    //Merge the two line containing the name of the month and apply a border around it
    $sheet->setCellValue('C' . $line, $month_name);
    $sheet->mergeCells('C' . $line . ':C' . ($line + 1));
    $col = $this->excel->column_name(34);
    $sheet->getStyle('C' . $line . ':' . $col . ($line + 1))->applyFromArray($styleBox);

    //Iterate on all days of the selected month
    $dayNum = 0;
    foreach ($month->days as $day) {
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
    if ($dayNum < 31) {
        $pad = (int) (35 - (31 - $dayNum));
        $colFrom = $this->excel->column_name($pad);
        $colTo = $this->excel->column_name(34);
        $sheet->mergeCells($colFrom . $line . ':' . $colTo . ($line + 1));
        $sheet->getStyle($colFrom . $line . ':' . $colTo . ($line + 1))->applyFromArray($styleMonthPad);
    }
    $line += 2;
}//Employee

//Autofit for all column containing the days
for ($ii = 1; $ii <= 31; $ii++) {
    $col = $this->excel->column_name($ii + 3);
    $sheet->getStyle($col . '3:' . $col . ($line - 1))->applyFromArray($dayBox);
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

exportSpreadsheet($this, 'year');
