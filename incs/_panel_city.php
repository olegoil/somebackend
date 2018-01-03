<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

$colname_region = "-1";
if (isset($themsg['region'])) {
  $colname_region = protect($themsg['region']);
}
  
if($colname_getUser5 == 'send') {

  // GET CITY
  $query_getCity = "SELECT * FROM city WHERE id_region = '".$colname_region."'";
  $getCity = mysql_query($query_getCity, $echoloyalty) or die(mysql_error());
  $row_getCity = mysql_fetch_assoc($getCity);
  $getCityRows  = mysql_num_rows($getCity);

  $cityArr = array();

  if($getCityRows > 0) {

	do{

		array_push($cityArr, array("city" => '1', "id_city" => $row_getCity['id_city'], "id_region" => $row_getCity['id_region'], "id_country" => $row_getCity['id_country'], "city_name" => $row_getCity['name']));

	} while($row_getCity = mysql_fetch_assoc($getCity));

	$cityArray = array("requests" => "1", "cityArr" => $cityArr);

	array_push($gotdata, $cityArray);

  }

}

}

?>