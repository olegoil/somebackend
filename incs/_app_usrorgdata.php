<?php

	// GET USER
  mysql_select_db($database_echoloyalty, $echoloyalty);
  $query_getUser = "SELECT * FROM users WHERE user_id = '".$colname_getUser."' && user_institution = '".$colname_getUser2."' && user_log_key = '".$colname_getUser3."'";
  $getUser = mysql_query($query_getUser, $echoloyalty) or die(mysql_error());
  $row_getUser = mysql_fetch_assoc($getUser);
  $artNumRows  = mysql_num_rows($getUser);
  // GET ORGANIZATION
  $query_getInst = "SELECT * FROM organizations WHERE org_id = '".$colname_getUser2."'";
  $getInst = mysql_query($query_getInst, $echoloyalty) or die(mysql_error());
  $row_getInst = mysql_fetch_assoc($getInst);
  $getInstRows  = mysql_num_rows($getInst);
  // GET CITY
  $orgCity = $row_getInst['org_city'];
  $orgCountry = $row_getInst['org_country'];
  $query_getCity = "SELECT name FROM city WHERE id_city = '".$orgCity."' && id_country = '".$orgCountry."'";
  $getCity = mysql_query($query_getCity, $echoloyalty) or die(mysql_error());
  $row_getCity = mysql_fetch_assoc($getCity);
  $getCityRows  = mysql_num_rows($getCity);

?>