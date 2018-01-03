<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

$colname_menueid = "-1";
if (isset($themsg['menueid'])) {
  $colname_menueid = protect($themsg['menueid']);
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
$colname_newmenusize = "-1";
if (isset($themsg['newmenusize'])) {
  $colname_newmenusize = protect($themsg['newmenusize']);
}
$colname_newmenucost = "-1";
if (isset($themsg['newmenucost'])) {
  $colname_newmenucost = protect($themsg['newmenucost']);
}
$colname_newmenucosts = "-1";
if (isset($themsg['newmenucosts'])) {
  $colname_newmenucosts = protect($themsg['newmenucosts']);
}
$colname_newmenuweight = "-1";
if (isset($themsg['newmenuweight'])) {
  $colname_newmenuweight = protect($themsg['newmenuweight']);
}
$colname_newmenuinterval = "-1";
if (isset($themsg['newmenuinterval'])) {
  $colname_newmenuinterval = protect($themsg['newmenuinterval']);
}
$colname_newmenudiscount = "-1";
if (isset($themsg['newmenudiscount'])) {
  $colname_newmenudiscount = protect($themsg['newmenudiscount']);
}
$colname_newmenuaction = "-1";
if (isset($themsg['newmenuaction'])) {
  $colname_newmenuaction = protect($themsg['newmenuaction']);
}
$colname_newmenucode = "-1";
if (isset($themsg['newmenucode'])) {
  $colname_newmenucode = protect($themsg['newmenucode']);
}
$colname_newcat = "-1";
if (isset($themsg['newcat'])) {
  $colname_newcat = protect($themsg['newcat']);
}

if($colname_getUser5 == 'del') {

	$query_getMenuChng = "SELECT * FROM menue WHERE menue_institution = '".$colname_getUser2."' && menue_id = '".$colname_menueid."' LIMIT 1";
	$getMenuChng = mysql_query($query_getMenuChng, $echoloyalty) or die(mysql_error());
	$row_getMenuChng = mysql_fetch_assoc($getMenuChng);
	$getMenuChngRows  = mysql_num_rows($getMenuChng);

	if($getMenuChngRows > 0) {

		$delMenue = "UPDATE menue SET menue_when='1', menue_del='1' WHERE menue_institution = '".$colname_getUser2."' && menue_id='".$colname_menueid."'";
		mysql_query($delMenue, $echoloyalty) or die(mysql_error());

		$menuearrmes = array("requests" => '1', "menueId" => $colname_menueid, "menueUpd" => '2');
		array_push($gotdata, $menuearrmes);

	}

}
else if($colname_getUser5 == 'change') {

  $query_getMenuChng = "SELECT * FROM menue WHERE menue_institution = '".$colname_getUser2."' && menue_id = '".$colname_chid."' LIMIT 1";
  $getMenuChng = mysql_query($query_getMenuChng, $echoloyalty) or die(mysql_error());
  $row_getMenuChng = mysql_fetch_assoc($getMenuChng);
  $getMenuChngRows  = mysql_num_rows($getMenuChng);

  if($getMenuChngRows > 0) {

	  $updMenue = "UPDATE menue SET menue_cat='".$colname_newcat."', menue_name='".$colname_newtitle."', menue_desc='".$colname_newmessage."', menue_size='".$colname_newmenusize."', menue_cost='".$colname_newmenucost."', menue_costs='".$colname_newmenucosts."', menue_weight='".$colname_newmenuweight."', menue_interval='".$colname_newmenuinterval."', menue_discount='".$colname_newmenudiscount."', menue_action='".$colname_newmenuaction."', menue_code='".$colname_newmenucode."', menue_when='".$when."' WHERE menue_institution = '".$colname_getUser2."' && menue_id='".$colname_chid."'";
	  mysql_query($updMenue, $echoloyalty) or die(mysql_error());

	  $menuearrmes = array("requests" => '1', "menueId" => $colname_chid, "menueUpd" => '1');
	  array_push($gotdata, $menuearrmes);

  }

}
  
}
else {

  // DT INPUT FOR SEARCHING
  $aColumns = array( 'menue_id', 'menue_name', 'menue_desc', 'menue_cost', 'menue_weight', 'menue_interval', 'menue_discount', 'menue_code', 'menue_when' );

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
  $sWhere = "WHERE menue_institution = '".$colname_getUser2."' && menue_when > '1' && menue_del = '0'";
  if ( $_POST['sSearch'] != "" )
  {
    $sWhere = "WHERE menue_institution = '".$colname_getUser2."' && menue_when > '1' && menue_del = '0' && (";
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
  $query_getDataC = "SELECT * FROM menue $sWhere $sOrder $sLimit";
  $getDataC = mysql_query($query_getDataC, $echoloyalty) or die(mysql_error());
  $row_getDataC = mysql_fetch_assoc($getDataC);
  $getDataCRows  = mysql_num_rows($getDataC);

  // DT INPUT
  $query_getDataLength = "SELECT COUNT(*) AS dataCnt FROM menue WHERE menue_institution = '".$colname_getUser2."' && menue_when > '1' && menue_del = '0'";
  $getDataLength = mysql_query($query_getDataLength, $echoloyalty) or die(mysql_error());
  $row_getDataLength = mysql_fetch_assoc($getDataLength);
  $getDataLengthRows  = mysql_num_rows($getDataLength);
  
  if($getDataCRows > 0) {

  	do {

  	  $query_getCatChng = "SELECT * FROM categories WHERE cat_institution = '".$colname_getUser2."' && cat_id = '".$row_getDataC['menue_cat']."' LIMIT 1";
  	  $getCatChng = mysql_query($query_getCatChng, $echoloyalty) or die(mysql_error());
  	  $row_getCatChng = mysql_fetch_assoc($getCatChng);
  	  $getCatChngRows  = mysql_num_rows($getCatChng);
  	  
  	  $gotdata['aaData'][] = array($row_getDataC['menue_id'], $row_getDataC['menue_name'], $row_getDataC['menue_desc'], $row_getDataC['menue_pic'], $row_getDataC['menue_institution'], $row_getDataC['menue_when'], $row_getCatChng['cat_name'], $row_getDataC['menue_size'], $row_getDataC['menue_cost'], $row_getDataC['menue_weight'], $row_getDataC['menue_discount'], $row_getDataC['menue_action'], $row_getDataC['menue_code'], $row_getCatChng['cat_ingr'], $row_getDataC['menue_cat'], $row_getDataC['menue_interval'], $row_getDataC['menue_costs']);
  		
  	} while ($row_getDataC = mysql_fetch_assoc($getDataC));

  }

  // DT OUTPUT
  $gotdata['sEcho'] = intval($_POST['sEcho']);
  $gotdata['iTotalRecords'] = $row_getDataLength['dataCnt'];
  $gotdata['iTotalDisplayRecords'] = $row_getDataLength['dataCnt'];

}

?>