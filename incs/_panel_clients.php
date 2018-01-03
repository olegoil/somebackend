<?php

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
$sWhere = "WHERE user_institution = '".$colname_getUser2."' && user_work_pos = '0' && user_del = '0'";
if ( $_POST['sSearch'] != "" )
{
  $sWhere = "WHERE user_institution = '".$colname_getUser2."' && user_work_pos = '0' && user_del = '0' && (";
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
$query_getDataLength = "SELECT COUNT(*) AS dataCnt FROM users WHERE user_institution = '".$colname_getUser2."' && user_work_pos='0' && user_del='0'";
$getDataLength = mysql_query($query_getDataLength, $echoloyalty) or die(mysql_error());
$row_getDataLength = mysql_fetch_assoc($getDataLength);
$getDataLengthRows  = mysql_num_rows($getDataLength);

if($getDataCRows > 0) {

  do {
    
    // $query_getCityName = "SELECT * FROM city WHERE id_city = '".$row_getDataC['user_city']."' && id_country = '".$row_getDataC['user_country']."'";
    // $getCityName = mysql_query($query_getCityName, $echoloyalty) or die(mysql_error());
    // $row_getCityName = mysql_fetch_assoc($getCityName);
    // $getCityNameRows  = mysql_num_rows($getCityName);
    
    $query_getLastBuy = "SELECT * FROM points WHERE points_institution = '".$colname_getUser2."' && points_bill > '0' && points_user = '".$row_getDataC['user_id']."' ORDER BY points_id DESC LIMIT 1";
    $getLastBuy = mysql_query($query_getLastBuy, $echoloyalty) or die(mysql_error());
    $row_getLastBuy = mysql_fetch_assoc($getLastBuy);
    $getLastBuyRows  = mysql_num_rows($getLastBuy);
    
    $query_getAllBuy = "SELECT *, SUM(points_bill) AS SumBills FROM points WHERE points_institution = '".$colname_getUser2."' && points_bill > '0' && points_user = '".$row_getDataC['user_id']."'";
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

    // DT OUTPUT
    $gotdata['aaData'][] = array($row_getDataC['user_id'], $row_getDataC['user_name'], $row_getDataC['user_surname'], $row_getDataC['user_middlename'], $row_getDataC['user_email'], $row_getDataC['user_tel'], $row_getDataC['user_mob'], $row_getDataC['user_gender'], $row_getDataC['user_birthday'], $row_getDataC['user_adress'], $row_getDataC['user_reg'], $lastBuy, $allBuy, $row_getDataC['user_pic']);
    
  } while ($row_getDataC = mysql_fetch_assoc($getDataC));

}

// DT OUTPUT
$gotdata['sEcho'] = intval($_POST['sEcho']);
$gotdata['iTotalRecords'] = $row_getDataLength['dataCnt'];
$gotdata['iTotalDisplayRecords'] = $row_getDataLength['dataCnt'];

?>