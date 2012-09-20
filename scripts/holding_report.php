<?php
require 'include/prepend.php';
include 'include/PHPExcel-1.7.7/PHPExcel.php';
include 'include/PHPExcel-1.7.7/PHPExcel/Writer/Excel2007.php';

$objPHPExcel = new PHPExcel();

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('ON');

$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Lot Date');
$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Lot Number');
$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Departure CPH');
$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Read Location CPH');
$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Destination CPH');
$objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Head Count');
$objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Read Count');

$row = 1;

$on_lots = ScotEID_CompletedLot::find(
  array(
    'conditions' => 
      array(
        'species_id'           => ScotEID_Species::SPECIES_SHEEP,
        'destination_location' => '67/111/0041',
        'movement_type_id.in'  => array(ScotEID_MovementType::MOVEMENT_TYPE_ON, ScotEID_MovementType::MOVEMENT_TYPE_MART)
      )
    )
  );
  
foreach($on_lots as $lot) {
  $row++;
  $objPHPExcel->getActiveSheet()->SetCellValue("A$row", $lot->get_lot_date());
  $objPHPExcel->getActiveSheet()->SetCellValue("B$row", $lot->get_lot_number());
  $objPHPExcel->getActiveSheet()->SetCellValue("C$row", $lot->get_departure_location() ? $lot->get_departure_location()->getCPH() : '');
  $objPHPExcel->getActiveSheet()->SetCellValue("D$row", $lot->get_read_location() ? $lot->get_read_location()->getCPH() : '');
  $objPHPExcel->getActiveSheet()->SetCellValue("E$row", $lot->get_destination_location() ? $lot->get_destination_location()->getCPH() : '');
  $objPHPExcel->getActiveSheet()->SetCellValue("F$row", $lot->get_head_count());
  $objPHPExcel->getActiveSheet()->SetCellValue("G$row", $lot->get_read_count());
}

$objPHPExcel->createSheet(1);
$objPHPExcel->setActiveSheetIndex(1);

$objPHPExcel->getActiveSheet()->setTitle('OFF');
$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Lot Date');
$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Lot Number');
$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Departure CPH');
$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Read Location CPH');
$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Destination CPH');
$objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Head Count');
$objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Read Count');

$off_lots = ScotEID_Lot::find(
  array(
      'conditions' => array(
        'species_id'           => ScotEID_Species::SPECIES_SHEEP,
        'departure_location'   => '67/111/0041',
        'movement_type_id.in'  => array(ScotEID_MovementType::MOVEMENT_TYPE_OFF, ScotEID_MovementType::MOVEMENT_TYPE_MART)
      )
    )
  );

foreach($off_lots as $lot) {
  $row++;
  $objPHPExcel->getActiveSheet()->SetCellValue("A$row", $lot->get_lot_date());
  $objPHPExcel->getActiveSheet()->SetCellValue("B$row", $lot->get_lot_number());
  $objPHPExcel->getActiveSheet()->SetCellValue("C$row", $lot->get_departure_location() ? $lot->get_departure_location()->getCPH() : '');
  $objPHPExcel->getActiveSheet()->SetCellValue("D$row", $lot->get_read_location() ? $lot->get_read_location()->getCPH() : '');
  $objPHPExcel->getActiveSheet()->SetCellValue("E$row", $lot->get_destination_location() ? $lot->get_destination_location()->getCPH() : '');
  $objPHPExcel->getActiveSheet()->SetCellValue("F$row", $lot->get_head_count());
  $objPHPExcel->getActiveSheet()->SetCellValue("G$row", $lot->get_read_count());
}

$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save('/Users/lewis/Desktop/movements.xlsx');
?>