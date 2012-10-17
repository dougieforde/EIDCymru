<?php
require 'include/prepend.php';

$ref = @$argv[1];

if(empty($ref)) {
    print "Usage: lot_summary <sams_movement_reference>\n";
} else {
    $lot = ScotEID_CompletedLot::find_by_sams_movement_reference($ref);
		$lot->load_flock_tags();
		$lot->load_tag_readings();
		var_dump($lot->get_flock_tag_summary());
}
