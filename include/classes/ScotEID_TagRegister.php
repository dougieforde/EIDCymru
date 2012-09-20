<?php
class ScotEID_TagRegister
{
  public static function record_error($samref, $query) {
    if(SCOTEID_WEBSERVICES_ENV == 'production') {
  		// record errors for analysis
  		$fp = fopen("C:\\PHP\\includes\\register_queries\\" . $samref .".txt", "w");
  		fwrite($fp,  $query);
  		fclose($fp);
    } else if(SCOTEID_WEBSERVICES_ENV == 'development') {
  	}
  	$to       = "lewis@thebackwoods.co.uk";
  	$subject  = "tag register error $samref";
  	$body     = "$query";
  	if (mail($to, $subject, $body)) 
  	{
  		return true;
  	} 
  	else 
  	{
  		return false;
  	}
  }
  
  public static function lot_updated($samref) {
    try {
      $samref = quote_int($samref);
    
      $query = "
        SELECT 
          movementtype, 
          lotdate, 
          readlocationcph, 
          lot_no, 
          destinationcph
        FROM 
          tblsslots
        WHERE 
          sams_movement_reference = $samref";
        
    	if ($result = dbw_query($query)) {
    	  $row = dbw_row($result);
  	  
    		$movetype         = quote_int($row['movementtype']);
    		$lotdate          = quote_str($row['lotdate']);
    		$readlocationcph  = quote_str($row['readlocationcph']);
    		$lotno            = quote_str($row['lot_no']);
    		$destcph          = quote_str($row['destinationcph']);
  		
    		$movement_type_testing = ScotEID_MovementType::MOVEMENT_TYPE_TESTING;
  		
    		//
    		// add any tags read which don't exist in the tag register into the tag register
  	  
    	  $query = "
    	    INSERT INTO 
    	      tblindividualsheepeidregister (eidhexnumber,animaleid,uknumber,country_code) 
    	    SELECT 
    	      i.eidhexnumber, 
    	      i.animaleid, 
    	      /* work out if it is  wysiwyg if not then cant make the uk number */ 
    	      if(i.eidhexnumber REGEXP '^[8-F]200',concat('UK',lpad(i.animaleid, 12, '0')),'unknown') ,
    	      i.country_code
    	    FROM 
    	      tblsheepitemread i 
    	      LEFT JOIN tblindividualsheepeidregister r ON 
    	        i.animaleid = r.animaleid
    	    WHERE 
    	      r.animaleid IS NULL AND 
    	      i.movementtype   <> $movement_type_testing AND 
    	      i.lotdate         = $lotdate AND 
    	      i.lot_no          = $lotno AND 
    	      i.movementtype    = $movetype AND 
    	      i.readlocationcph = $readlocationcph AND
    	      i.flock_tag = 0";	
          
        $result = dbw_query($query);
      
        //
        // now update the tag register
      
    		$movement_type_on           = ScotEID_MovementType::MOVEMENT_TYPE_ON;
    		$movement_type_off          = ScotEID_MovementType::MOVEMENT_TYPE_OFF;
    		$movement_type_mart         = ScotEID_MovementType::MOVEMENT_TYPE_MART;
    		$movement_type_death        = ScotEID_MovementType::MOVEMENT_TYPE_DEATH;
    		$movement_type_abattoir     = ScotEID_MovementType::MOVEMENT_TYPE_ABATTOIR;
    		$movement_type_dropout      = ScotEID_MovementType::MOVEMENT_TYPE_DROPOUT;
    		$movement_type_unreadable   = ScotEID_MovementType::MOVEMENT_TYPE_UNREADABLE;
    		$movement_type_insert_break = ScotEID_MovementType::MOVEMENT_TYPE_INSERT_BREAK;
  		
  		
    		$query = "
    		  UPDATE 
    		    tblindividualsheepeidregister r
    			JOIN tblsheepitemread i ON
    			  i.animaleid = r.animaleid
    			SET 
    			  r.lastmovecph  = $destcph,
    				r.lastmovedate = $lotdate,
    				r.lastmovetype = $movetype,
    				r.prevmoveref  = r.lastmoveref,
    				r.lastmoveref  = $samref
    			WHERE 
    			  (
    			    i.movementtype    = $movetype AND 
    			    i.lot_no LIKE $lotno AND 
    			    i.lotdate         = $lotdate AND 
    			    i.readlocationcph = $readlocationcph
    			  ) AND (
    			    r.lastmovecph IS NULL OR
  					
    					/* we only want to update if the location has changed so that the date is 
    					 * always the first date on which the tag was recorded. If the animal has died or 
    					 * the tag has dropped out then the tag can be updated on the same location */
  					
    					(
    					  (
    					    r.lastmovecph NOT LIKE $destcph OR 
    					    i.movementtype IN (
    					      $movement_type_death,
    					      $movement_type_dropout,
    					      $movement_type_unreadable
    					    )
    					  )
  					 
    				  	/* only update for more recent movements. If movement occurs on the same day 
      					 * as is unfortunately going to happen often then we need to work out the priority 
      					 * of the moves comparing the reviously recorded movement type and the new movement type */
  					
    						AND (
    						  i.lotdate > r.lastmovedate OR 
    						  (
    						    i.lotdate = r.lastmovedate AND 
    						    (
    						      i.movementtype IN ( /* these are always final moves */
    						        $movement_type_death,
    						        $movement_type_abattoir,
    						        $movement_type_dropout,
    						        $movement_type_insert_break,
    						        $movement_type_unreadable
    						      ) 
    						      OR 
    						      (
    						        i.movementtype = $movement_type_mart AND 
    						        r.lastmovetype NOT IN (
      						        $movement_type_death,
      						        $movement_type_abattoir,
      						        $movement_type_dropout,
      						        $movement_type_insert_break,
      						        $movement_type_unreadable
    						        )
    						      ) 
    						      OR /* compare a previous market move to the ones which take priority */
    						      (
    						        i.movementtype IN (
    						          $movement_type_on,
    						          $movement_type_off
    						        ) AND 
    						        r.lastmovetype NOT IN (
    						          $movement_type_mart,
    						          $movement_type_death,
    						          $movement_type_abattoir,
    						          $movement_type_dropout,
    						          $movement_type_insert_break,
    						          $movement_type_unreadable					          
    						        )
    						      )
    						    )
    						  )
    						)
    					)
    				);";
        // dbw_begin();
  	  
    		if ($result = dbw_query($query))
    		{	
    			if (dbw_affected_rows() > 2000)
    			{
    				// wrong so record error
    				static::record_error($samref,$query);
    				$retval = false;
    			}
    			else
    			{
    				// this is correct so commit it
    				$retval = true;
    			}
    		}
    		else
    		{
    			// wrong so record error 
    			$retval = False;
    		}

        if($retval) {			
          // dbw_commit();
        } else {
          static::record_error($samref,$query);
          // dbw_rollback();
        }

    		return $retval;
    	}
    	else
    	{
    		// wrong so record error 
    		static::record_error($samref,$query);
    		return false;
    	}
  	} catch(Exception $ex) {
  	  ScotEID_Mailer::deliver_exception($ex);
  	}
        return false;
  }
  
 
  public static function lot_deleted($samref) {
    try {
      $qsamref = quote_int($samref);
      $query = "
        SELECT
          DISTINCT l.sams_movement_reference
        FROM
          tblsslots l 
              JOIN tblsheepitemread i ON 
                l.readlocationcph = i.readlocationcph AND 
                i.lot_no          = l.lot_no AND 
                i.movementtype    = l.movementtype AND 
                l.lotdate         = i.lotdate
              JOIN tblindividualsheepeidregister r ON
                i.animaleid       = r.animaleid AND 
                i.country_code    = r.country_code
              WHERE 
                lastmoveref = $qsamref";
                  
      if ($result = dbw_query($query))
      {
        //remove the previous stored lot data for this movement from the register table
        $up_query = "
        UPDATE
          tblindividualsheepeidregister r
        SET
          lastmovecph  = null,
          lastmovedate = null,
          lastmoveref  = null,
          lastmovetype = null,
          prevmoveref  = null
        WHERE 
          lastmoveref  = $qsamref 
          OR
          prevmoveref  = $qsamref";
        
        $up_result = dbw_query($up_query);
        while($row = dbw_row($result))
        {
          static::lot_updated($row['sams_movement_reference']);
        }
      }
      return true;
    } catch(Exception $ex) {
      ScotEID_Mailer::deliver_exception($ex);
    }
  }
}
?>
