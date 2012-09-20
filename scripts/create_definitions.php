<?php
require 'include/prepend.php';
//
// Scotbeef

require 'php_ice_cube/IceCube.php';

$schedule = new IceCube\Schedule(strtotime("today"));

$schedule->add_recurrence_rule(IceCube\Rule::weekly()->day("monday", "tuesday", "wednesday", "thursday", "friday")->hour_of_day(7));
$schedule->add_exception_date(strtotime("25 December 2011")); // Christmas day
$schedule->add_exception_date(strtotime("25 April 2011")); // Easter Monday
$schedule->add_exception_date(strtotime("02 May 2011")); // May day?
$schedule->add_exception_date(strtotime("26 May 2011")); // "Last monday in May?"
$schedule->add_exception_date(strtotime("29 August 2011")); // "Bank holiday in Sept?"
$schedule->add_exception_date(strtotime("01 January 2012")); // New years day
$schedule->add_exception_date(strtotime("02 January 2012")); // 2nd

$def = new ScotEID_ReadScheduleDefinition();
$def->set_read_location("89/715/8500");
$def->set_schedule($schedule);
$def->save();