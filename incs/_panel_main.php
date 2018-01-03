<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

  $colname_me = "-1";
  if (isset($themsg['me'])) {
	$colname_me = $this->protect($themsg['me']);
  }
  $colname_points_cost = "-1";
  if (isset($themsg['points_cost'])) {
	$colname_points_cost = $this->protect($themsg['points_cost']);
  }
  $colname_starting_points = "-1";
  if (isset($themsg['starting_points'])) {
	$colname_starting_points = $this->protect($themsg['starting_points']);
  }
  $colname_money_percent = "-1";
  if (isset($themsg['money_percent'])) {
	$colname_money_percent = $this->protect($themsg['money_percent']);
  }
  $colname_risk_summ = "-1";
  if (isset($themsg['risk_summ'])) {
	$colname_risk_summ = $this->protect($themsg['risk_summ']);
  }
  $colname_org_admin = "0";
  if(isset($themsg->org_admin)) {
	  $colname_org_admin = $this->protect($themsg->org_admin);
  }
  $colname_points_owner = "0";
  if(isset($themsg->points_owner)) {
	  $colname_points_owner = $this->protect($themsg->points_owner);
  }
  $colname_points_involved = "0";
  if(isset($themsg->points_involved)) {
	  $colname_points_involved = $this->protect($themsg->points_involved);
  }
  $colname_points_scan_owner = "0";
  if(isset($themsg->points_scan_owner)) {
	  $colname_points_scan_owner = $this->protect($themsg->points_scan_owner);
  }
  $colname_points_scan_involved = "0";
  if(isset($themsg->points_scan_involved)) {
	  $colname_points_scan_involved = $this->protect($themsg->points_scan_involved);
  }
		
  if($colname_getUser5 == 'set') {

	$updOrg = "UPDATE organizations SET org_money_points='".$colname_points_cost."', org_starting_points='".$colname_starting_points."', org_money_percent='".$colname_money_percent."', org_promo_points_owner='".$colname_points_owner."', org_promo_points_involved='".$colname_points_involved."', org_promo_points_scan_owner='".$colname_points_scan_owner."', org_promo_points_scan_involved='".$colname_points_scan_involved."', org_risk_summ='".$colname_risk_summ."' WHERE org_id = '".$colname_getUser2."'";
	mysql_query($updOrg, $this->echoloyalty) or die(mysql_error());

	$newarrmes = array("requests" => '1', "orgUpd" => '1');
	array_push($gotdata, $newarrmes);

  }

}
else {

	$query_getNewsC = "SELECT news_id FROM news WHERE news_institution = '".$colname_getUser2."' AND news_state > '0' AND news_when > '1'";
	$getNewsC = mysql_query($query_getNewsC, $this->echoloyalty) or die(mysql_error());
	$row_getNewsC = mysql_fetch_assoc($getNewsC);
	$getNewsCRows  = mysql_num_rows($getNewsC);

	$query_getEventsC = "SELECT event_id FROM events WHERE event_institution = '".$colname_getUser2."' AND event_when > '1'";
	$getEventsC = mysql_query($query_getEventsC, $this->echoloyalty) or die(mysql_error());
	$row_getEventsC = mysql_fetch_assoc($getEventsC);
	$getEventsCRows  = mysql_num_rows($getEventsC);

	$query_getPointsC = "SELECT points_id FROM points WHERE points_institution = '".$colname_getUser2."'";
	$getPointsC = mysql_query($query_getPointsC, $this->echoloyalty) or die(mysql_error());
	$row_getPointsC = mysql_fetch_assoc($getPointsC);
	$getPointsCRows  = mysql_num_rows($getPointsC);

	$query_getPushC = "SELECT push_id FROM pushmessages WHERE push_institution = '".$colname_getUser2."'";
	$getPushC = mysql_query($query_getPushC, $this->echoloyalty) or die(mysql_error());
	$row_getPushC = mysql_fetch_assoc($getPushC);
	$getPushCRows  = mysql_num_rows($getPushC);

	$query_getInstallsC = "SELECT user_id FROM users WHERE user_institution = '".$colname_getUser2."'";
	$getInstallsC = mysql_query($query_getInstallsC, $this->echoloyalty) or die(mysql_error());
	$row_getInstallsC = mysql_fetch_assoc($getInstallsC);
	$getInstallsCRows  = mysql_num_rows($getInstallsC);

	// POINTS
	$query_getRevC = "SELECT SUM(points_bill) AS TotalBills FROM points WHERE points_institution = '".$colname_getUser2."' && points_proofed='1' && points_status='0'";
	$getRevC = mysql_query($query_getRevC, $this->echoloyalty) or die(mysql_error());
	$row_getRevC = mysql_fetch_assoc($getRevC);
	$getRevCRows  = mysql_num_rows($getRevC);
	
	// SHARES ALL
	$query_getShareAC = "SELECT share_id FROM shares WHERE share_institution = '".$colname_getUser2."'";
	$getShareAC = mysql_query($query_getShareAC, $this->echoloyalty) or die(mysql_error());
	$row_getShareAC = mysql_fetch_assoc($getShareAC);
	$getShareACRows  = mysql_num_rows($getShareAC);
	
	$usramountarr = array();
	$revarr = array();
	$loyalusr = array();
	$shareusr = array();
	
	for ($x = 0; $x <= 30; $x++) {
	  // LAST 30 DAYS
	  $timeamount = time() - $x*86400;
	  $afterday = $timeamount + 86400;
	  // USERS
	  $query_getUsers = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_reg < '".$timeamount."' && user_work_pos = '0'";
	  $getUsers = mysql_query($query_getUsers, $this->echoloyalty) or die(mysql_error());
	  $row_getUsers = mysql_fetch_assoc($getUsers);
	  $getUsersRows  = mysql_num_rows($getUsers);
	  
	  // TIME - PEOPLE ADD TO ARRAY
	  $usramountarr[$timeamount] = $getUsersRows;
	  
	  if($x < 20) {
		// POINTS
		$query_getBillsC = "SELECT SUM(points_bill) AS TotalBill FROM points WHERE points_institution = '".$colname_getUser2."' && points_when > '".$timeamount."' && points_when < '".$afterday."' && points_comment != '100'";
		$getBillsC = mysql_query($query_getBillsC, $this->echoloyalty) or die(mysql_error());
		$row_getBillsC = mysql_fetch_assoc($getBillsC);
		$getBillsCRows  = mysql_num_rows($getBillsC);
		
		// BILLS ADD TO ARRAY
		if($getBillsCRows > 0 && !empty($row_getBillsC['TotalBill'])) {
		  array_push($revarr, $row_getBillsC['TotalBill']);
		}
		else {
		  array_push($revarr, 0);
		}
		
		// LOYAL USER
		$query_getLoyalC = "SELECT points_user, count(points_user) AS cnt FROM points WHERE points_institution = '".$colname_getUser2."' && points_when > '".$timeamount."' && points_when < '".$afterday."' GROUP BY points_user HAVING COUNT(points_user)>1";
		$getLoyalC = mysql_query($query_getLoyalC, $this->echoloyalty) or die(mysql_error());
		$row_getLoyalC = mysql_fetch_assoc($getLoyalC);
		$getLoyalCRows  = mysql_num_rows($getLoyalC);
		
		if($row_getLoyalC['cnt'] === NULL) {
		  array_push($loyalusr, "0");
		}
		else {
		  array_push($loyalusr, $row_getLoyalC['cnt']);
		}
		
		// SHARES
		$query_getShareC = "SELECT share_id FROM shares WHERE share_institution = '".$colname_getUser2."' && share_when > '".$timeamount."' && share_when < '".$afterday."'";
		$getShareC = mysql_query($query_getShareC, $this->echoloyalty) or die(mysql_error());
		$row_getShareC = mysql_fetch_assoc($getShareC);
		$getShareCRows  = mysql_num_rows($getShareC);
		
		// SHARES ADD TO ARRAY
		if($getShareCRows > 0 && !empty($row_getShareC['share_id'])) {
		  array_push($shareusr, $getShareCRows);
		}
		else {
		  array_push($shareusr, 0);
		}
	  
	  }
	  
	}

	// LOYAL USER 45 DAYS
	$twoweeksago = $when - 60*60*24*45;
	$query_getLoyalCount = "SELECT points_user, count(points_user) AS cntusr FROM points WHERE points_institution = '".$colname_getUser2."' && points_comment='0' && points_got_spend='0' && points_when > '".$twoweeksago."' GROUP BY points_user HAVING COUNT(points_user)>1";
	$getLoyalCount = mysql_query($query_getLoyalCount, $this->echoloyalty) or die(mysql_error());
	$row_getLoyalCount = mysql_fetch_assoc($getLoyalCount);
	$getLoyalCountRows  = mysql_num_rows($getLoyalCount);

	$newarrmes = array("newsC" => $getNewsCRows, "eventsC" => $getEventsCRows, "pointsC" => $getPointsCRows, "pushC" => $getPushCRows, "revC" => $row_getRevC['TotalBills'], "shareAC" => $getShareACRows, "shareC" => $shareusr, "billC" => $revarr, "loyalC" => $loyalusr, "loyalCount" => $getLoyalCountRows, "usrC" => $usramountarr, "instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "org_points_cost" => $row_getInst['org_money_points'], "org_starting_points" => $row_getInst['org_starting_points'], "org_money_percent" => $row_getInst['org_money_percent'], "org_risk_summ" => $row_getInst['org_risk_summ'], "org_admin" => $row_getInst['org_admin'], "share_promo_code" => $row_getInst['org_promo_points_owner'], "use_promo_code" => $row_getInst['org_promo_points_involved'], "involved_promo_code" => $row_getInst['org_promo_points_scan_involved'], "owner_promo_code" => $row_getInst['org_promo_points_scan_owner']);
	array_push($gotdata, $newarrmes);

}

?>