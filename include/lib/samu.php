<?php
function csv_abattoir_samu_sheet($lotdate,$uid)
{
  $query = "SELECT departurecph, dep_keeper_flock_no, business, address2,address3,address4,postcode,name,business_telephone,'sheep',qty_sheep,'', readlocationcph, '', arrivaldate
  FROM tblsslots l left join tblsamholdings h on h.cph = l.departurecph
  where lotdate = '$lotdate' and l.uid = $uid";

  if ($result = dbw_query($query))
  {
    $tmpnam = tempnam("/tmp", "samu");
    $temp   = fopen($tmpnam, "w");

    while($row = mysqli_fetch_row($result)) {
      fputcsv($temp, $row);
    }

    fclose($temp);

    $r = file_get_contents($tmpnam);

    unlink($tmpnam);

    return $r;
  }

  return false;
}
?>
