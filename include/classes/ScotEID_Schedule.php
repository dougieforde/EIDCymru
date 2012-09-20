<?php
class ScotEID_Schedule
{
  private $uid;
  private $description;
  private $schedule;
  
  public function __construct($uid, $description, $start_date = null) {
    $this->uid = $uid;
    $this->description = $description;
    $this->schedule    = new IceCube\Schedule($start_date);
  }
  
  public function get_uid() { return $this->uid; }
  public function get_description() { return $this->description; }
  public function get_schedule() { return $this->schedule; }
  
  // Return a list of user ids who have lots scheduled for today
  public static function get_scheduled_uid($schedule_date = time()) {
    $uids = array();
    $schedules = self::get_all();
    foreach($schedules as $schedule) {
      if(true)
        $uids[] = $schedule->get_uid();
    }
  }
  
  // Returns an array of schedules
  public static function get_all() {
    $schedules = array();
    
    // Dingwall
    $s        = new ScotEID_Schedule(999, "Dingwall - Prime Stock", strtotime("2011-01-04"));
    $schedule = $s->get_schedule();
    $schedule->add_recurrence_rule(IceCube\Rule::weekly()->day("tuesday")->until(strtotime("2011-12-27")));
    $schedule->add_exception_date(strtotime("2011-08-02"));  // no prime stock on 02/08/2011
    $schedule->add_recurrence_date(strtotime("2011-08-08")); // prime stock on 08/08/2011
    $schedule->add_exception_date(strtotime("2011-08-09"));  // no prime stock on 09/11/2011
    $schedules[] = $s;
    
    $s        = new ScotEID_Schedule(999, "Dingwall - Store Sheep", strtotime("2011-01-26"));
    $schedule = $s->get_schedule();
    $schedule->add_recurrence_rule(IceCube\Rule::weekly()->day("wednesday"));
    $schedules[] = $s;
    
    // Lochmaddy
    $s        = new ScotEID_Schedule(999, "Lochmaddy", strtotime("2011-01-20"));
    $schedule = $s->get_schedule();
    $schedule->add_recurrence_date(strtotime("2011-01-20")); // Cattle & sheep
    $schedule->add_recurrence_date(strtotime("2011-04-22")); // Store cattle & sheep
    $schedule->add_recurrence_date(strtotime("2011-08-31")); // Lamb sale
    $schedule->add_recurrence_date(strtotime("2011-09-26")); // Lambs and ewes
    $schedule->add_recurrence_date(strtotime("2011-11-09")); // Cattle and sheep
    $schedules[] = $s;
    
    // Fort william
    $s        = new ScotEID_Schedule(999, "Fort William", strtotime("2011-02-10")); 
    $schedule = $s->get_schedule();
    $schedule->add_recurrence_date(strtotime("2011-02-10")); // All classes sheep and annual sale of cast rams
    $schedule->add_recurrence_date(strtotime("2011-05-27")); // Store & breeding cattle & sheep
    $schedule->add_recurrence_date(strtotime("2011-09-02")); // First lamb sale
    $schedule->add_recurrence_date(strtotime("2011-09-23")); // Lambs and ewes
    $schedule->add_recurrence_date(strtotime("2011-10-06")); // Lambs and ewes
    $schedule->add_recurrence_date(strtotime("2011-10-28")); // Ewes and lambs
    $schedule->add_recurrence_date(strtotime("2011-11-04")); // All breeds of lambs
    $schedule->add_recurrence_date(strtotime("2011-11-18")); // All classes of sheep
    $schedules[] = $s;
    
    // Orkney
    $s        = new ScotEID_Schedule(999, "Orkney");
    $schedule = $s->get_schedule();
    $schedule->add_recurrence_rule(IceCube\Rule::weekly()->day("monday")->until(strtotime("2011-03-22")));
    $schedule->add_exception_date(strtotime("2011-01-17"));
    $schedule->add_exception_date(strtotime("2011-02-14"));
    $schedule->add_exception_date(strtotime("2011-03-08"));
    $schedules[] = $s;
  }
}
?>