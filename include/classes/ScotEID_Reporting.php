<?php
class ScotEID_Reporting
{
  public static function bpex_statistics()
  {
    $r = array();
    
    $r['total_sent_to_bpex'] = static::count_sql("SELECT COUNT(*) AS c FROM tblsslots WHERE LotDate >= '2012-04-01' AND uid <> 5829 AND sent_to_bpex = 1");
    $r['weekly_sent_to_bpex'] = static::count_weekly_sql("SELECT COUNT(*) AS c FROM tblsslots WHERE LotDate >= '2012-04-01' AND uid <> 5829 AND sent_to_bpex = 1");
    
    $r['total_sent_to_bpex_signed_off_scoteid'] = static::count_sql("SELECT COUNT(*) AS c FROM tblsslots WHERE LotDate >= '2012-04-01' AND uid <> 5829 AND sent_to_bpex = 1 AND receiving_keeper_id <> 5829");
    $r['weekly_sent_to_bpex_signed_off_scoteid'] = static::count_weekly_sql("SELECT COUNT(*) AS c FROM tblsslots WHERE LotDate >= '2012-04-01' AND uid <> 5829 AND sent_to_bpex = 1 AND receiving_keeper_id <> 5829");
    
    $r['total_sent_to_bpex_signed_off_bpex'] = static::count_sql("SELECT COUNT(*) AS c FROM tblsslots WHERE LotDate >= '2012-04-01' AND uid <> 5829 AND sent_to_bpex = 1 AND receiving_keeper_id = 5829");
    $r['weekly_sent_to_bpex_signed_off_bpex'] = static::count_weekly_sql("SELECT COUNT(*) AS c FROM tblsslots WHERE LotDate >= '2012-04-01' AND uid <> 5829 AND sent_to_bpex = 1 AND receiving_keeper_id = 5829");
    
    $r['total_received_from_bpex'] = static::count_sql("SELECT COUNT(*) AS c FROM tblsslots WHERE LotDate >= '2012-04-01' AND uid = 5829");
    $r['weekly_received_from_bpex'] = static::count_weekly_sql("SELECT COUNT(*) AS c FROM tblsslots WHERE LotDate >= '2012-04-01' AND uid = 5829");
    
    $r['total_received_from_bpex_signed_off_scoteid'] = static::count_sql("SELECT COUNT(*) AS c FROM tblsslots WHERE LotDate >= '2012-04-01' AND uid = 5829 AND receiving_keeper_id <> 5829");
    $r['weekly_received_from_bpex_signed_off_scoteid'] = static::count_weekly_sql("SELECT COUNT(*) AS c FROM tblsslots WHERE LotDate >= '2012-04-01' AND uid = 5829 AND receiving_keeper_id <> 5829");
    
    $r['total_received_from_bpex_signed_off_bpex'] = static::count_sql("SELECT COUNT(*) AS c FROM tblsslots WHERE LotDate >= '2012-04-01' AND uid = 5829 AND receiving_keeper_id = 5829");
    $r['weekly_received_from_bpex_signed_off_bpex'] = static::count_weekly_sql("SELECT COUNT(*) AS c FROM tblsslots WHERE LotDate >= '2012-04-01' AND uid = 5829 AND receiving_keeper_id = 5829");
  
    return $r;
  }
  
  private static function count_sql($sql) {
    $q = dbw_query($sql);
    $row = dbw_row($q);
    if($row) {
      return (int) $row['c'];
    } else {
      return 0;
    }
  }
  
  private static function count_weekly_sql($sql) {
    return static::count_sql($sql . " AND YEARWEEK(LotDate) = YEARWEEK(NOW())");
  }
}
?>