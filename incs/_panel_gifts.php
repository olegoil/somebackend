<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

	$colname_giftid = "-1";
	if (isset($themsg['giftid'])) {
	  $colname_giftid = protect($themsg['giftid']);
	}
	$colname_chid = "-1";
	if (isset($themsg['chid'])) {
	  $colname_chid = protect($themsg['chid']);
	}
	$colname_newtitle = "-1";
	if (isset($themsg['newtitle'])) {
	  $colname_newtitle = protect($themsg['newtitle']);
	}
	$colname_newmessage = "-1";
	if (isset($themsg['newmessage'])) {
	  $colname_newmessage = protect($themsg['newmessage']);
	}
	$colname_newpoints = "-1";
	if (isset($themsg['newpoints'])) {
	  $colname_newpoints = protect($themsg['newpoints']);
	}
				  
	if($colname_getUser5 == 'del') {

		$query_getGiftsChng = "SELECT * FROM gifts WHERE gifts_institution = '".$colname_getUser2."' && gifts_id = '".$colname_giftid."' LIMIT 1";
		$getGiftsChng = mysql_query($query_getGiftsChng, $echoloyalty) or die(mysql_error());
		$row_getGiftsChng = mysql_fetch_assoc($getGiftsChng);
		$getGiftsChngRows  = mysql_num_rows($getGiftsChng);

		if($getGiftsChngRows > 0) {

			$delGifts = "UPDATE gifts SET gifts_when='1', gifts_del='1' WHERE gifts_institution = '".$colname_getUser2."' && gifts_id='".$colname_giftid."'";
			mysql_query($delGifts, $echoloyalty) or die(mysql_error());

			// $rootLink = '/httpdocs/admin/';

			// if(unlink($rootLink.'img/gifts/'.$colname_getUser2.'/slide/'.$row_getGiftsChng['gifts_pic']) && unlink($rootLink.'img/gifts/'.$colname_getUser2.'/pic/'.$row_getGiftsChng['gifts_pic']) && unlink($rootLink.'img/gifts/'.$colname_getUser2.'/th/'.$row_getGiftsChng['gifts_pic'])) {

			//     $delGifts = "DELETE FROM gifts WHERE gifts_institution = '".$colname_getUser2."' && gifts_id='".$colname_giftid."'";
			//     mysql_query($delGifts, $echoloyalty) or die(mysql_error());

			// }

			$newarrmes = array("requests" => '1', "giftsId" => $colname_giftid, "giftsUpd" => '2');
			array_push($gotdata, $newarrmes);

		}

	}
	else if($colname_getUser5 == 'first') {

		$query_getGiftsChng = "SELECT * FROM gifts WHERE gifts_institution = '".$colname_getUser2."' && gifts_id = '".$colname_giftid."' LIMIT 1";
		$getGiftsChng = mysql_query($query_getGiftsChng, $echoloyalty) or die(mysql_error());
		$row_getGiftsChng = mysql_fetch_assoc($getGiftsChng);
		$getGiftsChngRows  = mysql_num_rows($getGiftsChng);

		if($getGiftsChngRows > 0) {

		   $rootLink = '/var/www/vhosts/xxx.com/httpdocs/admin/';

			if($row_getGiftsChng['gifts_when'] > '2') {

				$updGifts = "UPDATE gifts SET gifts_when = '2' WHERE gifts_institution = '".$colname_getUser2."' && gifts_id='".$colname_giftid."'";
				mysql_query($updGifts, $echoloyalty) or die(mysql_error());

				$newarrmes = array("requests" => '1', "giftsId" => $colname_giftid, "giftsUpd" => '4');
			array_push($gotdata, $newarrmes);

			}
			else if($row_getGiftsChng['gifts_when'] == '2') {

				$updGifts = "UPDATE gifts SET gifts_when = '".$when."' WHERE gifts_institution = '".$colname_getUser2."' && gifts_id='".$colname_giftid."'";
				mysql_query($updGifts, $echoloyalty) or die(mysql_error());

				$newarrmes = array("requests" => '1', "giftsId" => $colname_giftid, "giftsUpd" => '3');
			array_push($gotdata, $newarrmes);

			}

		}

	}
	else if($colname_getUser5 == 'change') {

		$query_getGiftsChng = "SELECT * FROM gifts WHERE gifts_institution = '".$colname_getUser2."' && gifts_id = '".$colname_chid."' LIMIT 1";
		$getGiftsChng = mysql_query($query_getGiftsChng, $echoloyalty) or die(mysql_error());
		$row_getGiftsChng = mysql_fetch_assoc($getGiftsChng);
		$getGiftsChngRows  = mysql_num_rows($getGiftsChng);

		if($getGiftsChngRows > 0) {

			$chngGifts = "UPDATE gifts SET gifts_name='".$colname_newtitle."', gifts_desc='".$colname_newmessage."', gifts_points='".$colname_newpoints."', gifts_when='".$when."' WHERE gifts_institution = '".$colname_getUser2."' && gifts_id='".$colname_chid."'";
			mysql_query($chngGifts, $echoloyalty) or die(mysql_error());

			$newarrmes = array("requests" => '1', "giftsId" => $colname_chid, "giftsUpd" => '5');
			array_push($gotdata, $newarrmes);

		}

	}
	else {

	  $query_getGiftsChng = "SELECT * FROM gifts WHERE gifts_institution = '".$colname_getUser2."' && gifts_id = '".$colname_getUser5."' LIMIT 1";
	  $getGiftsChng = mysql_query($query_getGiftsChng, $echoloyalty) or die(mysql_error());
	  $row_getGiftsChng = mysql_fetch_assoc($getGiftsChng);
	  $getGiftsChngRows  = mysql_num_rows($getGiftsChng);
	  
	  if($getGiftsChngRows > 0) {
		  if($row_getGiftsChng['gifts_when'] == '1') {
			  $updGifts = "UPDATE gifts SET gifts_when='".$when."' WHERE gifts_id='".$colname_getUser5."'";
			  mysql_query($updGifts, $echoloyalty) or die(mysql_error());
			  $newarrmes = array("requests" => '1', "giftsId" => $colname_getUser5, "giftsUpd" => "0");
			  array_push($gotdata, $newarrmes);
		  }
		  else {
			  $updGifts = "UPDATE gifts SET gifts_when='1' WHERE gifts_id='".$colname_getUser5."'";
			  mysql_query($updGifts, $echoloyalty) or die(mysql_error());
			  $newarrmes = array("requests" => '1', "giftsId" => $colname_getUser5, "giftsUpd" => '1');
			  array_push($gotdata, $newarrmes);
		  }
	  }

	}

}
else {

	// DT INPUT FOR SEARCHING
	$aColumns = array( 'gifts_id', 'gifts_name', 'gifts_desc', 'gifts_points', 'gifts_when' );

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
	$sWhere = "WHERE gifts_institution = '".$colname_getUser2."' && gifts_del='0'";
	if ( $_POST['sSearch'] != "" )
	{
	  $sWhere = "WHERE gifts_institution = '".$colname_getUser2."' && gifts_del='0' && (";
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
	$query_getDataC = "SELECT * FROM gifts $sWhere $sOrder $sLimit";
	$getDataC = mysql_query($query_getDataC, $echoloyalty) or die(mysql_error());
	$row_getDataC = mysql_fetch_assoc($getDataC);
	$getDataCRows  = mysql_num_rows($getDataC);

	// DT INPUT
	$query_getDataLength = "SELECT COUNT(*) AS dataCnt FROM gifts WHERE gifts_institution = '".$colname_getUser2."' && gifts_del='0'";
	$getDataLength = mysql_query($query_getDataLength, $echoloyalty) or die(mysql_error());
	$row_getDataLength = mysql_fetch_assoc($getDataLength);
	$getDataLengthRows  = mysql_num_rows($getDataLength);
  
	if($getDataCRows > 0) {

		do {

			$gotdata['aaData'][] = array($row_getDataC['gifts_id'], $row_getDataC['gifts_name'], $row_getDataC['gifts_desc'], $row_getDataC['gifts_points'], $row_getDataC['gifts_pic'], $row_getDataC['gifts_institution'], $row_getDataC['gifts_when']);

		} while ($row_getDataC = mysql_fetch_assoc($getDataC));

	}
	
	// DT OUTPUT
	$gotdata['sEcho'] = intval($_POST['sEcho']);
	$gotdata['iTotalRecords'] = $row_getDataLength['dataCnt'];
	$gotdata['iTotalDisplayRecords'] = $row_getDataLength['dataCnt'];

}

?>