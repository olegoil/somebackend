<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {


	$colname_newsid = "-1";
	if (isset($themsg['newsid'])) {
	  $colname_newsid = protect($themsg['newsid']);
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

	if($colname_getUser5 == 'del') {

		$query_getNewsChng = "SELECT * FROM news WHERE news_institution = '".$colname_getUser2."' && news_id = '".$colname_newsid."' LIMIT 1";
		$getNewsChng = mysql_query($query_getNewsChng, $echoloyalty) or die(mysql_error());
		$row_getNewsChng = mysql_fetch_assoc($getNewsChng);
		$getNewsChngRows  = mysql_num_rows($getNewsChng);

		if($getNewsChngRows > 0) {

			$delNews = "UPDATE news SET news_when='1', news_state='0', news_del='1' WHERE news_institution = '".$colname_getUser2."' && news_id='".$colname_newsid."'";
			mysql_query($delNews, $echoloyalty) or die(mysql_error());

			// $rootLink = '/httpdocs/admin/';

			// if(unlink($rootLink.'img/news/'.$colname_getUser2.'/slide/'.$row_getNewsChng['news_pic']) && unlink($rootLink.'img/news/'.$colname_getUser2.'/pic/'.$row_getNewsChng['news_pic']) && unlink($rootLink.'img/news/'.$colname_getUser2.'/th/'.$row_getNewsChng['news_pic'])) {

			//     $delNews = "DELETE FROM news WHERE news_institution = '".$colname_getUser2."' && news_id='".$colname_newsid."'";
			//     mysql_query($delNews, $echoloyalty) or die(mysql_error());

			// }

			$newarrmes = array("requests" => '1', "newsId" => $colname_newsid, "newsUpd" => '2');
			array_push($gotdata, $newarrmes);

		}

	}
	else if($colname_getUser5 == 'change') {

		$query_getNewsChng = "SELECT * FROM news WHERE news_institution = '".$colname_getUser2."' && news_id = '".$colname_chid."' LIMIT 1";
		$getNewsChng = mysql_query($query_getNewsChng, $echoloyalty) or die(mysql_error());
		$row_getNewsChng = mysql_fetch_assoc($getNewsChng);
		$getNewsChngRows  = mysql_num_rows($getNewsChng);

		if($getNewsChngRows > 0) {

			$chngNews = "UPDATE news SET news_name='".$colname_newtitle."', news_message='".$colname_newmessage."', news_when='".$when."' WHERE news_institution = '".$colname_getUser2."' && news_id='".$colname_chid."'";
			mysql_query($chngNews, $echoloyalty) or die(mysql_error());

			$newarrmes = array("requests" => '1', "newsId" => $colname_chid, "newsUpd" => '3');
			array_push($gotdata, $newarrmes);

		}

	}
	else {

		$query_getNewsChng = "SELECT * FROM news WHERE news_institution = '".$colname_getUser2."' && news_id = '".$colname_newsid."' LIMIT 1";
		$getNewsChng = mysql_query($query_getNewsChng, $echoloyalty) or die(mysql_error());
		$row_getNewsChng = mysql_fetch_assoc($getNewsChng);
		$getNewsChngRows  = mysql_num_rows($getNewsChng);
		
		if($getNewsChngRows > 0) {
			if($row_getNewsChng['news_state'] == '0') {
				$updNews = "UPDATE news SET news_state='1', news_when='".$when."' WHERE news_id='".$colname_newsid."'";
				mysql_query($updNews, $echoloyalty) or die(mysql_error());
				$newarrmes = array("requests" => '1', "newsId" => $colname_newsid, "newsUpd" => '1');
				array_push($gotdata, $newarrmes);
			}
			else {
				$updNews = "UPDATE news SET news_state='0', news_when='".$when."' WHERE news_id='".$colname_newsid."'";
				mysql_query($updNews, $echoloyalty) or die(mysql_error());
				$newarrmes = array("requests" => '1', "newsId" => $colname_newsid, "newsUpd" => '0');
				array_push($gotdata, $newarrmes);
			}
		}

	}


}
else {
	
	// DT INPUT FOR SEARCHING
	$aColumns = array( 'news_id', 'news_name', 'news_message', 'news_when' );

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
	$sWhere = "WHERE news_institution = '".$colname_getUser2."' && news_del = '0' && news_when > '1'";
	if ( $_POST['sSearch'] != "" )
	{
	  $sWhere = "WHERE news_institution = '".$colname_getUser2."' && news_del = '0' && news_when > '1' && (";
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
	$query_getDataC = "SELECT * FROM news $sWhere $sOrder $sLimit";
	$getDataC = mysql_query($query_getDataC, $echoloyalty) or die(mysql_error());
	$row_getDataC = mysql_fetch_assoc($getDataC);
	$getDataCRows  = mysql_num_rows($getDataC);

	// DT INPUT
	$query_getDataLength = "SELECT COUNT(*) AS dataCnt FROM news WHERE news_institution = '".$colname_getUser2."' && news_del = '0' && news_when > '1'";
	$getDataLength = mysql_query($query_getDataLength, $echoloyalty) or die(mysql_error());
	$row_getDataLength = mysql_fetch_assoc($getDataLength);
	$getDataLengthRows  = mysql_num_rows($getDataLength);
  
  if($getDataCRows > 0) {

	do {
	  
	  $gotdata['aaData'][] = array($row_getDataC['news_id'], $row_getDataC['news_name'], $row_getDataC['news_message'], $row_getDataC['news_pic'], $row_getDataC['news_institution'], $row_getDataC['news_state'], $row_getDataC['news_when']);
	  
	} while ($row_getDataC = mysql_fetch_assoc($getDataC));

  }

	// DT OUTPUT
	$gotdata['sEcho'] = intval($_POST['sEcho']);
	$gotdata['iTotalRecords'] = $row_getDataLength['dataCnt'];
	$gotdata['iTotalDisplayRecords'] = $row_getDataLength['dataCnt'];

}

?>