<?php
class ScotEID_LotProblem extends ScotEID_ExtendedModel
{  
  const PROBLEM_OVER_READ = "over-read";
  
  public static function get_table_name() {
		return "lot_problems";
	}
	
	public function get_primary_key() {
		return array(
			'id' => quote_int($this->get_id())
		);
	}
	
	public function to_array() {
	  return array(
	    'id'   => $this->get_id(),
	    'type' => $this->get_type()
	  );
	}
	
	protected static function get_attribute_definitions() {
	  return array(
	    'id'                        => array('type' => 'serial'),
	    'sams_movement_reference'   => array('type' => 'int'),
	    'type'                      => array('type' => 'string'),
	    'created_at'                => array('type' => 'datetime')
	  );
	}
	
	public static function generate() {
	  dbw_query("DROP TEMPORARY TABLE IF EXISTS lot_problems_temp");
	  dbw_query("
  	  CREATE TEMPORARY TABLE `lot_problems_temp` (
        `sams_movement_reference` int(11) DEFAULT NULL,
        `type` varchar(50) DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`sams_movement_reference`,`type`)
      );");
	  
	  //
	  // landless keepers
	  
	  $sql = "	  
	  SELECT
	    l.SAMS_Movement_Reference,
	    'landless-keeper',
	    NOW()
    FROM
    	tblsslots l
    	LEFT JOIN tblsamholdings dep ON dep.cph = l.DepartureCPH
    	LEFT JOIN tblsamholdings dest ON dest.cph = l.DestinationCPH
    	LEFT JOIN tblsamholdings `read` ON `read`.cph = l.ReadLocationCPH
    WHERE
    	(l.DepartureCPH LIKE '%/%/7%' AND (dep.locationtype IS NULL OR dep.locationtype != 'ferry')) OR
    	(l.ReadLocationCPH LIKE '%/%/7%' AND (`read`.locationtype IS NULL OR `read`.locationtype != 'ferry')) OR
    	(l.DestinationCPH LIKE '%/%/7%' AND (dest.locationtype IS NULL OR dest.locationtype != 'ferry'));";
	  
	  static::insert_problems($sql);
	  
	  //
	  // over 110% read rate
	  
	  $sql = "
	    SELECT 
	      l.SAMS_Movement_Reference,
	      'over-read',
	      NOW()
	    FROM 
	      tblsslots l 
	    WHERE 
	      ((l.Qty_Reads/l.Qty_Sheep) * 100 > 110 AND l.Qty_Sheep >= 10) OR
	      (l.Qty_Sheep < 10 AND l.Qty_Reads > l.Qty_Sheep + 2);";
	  
	  static::insert_problems($sql);
	  
	  // 
	  // bad county/parish
	  
	  $fields = array(
	   array('ReadLocationCPH', 'read-location'),
	   array('DepartureCPH',    'departure-location'),
	   array('DestinationCPH',  'destination-location')
	  );
	  
	  foreach($fields as $field) {

      $column = $field[0];
      $desc   = $field[1];

	    $sql = "
	      SELECT
        	l.SAMS_Movement_Reference,
        	'invalid-$desc',
        	NOW()
        FROM
        	tblsslots l
        	LEFT JOIN ccppp_names cp ON cp.cc = LEFT(l.$column, 2) AND cp.ppp = MID(l.$column, 4, 3)
        WHERE
        	(
        		(
        			LEFT(l.$column, 2) > 65 AND
        			cp.cc IS NULL
        		) 
        	);";
        	
        static::insert_problems($sql);
	  }
	  
	  //
	  // insert any newly detected problems
	  dbw_query("INSERT IGNORE lot_problems(sams_movement_reference,type,created_at) SELECT * FROM lot_problems_temp");
	  
	  //
	  // delete any problems which are no longer present
	  dbw_query("
	    DELETE 
	      lot_problems 
	    FROM 
	      lot_problems 
	      LEFT JOIN lot_problems_temp ON 
	        lot_problems.sams_movement_reference = lot_problems_temp.sams_movement_reference AND
	        lot_problems.type = lot_problems_temp.type
	    WHERE
	      lot_problems_temp.sams_movement_reference IS NULL");
	}
	
	private static function insert_problems($sql) {
    $sql = "INSERT IGNORE lot_problems_temp(sams_movement_reference, type, created_at) " . $sql;
    try {
      dbw_query($sql);
    } catch(DBException $ex) {
      ScotEID_Mailer::deliver_exception($ex);
    }
	}
}
?>