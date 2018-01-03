<?php

	$colname_getSocial = "-1";
	if (isset($themsg['social'])) {
	  $colname_getSocial = $themsg['social'];
	}

	$lastshare = $when - 3600*24;

	// GET POINTS
	$query_getShares = "SELECT * FROM shares WHERE share_from = '".$row_getUserDevice['user_id']."' && share_where = '".$colname_getSocial."' && share_when > '".$lastshare."'";
	$getShares = mysql_query($query_getShares, $echoloyalty) or die(mysql_error());
	$row_getShares = mysql_fetch_assoc($getShares);
	$getSharesRows  = mysql_num_rows($getShares);

	if($getSharesRows == 0) {
		
		$insShares = "INSERT INTO shares (share_from, share_where, share_institution, share_when) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getSocial."', '".$colname_getUser2."', '".$when."')";
		mysql_query($insShares, $echoloyalty) or die(mysql_error());
		
	}

?>