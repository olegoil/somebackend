<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

	$colname_title = "-1";
	if (isset($themsg['title'])) {
	  $colname_title = protect($themsg['title']);
	}
	$colname_description = "-1";
	if (isset($themsg['description'])) {
	  $colname_description = protect($themsg['description']);
	}
	$colname_profid = "-1";
	if (isset($themsg['profid'])) {
	  $colname_profid = protect($themsg['profid']);
	}

	if($colname_getUser5 == 'del') {

		$query_getProfChng = "SELECT * FROM professions WHERE prof_institution = '".$colname_getUser2."' && prof_id = '".$colname_profid."' LIMIT 1";
		$getProfChng = mysql_query($query_getProfChng, $echoloyalty) or die(mysql_error());
		$row_getProfChng = mysql_fetch_assoc($getProfChng);
		$getProfChngRows  = mysql_num_rows($getProfChng);

		if($getProfChngRows > 0) {

			$delProf = "UPDATE professions SET prof_del='1' WHERE prof_institution = '".$colname_getUser2."' && prof_id='".$colname_profid."'";
			mysql_query($delProf, $echoloyalty) or die(mysql_error());

			$newarrmes = array("requests" => '1', "profId" => $colname_profid, "profDel" => '1');
			array_push($gotdata, $newarrmes);

		}

	}
	else if($colname_getUser5 == 'create') {

	  if($colname_profid == '0') {

		$insProf = "INSERT INTO professions (prof_name, prof_desc, prof_institution, prof_when) VALUES ('".$colname_title."', '".$colname_description."', '".$colname_getUser2."', '".$when."')";
		mysql_query($insProf, $echoloyalty) or die(mysql_error());

		$query_getProfChng = "SELECT * FROM professions WHERE prof_institution = '".$colname_getUser2."' && prof_when = '".$when."' ORDER BY prof_id DESC LIMIT 1";
		$getProfChng = mysql_query($query_getProfChng, $echoloyalty) or die(mysql_error());
		$row_getProfChng = mysql_fetch_assoc($getProfChng);
		$getProfChngRows  = mysql_num_rows($getProfChng);

		if($getProfChngRows > 0) {

		  $newarrmes = array("requests" => '1', "profId" => $row_getProfChng['prof_id'], "profIns" => '1', 'when' => $when);
		  array_push($gotdata, $newarrmes);

		}

	  }

	}
	else if($colname_getUser5 == 'change') {

	  if($colname_profid != '0') {
	  
		$query_getProfChng = "SELECT * FROM professions WHERE prof_institution = '".$colname_getUser2."' && prof_id = '".$colname_profid."' ORDER BY prof_id DESC LIMIT 1";
		$getProfChng = mysql_query($query_getProfChng, $echoloyalty) or die(mysql_error());
		$row_getProfChng = mysql_fetch_assoc($getProfChng);
		$getProfChngRows  = mysql_num_rows($getProfChng);

		if($getProfChngRows > 0) {

			$updProf = "UPDATE professions SET prof_name = '".$colname_title."', prof_desc = '".$colname_description."', prof_when = '".$when."' WHERE prof_institution = '".$colname_getUser2."' && prof_id = '".$colname_profid."'";
			 mysql_query($updProf, $echoloyalty) or die(mysql_error());

			$newarrmes = array("requests" => '1', "profId" => $colname_profid, "profUpd" => '1', "when" => $when);
			array_push($gotdata, $newarrmes);

		}

	  }
	  
	}

}
else {

	// DT INPUT FOR SEARCHING
	$aColumns = array( 'prof_id', 'prof_name', 'prof_desc', 'prof_institution', 'prof_when' );

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
	$sWhere = "WHERE (prof_institution = '".$colname_getUser2."' OR prof_institution = '0') && prof_when > '2' && prof_del = '0'";
	if ( $_POST['sSearch'] != "" )
	{
	  $sWhere = "WHERE (prof_institution = '".$colname_getUser2."' OR prof_institution = '0') && prof_when > '2' && prof_del = '0' && (";
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
	$query_getDataC = "SELECT * FROM professions $sWhere $sOrder $sLimit";
	$getDataC = mysql_query($query_getDataC, $echoloyalty) or die(mysql_error());
	$row_getDataC = mysql_fetch_assoc($getDataC);
	$getDataCRows  = mysql_num_rows($getDataC);

	// DT INPUT
	$query_getDataLength = "SELECT COUNT(*) AS dataCnt FROM professions WHERE prof_institution = '".$colname_getUser2."' && prof_when > '2' && prof_del = '0'";
	$getDataLength = mysql_query($query_getDataLength, $echoloyalty) or die(mysql_error());
	$row_getDataLength = mysql_fetch_assoc($getDataLength);
	$getDataLengthRows  = mysql_num_rows($getDataLength);

	if($getDataCRows > 0) {

	  do {
		
		$gotdata['aaData'][] = array($row_getDataC['prof_id'], $row_getDataC['prof_name'], $row_getDataC['prof_desc'], $row_getDataC['prof_institution'], $row_getDataC['prof_when']);
		
	  } while ($row_getDataC = mysql_fetch_assoc($getDataC));

	}

	// DT OUTPUT
	$gotdata['sEcho'] = intval($_POST['sEcho']);
	$gotdata['iTotalRecords'] = $row_getDataLength['dataCnt'];
	$gotdata['iTotalDisplayRecords'] = $row_getDataLength['dataCnt'];

}

?>