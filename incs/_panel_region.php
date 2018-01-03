<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {
  
$colname_country = "-1";
if (isset($themsg['country'])) {
  $colname_country = protect($themsg['country']);
}

if($colname_getUser5 == 'send') {

  // GET REGION
  $query_getRegion = "SELECT * FROM region WHERE id_country = '".$colname_country."'";
  $getRegion = mysql_query($query_getRegion, $echoloyalty) or die(mysql_error());
  $row_getRegion = mysql_fetch_assoc($getRegion);
  $getRegionRows  = mysql_num_rows($getRegion);

  $regArr = array();

  if($getRegionRows > 0) {

	  do {

		  array_push($regArr, array("region" => '1', "id_region" => $row_getRegion['id_region'], "id_country" => $row_getRegion['id_country'], "region_name" => $row_getRegion['name']));

	  } while($row_getRegion = mysql_fetch_assoc($getRegion));

	  $regionArray = array("requests" => "1", "regionArr" => $regArr);

	  array_push($gotdata, $regionArray);

  }

}

}
 
?>