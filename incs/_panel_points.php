<?php

$sendingok = true;

if(isset($colname_getUser5) && $colname_getUser5 != '%') {

	$colname_val = "-1";
	if (isset($themsg['val'])) {
	  $colname_val = protect($themsg['val']);
	}
	$colname_pointid = "-1";
	if (isset($themsg['pointid'])) {
	  $colname_pointid = protect($themsg['pointid']);
	}

	if($colname_getUser5 == 'proofed') {

	  $query_getPointsChng = "SELECT * FROM points WHERE points_institution = '".$colname_getUser2."' && points_id = '".$colname_pointid."' LIMIT 1";
	  $getPointsChng = mysql_query($query_getPointsChng, $echoloyalty) or die(mysql_error());
	  $row_getPointsChng = mysql_fetch_assoc($getPointsChng);
	  $getPointsChngRows  = mysql_num_rows($getPointsChng);

	  if($getPointsChngRows > 0) {

		  $updPoints = "UPDATE points SET points_status='".$colname_val."', points_proofed='1', points_when='".$when."' WHERE points_institution = '".$colname_getUser2."' && points_id='".$colname_pointid."'";
		  mysql_query($updPoints, $echoloyalty) or die(mysql_error());

		  if($colname_val == 0) {

				$query_getWallet = "SELECT * FROM wallet WHERE wallet_institution = '".$colname_getUser2."' && wallet_user = '".$row_getPointsChng['points_user']."' LIMIT 1";
				$getWallet = mysql_query($query_getWallet, $echoloyalty) or die(mysql_error());
				$row_getWallet = mysql_fetch_assoc($getWallet);
				$getWalletRows  = mysql_num_rows($getWallet);

				if($getWalletRows > 0) {

				  $wallet_old = $row_getWallet['wallet_total'];
				  $wallet_new = $wallet_old + $row_getPointsChng['points_points'];

				  $updWallet = "UPDATE wallet SET wallet_total='".$wallet_new."', wallet_when='".$when."' WHERE wallet_institution = '".$colname_getUser2."' && wallet_user='".$row_getPointsChng['points_user']."'";
				  mysql_query($updWallet, $echoloyalty) or die(mysql_error());

				}
				else {

				  $wallet_old = $row_getWallet['wallet_total'];
				  $wallet_new = $wallet_old + $row_getPointsChng['points_points'];

				  $insrtWallet = "INSERT INTO wallet (wallet_user, wallet_institution, wallet_total, wallet_when) VALUES ('".$row_getPointsChng['points_user']."', '".$colname_getUser2."', '".$wallet_new."', '".$when."')";
				  mysql_query($insrtWallet, $echoloyalty) or die(mysql_error());

				}

		  }

		  if($getPointsChngRows > 0 && $sendingok) {

				$query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_id = '".$row_getPointsChng['points_user']."'";
				$getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
				$row_getGCM = mysql_fetch_assoc($getGCM);
				$getGCMRows  = mysql_num_rows($getGCM);

				if($getGCMRows > 0) {

					  $apiKey =  urldecode($row_getInst['org_key']);

					  if($colname_val == 0) {

						$title = urldecode("Баллы зачисленны!");
						$messageios = "Баллы зачисленны! : " . $row_getPointsChng['points_points'];

						$messageand = urldecode($row_getPointsChng['points_points']);

						// iOS SETTINGS
						
						$badge = 1;
						$sound = 'default';
						$development = false;

						$payload = array();
						$payload['aps'] = array('alert' => html_entity_decode($messageios), 'badge' => intval($badge), 'sound' => $sound);
						$payload = json_encode($payload);

						$apns_url = NULL;
						$apns_cert = NULL;
						$apns_port = 2195;

						$rootLink = '/var/www/vhosts/xxx.com/httpdocs/src/MyApp/';

						if($development)
						{
						  $apns_url = 'gateway.sandbox.push.apple.com';
						  $apns_cert = $rootLink . $row_getInst['org_cert'];
						}
						else
						{
						  $apns_url = 'gateway.push.apple.com';
						  $apns_cert = $rootLink . $row_getInst['org_cert'];
						}

						$stream_context = stream_context_create();
						stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert);

						$apns = stream_socket_client('ssl://' . $apns_url . ':' . $apns_port, $error, $error_string, 2, STREAM_CLIENT_CONNECT, $stream_context);

						// SENDING
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

						  $apn_token = $row_getGCM['user_gcm'];

						  $apns_message = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $apn_token)) . chr(0) . chr(strlen($payload)) . $payload;
						  fwrite($apns, $apns_message);

						  @socket_close($apns);
						  @fclose($apns);

						}

					  }

				}

		  }

		  $newarrmes = array("requests" => '1', "pointsId" => $colname_pointid, "pointsUpd" => $colname_val);
		  array_push($gotdata, $newarrmes);

	  }

	}

}
else {

	// FOR SEARCHING
	$aColumns = array( 'points_id', 'points_user', 'points_bill', 'points_discount', 'points_points', 'points_waiter', 'points_usecure', 'points_wsecure', 'points_when', 'points_proofed' );

	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".mysql_real_escape_string( $_POST['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_POST['iDisplayLength'] );
	}

	/*
	 * Ordering
	 */
	if ( isset( $_POST['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_POST['iSortingCols'] ) ; $i++ )
		{
			if ( $_POST[ 'bSortable_'.intval($_POST['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $aColumns[ intval( $_POST['iSortCol_'.$i] ) ]."
				 	".mysql_real_escape_string( $_POST['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	
	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "WHERE points_institution = '".$colname_getUser2."'";
	if ( $_POST['sSearch'] != "" )
	{
		$sWhere = "WHERE points_institution = '".$colname_getUser2."' && (";
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			$sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string( $_POST['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}
	
	/* Individual column filtering */
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{
		if ( $_POST['bSearchable_'.$i] == "true" && $_POST['sSearch_'.$i] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE ";
			}
			else
			{
				$sWhere .= " AND ";
			}
			$sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string($_POST['sSearch_'.$i])."%' ";
		}
	}
	

  $query_getPointsC = "SELECT * FROM points $sWhere $sOrder $sLimit";
  $getPointsC = mysql_query($query_getPointsC, $echoloyalty) or die(mysql_error());
  $row_getPointsC = mysql_fetch_assoc($getPointsC);
  $getPointsCRows  = mysql_num_rows($getPointsC);
  
  $query_getPointsLength = "SELECT COUNT(*) AS dataCnt FROM points WHERE points_institution = '".$colname_getUser2."'";
  $getPointsLength = mysql_query($query_getPointsLength, $echoloyalty) or die(mysql_error());
  $row_getPointsLength = mysql_fetch_assoc($getPointsLength);
  $getPointsLengthRows  = mysql_num_rows($getPointsLength);

  if($getPointsCRows > 0) {
	
	do {
	  
	  // GET ORGANIZATION
	  $query_getOrg = "SELECT * FROM organizations WHERE org_id = '".$row_getPointsC['points_institution']."'";
	  $getOrg = mysql_query($query_getOrg, $echoloyalty) or die(mysql_error());
	  $row_getOrg = mysql_fetch_assoc($getOrg);
	  $getOrgRows  = mysql_num_rows($getOrg);
	  // GET USER DATA
	  $query_getMem = "SELECT * FROM users WHERE user_id = '".$row_getPointsC['points_user']."'";
	  $getMem = mysql_query($query_getMem, $echoloyalty) or die(mysql_error());
	  $row_getMem = mysql_fetch_assoc($getMem);
	  $getMemRows  = mysql_num_rows($getMem);
	  
		$timediff = 0;
		$waitertime = 0;
		$usertime = 0;

		if($row_getPointsC['points_waitertime'] > 0 && $row_getPointsC['points_usertime'] > 0) {
			
			$waitertime = $row_getPointsC['points_waitertime'];
			$usertime = $row_getPointsC['points_usertime'];
			
		  if($row_getPointsC['points_usertime'] > $row_getPointsC['points_waitertime']) {
			  $timediff = $row_getPointsC['points_usertime'] - $row_getPointsC['points_waitertime'];
		  }
		  else if ($row_getPointsC['points_usertime'] < $row_getPointsC['points_waitertime']) {
			  $timediff = $row_getPointsC['points_waitertime'] - $row_getPointsC['points_usertime'];
		  }

		}
		else {
							  
		  // GET TRANSACTION DATA
		  $query_getTransC = "SELECT * FROM transactions WHERE trans_when = '".$row_getPointsC['points_time']."' && trans_institution = '".$row_getPointsC['points_institution']."' && trans_waiterid = '".$row_getPointsC['points_waiter']."' && trans_ubill = '".$row_getPointsC['points_bill']."'";
		  $getTransC = mysql_query($query_getTransC, $echoloyalty) or die(mysql_error());
		  $row_getTransC = mysql_fetch_assoc($getTransC);
		  $getTransCRows  = mysql_num_rows($getTransC);
		  
		  if($getTransCRows > 0) {

					$waitertime = $row_getTransC['trans_waiterdatetime'];
					$usertime = $row_getTransC['trans_udatetime'];

			  if($row_getTransC['trans_udatetime'] > $row_getTransC['trans_waiterdatetime']) {
				$timediff = $row_getTransC['trans_udatetime'] - $row_getTransC['trans_waiterdatetime'];
			  }
			  else if ($row_getTransC['trans_udatetime'] < $row_getTransC['trans_waiterdatetime']) {
				  $timediff = $row_getTransC['trans_waiterdatetime'] - $row_getTransC['trans_udatetime'];
			  }

		  }

		}
			
	  $giftName = 0;
	  $giftPic = 0;

	  if($row_getMem['user_surname'] != '0') {
		$userIdent = $row_getMem['user_surname'];
	  }
	  else if($row_getMem['user_mob'] != '0') {
		$userIdent = '+'.$row_getMem['user_mob'];
	  }
	  else if($row_getMem['user_id'] != '0') {
		$userIdent = $row_getMem['user_id'];
	  }

	  if(isset($row_getPointsC['points_gift']) && $row_getPointsC['points_gift'] > '0') {

			$query_getCheckGift = "SELECT * FROM gifts WHERE gifts_id = '".$row_getPointsC['points_gift']."' LIMIT 1";
			$getCheckGift = mysql_query($query_getCheckGift, $echoloyalty) or die(mysql_error());
			$row_getCheckGift = mysql_fetch_assoc($getCheckGift);
			$getCheckGiftRows  = mysql_num_rows($getCheckGift);

			if($getCheckGiftRows > 0) {

			  $giftName = $row_getCheckGift['gifts_name'];

			  $giftPic = $row_getCheckGift['gifts_pic'];

			}

	  }

	  // GET OFFICE DATA
	  $query_getOffice = "SELECT * FROM organizations_office WHERE office_id = '".$row_getPointsC['points_office']."'";
	  $getOffice = mysql_query($query_getOffice, $echoloyalty) or die(mysql_error());
	  $row_getOffice = mysql_fetch_assoc($getOffice);
	  $getOfficeRows  = mysql_num_rows($getOffice);

	  $office = $row_getPointsC['points_office'];
	  if($getOfficeRows > 0) {
		$office = $row_getOffice['office_name'];
	  }
	  
	  $gotdata['aaData'][] = array($row_getPointsC['points_id'], $userIdent, $row_getPointsC['points_bill'], $row_getPointsC['points_discount'], $row_getPointsC['points_points'], $row_getPointsC['points_got_spend'], $row_getPointsC['points_waiter'], $row_getOrg['org_name'], $row_getPointsC['points_status'], $row_getPointsC['points_proofed'], $row_getPointsC['points_when'], $row_getPointsC['points_user'], $row_getPointsC['points_comment'], $giftName, $giftPic, $office, $timediff, $waitertime, $usertime);
	  
	} while ($row_getPointsC = mysql_fetch_assoc($getPointsC));
  
  }
  
  $gotdata['sEcho'] = intval($_POST['sEcho']);
  $gotdata['iTotalRecords'] = $row_getPointsLength['dataCnt'];
  $gotdata['iTotalDisplayRecords'] = $row_getPointsLength['dataCnt'];

}

?>