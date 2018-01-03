<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

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
  $colname_neworder = "-1";
  if (isset($themsg['neworder'])) {
    $colname_neworder = protect($themsg['neworder']);
  }
  $colname_newgood = "-1";
  if (isset($themsg['newgood'])) {
    $colname_newgood = protect($themsg['newgood']);
  }
  	  
  if($colname_getUser5 == 'del') {

    $query_getCatChng = "SELECT * FROM categories WHERE cat_institution = '".$colname_getUser2."' && cat_id = '".$colname_chid."' LIMIT 1";
    $getCatChng = mysql_query($query_getCatChng, $echoloyalty) or die(mysql_error());
    $row_getCatChng = mysql_fetch_assoc($getCatChng);
    $getCatChngRows  = mysql_num_rows($getCatChng);

    if($getCatChngRows > 0) {

  	$delCat = "UPDATE categories SET cat_when='1', cat_del='1' WHERE cat_institution = '".$colname_getUser2."' && cat_id='".$colname_chid."'";
  	mysql_query($delCat, $echoloyalty) or die(mysql_error());

  	$newarrmes = array("requests" => '1', "catId" => $colname_chid, "catUpd" => '2');
  	array_push($gotdata, $newarrmes);

    }

  }
  else if($colname_getUser5 == 'change') {

    $query_getCatChng = "SELECT * FROM categories WHERE cat_institution = '".$colname_getUser2."' && cat_id = '".$colname_chid."' LIMIT 1";
    $getCatChng = mysql_query($query_getCatChng, $echoloyalty) or die(mysql_error());
    $row_getCatChng = mysql_fetch_assoc($getCatChng);
    $getCatChngRows  = mysql_num_rows($getCatChng);

    if($getCatChngRows > 0) {

  	  $updCat = "UPDATE categories SET cat_name='".$colname_newtitle."', cat_desc='".$colname_newmessage."', cat_ingr='".$colname_newgood."', cat_order='".$colname_neworder."', cat_when='".$when."' WHERE cat_institution = '".$colname_getUser2."' && cat_id='".$colname_chid."'";
  	  mysql_query($updCat, $echoloyalty) or die(mysql_error());

  	  $newarrmes = array("requests" => '1', "catId" => $colname_chid, "catUpd" => '1');
  	  array_push($gotdata, $newarrmes);

    }

  }
  
}
else {

  // DT INPUT FOR SEARCHING
  $aColumns = array( 'cat_id', 'cat_name', 'cat_desc', 'cat_when' );

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
  $sWhere = "WHERE cat_institution = '".$colname_getUser2."' && cat_when > '1' && cat_del = '0'";
  if ( $_POST['sSearch'] != "" )
  {
    $sWhere = "WHERE cat_institution = '".$colname_getUser2."' && cat_when > '1' && cat_del = '0' && (";
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
  $query_getDataC = "SELECT * FROM categories $sWhere $sOrder $sLimit";
  $getDataC = mysql_query($query_getDataC, $echoloyalty) or die(mysql_error());
  $row_getDataC = mysql_fetch_assoc($getDataC);
  $getDataCRows  = mysql_num_rows($getDataC);

  // DT INPUT
  $query_getDataLength = "SELECT COUNT(*) AS dataCnt FROM categories WHERE cat_institution = '".$colname_getUser2."' && cat_when > '1' && cat_del = '0'";
  $getDataLength = mysql_query($query_getDataLength, $echoloyalty) or die(mysql_error());
  $row_getDataLength = mysql_fetch_assoc($getDataLength);
  $getDataLengthRows  = mysql_num_rows($getDataLength);
  
  if($getDataCRows > 0) {

  	do {

  	  $query_getGoodsN = "SELECT goods_name FROM goods WHERE goods_id = '".$row_getDataC['cat_ingr']."' && goods_institution = '".$colname_getUser2."' && goods_when > '1' && goods_del = '0'";
  	  $getGoodsN = mysql_query($query_getGoodsN, $echoloyalty) or die(mysql_error());
  	  $row_getGoodsN = mysql_fetch_assoc($getGoodsN);
  	  $getGoodsNRows  = mysql_num_rows($getGoodsN);

  	  $goodsName = 'Без группы';
  	  if($getGoodsNRows > 0) {
  		$goodsName = $row_getGoodsN['goods_name'];
  	  }
  	  
  	  $gotdata['aaData'][] = array($row_getDataC['cat_id'], $row_getDataC['cat_name'], $row_getDataC['cat_desc'], $row_getDataC['cat_pic'], $goodsName, $row_getDataC['cat_institution'], $row_getDataC['cat_when'], $row_getDataC['cat_ingr'], $row_getDataC['cat_order']);
  		
  	} while ($row_getDataC = mysql_fetch_assoc($getDataC));

  }

  // DT OUTPUT
  $gotdata['sEcho'] = intval($_POST['sEcho']);
  $gotdata['iTotalRecords'] = $row_getDataLength['dataCnt'];
  $gotdata['iTotalDisplayRecords'] = $row_getDataLength['dataCnt'];

}

?>