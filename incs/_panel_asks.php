<?php
$when = time();
$sendingok = true;

if(isset($colname_getUser5) && $colname_getUser5 != '%') {

  if ($colname_getUser5 == 'send') {

	$colname_Fullname = "-1";
	if (isset($themsg['fullname'])) {
	  $colname_Fullname = protect($themsg['fullname']);
	}
	$colname_Message = "-1";
	if (isset($themsg['message'])) {
	  $colname_Message = protect($themsg['message']);
	}

	$insertAsks = "INSERT INTO asks (asks_name, asks_message, asks_institution, asks_when) VALUES ('".$colname_Fullname."', '".$colname_Message."', '".$colname_getUser2."', '".$when."')";
	mysql_query($insertAsks, $echoloyalty) or die(mysql_error());

	$query_getAsks = "SELECT * FROM asks WHERE asks_institution = '".$colname_getUser2."' && asks_when='".$when."' ORDER BY asks_id DESC LIMIT 1";
	$getAsks = mysql_query($query_getAsks, $echoloyalty) or die(mysql_error());
	$row_getAsks = mysql_fetch_assoc($getAsks);
	$getAsksRows  = mysql_num_rows($getAsks);

	$newarrmes = array("requests" => '1', "asksId" => $row_getAsks['asks_id'], "asksUpd" => '1', "asksTime" => $row_getAsks['asks_when']);
	array_push($gotdata, $newarrmes);

  }
  else if ($colname_getUser5 == 'del') {

	$colname_asksid = "-1";
	if (isset($themsg['asksid'])) {
	  $colname_asksid = protect($themsg['asksid']);
	}

	$updateAsks = "UPDATE asks SET asks_when='1' WHERE asks_institution = '".$colname_getUser2."' && asks_id = '".$colname_asksid."'";
	mysql_query($updateAsks, $echoloyalty) or die(mysql_error());

	$newarrmes = array("requests" => '1', "asksId" => $colname_asksid, "asksUpd" => '3', "asksTime" => $when);
	array_push($gotdata, $newarrmes);

  }
  else {

	$query_getAsks = "SELECT * FROM asks WHERE asks_institution = '".$colname_getUser2."' && asks_id='".$colname_getUser5."'";
	$getAsks = mysql_query($query_getAsks, $echoloyalty) or die(mysql_error());
	$row_getAsks = mysql_fetch_assoc($getAsks);
	$getAsksRows  = mysql_num_rows($getAsks);

	$insertAsks = "INSERT INTO asks (asks_name, asks_message, asks_institution, asks_when) VALUES ('".$row_getAsks['asks_name']."', '".$row_getAsks['asks_message']."', '".$colname_getUser2."', '".$when."')";
	mysql_query($insertAsks, $echoloyalty) or die(mysql_error());
	
	$query_getLastAsks = "SELECT * FROM asks WHERE asks_institution = '".$colname_getUser2."' ORDER BY asks_id DESC LIMIT 1";
	$getLastAsks = mysql_query($query_getLastAsks, $echoloyalty) or die(mysql_error());
	$row_getLastAsks = mysql_fetch_assoc($getLastAsks);
	$getLastAsksRows  = mysql_num_rows($getLastAsks);

	$newarrmes = array("requests" => '1', "asksId" => $row_getLastAsks['asks_id'], "asksUpd" => '2', "asksTime" => $when, "asksName" => $row_getAsks['asks_name'], "asksMessage" => $row_getAsks['asks_message']);
	array_push($gotdata, $newarrmes);

  }

}
else {
	
// DT INPUT FOR SEARCHING
$aColumns = array( 'asks_id', 'asks_name', 'asks_message', 'asks_when' );

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
$sWhere = "WHERE asks_institution = '".$colname_getUser2."' && asks_del = '0'";
if ( $_POST['sSearch'] != "" )
{
  $sWhere = "WHERE asks_institution = '".$colname_getUser2."' && asks_del = '0' && (";
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
$query_getDataC = "SELECT * FROM asks $sWhere $sOrder $sLimit";
$getDataC = mysql_query($query_getDataC, $echoloyalty) or die(mysql_error());
$row_getDataC = mysql_fetch_assoc($getDataC);
$getDataCRows  = mysql_num_rows($getDataC);

// DT INPUT
$query_getDataLength = "SELECT COUNT(*) AS dataCnt FROM asks WHERE asks_institution = '".$colname_getUser2."' && asks_del = '0'";
$getDataLength = mysql_query($query_getDataLength, $echoloyalty) or die(mysql_error());
$row_getDataLength = mysql_fetch_assoc($getDataLength);
$getDataLengthRows  = mysql_num_rows($getDataLength);

$asksarr = array();
if($getDataCRows > 0) {

  do {

	$asksYes = 0;
	if(isset($row_getDataC['asks_yes'])) {
	  $asksYes = $row_getDataC['asks_yes'];
	}
	$asksNo = 0;
	if(isset($row_getDataC['asks_no'])) {
	  $asksNo = $row_getDataC['asks_no'];
	}
	$todevide = $asksYes + $asksNo;
	if($todevide == 0) {
	  $todevide = 1;
	}
	
	$askPercent = 100 * $asksYes / $todevide;
	$askPercentR = round($askPercent, 2);
	
	$gotdata['aaData'][] = array($row_getDataC['asks_id'], $row_getDataC['asks_name'], $row_getDataC['asks_message'], $askPercentR, $row_getDataC['asks_institution'], $row_getDataC['asks_when'], $asksYes, $asksNo);
	
  } while ($row_getDataC = mysql_fetch_assoc($getDataC));
  
}

// DT OUTPUT
$gotdata['sEcho'] = intval($_POST['sEcho']);
$gotdata['iTotalRecords'] = $row_getDataLength['dataCnt'];
$gotdata['iTotalDisplayRecords'] = $row_getDataLength['dataCnt'];

}

?>