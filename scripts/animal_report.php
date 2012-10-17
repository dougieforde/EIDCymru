<?php
require 'include/prepend.php';
include 'include/PHPExcel-1.7.7/PHPExcel.php';
include 'include/PHPExcel-1.7.7/PHPExcel/Writer/Excel2007.php';

$DATE_FORMAT = "%d/%m/%Y";
$TIMESTAMP_FORMAT = "%d/%m/%Y %T";

$STYLE_BOLD = array(
  'font' => array(
  'bold' => true
  )
);

function scoteid_investigate_tag_movement_references($tag_id) {
  $animaleid = quote_str($tag_id);
  
  $query = "
    SELECT 
      DISTINCT l.sams_movement_reference 
    FROM 
      tblsslots l
      JOIN tblsheepitemread i ON
        i.lotdate = l.lotdate AND 
        l.readlocationcph = i.readlocationcph AND
        i.lot_no = l.lot_no AND
        i.movementtype = l.movementtype AND
        i.Species = l.Species
    WHERE 
      animaleid = $animaleid;";  
    
  $r = array();

  if ($result = dbw_query($query))
  {
    while($row = dbw_row($result)) {
      $r[] = $row['sams_movement_reference'];
    }
  }
  
  return $r;
}

$animal_eids = array(
  54029501462,
  54093400861,
  54125401040,
  54226803417,
  54226803450
);

foreach($animal_eids as $animal_eid) {
  $sheet = 0;

  $objPHPExcel = new PHPExcel();

  $objPHPExcel->getActiveSheet()->setTitle("Movements - $animal_eid");
  $objPHPExcel->setActiveSheetIndex(0);
  $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
  $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
  $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
  $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
  $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
  $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
  $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
  $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
  $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Lot Date');
  $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Lot Number');
  $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Movement Type');
  $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Departure CPH');
  $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Read Location CPH');
  $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Destination CPH');
  $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Head Count');
  $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Read Count');
  $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($STYLE_BOLD);
  $objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($STYLE_BOLD);
  $objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($STYLE_BOLD);
  $objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($STYLE_BOLD);
  $objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray($STYLE_BOLD);
  $objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray($STYLE_BOLD);
  $objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray($STYLE_BOLD);
  $objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray($STYLE_BOLD);
  
  $lots = ScotEID_CompletedLot::find(array(
    'conditions' => array(
      'sams_movement_reference.in' => scoteid_investigate_tag_movement_references($animal_eid)
    ),
    'order' => array('lot_date', 'asc')
  ));
  
  $row = 1;
  foreach($lots as $lot) {
    $row++;
    
    $lot_number = $lot->get_lot_number();
    $subsheet_name = "Lot $lot_number ({$lot->get_sams_movement_reference()})";
    
    $objPHPExcel->getActiveSheet()->SetCellValue("A$row", strftime($DATE_FORMAT, $lot->get_lot_date()));
    $objPHPExcel->getActiveSheet()->SetCellValue("B$row", $lot->get_lot_number());
    $objPHPExcel->getActiveSheet()->getCell("B$row")
      ->getHyperlink()
      ->setUrl("sheet://'$subsheet_name'!A1");
      
    $objPHPExcel->getActiveSheet()->SetCellValue("C$row", $lot->get_movement_type() ? $lot->get_movement_type()->get_short_name() : '');
    $objPHPExcel->getActiveSheet()->SetCellValue("D$row", $lot->get_departure_location() ? $lot->get_departure_location()->getCPH() : '');
    $objPHPExcel->getActiveSheet()->SetCellValue("E$row", $lot->get_read_location() ? $lot->get_read_location()->getCPH() : '');
    $objPHPExcel->getActiveSheet()->SetCellValue("F$row", $lot->get_destination_location() ? $lot->get_destination_location()->getCPH() : '');
    $objPHPExcel->getActiveSheet()->SetCellValue("G$row", $lot->get_head_count());
    $objPHPExcel->getActiveSheet()->SetCellValue("H$row", $lot->get_read_count());
    
    // create a sheet for the reads in this lot
      
    $sheet++;
    $objPHPExcel->createSheet($sheet);
    $objPHPExcel->setActiveSheetIndex($sheet);
    $objPHPExcel->getActiveSheet()->setTitle($subsheet_name);
    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'UK Number');
    $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Timestamp');
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    
    $row2 = 0;
    foreach($lot->get_tag_readings() as $tag_reading) {
      $row2++;
      $objPHPExcel->getActiveSheet()->SetCellValue("A$row2", $tag_reading->get_animal()->get_animal_number());
      $objPHPExcel->getActiveSheet()->SetCellValue("B$row2", strftime($TIMESTAMP_FORMAT, $tag_reading->get_timestamp()));
      
      if($tag_reading->get_tag_id()->get_animal_id() == $animal_eid) 
      {
        $objPHPExcel->getActiveSheet()->getStyle("A$row2")->applyFromArray($STYLE_BOLD);
        $objPHPExcel->getActiveSheet()->getStyle("B$row2")->applyFromArray($STYLE_BOLD);
      }
      
    }
    $objPHPExcel->setActiveSheetIndex(0);
    
  }
  
  $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
  $objWriter->save(dirname(__FILE__) . "/$animal_eid.xlsx");
}

?>
