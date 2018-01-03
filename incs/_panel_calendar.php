<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

  $colname_sorderid = "0";
  if (isset($themsg['sorderid'])) {
	$colname_sorderid = protect($themsg['sorderid']);
  }
  $colname_suser = "0";
  if (isset($themsg['suser'])) {
	$colname_suser = protect($themsg['suser']);
  }
  $colname_susername = "0";
  if (isset($themsg['susername'])) {
	$colname_susername = protect($themsg['susername']);
  }
  $colname_susersur = "0";
  if (isset($themsg['susersur'])) {
	$colname_susersur = protect($themsg['susersur']);
  }
  $colname_susermidd = "0";
  if (isset($themsg['susermidd'])) {
	$colname_susermidd = protect($themsg['susermidd']);
  }
  $colname_susermob = "0";
  if (isset($themsg['susermob'])) {
	$colname_susermob = protect($themsg['susermob']);
  }
  $colname_susermail = "0";
  if (isset($themsg['susermail'])) {
	$colname_susermail = protect($themsg['susermail']);
  }
  $colname_stitle = "0";
  if (isset($themsg['stitle'])) {
	$colname_stitle = protect($themsg['stitle']);
  }
  $colname_sdescr = "0";
  if (isset($themsg['sdescr'])) {
	$colname_sdescr = protect($themsg['sdescr']);
  }
  $colname_soffice = "0";
  if (isset($themsg['soffice'])) {
	$colname_soffice = protect($themsg['soffice']);
  }
  $colname_sgoods = "0";
  if (isset($themsg['sgoods'])) {
	$colname_sgoods = protect($themsg['sgoods']);
  }
  $colname_scats = "0";
  if (isset($themsg['scats'])) {
	$colname_scats = protect($themsg['scats']);
  }
  $colname_smenue = "0";
  if (isset($themsg['smenue'])) {
	$colname_smenue = protect($themsg['smenue']);
  }
  $colname_sworkerid = "0";
  if (isset($themsg['sworkerid'])) {
	$colname_sworkerid = protect($themsg['sworkerid']);
  }
  $colname_sbill = "0";
  if (isset($themsg['sbill'])) {
	$colname_sbill = protect($themsg['sbill']);
  }
  $colname_sstatus = "0";
  if (isset($themsg['sstatus'])) {
	$colname_sstatus = protect($themsg['sstatus']);
  }
  $colname_start = "0";
  if (isset($themsg['start'])) {
	$colname_start = protect($themsg['start']);
  }
  $colname_stop = "0";
  if (isset($themsg['stop'])) {
	$colname_stop = protect($themsg['stop']);
  }
  $colname_sallday = "0";
  if (isset($themsg['sallday'])) {
	$colname_sallday = protect($themsg['sallday']);
  }
  $colname_lastOrder = "0";
  if (isset($themsg['lastOrder'])) {
	$colname_lastOrder = protect($themsg['lastOrder']);
  }
	
  if($colname_getUser5 == 'del') {

	  $query_getOrderChng = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."' && order_id = '".$colname_sorderid."' LIMIT 1";
	  $getOrderChng = mysql_query($query_getOrderChng, $echoloyalty) or die(mysql_error());
	  $row_getOrderChng = mysql_fetch_assoc($getOrderChng);
	  $getOrderChngRows  = mysql_num_rows($getOrderChng);

	  if($getOrderChngRows > 0) {

		  $delOrder = "UPDATE ordering SET order_when='".$when."', order_del='1', order_status='0' WHERE order_institution = '".$colname_getUser2."' && order_id='".$colname_sorderid."'";
		  mysql_query($delOrder, $echoloyalty) or die(mysql_error());

		  $newarrmes = array("requests" => '1', "orderId" => $colname_sorderid, "orderUpd" => '2');
		  array_push($gotdata, $newarrmes);

	  }

  }
  else if($colname_getUser5 == 'create') {

	if($colname_sorderid == '0') {

	  $insOrder = "INSERT INTO ordering (order_user, order_name, order_user_name_phone, order_user_surname_phone, order_user_middlename_phone, order_user_phone_phone, order_user_email_phone, order_desc, order_worker, order_institution, order_office, order_bill, order_goods, order_cats, order_order, order_status, order_start, order_end, order_allday, order_mobile, order_when) VALUES ('".$colname_suser."', '".$colname_stitle."', '".$colname_susername."', '".$colname_susersur."', '".$colname_susermidd."', '".$colname_susermob."', '".$colname_susermail."', '".$colname_sdescr."', '".$colname_sworkerid."', '".$colname_getUser2."', '".$colname_soffice."', '".$colname_sbill."', '".$colname_sgoods."', '".$colname_scats."', '".$colname_smenue."', '".$colname_sstatus."', '".$colname_start."', '".$colname_stop."', '".$colname_sallday."', '0', '".$when."')";
	  mysql_query($insOrder, $echoloyalty) or die(mysql_error());

	  $query_getOrderChng = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."' && order_user = '".$colname_suser."' && order_user_phone_phone = '".$colname_susermob."' && order_when = '".$when."' ORDER BY order_id DESC LIMIT 1";
	  $getOrderChng = mysql_query($query_getOrderChng, $echoloyalty) or die(mysql_error());
	  $row_getOrderChng = mysql_fetch_assoc($getOrderChng);
	  $getOrderChngRows  = mysql_num_rows($getOrderChng);

	  if($getOrderChngRows > 0) {

		$newarrmes = array("requests" => '1', "orderId" => $row_getOrderChng['order_id'], "orderIns" => '1');
		array_push($gotdata, $newarrmes);

	  }

	}

  }
  else if($colname_getUser5 == 'change') {

	if($colname_sorderid != '0') {
	
	  $query_getOrderChng = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."' && order_id = '".$colname_sorderid."' LIMIT 1";
	  $getOrderChng = mysql_query($query_getOrderChng, $echoloyalty) or die(mysql_error());
	  $row_getOrderChng = mysql_fetch_assoc($getOrderChng);
	  $getOrderChngRows  = mysql_num_rows($getOrderChng);

	  if($getOrderChngRows > 0) {

		  $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_id = '".$colname_suser."'";
		  $getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
		  $row_getGCM = mysql_fetch_assoc($getGCM);
		  $getGCMRows  = mysql_num_rows($getGCM);

		  if($getGCMRows > 0) {

			$ordertxt = '';
			if($colname_sstatus == '2') {
			  $ordertxt = ' одобрена';
			}
			else if($colname_sstatus == '3') {
			  $ordertxt = ' отклонена';
			}

			$apiKey =  urldecode($row_getInst['org_key']);
			
			$title = urldecode("Ваша запись!");

			$messageand = urldecode($ordertxt);

			// SENDING
			do {

			  if($row_getGCM['user_device_os'] == 'Android') {

				  // ANDROID PUSH
				  $registrationId = urldecode($row_getGCM['user_gcm']);

				  // ANDROID SETTINGS
				  $headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
				  $data = array(
					  'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title)),
					  'registration_ids' => array($registrationId)
				  );

				  $ch = curl_init();

				  curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
				  curl_setopt( $ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
				  curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
				  curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
				  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				  curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );

				  $response = curl_exec($ch);
				  curl_close($ch);

			  }
			  elseif($row_getGCM['user_device_os'] == 'iOS') {

				array_push($iosIDs, $row_getGCM['user_gcm']);

			  }

			} while ($row_getGCM = mysql_fetch_assoc($getGCM));

			sendGCM(0, $iosIDs, $row_getInst['org_cert'], "Ваша запись!", $ordertxt, 0, 0);

		  }

		  $updOrder = "UPDATE ordering SET order_user='".$colname_suser."', order_name='".$colname_stitle."', order_user_name_phone='".$colname_susername."', order_user_surname_phone='".$colname_susersur."', order_user_middlename_phone='".$colname_susermidd."', order_user_phone_phone='".$colname_susermob."', order_user_email_phone='".$colname_susermail."', order_desc='".$colname_sdescr."', order_worker='".$colname_sworkerid."', order_institution='".$colname_getUser2."', order_office='".$colname_soffice."', order_bill='".$colname_sbill."', order_goods='".$colname_sgoods."', order_cats='".$colname_scats."', order_order='".$colname_smenue."', order_status='".$colname_sstatus."', order_start='".$colname_start."', order_end='".$colname_stop."', order_allday='".$colname_sallday."', order_when='".$when."' WHERE order_id = '".$colname_sorderid."'";
		   mysql_query($updOrder, $echoloyalty) or die(mysql_error());

		  $newarrmes = array("requests" => '1', "orderId" => $colname_sorderid, "orderUpd" => '1');
		  array_push($gotdata, $newarrmes);

	  }

	}
	
  }
  else if($colname_getUser5 == 'refreshing') {

	// ORDERS
	$query_getOrder = "SELECT * FROM ordering WHERE (order_institution = '".$colname_getUser2."' && order_when > '".$colname_lastOrder."' && order_mobile = '1' && order_status = '0' && order_del = '0') OR (order_institution = '".$colname_getUser2."' && order_when > '".$colname_lastOrder."' && order_mobile = '1' && order_status = '4' && order_del = '0')";
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
		$usrmob = '';
		$usrname = $row_getOrder['order_user'];
		$usrsurname = '';
		$usrmiddname = '';

		if($getUsrDataRows > 0) {

		  if(isset($row_getOrder['order_mobile']) && $row_getOrder['order_mobile'] == '1') {
			$usrpic = $row_getUsrData['user_pic'];
			$usrname = $row_getUsrData['user_name'];
			$usrsurname = $row_getUsrData['user_surname'];
			$usrmiddname = $row_getUsrData['user_middlename'];
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
		
		array_push($orderarr, array("order_id" => $row_getOrder['order_id'], "user_mob" => $usrmob, "order_user_phone_phone" => $row_getOrder['order_user_phone_phone'], "order_user_email_phone" => $row_getOrder['order_user_email_phone'], "order_user" => $row_getOrder['order_user'], "order_user_pic" => $usrpic, "order_user_name" => $usrname, "order_user_name_phone" => $row_getOrder['order_user_name_phone'], "order_user_surname_phone" => $row_getOrder['order_user_surname_phone'], "order_user_middlename_phone" => $row_getOrder['order_user_middlename_phone'], "order_name" => $row_getOrder['order_name'], "order_user_surname" => $usrsurname, "order_user_middlename" => $usrmiddname, "order_desc" => $row_getOrder['order_desc'], "order_worker" => $row_getOrder['order_worker'], "order_institution" => $row_getOrder['order_institution'], "order_office_name" => $org_office, "order_office_id" => $org_office_id, "order_bill" => $row_getOrder['order_bill'], "order_goods" => $row_getOrder['order_goods'], "order_cats" => $row_getOrder['order_cats'], "order_order" => $row_getOrder['order_order'], "order_status" => $row_getOrder['order_status'], "order_start" => $row_getOrder['order_start'], "order_end" => $row_getOrder['order_end'], "order_allday" => $row_getOrder['order_allday'], "order_mobile" => $row_getOrder['order_mobile'], "order_when" => $row_getOrder['order_when'], "order_del" => $row_getOrder['order_del']));
	  
	  } while ($row_getOrder = mysql_fetch_assoc($getOrder));

	}

	$newarrmes = array("orderNewRows" => $getOrderRows, "orderNew" => $orderarr);
	array_push($gotdata, $newarrmes);

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
	  $usrmiddname = $row_getOrder['order_user_middname_phone'];

	  if($getUsrDataRows > 0) {

		if(isset($row_getOrder['order_mobile']) && $row_getOrder['order_mobile'] == '1') {
		  $usrpic = $row_getUsrData['user_pic'];
		  $usrname = $row_getUsrData['user_name'];
		  $usrsurname = $row_getUsrData['user_surname'];
		  $usrmiddname = $row_getUsrData['user_middlename'];
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
	  
	   array_push($orderarr, array("order_id" => $row_getOrder['order_id'], "user_mob" => $usrmob, "order_user_phone_phone" => $row_getOrder['order_user_phone_phone'], "order_user_email_phone" => $row_getOrder['order_user_email_phone'], "order_user" => $row_getOrder['order_user'], "order_user_pic" => $usrpic, "order_user_name" => $usrname, "order_user_name_phone" => $row_getOrder['order_user_name_phone'], "order_user_surname_phone" => $row_getOrder['order_user_surname_phone'], "order_user_middlename_phone" => $row_getOrder['order_user_middlename_phone'], "order_name" => $row_getOrder['order_name'], "order_user_surname" => $usrsurname, "order_user_middlename" => $usrmiddname, "order_desc" => $row_getOrder['order_desc'], "order_worker" => $row_getOrder['order_worker'], "order_institution" => $row_getOrder['order_institution'], "order_office_name" => $org_office, "order_office_id" => $org_office_id, "order_bill" => $row_getOrder['order_bill'], "order_goods" => $row_getOrder['order_goods'], "order_cats" => $row_getOrder['order_cats'], "order_order" => $row_getOrder['order_order'], "order_status" => $row_getOrder['order_status'], "order_start" => $row_getOrder['order_start'], "order_end" => $row_getOrder['order_end'], "order_allday" => $row_getOrder['order_allday'], "order_mobile" => $row_getOrder['order_mobile'], "order_when" => $row_getOrder['order_when'], "order_del" => $row_getOrder['order_del']));
	
	} while ($row_getOrder = mysql_fetch_assoc($getOrder));

  }
  // WORKERS
  $query_getUsersC = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_work_pos >= '2'";
  $getUsersC = mysql_query($query_getUsersC, $echoloyalty) or die(mysql_error());
  $row_getUsersC = mysql_fetch_assoc($getUsersC);
  $getUsersCRows  = mysql_num_rows($getUsersC);

  $usrsarr = array();
  if($getUsersCRows > 0) {

	$workers = array();
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

	  array_push($usrsarr, array("user_id" => $row_getUsersC['user_id'], "user_name" => $row_getUsersC['user_name'], "user_surname" => $row_getUsersC['user_surname'], "user_email" => $row_getUsersC['user_email'], "user_tel" => $row_getUsersC['user_tel'], "user_mob" => $row_getUsersC['user_mob'], "user_gender" => $row_getUsersC['user_gender'], "user_adress" => $row_getUsersC['user_adress'], "user_reg" => $row_getUsersC['user_reg'], "user_pic" => $row_getUsersC['user_pic'], "user_work_pos" => $row_getUsersC['user_work_pos'], "user_menue_exe" => $row_getUsersC['user_menue_exe'], "user_institution" => $row_getUsersC['user_institution'], "user_office" => $row_getUsersC['user_office'], "user_profession" => $prof));

	} while ($row_getUsersC = mysql_fetch_assoc($getUsersC));

	array_push($usrsarr, $workers);

  }
  // OFFICES
  $query_getOffice = "SELECT * FROM organizations_office WHERE office_institution = '".$colname_getUser2."' && office_reg > '2'";
  $getOffice = mysql_query($query_getOffice, $echoloyalty) or die(mysql_error());
  $row_getOffice = mysql_fetch_assoc($getOffice);
  $getOfficeRows = mysql_num_rows($getOffice);

  $officearr = array();
  if($getOfficeRows > 0) {
  
	do {
	  
	  array_push($officearr, array($row_getOffice['office_id'], $row_getOffice['office_name'], $row_getOffice['office_start'], $row_getOffice['office_stop'], $row_getOffice['office_country'], $row_getOffice['office_city'], $row_getOffice['office_adress'], $row_getOffice['office_timezone'], $row_getOffice['office_tel'], $row_getOffice['office_fax'], $row_getOffice['office_mob'], $row_getOffice['office_email'], $row_getOffice['office_pwd'], $row_getOffice['office_skype'], $row_getOffice['office_site'], $row_getOffice['office_tax_id'], $row_getOffice['office_logo'], $row_getOffice['office_institution'], $row_getOffice['office_log'], $row_getOffice['office_reg']));
	  
	} while ($row_getOffice = mysql_fetch_assoc($getOffice));
  
  }
  // MENUE
  $query_getMenueC = "SELECT * FROM menue WHERE menue_institution = '".$colname_getUser2."' && menue_when > '1'";
  $getMenueC = mysql_query($query_getMenueC, $echoloyalty) or die(mysql_error());
  $row_getMenueC = mysql_fetch_assoc($getMenueC);
  $getMenueCRows  = mysql_num_rows($getMenueC);
  
  $menuearr = array();
  if($getMenueCRows > 0) {

	do {

	  $query_getCatChng = "SELECT * FROM categories WHERE cat_institution = '".$colname_getUser2."' && cat_id = '".$row_getMenueC['menue_cat']."' LIMIT 1";
	  $getCatChng = mysql_query($query_getCatChng, $echoloyalty) or die(mysql_error());
	  $row_getCatChng = mysql_fetch_assoc($getCatChng);
	  $getCatChngRows  = mysql_num_rows($getCatChng);
	  
	  array_push($menuearr, array($row_getMenueC['menue_id'], $row_getMenueC['menue_name'], $row_getMenueC['menue_desc'], $row_getMenueC['menue_pic'], $row_getMenueC['menue_institution'], $row_getMenueC['menue_when'], $row_getCatChng['cat_name'], $row_getMenueC['menue_size'], $row_getMenueC['menue_cost'], $row_getMenueC['menue_weight'], $row_getMenueC['menue_discount'], $row_getMenueC['menue_action'], $row_getMenueC['menue_code'], $row_getCatChng['cat_ingr'], $row_getMenueC['menue_cat'], $row_getMenueC['menue_costs']));
		
	} while ($row_getMenueC = mysql_fetch_assoc($getMenueC));

  }
  // CATEGORIES
  $query_getCatC = "SELECT * FROM categories WHERE cat_institution = '".$colname_getUser2."' && cat_when > '1'";
  $getCatC = mysql_query($query_getCatC, $echoloyalty) or die(mysql_error());
  $row_getCatC = mysql_fetch_assoc($getCatC);
  $getCatCRows  = mysql_num_rows($getCatC);
  
  $catarr = array();
  if($getCatCRows > 0) {

	  do {
		  
		  array_push($catarr, array("cat_id" => $row_getCatC['cat_id'], "cat_name" => $row_getCatC['cat_name'], "cat_desc" => $row_getCatC['cat_desc'], "cat_pic" => $row_getCatC['cat_pic'], "cat_ingr" => $row_getCatC['cat_ingr'], "cat_inst" => $row_getCatC['cat_institution'], "cat_when" => $row_getCatC['cat_when']));
		  
	  } while ($row_getCatC = mysql_fetch_assoc($getCatC));

  }
  // GOODS
  $query_getGroupC = "SELECT * FROM goods WHERE goods_institution = '".$colname_getUser2."' && goods_when > '1'";
  $getGroupC = mysql_query($query_getGroupC, $echoloyalty) or die(mysql_error());
  $row_getGroupC = mysql_fetch_assoc($getGroupC);
  $getGroupCRows  = mysql_num_rows($getGroupC);
  
  $grouparr = array();
  if($getGroupCRows > 0) {

	  do {
		  
		  array_push($grouparr, array("goods_id" => $row_getGroupC['goods_id'], "goods_name" => $row_getGroupC['goods_name'], "goods_desc" => $row_getGroupC['goods_desc'], "goods_pic" => $row_getGroupC['goods_pic'], "goods_institution" => $row_getGroupC['goods_institution'], "goods_when" => $row_getGroupC['goods_when']));
		  
	  } while ($row_getGroupC = mysql_fetch_assoc($getGroupC));

  }
  // SCHEDULE
  $query_getSchedule = "SELECT * FROM schedule WHERE schedule_institution = '".$colname_getUser2."' && schedule_when > '2'";
  $getSchedule = mysql_query($query_getSchedule, $echoloyalty) or die(mysql_error());
  $row_getSchedule = mysql_fetch_assoc($getSchedule);
  $getScheduleRows = mysql_num_rows($getSchedule);
		  
  $schedulearr = array();
  if($getScheduleRows > 0) {

	do {
	  
	  array_push($schedulearr, array("schedule_id" => $row_getSchedule['schedule_id'], "schedule_employee" => $row_getSchedule['schedule_employee'], "schedule_menue" => $row_getSchedule['schedule_menue'], "schedule_office" => $row_getSchedule['schedule_office'], "schedule_start" => $row_getSchedule['schedule_start'], "schedule_stop" => $row_getSchedule['schedule_stop'], "schedule_institution" => $row_getSchedule['schedule_institution'], "schedule_when" => $row_getSchedule['schedule_when']));
	
	} while ($row_getSchedule = mysql_fetch_assoc($getSchedule));

  }

  $newarrmes = array("orderC" => $getOrderRows, "instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "orderAll" => $orderarr, "workersAll" => $usrsarr, "officeAll" => $officearr, "menueAll" => $menuearr, "catsAll" => $catarr, "goodsAll" => $grouparr, "scheduleAll" => $schedulearr);
  array_push($gotdata, $newarrmes);

}

?>