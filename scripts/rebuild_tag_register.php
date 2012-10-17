<?php
require 'include/prepend.php';

$offset = 0;
$limit  = 100;

$min = 0;

if(isset($argv[1])) {
  $min = (int) $argv[1];
}

while(true) {
	$lots = ScotEID_CompletedLot::find(array('conditions' => array('sams_movement_reference.gt' => $min, 'species_id' => ScotEID_Species::SPECIES_SHEEP), 'limit' => $limit, 'offset' => $offset, 'order' => array('sams_movement_reference', 'asc')));
	
	if(count($lots) == 0)
		break;
		
	foreach($lots as $lot) {
		print "Updating sams ref: " . $lot->get_sams_movement_reference() . ", offset: " . $offset . "\n";
		if(!ScotEID_TagRegister::lot_updated($lot->get_sams_movement_reference())) {
                	print "\t- ERROR!\n";
			exit;
		}
	}	
		
	$offset += $limit;
}

?>
