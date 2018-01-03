<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

  $colname_scheduleid = "-1";
  if (isset($themsg['scheduleid'])) {
	$colname_scheduleid = protect($themsg['scheduleid']);
  }
  $colname_scheduleemployee = "-1";
  if (isset($themsg['scheduleemployee'])) {
	$colname_scheduleemployee = protect($themsg['scheduleemployee']);
  }
  $colname_schedulemenue = "-1";
  if (isset($themsg['schedulemenue'])) {
	$colname_schedulemenue = protect($themsg['schedulemenue']);
  }
  $colname_scheduleoffice = "-1";
  if (isset($themsg['scheduleoffice'])) {
	$colname_scheduleoffice = protect($themsg['scheduleoffice']);
  }
  $colname_schedulestart = "-1";
  if (isset($themsg['schedulestart'])) {
	$colname_schedulestart = protect($themsg['schedulestart']);
  }
  $colname_schedulestop = "-1";
  if (isset($themsg['schedulestop'])) {
	$colname_schedulestop = protect($themsg['schedulestop']);
  }
	
  if($colname_getUser5 == 'del') {

	  $query_getScheduleChng = "SELECT * FROM schedule WHERE schedule_institution = '".$colname_getUser2."' && schedule_id = '".$colname_scheduleid."' LIMIT 1";
	  $getScheduleChng = mysql_query($query_getScheduleChng, $echoloyalty) or die(mysql_error());
	  $row_getScheduleChng = mysql_fetch_assoc($getScheduleChng);
	  $getScheduleChngRows  = mysql_num_rows($getScheduleChng);

	  if($getScheduleChngRows > 0) {

		  $delSchedule = "UPDATE schedule SET schedule_when='1' WHERE schedule_institution = '".$colname_getUser2."' && schedule_id='".$colname_scheduleid."'";
		  mysql_query($delSchedule, $echoloyalty) or die(mysql_error());

		  $newarrmes = array("requests" => '1', "scheduleId" => $colname_scheduleid, "scheduleDel" => '2');
		  array_push($gotdata, $newarrmes);

	  }

  }
  else if($colname_getUser5 == 'create') {

		if($colname_scheduleid == '0') {

		  $insSchedule = "INSERT INTO schedule (schedule_employee, schedule_menue, schedule_office, schedule_start, schedule_stop, schedule_institution, schedule_when) VALUES ('".$colname_scheduleemployee."', '".$colname_schedulemenue."', '".$colname_scheduleoffice."', '".$colname_schedulestart."', '".$colname_schedulestop."', '".$colname_getUser2."', '".$when."')";
		  mysql_query($insSchedule, $echoloyalty) or die(mysql_error());

		  $query_getScheduleChng = "SELECT * FROM schedule WHERE schedule_institution = '".$colname_getUser2."' && schedule_when = '".$when."' ORDER BY schedule_id DESC LIMIT 1";
		  $getScheduleChng = mysql_query($query_getScheduleChng, $echoloyalty) or die(mysql_error());
		  $row_getScheduleChng = mysql_fetch_assoc($getScheduleChng);
		  $getScheduleChngRows  = mysql_num_rows($getScheduleChng);

		  if($getScheduleChngRows > 0) {

			$newarrmes = array("requests" => '1', "orderId" => $row_getScheduleChng['schedule_id'], "orderIns" => '1', 'when' => $when);
			array_push($gotdata, $newarrmes);

		  }

		}

  }
  else if($colname_getUser5 == 'change') {

		if($colname_scheduleid != '0') {
		
		  $query_getScheduleChng = "SELECT * FROM schedule WHERE schedule_institution = '".$colname_getUser2."' && schedule_id = '".$colname_scheduleid."' ORDER BY schedule_id DESC LIMIT 1";
		  $getScheduleChng = mysql_query($query_getScheduleChng, $echoloyalty) or die(mysql_error());
		  $row_getScheduleChng = mysql_fetch_assoc($getScheduleChng);
		  $getScheduleChngRows  = mysql_num_rows($getScheduleChng);

		  if($getScheduleChngRows > 0) {

			  $updSchedule = "UPDATE schedule SET schedule_employee='".$colname_scheduleemployee."', schedule_menue='".$colname_schedulemenue."', schedule_office='".$colname_scheduleoffice."', schedule_start='".$colname_schedulestart."', schedule_stop='".$colname_schedulestop."', schedule_when='".$when."' WHERE schedule_id = '".$colname_scheduleid."'";
			   mysql_query($updSchedule, $echoloyalty) or die(mysql_error());

			  $newarrmes = array("requests" => '1', "orderId" => $colname_scheduleid, "orderUpd" => '1', "when" => $when);
			  array_push($gotdata, $newarrmes);

		  }

		}
	
  }

}
else {
  
  // ORDERS
  $query_getOrder = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."' && order_when > '1' && order_del = '0'";
  $getOrder = mysql_query($query_getOrder, $echoloyalty) or die(mysql_error());
  $row_getOrder = mysql_fetch_assoc($getOrder);
  $getOrderRows = mysql_num_rows($getOrder);
		  
  $orderarr = array();
  if($getOrderRows > 0) {

		do {

		  // USER DATA
		  $query_getUsrData = "SELECT * FROM users WHERE user_id = '".$row_getOrder['order_user']."'";
		  $getUsrData = mysql_query($query_getUsrData, $echoloyalty) or die(mysql_error());
		  $row_getUsrData = mysql_fetch_assoc($getUsrData);
		  $getUsrDataRows  = mysql_num_rows($getUsrData);

		  $usrpic = 'user.png';
		  $usrmob = $row_getOrder['order_user_phone_phone'];
		  $usrname = $row_getOrder['order_user_name_phone'];
		  $usrsurname = $row_getOrder['order_user_surname_phone'];

		  if($getUsrDataRows > 0) {

				if(isset($row_getOrder['order_mobile']) && $row_getOrder['order_mobile'] == '1') {
				  $usrpic = $row_getUsrData['user_pic'];
				  $usrname = $row_getUsrData['user_name'];
				  $usrsurname = $row_getUsrData['user_surname'];
				  $usrmob = $row_getUsrData['user_mob'];
				}

		  }

		  $org_office = $row_getInst['org_name'];
		  $org_office_id = $row_getInst['org_id'];

		  $query_getOfficeData = "SELECT * FROM organizations_office WHERE office_id = '".$row_getOrder['order_office']."'";
		  $getOfficeData = mysql_query($query_getOfficeData, $echoloyalty) or die(mysql_error());
		  $row_getOfficeData = mysql_fetch_assoc($getOfficeData);
		  $getOfficeDataRows  = mysql_num_rows($getOfficeData);

		  if($getOfficeDataRows > 0) {
			$org_office = $row_getOfficeData['office_name'];
			$org_office_id = $row_getOfficeData['office_id'];
		  }
		  
		   array_push($orderarr, array("order_id" => $row_getOrder['order_id'], "user_mob" => $usrmob, "order_user_phone_phone" => $row_getOrder['order_user_phone_phone'], "order_user_email_phone" => $row_getOrder['order_user_email_phone'], "order_user" => $row_getOrder['order_user'], "order_user_pic" => $usrpic, "order_user_name" => $usrname, "order_user_name_phone" => $row_getOrder['order_user_name_phone'], "order_user_surname_phone" => $row_getOrder['order_user_surname_phone'], "order_name" => $row_getOrder['order_name'], "order_user_surname" => $usrsurname, "order_desc" => $row_getOrder['order_desc'], "order_worker" => $row_getOrder['order_worker'], "order_institution" => $row_getOrder['order_institution'], "order_office_name" => $org_office, "order_office_id" => $org_office_id, "order_bill" => $row_getOrder['order_bill'], "order_goods" => $row_getOrder['order_goods'], "order_cats" => $row_getOrder['order_cats'], "order_order" => $row_getOrder['order_order'], "order_status" => $row_getOrder['order_status'], "order_start" => $row_getOrder['order_start'], "order_end" => $row_getOrder['order_end'], "order_allday" => $row_getOrder['order_allday'], "order_mobile" => $row_getOrder['order_mobile'], "order_when" => $row_getOrder['order_when'], "order_del" => $row_getOrder['order_del']));
		
		} while ($row_getOrder = mysql_fetch_assoc($getOrder));

  }

  $query_getSchedule = "SELECT * FROM schedule WHERE schedule_institution = '".$colname_getUser2."' && schedule_when > '2'";
  $getSchedule = mysql_query($query_getSchedule, $echoloyalty) or die(mysql_error());
  $row_getSchedule = mysql_fetch_assoc($getSchedule);
  $getScheduleRows = mysql_num_rows($getSchedule);
		  
  $schedulearr = array();
  if($getScheduleRows > 0) {

		do {

		  $org_office = $row_getInst['org_name'];
		  $org_office_id = $row_getInst['org_id'];

		  $query_getOfficeData = "SELECT * FROM organizations_office WHERE office_id = '".$row_getSchedule['schedule_office']."'";
		  $getOfficeData = mysql_query($query_getOfficeData, $echoloyalty) or die(mysql_error());
		  $row_getOfficeData = mysql_fetch_assoc($getOfficeData);
		  $getOfficeDataRows  = mysql_num_rows($getOfficeData);

		  if($getOfficeDataRows > 0) {
			$org_office = $row_getOfficeData['office_name'];
			$org_office_id = $row_getOfficeData['office_id'];
		  }
		  
		  array_push($schedulearr, array("schedule_id" => $row_getSchedule['schedule_id'], "schedule_employee" => $row_getSchedule['schedule_employee'], "schedule_menue" => $row_getSchedule['schedule_menue'], "schedule_start" => $row_getSchedule['schedule_start'], "schedule_stop" => $row_getSchedule['schedule_stop'], "schedule_institution" => $row_getSchedule['schedule_institution'], "schedule_when" => $row_getSchedule['schedule_when']));
		
		} while ($row_getSchedule = mysql_fetch_assoc($getSchedule));

  }

  $query_getUsersC = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_work_pos >= '2' && user_reg > '0' && user_del='0'";
  $getUsersC = mysql_query($query_getUsersC, $echoloyalty) or die(mysql_error());
  $row_getUsersC = mysql_fetch_assoc($getUsersC);
  $getUsersCRows  = mysql_num_rows($getUsersC);

  $usrsarr = array();
  if($getUsersCRows > 0) {

		do {

		  // GET PROFESSION
		  $query_getProf = "SELECT * FROM professions WHERE prof_id = '".$row_getUsersC['user_work_pos']."' && prof_when > '2' && (prof_institution = '0' OR prof_institution = '".$colname_getUser2."')";
		  $getProf = mysql_query($query_getProf, $echoloyalty) or die(mysql_error());
		  $row_getUProf = mysql_fetch_assoc($getProf);
		  $getProfRows  = mysql_num_rows($getProf);

		  $prof = '';
		  if($getProfRows > 0) {
			$prof = $row_getUProf['prof_name'];
		  }

		  array_push($usrsarr, array("user_id" => $row_getUsersC['user_id'], "user_name" => $row_getUsersC['user_name'], "user_surname" => $row_getUsersC['user_surname'], "user_middlename" => $row_getUsersC['user_middlename'], "user_mob" => $row_getUsersC['user_mob'], "user_institution" => $row_getUsersC['user_institution'], "user_pic" => $row_getUsersC['user_pic'], "user_profession" => $prof));

		} while ($row_getUsersC = mysql_fetch_assoc($getUsersC));

  }

  $newarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "scheduleAll" => $schedulearr, "workersAll" => $usrsarr, "orderAll" => $orderarr);
  array_push($gotdata, $newarrmes);

}

?>