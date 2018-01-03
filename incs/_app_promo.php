<?php

	  $colname_getPromo = "-1";
	  if (isset($themsg['promo'])) {
	    $colname_getPromo = $themsg['promo'];
	  }

	  $promoOK = 0;

	  if($row_getUserDevice['user_promo'] == '0' && $colname_getPromo > '1') {

		    $query_getPromo = "SELECT * FROM users WHERE user_id = '".$colname_getPromo."' && user_institution = '".$colname_getUser2."' && user_work_pos != '1'";
		    $getPromo = mysql_query($query_getPromo, $echoloyalty) or die(mysql_error());
		    $row_getPromo = mysql_fetch_assoc($getPromo);
		    $getPromoRows  = mysql_num_rows($getPromo);
		    
		    if($getPromoRows > 0 && $colname_getPromo != $row_getUserDevice['user_id']) {

		      if($row_getUserDevice['user_work_pos'] == '0') {

			        $pointsComment = 5;
			        $promoOK = $colname_getPromo;

			        // POINTS GET INVOLVED
			        $insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_status, points_comment, points_proofed, points_when, points_time) VALUES ('".$row_getUserDevice['user_id']."', '0', '0', '".$row_getInst['org_promo_points_involved']."', '0', '0', '".$colname_getUser2."', '0', '".$pointsComment."', '1', '".$when."', '".$when."')";
			        mysql_query($insPoints, $echoloyalty) or die(mysql_error());
			        
			        $query_getWallet = "SELECT * FROM wallet WHERE wallet_institution = '".$colname_getUser2."' && wallet_user = '".$row_getUserDevice['user_id']."' LIMIT 1";
			        $getWallet = mysql_query($query_getWallet, $echoloyalty) or die(mysql_error());
			        $row_getWallet = mysql_fetch_assoc($getWallet);
			        $getWalletRows  = mysql_num_rows($getWallet);

			        $newWallet = $row_getWallet['wallet_total'] + $row_getInst['org_promo_points_involved'];

			        $updWallet = "UPDATE wallet SET wallet_total='".$newWallet."', wallet_when='".$when."' WHERE wallet_user='".$row_getUserDevice['user_id']."'";
			        mysql_query($updWallet, $echoloyalty) or die(mysql_error());

			        if($row_getPromo['user_work_pos'] == '0') {

			          // POINTS GET OWNER
			          $insPointsOWN = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_status, points_comment, points_proofed, points_when, points_time) VALUES ('".$colname_getPromo."', '0', '0', '".$row_getInst['org_promo_points_owner']."', '0', '0', '".$colname_getUser2."', '0', '".$pointsComment."', '1', '".$when."', '".$when."')";
			          mysql_query($insPointsOWN, $echoloyalty) or die(mysql_error());
			          
			          $query_getWalletOWN = "SELECT * FROM wallet WHERE wallet_institution = '".$colname_getUser2."' && wallet_user = '".$colname_getPromo."' LIMIT 1";
			          $getWalletOWN = mysql_query($query_getWalletOWN, $echoloyalty) or die(mysql_error());
			          $row_getWalletOWN = mysql_fetch_assoc($getWalletOWN);
			          $getWalletOWNRows  = mysql_num_rows($getWalletOWN);

			          $newWalletOWN = $row_getWalletOWN['wallet_total'] + $row_getInst['org_promo_points_owner'];

			          $updWalletOWN = "UPDATE wallet SET wallet_total='".$newWalletOWN."', wallet_when='".$when."' WHERE wallet_user='".$colname_getPromo."'";
			          mysql_query($updWalletOWN, $echoloyalty) or die(mysql_error());

			        }

			        // UPDATE PROMOCODE USED
			        $updPromo = "UPDATE users SET user_promo='".$colname_getPromo."' WHERE user_id='".$row_getUserDevice['user_id']."'";
			        mysql_query($updPromo, $echoloyalty) or die(mysql_error());

			        $insPromo = "INSERT INTO promo (promo_from, promo_to, promo_institution, promo_when) VALUES ('".$colname_getPromo."', '".$row_getUserDevice['user_id']."', '".$colname_getUser2."', '".$when."')";
			        mysql_query($insPromo, $echoloyalty) or die(mysql_error());

		      }
		      else {
		        $promoOK = 2;
		      }
		      
		    }
		    else {
		      $promoOK = 3;
		    }

		    $newarrmes = array("promo" => '1', "promoOK" => $promoOK, "when" => $when);
		    array_push($gotdata, $newarrmes);

	  }
	  else if($row_getUserDevice['user_promo'] == '0' && $colname_getPromo == '1') {

		    // UPDATE PROMOCODE USED
		    $updPromo = "UPDATE users SET user_promo='1' WHERE user_id='".$row_getUserDevice['user_id']."'";
		    mysql_query($updPromo, $echoloyalty) or die(mysql_error());

		    $newarrmes = array("promo" => '1', "promoOK" => $promoOK, "when" => $when);
		    array_push($gotdata, $newarrmes);

	  }
	  else if(isset($colname_getPromo) && $colname_getPromo != '1') {
		    $promoOK = 3;
		    $newarrmes = array("promo" => '1', "promoOK" => $promoOK, "when" => $when);
		    array_push($gotdata, $newarrmes);
	  }
	  else {
		    $newarrmes = array("promo" => '1', "promoOK" => $promoOK, "when" => $when);
		    array_push($gotdata, $newarrmes);
	  }

?>