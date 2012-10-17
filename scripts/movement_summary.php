<?php
require 'include/prepend.php';

$lot_date 				  	= @$argv[1];
$departure_location   = @$argv[2];
$destination_location = @$argv[3];

$lot_date 							= strtotime($lot_date);
$departure_location 		= ScotEID_Holding::try_parse($departure_location);
$destination_location		= ScotEID_Holding::try_parse($destination_location);

if(empty($lot_date) || !($departure_location->valid()) || !($destination_location->valid())) {
    print "Usage: movement_summary <lot_date> <departure_location> <destination_location>\n";
} else {
	
	$summary = ScotEID_Lot::get_movement_summary($lot_date, $departure_location, $destination_location);
	var_dump($summary);
	die();

}