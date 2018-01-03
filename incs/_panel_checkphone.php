<?php
$colname_phonenumber = "-1";
  if (isset($themsg['phonenumber'])) {
	$colname_phonenumber = protect($themsg['phonenumber']);
  }

  $query_getOrderPhone = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."' && order_user_phone_phone = '".$colname_phonenumber."' && order_status > '0' ORDER BY order_id DESC";
  $getOrderPhone = mysql_query($query_getOrderPhone, $echoloyalty) or die(mysql_error());
  $row_getOrderPhone = mysql_fetch_assoc($getOrderPhone);
  $getOrderPhoneRows  = mysql_num_rows($getOrderPhone);

  $username = 0;
  $surname = 0;
  $middname = 0;
  $useremail = 0;
  $orders = $getOrderPhoneRows;
  $rejects = 0;
  if($getOrderPhoneRows > 0) {

	$username = $row_getOrderPhone['order_user_name_phone'];
	$surname = $row_getOrderPhone['order_user_surname_phone'];
	$middname = $row_getOrderPhone['order_user_middlename_phone'];
	$useremail = $row_getOrderPhone['order_user_email_phone'];

	do {

	  if($row_getOrderPhone['order_status'] == '3' || $row_getOrderPhone['order_status'] == '4') {
		$rejects = $rejects+1;
	  }

	} while ($row_getOrderPhone = mysql_fetch_assoc($getOrderPhone));

  }

  $newarrmes = array("checkphone" => '1', "username" => $username, "surname" => $surname, "middname" => $middname, "orders" => $orders, "rejects" => $rejects, "useremail" => $useremail);
  array_push($gotdata, $newarrmes);
?>