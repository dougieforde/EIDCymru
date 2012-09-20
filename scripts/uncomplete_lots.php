<?php
require 'include/prepend.php';

$lot_date = $argv[1];
$uid      = $argv[2];

if(empty($uid) || empty($lot_date)) {
    print "Usage: uncomplete_lot <lot_date> <uid>\n";
} else {
    $lot_date = strtotime($lot_date);

    $lots = ScotEID_CompletedLot::find_by_uid_and_date($uid, $lot_date);
    
    foreach($lots as $lot) {
        if($lot->uncomplete()) {
            print "* Lot {$lot->get_lot_number()} uncompleted successfully\n";
        } else {
            print "! Failed to uncomplete lot {$lot->get_lot_number()}\n";
        }
    }
}
