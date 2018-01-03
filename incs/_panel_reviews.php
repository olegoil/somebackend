<?php

// DT INPUT FOR SEARCHING
$aColumns = array( 'reviews_id', 'reviews_from', 'reviews_to', 'reviews_message', 'reviews_rate' );

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
$sWhere = "WHERE reviews_institution = '".$colname_getUser2."' && reviews_when > '2' && reviews_del = '0'";
if ( $_POST['sSearch'] != "" )
{
  $sWhere = "WHERE reviews_institution = '".$colname_getUser2."' && reviews_when > '2' && reviews_del = '0' && (";
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
$query_getDataC = "SELECT * FROM reviews $sWhere $sOrder $sLimit";
$getDataC = mysql_query($query_getDataC, $echoloyalty) or die(mysql_error());
$row_getDataC = mysql_fetch_assoc($getDataC);
$getDataCRows  = mysql_num_rows($getDataC);

// DT INPUT
$query_getDataLength = "SELECT COUNT(*) AS dataCnt FROM reviews WHERE reviews_institution = '".$colname_getUser2."' && reviews_when > '2' && reviews_del = '0'";
$getDataLength = mysql_query($query_getDataLength, $echoloyalty) or die(mysql_error());
$row_getDataLength = mysql_fetch_assoc($getDataLength);
$getDataLengthRows  = mysql_num_rows($getDataLength);

if($getDataCRows > 0) {

  do {
	
	$query_getUsersC = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_id = '".$row_getDataC['reviews_from']."'";
	$getUsersC = mysql_query($query_getUsersC, $echoloyalty) or die(mysql_error());
	$row_getUsersC = mysql_fetch_assoc($getUsersC);
	$getUsersCRows  = mysql_num_rows($getUsersC);
			  
	$gotdata['aaData'][] = array($row_getDataC['reviews_id'], $row_getUsersC['user_surname'], $row_getDataC['reviews_from'], $row_getDataC['reviews_message'], $row_getDataC['reviews_pic'], $row_getDataC['reviews_answered'], $row_getDataC['reviews_when'], $row_getDataC['reviews_rate']);
	
  } while ($row_getDataC = mysql_fetch_assoc($getDataC));

}

// DT OUTPUT
$gotdata['sEcho'] = intval($_POST['sEcho']);
$gotdata['iTotalRecords'] = $row_getDataLength['dataCnt'];
$gotdata['iTotalDisplayRecords'] = $row_getDataLength['dataCnt'];

?>