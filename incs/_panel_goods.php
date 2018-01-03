<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

$colname_catid= "-1";
if (isset($themsg['catid'])) {
  $colname_catid = protect($themsg['catid']);
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

	$query_getGoodChng = "SELECT * FROM goods WHERE goods_institution = '".$colname_getUser2."' && goods_id = '".$colname_catid."' LIMIT 1";
	$getGoodChng = mysql_query($query_getGoodChng, $echoloyalty) or die(mysql_error());
	$row_getGoodChng = mysql_fetch_assoc($getGoodChng);
	$getGoodChngRows  = mysql_num_rows($getGoodChng);

	if($getGoodChngRows > 0) {

	  $delGood = "UPDATE goods SET goods_when='1', goods_del='1' WHERE goods_institution = '".$colname_getUser2."' && goods_id='".$colname_catid."'";
	  mysql_query($delGood, $echoloyalty) or die(mysql_error());

	  $newarrmes = array("requests" => '1', "goodsId" => $colname_catid, "goodsUpd" => '2');
	  array_push($gotdata, $newarrmes);

	}

}
else if($colname_getUser5 == 'change') {

  $query_getGoodChng = "SELECT * FROM goods WHERE goods_institution = '".$colname_getUser2."' && goods_id = '".$colname_chid."' LIMIT 1";
  $getGoodChng = mysql_query($query_getGoodChng, $echoloyalty) or die(mysql_error());
  $row_getGoodChng = mysql_fetch_assoc($getGoodChng);
  $getGoodChngRows  = mysql_num_rows($getGoodChng);

  if($getGoodChngRows > 0) {

	  $updGood = "UPDATE goods SET goods_name='".$colname_newtitle."', goods_desc='".$colname_newmessage."', goods_when='".$when."' WHERE goods_institution = '".$colname_getUser2."' && goods_id='".$colname_chid."'";
	  mysql_query($updGood, $echoloyalty) or die(mysql_error());

	  $newarrmes = array("requests" => '1', "goodsId" => $colname_chid, "goodsUpd" => '1');
	  array_push($gotdata, $newarrmes);

  }

}
  
}
else {

	// DT INPUT FOR SEARCHING
	$aColumns = array( 'goods_id', 'goods_name', 'goods_desc', 'goods_when' );

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
	$sWhere = "WHERE goods_institution = '".$colname_getUser2."' && goods_when > '1' && goods_del = '0'";
	if ( $_POST['sSearch'] != "" )
	{
	  $sWhere = "WHERE goods_institution = '".$colname_getUser2."' && goods_when > '1' && goods_del = '0' && (";
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
	$query_getDataC = "SELECT * FROM goods $sWhere $sOrder $sLimit";
	$getDataC = mysql_query($query_getDataC, $echoloyalty) or die(mysql_error());
	$row_getDataC = mysql_fetch_assoc($getDataC);
	$getDataCRows  = mysql_num_rows($getDataC);

	// DT INPUT
	$query_getDataLength = "SELECT COUNT(*) AS dataCnt FROM goods WHERE goods_institution = '".$colname_getUser2."' && goods_when > '1' && goods_del = '0'";
	$getDataLength = mysql_query($query_getDataLength, $echoloyalty) or die(mysql_error());
	$row_getDataLength = mysql_fetch_assoc($getDataLength);
	$getDataLengthRows  = mysql_num_rows($getDataLength);

	if($getDataCRows > 0) {

	  do {
		  
		  $gotdata['aaData'][] = array($row_getDataC['goods_id'], $row_getDataC['goods_name'], $row_getDataC['goods_desc'], $row_getDataC['goods_pic'], $row_getDataC['goods_institution'], $row_getDataC['goods_when']);
		  
	  } while ($row_getDataC = mysql_fetch_assoc($getDataC));

	}

	// DT OUTPUT
	$gotdata['sEcho'] = intval($_POST['sEcho']);
	$gotdata['iTotalRecords'] = $row_getDataLength['dataCnt'];
	$gotdata['iTotalDisplayRecords'] = $row_getDataLength['dataCnt'];

}

?>