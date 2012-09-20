<?php
class ScotEID_ReadSchedule
{
    public static function run_sheep_warnings() 
    {
      $date = strtotime("today");
      
      $items = ScotEID_ReadScheduleItem::find_sheep_scheduled_by_date($date);
      $qdate = quote_date($date);
      
      foreach($items as $item) {        
        if($item->get_starts_at() < strtotime("-1 hour")) {
          $qreadlocation = quote_str($item->get_read_location());
        
          $r  = dbw_query("SELECT count(*) AS count FROM " . ScotEID_TagReading::COMPLETED_TABLE_NAME  . " WHERE ReadLocationCPH = $qreadlocation AND LotDate = $qdate");
          $r2 = dbw_query("SELECT count(*) AS count FROM " . ScotEID_TagReading::INCOMPLETE_TABLE_NAME . " WHERE ReadLocationCPH = $qreadlocation AND LotDate = $qdate");
        
          $c = 0;
        
          if($row = dbw_row($r)) {
            $c += $row['count'];
          }
        
          if($row = dbw_row($r2)) {
            $c += $row['count'];
          }        
                
          if($c == 0 && $item->get_warning_count() == 0) {
            $item->set_warning_count($item->get_warning_count()+1);
            if($item->save()) {
              ScotEID_Mailer::deliver_read_reminder($item);
            }
          }
        }
      }
    }
    
    // Generate fixed schedule date entries for the current year
    // These "schedules" should be put in the database
    public static function generate() {
      
      require 'php_ice_cube/IceCube.php';
      
      $TO   = strtotime("01 January 2012");
      
      $qdate = quote_date(strtotime("today"));
      
      dbw_begin();
      dbw_query("DELETE FROM " . ScotEID_ReadScheduleItem::TABLE_NAME . " WHERE generated = 1 AND DATE(starts_at) < $qdate");
            
      $definitions = ScotEID_ReadScheduleDefinition::find_all();
      foreach($definitions as $definition) {
        $schedule = $definition->get_schedule();
        foreach($schedule->occurrences_between(strtotime("tomorrow"), $TO) as $date) {
          $item = new ScotEID_ReadScheduleItem();
          $item->set_read_location($definition->get_read_location());
          $item->set_generated(true);
          $item->set_starts_at($date);
          $item->set_cattle($definition->get_cattle());
          $item->set_sheep($definition->get_sheep());
          $item->save();
        }
      }
      
      dbw_commit();
    }
}
?>