<?php

require 'include/prepend.php';
require 'include/script_helpers.php';

// find pending movements

$base_conditions = <<<C
  #lot_date# > '2012-03-29' AND
  #species_id# = 3 AND
  (
    (
      #destination_location# IS NOT NULL AND
      LEFT(#destination_location#, 2) <= 65
    ) 
    OR 
    (
      #destination_location# IS NOT NULL AND
      #departure_location# IS NOT NULL AND
      LEFT(#departure_location#, 2) <= 65
    )
  )
C;

$conditions = "$base_conditions AND #sent_to_bpex# = 0";

$lots = ScotEID_CompletedLot::find(
  array('conditions' => $conditions, 'limit' => 100)
);

print_success("Lots to be sent to BPEX: " . count($lots));

$service = new ScotEID_BPEXService();
$service->register_batch($lots);

// find any movements where source or destination site were not matched
// and attempt to resend with cph only

/*
//
// source site only

$conditions = "$base_conditions AND (#sent_to_bpex# & 4 <> 0 OR #sent_to_bpex# & 16 <> 0) AND #sent_to_bpex# & 8 = 0";
$lots = ScotEID_CompletedLot::find(
  array('conditions' => $conditions, 'limit' => 20)
);
print_success("Lots to be sent to BPEX with source CPH only: " . count($lots));
$service->register_batch($lots, array('source_cph_only' => true));

//
// destination site only

$conditions = "$base_conditions AND #sent_to_bpex# & 4 = 0 AND (#sent_to_bpex# & 8 <> 0 OR #sent_to_bpex# & 32 <> 0)";
$lots = ScotEID_CompletedLot::find(
  array('conditions' => $conditions, 'limit' => 20)
);
print_success("Lots to be sent to BPEX with destination CPH only: " . count($lots));
$service->register_batch($lots, array('destination_cph_only' => true));

//
// source site and destination site

$conditions = "$base_conditions AND (#sent_to_bpex# & 4 <> 0 OR #sent_to_bpex# & 16 <> 0) AND (#sent_to_bpex# & 8 <> 0 OR #sent_to_bpex# & 32 <> 0)";
$lots = ScotEID_CompletedLot::find(
  array('conditions' => $conditions, 'limit' => 20)
);
print_success("Lots to be sent to BPEX with source/destination CPH only: " . count($lots));
$service->register_batch($lots, array('source_cph_only' => true, 'destination_cph_only' => true));
*/

print_success("Done");

?>
