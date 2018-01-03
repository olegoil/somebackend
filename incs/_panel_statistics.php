<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

if($colname_getUser5 == 'bestwaiter') {

  $colname_getTmFrom = time();
  if (isset($themsg['tmfrom'])) {
	$colname_getTmFrom = protect($themsg['tmfrom']);
  }
  $colname_getTmTo = time();
  if (isset($themsg['tmto'])) {
	$colname_getTmTo = protect($themsg['tmto']);
  }

  // PROMOCODE USE
  $query_getWorkerPoints = "SELECT *, COUNT(*) AS PromoUsed FROM promo RIGHT JOIN users ON users.user_id = promo.promo_from AND users.user_work_pos >= '2' WHERE promo.promo_institution = '".$colname_getUser2."' && promo.promo_when BETWEEN '".$colname_getTmFrom."' AND '".$colname_getTmTo."' GROUP BY promo.promo_from ORDER BY PromoUsed DESC LIMIT 5";
  $getWorkerPoints = mysql_query($query_getWorkerPoints, $echoloyalty) or die(mysql_error());
  $row_getWorkerPoints = mysql_fetch_assoc($getWorkerPoints);
  $getWorkerPointsRows  = mysql_num_rows($getWorkerPoints);

  $workerPoints = array();

  if($getWorkerPointsRows > 0) {

	do {

	  $workerName = $row_getWorkerPoints['user_name'] . ' ' . $row_getWorkerPoints['user_surname'];
	  $workerPoints[$workerName] = $row_getWorkerPoints['PromoUsed'];

	} while ($row_getWorkerPoints = mysql_fetch_assoc($getWorkerPoints));

  }
  else {
	  $workerPoints['никто'] = 1;
  }

  $newarrmes = array("requests" => '1', "workerPoints" => $workerPoints);
  array_push($gotdata, $newarrmes);

}

}
else {

// GENDER MALE
$query_getUsersMale = "SELECT user_id FROM users WHERE user_gender = '1' && user_institution = '".$colname_getUser2."'";
$getUsersMale = mysql_query($query_getUsersMale, $echoloyalty) or die(mysql_error());
$row_getUsersMale = mysql_fetch_assoc($getUsersMale);
$getUsersMaleRows  = mysql_num_rows($getUsersMale);

// GENDER FEMALE
$query_getUsersFemale = "SELECT user_id FROM users WHERE user_gender = '2' && user_institution = '".$colname_getUser2."'";
$getUsersFemale = mysql_query($query_getUsersFemale, $echoloyalty) or die(mysql_error());
$row_getUsersFemale = mysql_fetch_assoc($getUsersFemale);
$getUsersFemaleRows  = mysql_num_rows($getUsersFemale);

// GENDER NO
$query_getUsersNo = "SELECT user_id FROM users WHERE user_gender = '0' && user_institution = '".$colname_getUser2."'";
$getUsersNo = mysql_query($query_getUsersNo, $echoloyalty) or die(mysql_error());
$row_getUsersNo = mysql_fetch_assoc($getUsersNo);
$getUsersNoRows  = mysql_num_rows($getUsersNo);

$genderMale = round(100 * $getUsersMaleRows / ($getUsersMaleRows + $getUsersFemaleRows + $getUsersNoRows));
$genderFemale = round(100 * $getUsersFemaleRows / ($getUsersMaleRows + $getUsersFemaleRows + $getUsersNoRows));
$genderNo = round(100 * $getUsersNoRows / ($getUsersMaleRows + $getUsersFemaleRows + $getUsersNoRows));

$age16 = date("Y-m-d ", time() - 31536000*16);
$age20 = date("Y-m-d ", time() - 31536000*20);
$age25 = date("Y-m-d ", time() - 31536000*25);
$age30 = date("Y-m-d ", time() - 31536000*30);
$age40 = date("Y-m-d ", time() - 31536000*40);

$query_getMemAge15 = "SELECT * FROM users WHERE user_birthday < '".$age16."' && user_birthday > '".$age20."' && user_institution = '".$colname_getUser2."'";
$getMemAge15 = mysql_query($query_getMemAge15, $echoloyalty) or die(mysql_error());
$row_getMemAge15 = mysql_fetch_assoc($getMemAge15);
$totalRows_getMemAge15 = mysql_num_rows($getMemAge15);

$query_getMemAge20 = "SELECT * FROM users WHERE user_birthday < '".$age20."' && user_birthday > '".$age25."' && user_institution = '".$colname_getUser2."'";
$getMemAge20 = mysql_query($query_getMemAge20, $echoloyalty) or die(mysql_error());
$row_getMemAge20 = mysql_fetch_assoc($getMemAge20);
$totalRows_getMemAge20 = mysql_num_rows($getMemAge20);

$query_getMemAge25 = "SELECT * FROM users WHERE user_birthday < '".$age25."' && user_birthday > '".$age30."' && user_institution = '".$colname_getUser2."'";
$getMemAge25 = mysql_query($query_getMemAge25, $echoloyalty) or die(mysql_error());
$row_getMemAge25 = mysql_fetch_assoc($getMemAge25);
$totalRows_getMemAge25 = mysql_num_rows($getMemAge25);

$query_getMemAge30 = "SELECT * FROM users WHERE user_birthday < '".$age30."' && user_birthday > '".$age40."' && user_institution = '".$colname_getUser2."'";
$getMemAge30 = mysql_query($query_getMemAge30, $echoloyalty) or die(mysql_error());
$row_getMemAge30 = mysql_fetch_assoc($getMemAge30);
$totalRows_getMemAge30 = mysql_num_rows($getMemAge30);

$query_getMemAge40 = "SELECT * FROM users WHERE user_birthday < '".$age40."' && user_institution = '".$colname_getUser2."'";
$getMemAge40 = mysql_query($query_getMemAge40, $echoloyalty) or die(mysql_error());
$row_getMemAge40 = mysql_fetch_assoc($getMemAge40);
$totalRows_getMemAge40 = mysql_num_rows($getMemAge40);

// INSTALLED FROM
$query_getInstFrom = "SELECT user_install_where, COUNT(*) AS CountInst FROM users WHERE user_institution = '".$colname_getUser2."' GROUP BY user_install_where";
$getInstFrom = mysql_query($query_getInstFrom, $echoloyalty) or die(mysql_error());
$row_getInstFrom = mysql_fetch_assoc($getInstFrom);
$getInstFromRows  = mysql_num_rows($getInstFrom);

$inst0 = 0;
$inst1 = 0;
$inst2 = 0;
$inst3 = 0;
$inst4 = 0;
$inst5 = 0;

if($getInstFromRows > 0) {
  
  do {

	  if(isset($row_getInstFrom['user_install_where'])) {

		  switch($row_getInstFrom['user_install_where']) {
		  case '0':
			  $inst0 = $row_getInstFrom['CountInst'];
			  break;
		  case '1':
			  $inst1 = $row_getInstFrom['CountInst'];
			  break;
		  case '2':
			  $inst2 = $row_getInstFrom['CountInst'];
			  break;
		  case '3':
			  $inst3 = $row_getInstFrom['CountInst'];
			  break;
		  case '4':
			  $inst4 = $row_getInstFrom['CountInst'];
			  break;
		  case '5':
			  $inst5 = $row_getInstFrom['CountInst'];
			  break;
		  }

	  }

  } while ($row_getInstFrom = mysql_fetch_assoc($getInstFrom));

}

// PREFERRED
// $query_getInstClicks = "SELECT clicks_what, COUNT(*) AS CountClicks FROM clicks WHERE clicks_institution = '".$colname_getUser2."' GROUP BY clicks_what ORDER BY 2 DESC LIMIT 5";
// $getInstClicks = mysql_query($query_getInstClicks, $echoloyalty) or die(mysql_error());
// $row_getInstClicks = mysql_fetch_assoc($getInstClicks);
// $getInstClicksRows  = mysql_num_rows($getInstClicks);

$clickarr = array();
// if($getInstClicksRows > 0) {

//   do {
//       if(isset($row_getInstClicks['clicks_what'])) {

//           // PREFERRED NAME
//           $query_getInstClicksN = "SELECT clicks_name_n FROM clicks_name WHERE clicks_name_id = '".$row_getInstClicks['clicks_what']."' && clicks_name_institution = '".$colname_getUser2."' LIMIT 1";
//           $getInstClicksN = mysql_query($query_getInstClicksN, $echoloyalty) or die(mysql_error());
//           $row_getInstClicksN = mysql_fetch_assoc($getInstClicksN);
//           $getInstClicksNRows  = mysql_num_rows($getInstClicksN);

//           $clickarr[$row_getInstClicksN['clicks_name_n']] = $row_getInstClicks['CountClicks'];

//       }
//   } while ($row_getInstClicks = mysql_fetch_assoc($getInstClicks));

// }

$pointaddarr = array();
$pointsubarr = array();
$pointmiddlearr = array();
$revallsarr = array();

for ($x = 0; $x <= 7; $x++) {
	// LAST 8 DAYS
	$tmamount = time() - $x*86400;
	$aftday = $tmamount + 86400;

	// POINTS ADDED
	$query_getPoitsAdded = "SELECT SUM(points_points) AS SumPoints FROM points WHERE points_got_spend = '1' && points_proofed = '1' && points_institution = '".$colname_getUser2."' && points_when > '".$tmamount."' && points_when < '".$aftday."'";
	$getPoitsAdded = mysql_query($query_getPoitsAdded, $echoloyalty) or die(mysql_error());
	$row_getPoitsAdded = mysql_fetch_assoc($getPoitsAdded);
	$getPoitsAddedRows  = mysql_num_rows($getPoitsAdded);

	// POINTS SPENT
	$query_getPoitsSub = "SELECT SUM(points_points) AS SubPoints FROM points WHERE points_got_spend = '0' && points_proofed = '1' && points_institution = '".$colname_getUser2."' && points_when > '".$tmamount."' && points_when < '".$aftday."'";
	$getPoitsSub = mysql_query($query_getPoitsSub, $echoloyalty) or die(mysql_error());
	$row_getPoitsSub = mysql_fetch_assoc($getPoitsSub);
	$getPoitsSubRows  = mysql_num_rows($getPoitsSub);
	
	// POINTS TIME ADD TO ARRAY
	if(isset($row_getPoitsAdded['SumPoints'])) {
		$pointaddarr[$tmamount] = $row_getPoitsAdded['SumPoints'];
	}
	else {
		$pointaddarr[$tmamount] = 0;
	}
	// POINTS TIME ADD TO ARRAY
	if(isset($row_getPoitsSub['SubPoints'])) {
		$pointsubarr[$tmamount] = $row_getPoitsSub['SubPoints'];
	}
	else {
		$pointsubarr[$tmamount] = 0;
	}

	// POINTS TIME MIDDLE ADD TO ARRAY
	$pointsum = $row_getPoitsAdded['SumPoints'] + $row_getPoitsSub['SubPoints'];
	if($row_getPoitsAdded['SumPoints'] > 0 && $row_getPoitsSub['SubPoints'] > 0) {
		$pointmiddlearr[$tmamount] = $pointsum / 2;
	}
	else {
		$pointmiddlearr[$tmamount] = $pointsum;
	}

	// ДОХОД
	$query_getRevA = "SELECT SUM(points_bill) AS TotalBills FROM points WHERE points_institution = '".$colname_getUser2."' && points_when > '".$tmamount."' && points_when < '".$aftday."'";
	$getRevA = mysql_query($query_getRevA, $echoloyalty) or die(mysql_error());
	$row_getRevA = mysql_fetch_assoc($getRevA);
	$getRevARows  = mysql_num_rows($getRevA);

	// REVENUE ADD TO ARRAY
	if(isset($row_getRevA['TotalBills'])) {
		$revallsarr[$tmamount] = $row_getRevA['TotalBills'];
	}
	else {
		$revallsarr[$tmamount] = 0;
	}

}

$usramountarr = array();
$pointsarr = array();

for ($x = 0; $x <= 30; $x++) {
  // LAST 30 DAYS
  $timeamount = time() - $x*86400;
			if($x == 0) {
				$afterday = $timeamount - 86400;
			}
			else {
				$afterday = $timeamount - ($x+1)*86400;
			}
  // USERS
  $query_getUsers = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_reg < '".$timeamount."' && user_reg > '".$afterday."'";
  $getUsers = mysql_query($query_getUsers, $echoloyalty) or die(mysql_error());
  $row_getUsers = mysql_fetch_assoc($getUsers);
  $getUsersRows  = mysql_num_rows($getUsers);
  
  // TIME - PEOPLE ADD TO ARRAY
  $usramountarr[$timeamount] = $getUsersRows;
  
  // USING SYSTEM
  $query_getPoints = "SELECT * FROM points WHERE points_institution = '".$colname_getUser2."' && points_when < '".$timeamount."' && points_when > '".$afterday."' GROUP BY points_user";
  $getPoints = mysql_query($query_getPoints, $echoloyalty) or die(mysql_error());
  $row_getPoints = mysql_fetch_assoc($getPoints);
  $getPointsRows  = mysql_num_rows($getPoints);
  
  // TIME - USING ADD TO ARRAY
  $pointsarr[$timeamount] = $getPointsRows;
  
}

// PROMOCODE USE
$nowY = date('Y', time());
$nowM = date('m', time());
$lastDay = date('t', time());
$monthbegin = strtotime(date($nowY.'-'.$nowM.'-'.'01'.' '.'00:00:00'));
$monthend = strtotime(date($nowY.'-'.$nowM.'-'.$lastDay.' '.'23:59:59'));

$query_getWorkerPoints = "SELECT *, COUNT(*) AS PromoUsed FROM promo RIGHT JOIN users ON users.user_id = promo.promo_from AND users.user_work_pos >= '2' WHERE promo.promo_institution = '".$colname_getUser2."' && promo.promo_when BETWEEN '".$monthbegin."' AND '".$monthend."' GROUP BY promo.promo_from ORDER BY PromoUsed DESC LIMIT 5";
$getWorkerPoints = mysql_query($query_getWorkerPoints, $echoloyalty) or die(mysql_error());
$row_getWorkerPoints = mysql_fetch_assoc($getWorkerPoints);
$getWorkerPointsRows  = mysql_num_rows($getWorkerPoints);

$workerPoints = array();

if($getWorkerPointsRows > 0) {

  do {

	$workerName = $row_getWorkerPoints['user_name'] . ' ' . $row_getWorkerPoints['user_surname'];
	$workerPoints[$workerName] = $row_getWorkerPoints['PromoUsed'];

  } while ($row_getWorkerPoints = mysql_fetch_assoc($getWorkerPoints));

}
else {
	$workerPoints['никто'] = 1;
}

$thetime = time();

$newarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "usrAll" => $usramountarr, "pointsAll" => $pointsarr, "gendM" => $genderMale, "gendF" => $genderFemale, "gendN" => $genderNo, "age16" => $totalRows_getMemAge15, "age20" => $totalRows_getMemAge20, "age25" => $totalRows_getMemAge25, "age30" => $totalRows_getMemAge30, "age40" => $totalRows_getMemAge40, "inst0" => $inst0, "inst1" => $inst1, "inst2" => $inst2, "inst3" => $inst3, "inst4" => $inst4, "inst5" => $inst5, "clickarr" => $clickarr, "pointsAdd" => $pointaddarr, "pointsSub" => $pointsubarr, "pointsMid" => $pointmiddlearr, "revAll" => $revallsarr, "workerPoints" => $workerPoints, "time" => $thetime);
array_push($gotdata, $newarrmes);

}

?>