<?php
require 'include/prepend.php';

$ref  = $argv[1];
$refs = explode(',', $ref);

if(count($refs) == 0) {
    print "Usage: uncomplete_lots_by_ref <ref1[,ref2][,...]>\n";
} else {
    $lots = ScotEID_CompletedLot::find(array('conditions' => array('sams_movement_reference.in' => $refs)));
    
    foreach($lots as $lot) {
        if($lot->uncomplete()) {
            print "* Lot {$lot->get_lot_number()} uncompleted successfully\n";
        } else {
            print "! Failed to uncomplete lot {$lot->get_lot_number()}\n";
        }
    }
}
