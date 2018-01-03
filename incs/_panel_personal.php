<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

	$colname_name = "-1";
	if (isset($themsg['name'])) {
	  $colname_name = protect($themsg['name']);
	}
	$colname_surname = "-1";
	if (isset($themsg['surname'])) {
	  $colname_surname = protect($themsg['surname']);
	}
	$colname_patronymics = "-1";
	if (isset($themsg['patronymics'])) {
	  $colname_patronymics = protect($themsg['patronymics']);
	}
	$colname_email = "-1";
	if (isset($themsg['email'])) {
	  $colname_email = protect($themsg['email']);
	}
	$colname_pwd = "-1";
	if (isset($themsg['pwd'])) {
	  $colname_pwd = protect($themsg['pwd']);
	}
	$colname_phone2 = "-1";
	if (isset($themsg['phone2'])) {
	  $colname_phone2 = protect($themsg['phone2']);
	}
	$colname_phone1 = "-1";
	if (isset($themsg['phone1'])) {
	  $colname_phone1 = protect($themsg['phone1']);
	}
	$colname_working_pos = "-1";
	if (isset($themsg['working_pos'])) {
	  $colname_working_pos = protect($themsg['working_pos']);
	}
	$colname_office = "-1";
	if (isset($themsg['office'])) {
	  $colname_office = protect($themsg['office']);
	}
	$colname_gender = "-1";
	if (isset($themsg['gender'])) {
	  $colname_gender = protect($themsg['gender']);
	}
	$colname_workid = "-1";
	if (isset($themsg['workid'])) {
	  $colname_workid = protect($themsg['workid']);
	}
	
	if($colname_getUser5 == 'send') {

		$query_getUsersProof = "SELECT * FROM users WHERE (user_institution = '".$colname_getUser2."' && user_mob = '".$colname_phone1."' && user_mob != '') OR (user_institution = '".$colname_getUser2."' && user_tel = '".$colname_phone2."' && user_tel != '')";
		$getUsersProof = mysql_query($query_getUsersProof, $echoloyalty) or die(mysql_error());
		$row_getUsersProof = mysql_fetch_assoc($getUsersProof);
		$getUsersProofRows  = mysql_num_rows($getUsersProof);
		
		if($getUsersProofRows == 0) {

			$query_getUsersProofPhone = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_pwd = '".$colname_pwd."'";
			$getUsersProofPhone = mysql_query($query_getUsersProofPhone, $echoloyalty) or die(mysql_error());
			$row_getUsersProofPhone = mysql_fetch_assoc($getUsersProofPhone);
			$getUsersProofPhoneRows = mysql_num_rows($getUsersProofPhone);

			if($getUsersProofPhoneRows == 0) {

				$insrtUsr = "INSERT INTO users (user_name, user_surname, user_middlename, user_email, user_pwd, user_tel, user_mob, user_mob_confirm, user_work_pos, user_office, user_institution, user_gender, user_promo, user_upd, user_reg) VALUES ('".$colname_name."', '".$colname_surname."', '".$colname_patronymics."', '".$colname_email."', '".$colname_pwd."', '".$colname_phone2."', '".$colname_phone1."', '1', '".$colname_working_pos."', '".$colname_office."', '".$colname_getUser2."', '".$colname_gender."', '1', '".$when."', '".$when."')";
				mysql_query($insrtUsr, $echoloyalty) or die(mysql_error());

				$query_getNewPersonal = "SELECT * FROM users WHERE user_reg = '".$when."' && user_institution = '".$colname_getUser2."' LIMIT 1";
				$getNewPersonal = mysql_query($query_getNewPersonal, $echoloyalty) or die(mysql_error());
				$row_getNewPersonal = mysql_fetch_assoc($getNewPersonal);
				$getNewPersonalRows  = mysql_num_rows($getNewPersonal);

				$startwallet = 0;

				$insrtWallet = "INSERT INTO wallet (wallet_user, wallet_institution, wallet_total, wallet_when) VALUES ('".$row_getNewPersonal['user_id']."', '".$colname_getUser2."', '".$startwallet."', '".$when."')";
				mysql_query($insrtWallet, $echoloyalty) or die(mysql_error());

				$newarrmes = array("requests" => '1', "usrId" => $row_getNewPersonal['user_id'], "usrReg" => $when);
				array_push($gotdata, $newarrmes);

			}
			else {
				$newarrmes = array("requests" => '3', "usrReg" => $when);
				array_push($gotdata, $newarrmes);
			}
			
		}
		else {
			$newarrmes = array("requests" => '2', "gettherows" => $getUsersProofRows, "getthephone" => $colname_phone1);
			array_push($gotdata, $newarrmes);
		}

	}
	else if($colname_getUser5 == 'del') {

		$query_getUsersEmployee = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_id = '".$colname_workid."'";
		$getUsersEmployee = mysql_query($query_getUsersEmployee, $echoloyalty) or die(mysql_error());
		$row_getUsersEmployee = mysql_fetch_assoc($getUsersEmployee);
		$getUsersEmployeeRows  = mysql_num_rows($getUsersEmployee);

		if($getUsersEmployeeRows > 0) {

			$updEmployee = "UPDATE users SET user_del = '1', user_upd = '".$when."' WHERE user_institution = '".$colname_getUser2."' && user_id = '".$colname_workid."'";
			mysql_query($updEmployee, $echoloyalty) or die(mysql_error());

			$newarrmes = array("del" => '1', "workerid" => $colname_workid);
			array_push($gotdata, $newarrmes);
		}
		else {
			$newarrmes = array("del" => '2', "workerid" => $colname_workid);
			array_push($gotdata, $newarrmes);
		}

	}
	
}
else {

	// DT INPUT FOR SEARCHING
	$aColumns = array( 'user_id', 'user_name', 'user_surname', 'user_email', 'user_tel', 'user_mob', 'user_birthday', 'user_adress', 'user_reg' );

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
	$sWhere = "WHERE user_institution = '".$colname_getUser2."' && user_work_pos >= '2' && user_del = '0'";
	if ( $_POST['sSearch'] != "" )
	{
	  $sWhere = "WHERE user_institution = '".$colname_getUser2."' && user_work_pos >= '2' && user_del = '0' && (";
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
	$query_getDataC = "SELECT * FROM users $sWhere $sOrder $sLimit";
	$getDataC = mysql_query($query_getDataC, $echoloyalty) or die(mysql_error());
	$row_getDataC = mysql_fetch_assoc($getDataC);
	$getDataCRows  = mysql_num_rows($getDataC);

	// DT INPUT
	$query_getDataLength = "SELECT COUNT(*) AS dataCnt FROM users WHERE user_institution = '".$colname_getUser2."' && user_work_pos >= '2' && user_del = '0'";
	$getDataLength = mysql_query($query_getDataLength, $echoloyalty) or die(mysql_error());
	$row_getDataLength = mysql_fetch_assoc($getDataLength);
	$getDataLengthRows  = mysql_num_rows($getDataLength);

	$usrsarr = array();
	if($getDataCRows > 0) {

		do {
		  
		  $query_getCityName = "SELECT * FROM city WHERE id_city = '".$row_getDataC['user_city']."' && id_country = '".$row_getDataC['user_country']."'";
		  $getCityName = mysql_query($query_getCityName, $echoloyalty) or die(mysql_error());
		  $row_getCityName = mysql_fetch_assoc($getCityName);
		  $getCityNameRows  = mysql_num_rows($getCityName);
		  
		  $query_getLastBuy = "SELECT * FROM points WHERE points_institution = '".$colname_getUser2."' && points_user = '".$row_getDataC['user_id']."' ORDER BY points_id DESC LIMIT 1";
		  $getLastBuy = mysql_query($query_getLastBuy, $echoloyalty) or die(mysql_error());
		  $row_getLastBuy = mysql_fetch_assoc($getLastBuy);
		  $getLastBuyRows  = mysql_num_rows($getLastBuy);
		  
		  $query_getAllBuy = "SELECT SUM(points_bill) AS SumBills FROM points WHERE points_institution = '".$colname_getUser2."' && points_user = '".$row_getDataC['user_id']."'";
		  $getAllBuy = mysql_query($query_getAllBuy, $echoloyalty) or die(mysql_error());
		  $row_getAllBuy = mysql_fetch_assoc($getAllBuy);
		  $getAllBuyRows  = mysql_num_rows($getAllBuy);
		  
		  $lastBuy = 0;
		  $allBuy = 0;
		  if(isset($row_getLastBuy['points_bill'])) {
			$lastBuy = $row_getLastBuy['points_bill'];
		  }
		  if(isset($row_getAllBuy['SumBills'])) {
			$allBuy = $row_getAllBuy['SumBills'];
		  }
		  
		  $gotdata['aaData'][] = array($row_getDataC['user_id'], $row_getDataC['user_name'], $row_getDataC['user_surname'], $row_getDataC['user_middlename'], $row_getDataC['user_email'], $row_getDataC['user_tel'], $row_getDataC['user_mob'], $row_getDataC['user_gender'], $row_getDataC['user_birthday'], $row_getDataC['user_adress'], $row_getDataC['user_reg'], $lastBuy, $allBuy, $row_getDataC['user_pic'], $row_getDataC['user_work_pos'], $row_getDataC['user_office'], $row_getDataC['user_pwd']);
		  
		} while ($row_getDataC = mysql_fetch_assoc($getDataC));

	}

	// DT OUTPUT
	$gotdata['sEcho'] = intval($_POST['sEcho']);
	$gotdata['iTotalRecords'] = $row_getDataLength['dataCnt'];
	$gotdata['iTotalDisplayRecords'] = $row_getDataLength['dataCnt'];
		  
}

?>