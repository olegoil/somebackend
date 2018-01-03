<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

$colname_profid= "-1";
if (isset($themsg['profid'])) {
  $colname_profid = protect($themsg->profid);
}
$colname_usrname2 = "-1";
if (isset($themsg->usrname2)) {
  $colname_usrname2 = protect($themsg->usrname2);
}
$colname_usrsurname = "-1";
if (isset($themsg->usrsurname)) {
  $colname_usrsurname = protect($themsg->usrsurname);
}
$colname_usremail = "-1";
if (isset($themsg->usremail)) {
  $colname_usremail = protect($themsg->usremail);
}
$colname_usrgender = "-1";
if (isset($themsg->usrgender)) {
  $colname_usrgender = protect($themsg->usrgender);
}
$colname_usrbirthday = "-1";
if (isset($themsg->usrbirthday)) {
  $colname_usrbirthday = protect($themsg->usrbirthday);
}
$colname_usrmob = "-1";
if (isset($themsg->usrmob)) {
  $colname_usrmob = protect($themsg->usrmob);
}
$colname_usrtel = "-1";
if (isset($themsg->usrtel)) {
  $colname_usrtel = protect($themsg->usrtel);
}
$colname_usradress = "-1";
if (isset($themsg->usradress)) {
  $colname_usradress = protect($themsg->usradress);
}
$colname_usrc = "-1";
if (isset($themsg->usrc)) {
  $colname_usrc = protect($themsg->usrc);
}
$colname_usrr = "-1";
if (isset($themsg->usrr)) {
  $colname_usrr = protect($themsg->usrr);
}
$colname_usrs = "-1";
if (isset($themsg->usrs)) {
  $colname_usrs = protect($themsg->usrs);
}
$colname_usrdiscount = "-1";
if (isset($themsg->usrdiscount)) {
  $colname_usrdiscount = protect($themsg->usrdiscount);
}
$colname_pswd = "-1";
if (isset($themsg->pswd)) {
  $colname_pswd = protect($themsg->pswd);
}
$colname_usrdis = "-1";
if (isset($themsg->usrdis)) {
  $colname_usrdis = protect($themsg->usrdis);
}
$colname_usrworkpos = "-1";
if (isset($themsg->usrworkpos)) {
  $colname_usrworkpos = protect($themsg->usrworkpos);
}
$colname_usrmenueexe = "-1";
if (isset($themsg->usrmenueexe)) {
  $colname_usrmenueexe = protect($themsg->usrmenueexe);
}
		$colname_usrwallet = "-1";
if (isset($themsg->usrwallet)) {
  $colname_usrwallet = protect($themsg->usrwallet);
}

if($colname_getUser5 == 'send') {

  $profUpd = 0;
		  $pointsComment = 6;

  // GET MEMBER
  $query_getMember = "SELECT * FROM users WHERE user_id = '".$colname_profid."' LIMIT 1";
  $getMember = mysql_query($query_getMember, $echoloyalty) or die(mysql_error());
  $row_getMember = mysql_fetch_assoc($getMember);
  $getMemberRows  = mysql_num_rows($getMember);
  
		  // GET WALLET
  $query_getMemsWallet = "SELECT * FROM wallet WHERE wallet_user = '".$colname_profid."' LIMIT 1";
  $getMemsWallet = mysql_query($query_getMemsWallet, $echoloyalty) or die(mysql_error());
  $row_getMemsWallet = mysql_fetch_assoc($getMemsWallet);
  $getMemsWalletRows  = mysql_num_rows($getMemsWallet);

  if($getMemberRows > 0 && $colname_getUser == $row_getMember['user_id']) {

	$colname_getUsrDiscount = $row_getMember['user_discount'];
	if (isset($colname_usrdiscount) && $colname_usrdiscount != '0') {
	  $colname_getUsrDiscount = $colname_usrdiscount;
	}
	$colname_getUsrMob = $row_getMember['user_mob'];
	if (isset($colname_usrmob) && $colname_usrmob != '0') {
	  $colname_getUsrMob = $colname_usrmob;
	}
	$colname_getUsrTel = $row_getMember['user_tel'];
	if (isset($colname_usrtel) && $colname_usrtel != '0') {
	  $colname_getUsrTel = $colname_usrtel;
	}
	$colname_getUsrPwd = $row_getMember['user_pwd'];
	if (isset($colname_pswd) && $colname_pswd != '0' && $colname_pswd != '') {

	  $salt = "dockbox";
	  $pwdmd5 = md5($colname_pswd) . $salt;
	  $pwdhash = sha1($pwdmd5);
	  $colname_getUsrPwd = $pwdhash;

	}
	$colname_getUsrAdress = $row_getMember['user_adress'];
	if (isset($colname_usradress) && $colname_usradress != '0') {
	  $colname_getUsrAdress = $colname_usradress;
	}
	$colname_getUsrCity = $row_getMember['user_city'];
	if (isset($colname_usrs) && $colname_usrs != '0') {
	  $colname_getUsrCity = $colname_usrs;
	}
	$colname_getUsrRegion = $row_getMember['user_region'];
	if (isset($colname_usrr) && $colname_usrr != '0') {
	  $colname_getUsrRegion = $colname_usrr;
	}
	$colname_getUsrCountry = $row_getMember['user_country'];
	if (isset($colname_usrc) && $colname_usrc != '0') {
	  $colname_getUsrCountry = $colname_usrc;
	}
	$colname_getUsrEmail = $row_getMember['user_email'];
	if (isset($colname_usremail) && $colname_usremail != '0') {
	  $colname_getUsrEmail = $colname_usremail;
	}
	$colname_getUsrGender = $row_getMember['user_gender'];
	if (isset($colname_usrgender) && $colname_usrgender != '0') {
	  $colname_getUsrGender = $colname_usrgender;
	}
	$colname_getUsrBirth = $row_getMember['user_birthday'];
	if (isset($colname_usrbirthday) && $colname_usrbirthday != '0') {
	  $colname_getUsrBirth = $colname_usrbirthday;
	}
	$colname_getUsrSur = $row_getMember['user_surname'];
	if (isset($colname_usrsurname) && $colname_usrsurname != '0') {
	  $colname_getUsrSur = $colname_usrsurname;
	}
	$colname_getUsrName = $row_getMember['user_name'];
	if (isset($colname_usrname2) && $colname_usrname2 != '0') {
	  $colname_getUsrName = $colname_usrname2;
	}
	$colname_getUsrEx = $row_getMember['user_menue_exe'];
	if (isset($colname_usrmenueexe) && $colname_usrmenueexe != '0') {
	  $colname_getUsrEx = $colname_usrmenueexe;
	}
	
	if (isset($colname_usrwallet) && $colname_usrwallet != $row_getMemsWallet['wallet_total']) {
	  if($colname_usrwallet > $row_getMemsWallet['wallet_total']) {
					$pointsadd = $colname_usrwallet - $row_getMemsWallet['wallet_total'];
					// UPDATE WALLET
					$updWallet = "UPDATE wallet SET wallet_total='".$colname_usrwallet."', wallet_when='".$when."' WHERE wallet_user='".$row_getMember['user_id']."'";
					mysql_query($updWallet, $echoloyalty) or die(mysql_error());
					// INSERT INTO POINTS
					$insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_gift, points_when, points_time) VALUES ('".$row_getMember['user_id']."', '0', '0', '".$pointsadd."', '0', '".$colname_getUser."', '".$colname_getUser2."', '0', '0', '".$pointsComment."', '1', '0', '".$when."', '".$when."')";
					  mysql_query($insPoints, $echoloyalty) or die(mysql_error());
			  }
			  else if($colname_usrwallet < $row_getMemsWallet['wallet_total']) {
					$pointssub = $row_getMemsWallet['wallet_total'] - $colname_usrwallet;
					// UPDATE WALLET
					$updWallet = "UPDATE wallet SET wallet_total='".$colname_usrwallet."', wallet_when='".$when."' WHERE wallet_user='".$row_getMember['user_id']."'";
					mysql_query($updWallet, $echoloyalty) or die(mysql_error());
					// INSERT INTO POINTS
					$insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_gift, points_when, points_time) VALUES ('".$row_getMember['user_id']."', '0', '0', '".$pointssub."', '1', '".$colname_getUser."', '".$colname_getUser2."', '0', '0', '".$pointsComment."', '1', '0', '".$when."', '".$when."')";
					  mysql_query($insPoints, $echoloyalty) or die(mysql_error());
			  }
	}

	$updMember = "UPDATE users SET user_name='".$colname_getUsrName."', user_surname='".$colname_getUsrSur."', user_email='".$colname_getUsrEmail."', user_menue_exe='".$colname_getUsrEx."', user_pwd='".$colname_getUsrPwd."', user_mob='".$colname_getUsrMob."', user_tel='".$colname_getUsrTel."', user_gender='".$colname_getUsrGender."', user_birthday='".$colname_getUsrBirth."', user_country='".$colname_getUsrCountry."', user_region='".$colname_getUsrRegion."', user_city='".$colname_getUsrCity."', user_adress='".$colname_getUsrAdress."', user_discount='".$colname_getUsrDiscount."', user_upd='".$when."' WHERE user_id='".$row_getMember['user_id']."'";
	mysql_query($updMember, $echoloyalty) or die(mysql_error());

	$profUpd = '1';

  }
  else if($getMemberRows > 0 && $colname_getUser != $row_getMember['user_id']) {

	$colname_getUsrSur = $row_getMember['user_surname'];
	if (isset($colname_usrsurname) && $colname_usrsurname != '0') {
	  $colname_getUsrSur = $colname_usrsurname;
	}
	$colname_getUsrName = $row_getMember['user_name'];
	if (isset($colname_usrname2) && $colname_usrname2 != '0') {
	  $colname_getUsrName = $colname_usrname2;
	}
	$colname_getUsrDiscount = $row_getMember['user_discount'];
	if (isset( $colname_usrdis) &&  $colname_usrdis != '0') {
	  $colname_getUsrDiscount =  $colname_usrdis;
	}
	$colname_getUsrWorkPos = $row_getMember['user_work_pos'];
	if (isset($colname_usrworkpos) && $colname_usrworkpos != '0') {
	  $colname_getUsrWorkPos = $colname_usrworkpos;
	}
	$colname_getUsrMob = $row_getMember['user_mob'];
	if (isset($colname_usrmob) && $colname_usrmob != '0') {
	  $colname_getUsrMob = $colname_usrmob;
	}
	$colname_getUsrTel = $row_getMember['user_tel'];
	if (isset($colname_usrtel) && $colname_usrtel != '0') {
	  $colname_getUsrTel = $colname_usrtel;
	}
	$colname_getUsrPwd = $row_getMember['user_pwd'];
	if (isset($colname_pswd) && $colname_pswd != '0') {
	  $colname_getUsrPwd = $colname_pswd;
	}
	$colname_getUsrEmail = $row_getMember['user_email'];
	if (isset($colname_usremail) && $colname_usremail != '0') {
	  $colname_getUsrEmail = $colname_usremail;
	}
	$colname_getUsrGender = $row_getMember['user_gender'];
	if (isset($colname_usrgender) && $colname_usrgender != '0') {
	  $colname_getUsrGender = $colname_usrgender;
	}
	$colname_getUsrBirth = $row_getMember['user_birthday'];
	if (isset($colname_usrbirthday) && $colname_usrbirthday != '0') {
	  $colname_getUsrBirth = $colname_usrbirthday;
	}
	$colname_getUsrEx = $row_getMember['user_menue_exe'];
	if (isset($colname_usrmenueexe)) {
	  $colname_getUsrEx = $colname_usrmenueexe;
	}
	
			if (isset($colname_usrwallet) && $colname_usrwallet != $row_getMemsWallet['wallet_total']) {
			  if($colname_usrwallet > $row_getMemsWallet['wallet_total']) {
					$pointsadd = $colname_usrwallet - $row_getMemsWallet['wallet_total'];
					// UPDATE WALLET
					$updWallet = "UPDATE wallet SET wallet_total='".$colname_usrwallet."', wallet_when='".$when."' WHERE wallet_user='".$row_getMember['user_id']."'";
					mysql_query($updWallet, $echoloyalty) or die(mysql_error());
					// INSERT INTO POINTS
					$insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_gift, points_when, points_time) VALUES ('".$row_getMember['user_id']."', '0', '0', '".$pointsadd."', '0', '".$colname_getUser."', '".$colname_getUser2."', '0', '0', '".$pointsComment."', '1', '0', '".$when."', '".$when."')";
					mysql_query($insPoints, $echoloyalty) or die(mysql_error());
			  }
			  else if($colname_usrwallet < $row_getMemsWallet['wallet_total']) {
					$pointssub = $row_getMemsWallet['wallet_total'] - $colname_usrwallet;
					// UPDATE WALLET
					$updWallet = "UPDATE wallet SET wallet_total='".$colname_usrwallet."', wallet_when='".$when."' WHERE wallet_user='".$row_getMember['user_id']."'";
					mysql_query($updWallet, $echoloyalty) or die(mysql_error());
					// INSERT INTO POINTS
					$insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_gift, points_when, points_time) VALUES ('".$row_getMember['user_id']."', '0', '0', '".$pointssub."', '1', '".$colname_getUser."', '".$colname_getUser2."', '0', '0', '".$pointsComment."', '1', '0', '".$when."', '".$when."')";
					mysql_query($insPoints, $echoloyalty) or die(mysql_error());
			  }
			}

	$updMember = "UPDATE users SET user_name='".$colname_getUsrName."', user_surname='".$colname_getUsrSur."', user_email='".$colname_getUsrEmail."', user_menue_exe='".$colname_getUsrEx."', user_pwd='".$colname_getUsrPwd."', user_tel='".$colname_getUsrTel."', user_gender='".$colname_getUsrGender."', user_birthday='".$colname_getUsrBirth."', user_mob='".$colname_getUsrMob."', user_work_pos='".$colname_getUsrWorkPos."', user_discount='".$colname_getUsrDiscount."', user_upd='".$when."' WHERE user_id='".$row_getMember['user_id']."'";
	mysql_query($updMember, $echoloyalty) or die(mysql_error());

	$profUpd = '1';

  }

  $newarrmes = array("requests" => '1', "profId" => $colname_profid, "profUpd" => $profUpd);
  array_push($gotdata, $newarrmes);

}
else {

	$usrArr = array();
	// GET MEMBER
	$query_getMember = "SELECT * FROM users WHERE user_id = '".$colname_getUser5."'";
	$getMember = mysql_query($query_getMember, $echoloyalty) or die(mysql_error());
	$row_getMember = mysql_fetch_assoc($getMember);
	$getMemberRows  = mysql_num_rows($getMember);

	// GET PROFESSIONS
	$professionArr = array();
	$query_getProf = "SELECT * FROM professions WHERE prof_when > '2' && (prof_institution = '".$colname_getUser2."' OR prof_institution = '0') ORDER BY prof_id ASC";
	$getProf = mysql_query($query_getProf, $echoloyalty) or die(mysql_error());
	$row_getProf = mysql_fetch_assoc($getProf);
	$getProfRows  = mysql_num_rows($getProf);

	if($getProfRows > 0) {

		do {

			array_push($professionArr, array($row_getProf['prof_id'], $row_getProf['prof_name'], $row_getProf['prof_desc'], $row_getProf['prof_institution'], $row_getProf['prof_when']));

		} while ($row_getProf = mysql_fetch_assoc($getProf));

	}

	// GET COUNTRY
	$query_getCountry = "SELECT * FROM country WHERE id_country = '".$row_getMember['user_country']."'";
	$getCountry = mysql_query($query_getCountry, $echoloyalty) or die(mysql_error());
	$row_getCountry = mysql_fetch_assoc($getCountry);
	$getCountryRows  = mysql_num_rows($getCountry);

	// GET REGION
	$query_getRegion = "SELECT * FROM region WHERE id_region = '".$row_getMember['user_region']."'";
	$getRegion = mysql_query($query_getRegion, $echoloyalty) or die(mysql_error());
	$row_getRegion = mysql_fetch_assoc($getRegion);
	$getRegionRows  = mysql_num_rows($getRegion);

	// GET CITY
	$query_getCity = "SELECT * FROM city WHERE id_city = '".$row_getMember['user_city']."'";
	$getCity = mysql_query($query_getCity, $echoloyalty) or die(mysql_error());
	$row_getCity = mysql_fetch_assoc($getCity);
	$getCityRows  = mysql_num_rows($getCity);

	// GET CLICKS
	$clicksArr = array();
	$query_getClicks = "SELECT *, COUNT(*) AS clickCount FROM clicks WHERE clicks_user = '".$colname_getUser5."' && clicks_institution = '".$colname_getUser2."' GROUP BY clicks_what DESC";
	$getClicks = mysql_query($query_getClicks, $echoloyalty) or die(mysql_error());
	$row_getClicks = mysql_fetch_assoc($getClicks);
	$getClicksRows  = mysql_num_rows($getClicks);

	if($getClicksRows > 0) {

		do {

			// GET CLICKSNAME
			$query_getClicksN = "SELECT * FROM clicks_name WHERE clicks_name_id = '".$row_getClicks['clicks_what']."'";
			$getClicksN = mysql_query($query_getClicksN, $echoloyalty) or die(mysql_error());
			$row_getClicksN = mysql_fetch_assoc($getClicksN);
			$getClicksNRows  = mysql_num_rows($getClicksN);

			array_push($clicksArr, array($row_getClicks['clickCount'], $row_getClicksN['clicks_name_n'], $row_getClicks['clicks_when']));

		} while ($row_getClicks = mysql_fetch_assoc($getClicks));

	}

	// GET POINTS
	$pointsArr = array();
	$query_getPoints = "SELECT * FROM points WHERE points_user = '".$row_getMember['user_id']."' && points_institution = '".$colname_getUser2."'";
	$getPoints = mysql_query($query_getPoints, $echoloyalty) or die(mysql_error());
	$row_getPoints = mysql_fetch_assoc($getPoints);
	$getPointsRows  = mysql_num_rows($getPoints);

	if($getPointsRows > 0) {

		$query_getOrg = "SELECT * FROM organizations WHERE org_id = '".$row_getPoints['points_institution']."'";
		$getOrg = mysql_query($query_getOrg, $echoloyalty) or die(mysql_error());
		$row_getOrg = mysql_fetch_assoc($getOrg);
		$getOrgRows  = mysql_num_rows($getOrg);

		do{

			array_push($pointsArr, array($row_getPoints['points_bill'], $row_getPoints['points_discount'], $row_getPoints['points_points'], $row_getPoints['points_got_spend'], $row_getPoints['points_waiter'], $row_getOrg['org_name'], $row_getPoints['points_status'], $row_getPoints['points_proofed'], $row_getPoints['points_when'], $row_getPoints['points_id']));

		} while ($row_getPoints = mysql_fetch_assoc($getPoints));

	}

	// ASSOCIATED TO WORKER MENUE
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

		$query_getGroupChng = "SELECT * FROM goods WHERE goods_institution = '".$colname_getUser2."' && goods_id = '".$row_getCatChng['cat_ingr']."' LIMIT 1";
		$getGroupChng = mysql_query($query_getGroupChng, $echoloyalty) or die(mysql_error());
		$row_getGroupChng = mysql_fetch_assoc($getGroupChng);
		$getGroupChngRows  = mysql_num_rows($getGroupChng);

		$goodsName = '';
		if($getGroupChngRows > 0) {
		  $goodsName = $row_getGroupChng['goods_name'];
		}
		
		array_push($menuearr, array($row_getMenueC['menue_id'], $row_getMenueC['menue_name'], $row_getMenueC['menue_desc'], $row_getMenueC['menue_pic'], $row_getMenueC['menue_institution'], $row_getMenueC['menue_when'], $row_getCatChng['cat_name'], $row_getMenueC['menue_size'], $row_getMenueC['menue_cost'], $row_getMenueC['menue_weight'], $row_getMenueC['menue_discount'], $row_getMenueC['menue_action'], $row_getMenueC['menue_code'], $row_getCatChng['cat_ingr'], $row_getMenueC['menue_cat'], $row_getMenueC['menue_costs'], $goodsName));
		  
	  } while ($row_getMenueC = mysql_fetch_assoc($getMenueC));

	}

	// GET WALLET
	$walletArr = array();
	$query_getWallet = "SELECT * FROM wallet WHERE wallet_user = '".$row_getMember['user_id']."' && wallet_institution = '".$colname_getUser2."'";
	$getWallet = mysql_query($query_getWallet, $echoloyalty) or die(mysql_error());
	$row_getWallet = mysql_fetch_assoc($getWallet);
	$getWalletRows  = mysql_num_rows($getWallet);

	if($getWalletRows > 0) {

		array_push($walletArr, array($row_getWallet['wallet_institution'], $row_getWallet['wallet_total'], $row_getWallet['wallet_when']));

	}

	// if($row_getMember['user_gender'] == '1') {
	//     $memGender = 'Мужской';
	// }
	// else if($row_getMember['user_gender'] == '2') {
	//     $memGender = 'Женский';
	// }
	// else {
	//     $memGender = 'Не указано';
	// }

	if($row_getMember['user_id'] == $row_getUser['user_id']) {$memPWD = '';} else {$memPWD = $row_getMember['user_pwd'];}

	array_push($usrArr, array("mem_id" => $row_getMember['user_id'], "mem_name" => $row_getMember['user_name'], "mem_surname" => $row_getMember['user_surname'], "mem_middlename" => $row_getMember['user_middlename'], "mem_email" => $row_getMember['user_email'], "mem_tel" => $row_getMember['user_tel'], "mem_mob" => $row_getMember['user_mob'], "mem_work_pos" => $row_getMember['user_work_pos'], "mem_menue_exe" => $row_getMember['user_menue_exe'], "mem_institution" => $row_getMember['user_institution'], "mem_pic" => 'img/user/'.$colname_getUser2.'/pic/'.$row_getMember['user_pic'], "mem_gender" => $row_getMember['user_gender'], "mem_birthday" => $row_getMember['user_birthday'], "mem_country_id" => $row_getCountry['id_country'], "mem_country" => $row_getCountry['name'], "mem_region_id" => $row_getRegion['id_region'], "mem_region" => $row_getRegion['name'], "mem_city_id" => $row_getCity['id_city'], "mem_city" => $row_getCity['name'], "mem_adress" => $row_getMember['user_adress'], "mem_install_where" => $row_getMember['user_install_where'], "user_discount" => $row_getMember['user_discount'], "mem_log" => $row_getMember['user_log'], "mem_reg" => $row_getMember['user_reg'], "mem_clicks" => $clicksArr, "mem_points" => $pointsArr, "mem_wallet" => $walletArr, "mem_pwd" => $memPWD, "professionArr" => $professionArr, "menueArr" => $menuearr));

}

}

$asksarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "usrArr" => $usrArr);
array_push($gotdata, $asksarrmes);

?>