<?php
$when = time();
$sendingok = true;

if(isset($colname_getUser5) && $colname_getUser5 != '%') {

	$colname_userid = "-1";
	if (isset($themsg['userid'])) {
	  $colname_userid = protect($themsg['userid']);
	}
	$colname_lastmes = "-1";
	if (isset($themsg['lastmes'])) {
	  $colname_lastmes = protect($themsg['lastmes']);
	}
	$colname_message = "-1";
	if (isset($themsg['message'])) {
	  $colname_message = protect($themsg['message']);
	}
		
	if($colname_getUser5 == 'send') {

		$insrtMessage = "INSERT INTO chat (chat_from, chat_to, chat_name, chat_message, chat_institution, chat_answered, chat_when) VALUES ('1', '".$colname_userid."', 'support', '".$colname_message."', '".$colname_getUser2."', '1', '".$when."')";
		mysql_query($insrtMessage, $echoloyalty) or die(mysql_error());

		$query_getSupportC = "SELECT * FROM chat WHERE chat_institution = '".$colname_getUser2."' && chat_to = '".$colname_userid."' && chat_from = '1' ORDER BY chat_id DESC LIMIT 1";
		$getSupportC = mysql_query($query_getSupportC, $echoloyalty) or die(mysql_error());
		$row_getSupportC = mysql_fetch_assoc($getSupportC);
		$getSupportCRows  = mysql_num_rows($getSupportC);
		
		$supportarr = array();
		if($getSupportCRows > 0) {
				
			array_push($supportarr, array($row_getSupportC['chat_id'], $row_getSupportC['chat_from'], $row_getSupportC['chat_to'], $row_getSupportC['chat_name'], $row_getSupportC['chat_message'], $row_getSupportC['chat_read'], $row_getSupportC['chat_institution'], $row_getSupportC['chat_answered'], $row_getSupportC['chat_when']));
				
		}

		$query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_id = '".$colname_userid."'";
		$getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
		$row_getGCM = mysql_fetch_assoc($getGCM);
		$getGCMRows  = mysql_num_rows($getGCM);

		if($getGCMRows > 0) {

		  $apiKey =  urldecode($row_getInst['org_key']);
		  
		  $title = urldecode("Техподдержка: ");

		  $messageand = urldecode($colname_message);

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

		  sendGCM(0, $iosIDs, $row_getInst['org_cert'], "Техподдержка: ", $colname_message, 0, 0);

		}

		$newarrmes = array("requests" => '1', "supportArr" => $supportarr);
		array_push($gotdata, $newarrmes);

	}
	else if($colname_getUser5 == 'get') {

		if($colname_lastmes == '0') {

			$query_getSupportC = "SELECT * FROM chat WHERE chat_institution = '".$colname_getUser2."' && chat_to = '1' && chat_from = '".$colname_userid."' OR chat_institution = '".$colname_getUser2."' && chat_to = '".$colname_userid."' && chat_from = '1' ORDER BY chat_id DESC";
			$getSupportC = mysql_query($query_getSupportC, $echoloyalty) or die(mysql_error());
			$row_getSupportC = mysql_fetch_assoc($getSupportC);
			$getSupportCRows  = mysql_num_rows($getSupportC);
			
			$supportarr = array();
			if($getSupportCRows > 0) {

				do {
					
					array_push($supportarr, array($row_getSupportC['chat_id'], $row_getSupportC['chat_from'], $row_getSupportC['chat_to'], $row_getSupportC['chat_name'], $row_getSupportC['chat_message'], $row_getSupportC['chat_read'], $row_getSupportC['chat_institution'], $row_getSupportC['chat_answered'], $row_getSupportC['chat_when']));
					
				} while ($row_getSupportC = mysql_fetch_assoc($getSupportC));

			}

			$newarrmes = array("requests" => '1', "supportArr" => $supportarr);
			array_push($gotdata, $newarrmes);

		}
		else if($colname_lastmes > '0') {

			$query_getSupportC = "SELECT * FROM chat WHERE chat_id > '".$colname_lastmes."' && chat_institution = '".$colname_getUser2."' && chat_to = '1' && chat_from = '".$colname_userid."' OR chat_id > '".$colname_lastmes."' && chat_institution = '".$colname_getUser2."' && chat_to = '".$colname_userid."' && chat_from = '1' ORDER BY chat_id DESC";
			$getSupportC = mysql_query($query_getSupportC, $echoloyalty) or die(mysql_error());
			$row_getSupportC = mysql_fetch_assoc($getSupportC);
			$getSupportCRows  = mysql_num_rows($getSupportC);
			
			$supportarr = array();
			if($getSupportCRows > 0) {

				do {
					
					array_push($supportarr, array($row_getSupportC['chat_id'], $row_getSupportC['chat_from'], $row_getSupportC['chat_to'], $row_getSupportC['chat_name'], $row_getSupportC['chat_message'], $row_getSupportC['chat_read'], $row_getSupportC['chat_institution'], $row_getSupportC['chat_answered'], $row_getSupportC['chat_when']));
					
				} while ($row_getSupportC = mysql_fetch_assoc($getSupportC));

			}

			$newarrmes = array("requests" => '1', "supportArr" => $supportarr);
			array_push($gotdata, $newarrmes);

		}

	}
	else if($colname_getUser5 == 'close') {
		$updMessage = "UPDATE chat SET chat_answered = '1' WHERE (chat_from = '".$colname_userid."' && chat_to = '1' OR chat_from = '1' && chat_to = '".$colname_userid."') && chat_institution = '".$colname_getUser2."'";
		mysql_query($updMessage, $echoloyalty) or die(mysql_error());
	}
	else {
		
		// DT INPUT FOR SEARCHING
		$aColumns = array( 'chat_id', 'chat_from', 'chat_to', 'chat_name', 'chat_message', 'chat_when' );

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
			$sOrder = "ORDER BY chat_id DESC";
		  }
		}

		// DT INPUT
		$sWhere = "WHERE chat_institution = '".$colname_getUser2."' && chat_to = '1' && chat_from = '".$colname_getUser5."' OR chat_institution = '".$colname_getUser2."' && chat_to = '".$colname_getUser5."' && chat_from = '1'";
		if ( $_POST['sSearch'] != "" )
		{
		  $sWhere = "WHERE chat_institution = '".$colname_getUser2."' && chat_to = '1' && chat_from = '".$colname_getUser5."' OR chat_institution = '".$colname_getUser2."' && chat_to = '".$colname_getUser5."' && chat_from = '1' && (";
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
		$query_getDataC = "SELECT * FROM chat $sWhere $sOrder $sLimit";
		$getDataC = mysql_query($query_getDataC, $echoloyalty) or die(mysql_error());
		$row_getDataC = mysql_fetch_assoc($getDataC);
		$getDataCRows  = mysql_num_rows($getDataC);
		
		if($getDataCRows > 0) {

			do {
				
				$gotdata['aaData'][] = array($row_getDataC['chat_id'], $row_getDataC['chat_from'], $row_getDataC['chat_to'], $row_getDataC['chat_name'], $row_getDataC['chat_message'], $row_getDataC['chat_read'], $row_getDataC['chat_institution'], $row_getDataC['chat_answered'], $row_getDataC['chat_when']);
				
			} while ($row_getDataC = mysql_fetch_assoc($getDataC));
			
		}
		
		// DT OUTPUT
		$gotdata['sEcho'] = intval($_POST['sEcho']);
		$gotdata['iTotalRecords'] = $getDataCRows;
		$gotdata['iTotalDisplayRecords'] = $getDataCRows;

	}

}
else {
	
	// DT INPUT FOR SEARCHING
	$aColumns = array( 'chat_id', 'chat_from', 'chat_to', 'chat_name', 'chat_message', 'chat_when' );

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
		$sOrder = "ORDER BY chat_id DESC";
	  }
	}

	// DT INPUT
	$sWhere = "WHERE chat_id = (SELECT max(chat_id) FROM chat p2 WHERE p2.chat_from = p.chat_from && chat_institution = '".$colname_getUser2."' && chat_to = '1' && chat_from != '1' && chat_answered = '0') && chat_institution = '".$colname_getUser2."' && chat_to = '1' && chat_from != '1' && chat_answered = '0' && chat_del = '0'";
	if ( $_POST['sSearch'] != "" )
	{
	  $sWhere = "WHERE chat_id = (SELECT max(chat_id) FROM chat p2 WHERE p2.chat_from = p.chat_from && chat_institution = '".$colname_getUser2."' && chat_to = '1' && chat_from != '1' && chat_answered = '0') && chat_institution = '".$colname_getUser2."' && chat_to = '1' && chat_from != '1' && chat_answered = '0' && chat_del = '0' && (";
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

	$sGroup = "GROUP BY chat_from";
	
	// DT INPUT
	$query_getDataC = "SELECT * FROM chat p $sWhere $sGroup $sOrder $sLimit";
	$getDataC = mysql_query($query_getDataC, $echoloyalty) or die(mysql_error());
	$row_getDataC = mysql_fetch_assoc($getDataC);
	$getDataCRows  = mysql_num_rows($getDataC);
	
	if($getDataCRows > 0) {

		do {
			
			$gotdata['aaData'][] = array($row_getDataC['chat_id'], $row_getDataC['chat_from'], $row_getDataC['chat_to'], $row_getDataC['chat_name'], $row_getDataC['chat_message'], $row_getDataC['chat_read'], $row_getDataC['chat_institution'], $row_getDataC['chat_answered'], $row_getDataC['chat_when']);
			
		} while ($row_getDataC = mysql_fetch_assoc($getDataC));

	}
	
	// DT OUTPUT
	$gotdata['sEcho'] = intval($_POST['sEcho']);
	$gotdata['iTotalRecords'] = $getDataCRows;
	$gotdata['iTotalDisplayRecords'] = $getDataCRows;

}

?>