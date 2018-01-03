<?php
// GET COUNTRY
$query_getCountry = "SELECT * FROM country";
$getCountry = mysql_query($query_getCountry, $echoloyalty) or die(mysql_error());
$row_getCountry = mysql_fetch_assoc($getCountry);
$getCountryRows  = mysql_num_rows($getCountry);

$countryArr = array();

do{

	array_push($countryArr, array("country" => '1', "id_country" => $row_getCountry['id_country'], "country_name" => $row_getCountry['name']));

} while($row_getCountry = mysql_fetch_assoc($getCountry));

$countryArray = array("requests" => "1", "countryArr" => $countryArr);

array_push($gotdata, $countryArray);

?>