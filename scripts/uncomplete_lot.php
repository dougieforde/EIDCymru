<?php
require 'include/prepend.php';

$ref = @$argv[1];

if(empty($ref)) {
    print "Usage: uncomplete_lot <sams_movement_reference>\n";
} else {
    $lot = ScotEID_CompletedLot::find_by_sams_movement_reference($ref);
    if($lot->uncomplete()) {
        print "* Lot {$lot->get_lot_number()} uncompleted successfully\n";
    } else {
        print "! Failed to uncomplete lot {$lot->get_lot_number()}\n";
    }
}
