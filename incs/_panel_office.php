<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

$colname_office_id = "-1";
if (isset($themsg['officeid'])) {
  $colname_office_id = protect($themsg['officeid']);
}
$colname_name = "-1";
if (isset($themsg['name'])) {
  $colname_name = protect($themsg['name']);
}
$colname_start = "-1";
if (isset($themsg['start'])) {
  $colname_start = protect($themsg['start']);
}
$colname_stop = "-1";
if (isset($themsg['stop'])) {
  $colname_stop = protect($themsg['stop']);
}
$colname_country = "-1";
if (isset($themsg['country'])) {
  $colname_country = protect($themsg['country']);
}
$colname_city = "-1";
if (isset($themsg['city'])) {
  $colname_city = protect($themsg['city']);
}
$colname_adress = "-1";
if (isset($themsg['adress'])) {
  $colname_adress = protect($themsg['adress']);
}
$colname_timezone = "-1";
if (isset($themsg['timezone'])) {
  $colname_timezone = protect($themsg['timezone']);
}
$colname_tel = "-1";
if (isset($themsg['tel'])) {
  $colname_tel = protect($themsg['tel']);
}
$colname_fax = "-1";
if (isset($themsg['fax'])) {
  $colname_fax = protect($themsg['fax']);
}
$colname_mob = "-1";
if (isset($themsg['mob'])) {
  $colname_mob = protect($themsg['mob']);
}
$colname_email = "-1";
if (isset($themsg['email'])) {
  $colname_email = protect($themsg['email']);
}
$colname_pwd = "-1";
if (isset($themsg['pwd'])) {
  $colname_pwd = protect($themsg['pwd']);
}
$colname_skype = "-1";
if (isset($themsg['skype'])) {
  $colname_skype = protect($themsg['skype']);
}
$colname_site = "-1";
if (isset($themsg['site'])) {
  $colname_site = protect($themsg['site']);
}
$colname_tax_id = "-1";
if (isset($themsg['tax_id'])) {
  $colname_tax_id = protect($themsg['tax_id']);
}

if($colname_getUser5 == 'del') {

  if($colname_office_id > 0) {

	$query_getOffice = "SELECT * FROM organizations_office WHERE office_institution = '".$colname_getUser2."' ORDER BY office_id DESC LIMIT 1";
	$getOffice = mysql_query($query_getOffice, $echoloyalty) or die(mysql_error());
	$row_getOffice = mysql_fetch_assoc($getOffice);
	$getOfficeRows  = mysql_num_rows($getOffice);

	if($getOfficeRows > 0) {

		$updOffice = "UPDATE organizations_office SET office_log='".$when."', office_del='1' WHERE office_institution='".$colname_getUser2."' && office_id='".$colname_office_id."'";
		mysql_query($updOffice, $echoloyalty) or die(mysql_error());

		$newarrmes = array("requests" => '1', "officeId" => $colname_office_id, "officeDel" => '1');
		array_push($gotdata, $newarrmes);

	}

  }

}
else if($colname_getUser5 == 'create') {
	
	if($colname_office_id == 0) {

		$insProf = "INSERT INTO organizations_office (office_name, office_start, office_stop, office_country, office_city, office_adress, office_timezone, office_tel, office_fax, office_mob, office_email, office_pwd, office_skype, office_site, office_tax_id, office_logo, office_institution, office_log, office_reg) VALUES ('".$colname_name."', '".$colname_start."', '".$colname_stop."', '".$colname_country."', '".$colname_city."', '".$colname_adress."', '".$colname_timezone."', '".$colname_tel."', '".$colname_fax."', '".$colname_mob."', '".$colname_email."', '".$colname_pwd."', '".$colname_skype."', '".$colname_site."', '".$colname_tax_id."', '0', '".$colname_getUser2."', '".$when."', '".$when."')";
		mysql_query($insProf, $echoloyalty) or die(mysql_error());

		$query_getOfficeNew = "SELECT * FROM organizations_office WHERE office_institution = '".$colname_getUser2."' && office_reg = '".$when."' ORDER BY office_id DESC LIMIT 1";
		$getOfficeNew = mysql_query($query_getOfficeNew, $echoloyalty) or die(mysql_error());
		$row_getOfficeNew = mysql_fetch_assoc($getOfficeNew);
		$getOfficeNewRows  = mysql_num_rows($getOfficeNew);

		if($getOfficeNewRows > 0) {

		  $newarrmes = array("requests" => '1', "officeId" => $row_getOfficeNew['office_id'], "officeIns" => '1', 'when' => $when);
		  array_push($gotdata, $newarrmes);

		}

	}
  
}
else if($colname_getUser5 == 'change') {

  if($colname_office_id > 0) {
  
	$query_getOffice = "SELECT * FROM organizations_office WHERE office_institution = '".$colname_getUser2."' ORDER BY office_id DESC LIMIT 1";
	$getOffice = mysql_query($query_getOffice, $echoloyalty) or die(mysql_error());
	$row_getOffice = mysql_fetch_assoc($getOffice);
	$getOfficeRows  = mysql_num_rows($getOffice);

	if($getOfficeRows > 0) {

	  $updOffice = "UPDATE organizations_office SET office_name='".$colname_name."', office_start='".$colname_start."', office_stop='".$colname_stop."', office_country='".$colname_country."', office_city='".$colname_city."', office_adress='".$colname_adress."', office_timezone='".$colname_timezone."', office_tel='".$colname_tel."', office_fax='".$colname_fax."', office_mob='".$colname_mob."', office_email='".$colname_email."', office_pwd='".$colname_pwd."', office_skype='".$colname_skype."', office_site='".$colname_site."', office_tax_id='".$colname_tax_id."', office_log='".$when."' WHERE office_institution='".$colname_getUser2."' && office_id='".$colname_office_id."'";
	   mysql_query($updOffice, $echoloyalty) or die(mysql_error());

	  $newarrmes = array("requests" => '1', "officeId" => $colname_profid, "officeUpd" => '1', "when" => $when);
	  array_push($gotdata, $newarrmes);

	}

  }
  
}

}
else {

// DT INPUT FOR SEARCHING
$aColumns = array( 'office_name', 'office_adress', 'office_tel', 'office_fax', 'office_mob', 'office_email', 'office_skype', 'office_site' );

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
$sWhere = "WHERE office_institution = '".$colname_getUser2."' && office_reg > '2' && office_del = '0'";
if ( $_POST['sSearch'] != "" )
{
  $sWhere = "WHERE office_institution = '".$colname_getUser2."' && office_reg > '2' && office_del = '0' && (";
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
$query_getDataC = "SELECT * FROM organizations_office $sWhere $sOrder $sLimit";
$getDataC = mysql_query($query_getDataC, $echoloyalty) or die(mysql_error());
$row_getDataC = mysql_fetch_assoc($getDataC);
$getDataCRows  = mysql_num_rows($getDataC);

// DT INPUT
$query_getDataLength = "SELECT COUNT(*) AS dataCnt FROM organizations_office WHERE office_institution = '".$colname_getUser2."' && office_reg > '2' && office_del = '0'";
$getDataLength = mysql_query($query_getDataLength, $echoloyalty) or die(mysql_error());
$row_getDataLength = mysql_fetch_assoc($getDataLength);
$getDataLengthRows  = mysql_num_rows($getDataLength);

if($getDataCRows > 0) {

  do {
	
	$gotdata['aaData'][] = array($row_getDataC['office_id'], $row_getDataC['office_name'], $row_getDataC['office_start'], $row_getDataC['office_stop'], $row_getDataC['office_country'], $row_getDataC['office_city'], $row_getDataC['office_adress'], $row_getDataC['office_timezone'], $row_getDataC['office_tel'], $row_getDataC['office_fax'], $row_getDataC['office_mob'], $row_getDataC['office_email'], $row_getDataC['office_pwd'], $row_getDataC['office_skype'], $row_getDataC['office_site'], $row_getDataC['office_tax_id'], $row_getDataC['office_logo'], $row_getDataC['office_institution'], $row_getDataC['office_log'], $row_getDataC['office_reg']);
	
  } while ($row_getDataC = mysql_fetch_assoc($getDataC));

}

// DT OUTPUT
$gotdata['sEcho'] = intval($_POST['sEcho']);
$gotdata['iTotalRecords'] = $row_getDataLength['dataCnt'];
$gotdata['iTotalDisplayRecords'] = $row_getDataLength['dataCnt'];

}

?>