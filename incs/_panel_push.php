<?php
// iOS SETTINGS
$iosIDs = array();

$when = time();
$sendingok = true;

if(isset($colname_getUser5) && $colname_getUser5 != '%') {
  
$colname_fullname = "-1";
if (isset($themsg['fullname'])) {
  $colname_fullname = protect($themsg['fullname']);
}
$colname_message = "-1";
if (isset($themsg['message'])) {
  $colname_message = protect($themsg['message']);
}
$colname_selrec = "-1";
if (isset($themsg['selrec'])) {
  $colname_selrec = protect($themsg['selrec']);
}

if($colname_getUser5 == 'send') {

	$rece = (int)$colname_selrec;

	if($rece > 8) {

		$insertPush = "INSERT INTO pushmessages (push_name, push_message, push_status, push_receiver, push_institution, push_when) VALUES ('".$colname_fullname."', '".$colname_message."', '1', '".$rece."', '".$colname_getUser2."', '".$when."')";
		mysql_query($insertPush, $echoloyalty) or die(mysql_error());

		$query_getPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_when='".$when."' ORDER BY push_id DESC LIMIT 1";
		$getPushed = mysql_query($query_getPushed, $echoloyalty) or die(mysql_error());
		$row_getPushed = mysql_fetch_assoc($getPushed);
		$getPushedRows  = mysql_num_rows($getPushed);
		
		$query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_id = '".$rece."'";
		$getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
		$row_getGCM = mysql_fetch_assoc($getGCM);
		$getGCMRows  = mysql_num_rows($getGCM);

		if($getGCMRows > 0 && $sendingok) {

		  $apiKey =  urldecode($row_getInst['org_key']);
		  
		  $title = urldecode($colname_fullname);

		  $messageand = urldecode($colname_message);

		  // SENDING
		  do {

			if($row_getGCM['user_device_os'] == 'Android') {

				// ANDROID PUSH
				$registrationId = urldecode($row_getGCM['user_gcm']);

				// ANDROID SETTINGS
				$headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
				$data = array(
					'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title), 'push_id' => $row_getPushed['push_id']),
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

				$respsuc = get_object_vars(json_decode($response));

				$recsuccess = $respsuc['success'];
				if($respsuc['success'] == 0) {
				  $recsuccess = 2;
				}

				// TO WHOM IS MESSAGE SENDING
				$insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$row_getPushed['push_id']."', '".$row_getGCM['user_id']."', '".$recsuccess."', '0', '".$colname_getUser2."', '".$when."')";
				mysql_query($insPush, $echoloyalty) or die(mysql_error());

			}
			elseif($row_getGCM['user_device_os'] == 'iOS') {

			  array_push($iosIDs, $row_getGCM['user_gcm']);

			}

		  } while ($row_getGCM = mysql_fetch_assoc($getGCM));

		  sendGCM(0, $iosIDs, $row_getInst['org_cert'], $colname_fullname, $colname_message, $row_getPushed['push_id'], $colname_getUser2);

		}

		$newarrmes = array("requests" => '1', "pushId" => $row_getPushed['push_id'], "pushUpd" => '1', "pushTime" => $row_getPushed['push_when']);
		array_push($gotdata, $newarrmes);

	}
	else if ($rece == 4 || $rece == 5  || $rece == 6 || $rece == 7 || $rece == 8) {

		$insertPush = "INSERT INTO pushmessages (push_name, push_message, push_status, push_receiver, push_institution, push_when) VALUES ('".$colname_fullname."', '".$colname_message."', '1', '".$rece."', '".$colname_getUser2."', '".$when."')";
		mysql_query($insertPush, $echoloyalty) or die(mysql_error());

		$query_getPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_when='".$when."' ORDER BY push_id DESC LIMIT 1";
		$getPushed = mysql_query($query_getPushed, $echoloyalty) or die(mysql_error());
		$row_getPushed = mysql_fetch_assoc($getPushed);
		$getPushedRows  = mysql_num_rows($getPushed);

		$query_getGCM = '';
		
		if($rece == 4) {
		  $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_device_os = 'iOS'";
		}
		else if ($rece == 5) {
		  $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_device_os = 'Android'";
		}
		else if ($rece == 6) {
		  $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_gender = '1'";
		}
		else if ($rece == 7) {
		  $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_gender = '2'";
		}
		else if ($rece == 8) {
		  $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_gender != '1' && user_gender != '2'";
		}

		
		$getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
		$row_getGCM = mysql_fetch_assoc($getGCM);
		$getGCMRows  = mysql_num_rows($getGCM);

		if($getGCMRows > 0 && $sendingok) {

		  $apiKey =  urldecode($row_getInst['org_key']);
		  
		  $title = urldecode($colname_fullname);

		  $messageand = urldecode($colname_message);

		  // SENDING
		  do {

			if($row_getGCM['user_device_os'] == 'Android') {

				// ANDROID PUSH
				$registrationId = urldecode($row_getGCM['user_gcm']);

				// ANDROID SETTINGS
				$headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
				$data = array(
					'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title), 'push_id' => $row_getPushed['push_id']),
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

				$respsuc = get_object_vars(json_decode($response));

				$recsuccess = $respsuc['success'];
				if($respsuc['success'] == 0) {
				  $recsuccess = 2;
				}

				// TO WHOM IS MESSAGE SENDING
				$insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$row_getPushed['push_id']."', '".$row_getGCM['user_id']."', '".$recsuccess."', '0', '".$colname_getUser2."', '".$when."')";
				mysql_query($insPush, $echoloyalty) or die(mysql_error());

			}
			elseif($row_getGCM['user_device_os'] == 'iOS') {

			  array_push($iosIDs, $row_getGCM['user_gcm']);

			}

		  } while ($row_getGCM = mysql_fetch_assoc($getGCM));

		  sendGCM(0, $iosIDs, $row_getInst['org_cert'], $colname_fullname, $colname_message, $row_getPushed['push_id'], $colname_getUser2);

		}

		$newarrmes = array("requests" => '1', "pushId" => $row_getPushed['push_id'], "pushUpd" => '1', "pushTime" => $row_getPushed['push_when']);
		array_push($gotdata, $newarrmes);

	}
	else if ($rece == 2) {

		$then = $when - 60*60*24*14; // 14 days ago

		$insertPush = "INSERT INTO pushmessages (push_name, push_message, push_status, push_receiver, push_institution, push_when) VALUES ('".$colname_fullname."', '".$colname_message."', '1', '".$rece."', '".$colname_getUser2."', '".$when."')";
		mysql_query($insertPush, $echoloyalty) or die(mysql_error());

		$query_getPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_when='".$when."' ORDER BY push_id DESC LIMIT 1";
		$getPushed = mysql_query($query_getPushed, $echoloyalty) or die(mysql_error());
		$row_getPushed = mysql_fetch_assoc($getPushed);
		$getPushedRows  = mysql_num_rows($getPushed);
		
		$query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_log < '".$then."'";
		$getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
		$row_getGCM = mysql_fetch_assoc($getGCM);
		$getGCMRows  = mysql_num_rows($getGCM);

		if($getGCMRows > 0 && $sendingok) {

		  $apiKey =  urldecode($row_getInst['org_key']);
		  
		  $title = urldecode($colname_fullname);

		  $messageand = urldecode($colname_message);

		  // SENDING
		  do {

			if($row_getGCM['user_device_os'] == 'Android') {

				// ANDROID PUSH
				$registrationId = urldecode($row_getGCM['user_gcm']);

				// ANDROID SETTINGS
				$headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
				$data = array(
					'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title), 'push_id' => $row_getPushed['push_id']),
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

				$respsuc = get_object_vars(json_decode($response));

				$recsuccess = $respsuc['success'];
				if($respsuc['success'] == 0) {
				  $recsuccess = 2;
				}

				// TO WHOM IS MESSAGE SENDING
				$insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$row_getPushed['push_id']."', '".$row_getGCM['user_id']."', '".$recsuccess."', '0', '".$colname_getUser2."', '".$when."')";
				mysql_query($insPush, $echoloyalty) or die(mysql_error());

			}
			elseif($row_getGCM['user_device_os'] == 'iOS') {

			  array_push($iosIDs, $row_getGCM['user_gcm']);

			}

		  } while ($row_getGCM = mysql_fetch_assoc($getGCM));

		  sendGCM(0, $iosIDs, $row_getInst['org_cert'], $colname_fullname, $colname_message, $row_getPushed['push_id'], $colname_getUser2);

		}

		$newarrmes = array("requests" => '1', "pushId" => $row_getPushed['push_id'], "pushUpd" => '1', "pushTime" => $row_getPushed['push_when']);
		array_push($gotdata, $newarrmes);

	}
	else if ($rece == 1) {

		$insertPush = "INSERT INTO pushmessages (push_name, push_message, push_status, push_receiver, push_institution, push_when) VALUES ('".$colname_fullname."', '".$colname_message."', '1', '".$rece."', '".$colname_getUser2."', '".$when."')";
		mysql_query($insertPush, $echoloyalty) or die(mysql_error());

		$query_getPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_when='".$when."' ORDER BY push_id DESC LIMIT 1";
		$getPushed = mysql_query($query_getPushed, $echoloyalty) or die(mysql_error());
		$row_getPushed = mysql_fetch_assoc($getPushed);
		$getPushedRows  = mysql_num_rows($getPushed);
		
		$query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0'";
		$getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
		$row_getGCM = mysql_fetch_assoc($getGCM);
		$getGCMRows  = mysql_num_rows($getGCM);

		if($getGCMRows > 0 && $sendingok) {

		  $apiKey =  urldecode($row_getInst['org_key']);
		  
		  $title = urldecode($colname_fullname);

		  $messageand = urldecode($colname_message);

		  // SENDING
		  do {

			if($row_getGCM['user_device_os'] == 'Android') {

				// ANDROID PUSH
				$registrationId = urldecode($row_getGCM['user_gcm']);

				// ANDROID SETTINGS
				$headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
				$data = array(
					'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title), 'push_id' => $row_getPushed['push_id']),
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

				$respsuc = get_object_vars(json_decode($response));

				$recsuccess = $respsuc['success'];
				if($respsuc['success'] == 0) {
				  $recsuccess = 2;
				}

				// TO WHOM IS MESSAGE SENDING
				$insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$row_getPushed['push_id']."', '".$row_getGCM['user_id']."', '".$recsuccess."', '0', '".$colname_getUser2."', '".$when."')";
				mysql_query($insPush, $echoloyalty) or die(mysql_error());

			}
			elseif($row_getGCM['user_device_os'] == 'iOS') {

			  array_push($iosIDs, $row_getGCM['user_gcm']);

			}

		  } while ($row_getGCM = mysql_fetch_assoc($getGCM));

		  sendGCM(0, $iosIDs, $row_getInst['org_cert'], $colname_fullname, $colname_message, $row_getPushed['push_id'], $colname_getUser2);

		}

		$newarrmes = array("requests" => '1', "pushId" => $row_getPushed['push_id'], "pushUpd" => '1', "pushTime" => $row_getPushed['push_when']);
		array_push($gotdata, $newarrmes);

	}

}

}
else {

// DT INPUT FOR SEARCHING
$aColumns = array( 'push_id', 'push_name', 'push_message', 'push_when' );

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

// DT INPUT
$sWhere = "WHERE push_institution = '".$colname_getUser2."' && push_del = '0'";
if ( $_POST['sSearch'] != "" )
{
  $sWhere = "WHERE push_institution = '".$colname_getUser2."' && push_del = '0' && (";
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

// DT INPUT
$query_getDataC = "SELECT * FROM pushmessages $sWhere $sOrder $sLimit";
$getDataC = mysql_query($query_getDataC, $echoloyalty) or die(mysql_error());
$row_getDataC = mysql_fetch_assoc($getDataC);
$getDataCRows  = mysql_num_rows($getDataC);

// DT INPUT
$query_getDataLength = "SELECT COUNT(*) AS dataCnt FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_del = '0'";
$getDataLength = mysql_query($query_getDataLength, $echoloyalty) or die(mysql_error());
$row_getDataLength = mysql_fetch_assoc($getDataLength);
$getDataLengthRows  = mysql_num_rows($getDataLength);

if($getDataCRows > 0) {
  
  do {
	
	$gotdata['aaData'][] = array($row_getDataC['push_id'], $row_getDataC['push_name'], $row_getDataC['push_message'], $row_getDataC['push_status'], $row_getDataC['push_institution'], $row_getDataC['push_when']);
	
  } while ($row_getDataC = mysql_fetch_assoc($getDataC));
  
}

// DT OUTPUT
$gotdata['sEcho'] = intval($_POST['sEcho']);
$gotdata['iTotalRecords'] = $row_getDataLength['dataCnt'];
$gotdata['iTotalDisplayRecords'] = $row_getDataLength['dataCnt'];

}

?>