<?php

	$colname_getUsrId = -1;
    if (isset($themsg['usrid'])) {
      $colname_getUsrId = $themsg['usrid'];
    }
    $colname_getUmobile = -1;
    if (isset($themsg['umobile'])) {
      $colname_getUmobile = $themsg['umobile'];
    }
    $colname_getUdatetime = -1;
    if (isset($themsg['udatetime'])) {
      $colname_getUdatetime = $themsg['udatetime'];
    }
    $colname_getUsecure = -1;
    if (isset($themsg['usecure'])) {
      $colname_getUsecure = $themsg['usecure'];
    }
    $colname_getUbill = -1;
    if (isset($themsg['ubill'])) {
      // OLD APP
      if($colname_getUsrId > -1) {
        $colname_getUbill = $themsg['ubill'] / 100;
      }
      // NEW APP
      else {
        $colname_getUbill = $themsg['ubill'];
      }
    }
    $colname_getWaiterId = -1;
    if (isset($themsg['waiterid'])) {
      $colname_getWaiterId = $themsg['waiterid'];
    }
    $colname_getWaiterdevice = -1;
    if (isset($themsg['waiterdevice'])) {
      $colname_getWaiterdevice = $themsg['waiterdevice'];
    }
    $colname_getWaitersum = -1;
    if (isset($themsg['waitersum'])) {
      $colname_getWaitersum = $themsg['waitersum'];
    }
    $colname_getWaitercheck = -1;
    if (isset($themsg['waitercheck'])) {
      $colname_getWaitercheck = $themsg['waitercheck'];
    }
    $colname_getWaiterDateTime = -1;
    if (isset($themsg['waiterdatetime'])) {
      $colname_getWaiterDateTime = $themsg['waiterdatetime'];
    }
    $colname_getWaitersecure = -1;
    if (isset($themsg['waitersecure'])) {
      $colname_getWaitersecure = $themsg['waitersecure'];
    }
    $colname_getGift = -1;
    if (isset($themsg['giftid'])) {
      $colname_getGift = $themsg['giftid'];
    }
    $colname_getDiscount = -1;
    if (isset($themsg['discount'])) {
      $colname_getDiscount = $themsg['discount'];
    }
    $colname_getOffice = -1;
    if (isset($themsg['office'])) {
      $colname_getOffice = $themsg['office'];
    }

    // last 12 hours
    $when12h = $when - 60*60*12;
    
    // last 3 minutes
    $when3m = $when - 60*3;
    
    $pointsComment = 0;

    $discountComment = 0;

    $pointsProofed = 0;

    $giftState = 0;

    // secure code
    $query_getTransSecure = "SELECT * FROM transactions WHERE trans_usecure = '".$colname_getUsecure."' LIMIT 1";
    $getTransSecure = mysql_query($query_getTransSecure, $echoloyalty) or die(mysql_error());
    $row_getTransSecure = mysql_fetch_assoc($getTransSecure);
    $getTransSecureRows  = mysql_num_rows($getTransSecure);
    
    // ПОДАРОК
    if(isset($colname_getGift) && $colname_getGift != '' && $colname_getGift > 0) {

        // CHECK GIFT
        $query_getCheckGift = "SELECT * FROM gifts WHERE gifts_id = '".$colname_getGift."' LIMIT 1";
        $getCheckGift = mysql_query($query_getCheckGift, $echoloyalty) or die(mysql_error());
        $row_getCheckGift = mysql_fetch_assoc($getCheckGift);
        $getCheckGiftRows  = mysql_num_rows($getCheckGift);

        $giftName = $row_getCheckGift['gifts_name'];

        $giftPic = $row_getCheckGift['gifts_pic'];

        if($getCheckGiftRows > 0) {

            // user transaction times
            $query_getWallet = "SELECT * FROM wallet WHERE wallet_user = '".$row_getUserDevice['user_id']."' && wallet_total >= '".$colname_getUbill."' LIMIT 1";
            $getWallet = mysql_query($query_getWallet, $echoloyalty) or die(mysql_error());
            $row_getWallet = mysql_fetch_assoc($getWallet);
            $getWalletRows  = mysql_num_rows($getWallet);

            if($getWalletRows > 0) {

                $waiterCheck = $colname_getWaitercheck;
                if($row_getCheckGift['gifts_when'] == '2') {
                    $waiterCheck = 'First Gift';
                }

                $insTrans = "INSERT INTO transactions (trans_usrid, trans_udevice, trans_umobile, trans_udatetime, trans_usecure, trans_ubill, trans_waiterid, trans_waiterdevice, trans_waitersum, trans_waitercheck, trans_waiterdatetime, trans_waitersecure, trans_got_spend, trans_institution, trans_gift, trans_when) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getDeviceId."', '".$row_getUserDevice['user_mob']."', '".$colname_getUdatetime."', '".$colname_getUsecure."', '".$colname_getUbill."', '".$colname_getWaiterId."', '".$colname_getWaiterdevice."', '".$colname_getWaitersum."', '".$waiterCheck."', '".$colname_getWaiterDateTime."', '".$colname_getWaitersecure."', '1', '".$colname_getUser2."', '".$colname_getGift."', '".$when."')";
                mysql_query($insTrans, $echoloyalty) or die(mysql_error());

                $insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_gift, points_waitertime, points_usertime, points_when, points_time) VALUES ('".$row_getUserDevice['user_id']."', '0', '0', '".$colname_getUbill."', '1', '".$colname_getWaiterId."', '".$colname_getUser2."', '".$colname_getOffice."', '0', '".$pointsComment."', '1', '".$colname_getGift."', '".$colname_getWaiterDateTime."', '".$colname_getUdatetime."', '".$when."', '".$when."')";
                mysql_query($insPoints, $echoloyalty) or die(mysql_error());

                $newWallet = $row_getWallet['wallet_total'] - $colname_getUbill;

                $updWallet = "UPDATE wallet SET wallet_total='".$newWallet."', wallet_when='".$when."' WHERE wallet_user='".$row_getUserDevice['user_id']."'";
                mysql_query($updWallet, $echoloyalty) or die(mysql_error());

                $giftState = 1;

            }
            else {
                $giftState = 3;
            }

        }
        else {
            $giftState = 2;
        }

    }
    // СКИДКА
    else if(isset($colname_getDiscount) && $colname_getDiscount != '' && $colname_getDiscount > 0 && $getTransSecureRows == 0) {

        if(isset($row_getUserDevice['user_discount']) && $row_getUserDevice['user_discount'] != '' && $row_getUserDevice['user_discount'] != '0') {

            if($row_getUserDevice['user_discount'] == $colname_getDiscount) {

                // user transaction times
                $query_getTransDevice = "SELECT * FROM transactions WHERE trans_udevice = '".$colname_getDeviceId."' && trans_when > '".$when12h."'";
                $getTransDevice = mysql_query($query_getTransDevice, $echoloyalty) or die(mysql_error());
                $row_getTransDevice = mysql_fetch_assoc($getTransDevice);
                $getTransDeviceRows  = mysql_num_rows($getTransDevice);

                if($getTransDeviceRows < 2) {

                    // waiter transaction times
                    $query_getTransWaiter = "SELECT * FROM transactions WHERE trans_waiterid = '".$colname_getWaiterId."' && trans_when > '".$when3m."'";
                    $getTransWaiter = mysql_query($query_getTransWaiter, $echoloyalty) or die(mysql_error());
                    $row_getTransWaiter = mysql_fetch_assoc($getTransWaiter);
                    $getTransWaiterRows  = mysql_num_rows($getTransWaiter);

                    if($getTransWaiterRows < 2) {

                        // time difference
                        $timeDifference = $colname_getWaiterDateTime - $colname_getUdatetime;
                        if(abs($timeDifference) < 600) {

                            

                        }
                        else {
                            // timedifference between user waiter more than 10m
                            $pointsComment = 1;
                        }

                    }
                    else {
                        // 2 or more transactions from same waiter within 10m
                        $pointsComment = 2;
                    }

                }
                else {
                    // 2 or more transactions on same device within 12h
                    $pointsComment = 3;
                }

                $insTrans = "INSERT INTO transactions (trans_usrid, trans_udevice, trans_umobile, trans_udatetime, trans_usecure, trans_ubill, trans_waiterid, trans_waiterdevice, trans_waitersum, trans_waitercheck, trans_waiterdatetime, trans_waitersecure, trans_institution, trans_when) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getDeviceId."', '".$row_getUserDevice['user_mob']."', '".$colname_getUdatetime."', '".$colname_getUsecure."', '".$colname_getUbill."', '".$colname_getWaiterId."', '".$colname_getWaiterdevice."', '".$colname_getWaitersum."', '".$colname_getWaitercheck."', '".$colname_getWaiterDateTime."', '".$colname_getWaitersecure."', '".$colname_getUser2."', '".$when."')";
                mysql_query($insTrans, $echoloyalty) or die(mysql_error());

                $insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_waitertime, points_usertime, points_when, points_time) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getUbill."', '5', '0', '0', '".$colname_getWaiterId."', '".$colname_getUser2."', '".$colname_getOffice."', '2', '".$pointsComment."', '1', '".$colname_getWaiterDateTime."', '".$colname_getUdatetime."', '".$when."', '".$when."')";
                mysql_query($insPoints, $echoloyalty) or die(mysql_error());

            }
            else {
                // foreign discount number
                $discountComment = '2';
			
			             $insTrans = "INSERT INTO transactions (trans_usrid, trans_udevice, trans_umobile, trans_udatetime, trans_usecure, trans_ubill, trans_waiterid, trans_waiterdevice, trans_waitersum, trans_waitercheck, trans_waiterdatetime, trans_waitersecure, trans_institution, trans_when) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getDeviceId."', '".$row_getUserDevice['user_mob']."', '".$colname_getUdatetime."', '".$colname_getUsecure."', '".$colname_getUbill."', '".$colname_getWaiterId."', '".$colname_getWaiterdevice."', '".$colname_getWaitersum."', 'foreign discount', '".$colname_getWaiterDateTime."', '".$colname_getWaitersecure."', '".$colname_getUser2."', '".$when."')";
                mysql_query($insTrans, $echoloyalty) or die(mysql_error());

                $insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_waitertime, points_usertime, points_when, points_time) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getUbill."', '5', '0', '0', '".$colname_getWaiterId."', '".$colname_getUser2."', '".$colname_getOffice."', '2', '4', '1', '".$colname_getWaiterDateTime."', '".$colname_getUdatetime."', '".$when."', '".$when."')";
                mysql_query($insPoints, $echoloyalty) or die(mysql_error());
			
            }

        }
        else {
            // users does not have any discount number
            $discountComment = '3';
        }
        
    }
    // БАЛЛЫ
    else if($getTransSecureRows == 0) {

        $percentdecimal = $row_getInst['org_money_percent'] / 100;
        $billPercent = $colname_getUbill * $percentdecimal;
        $gotPoints = $billPercent * $row_getInst['org_points_points'] / $row_getInst['org_money_points'];
        
        // user transaction times
        $query_getTransDevice = "SELECT * FROM transactions WHERE trans_udevice = '".$colname_getDeviceId."' && trans_when > '".$when12h."'";
        $getTransDevice = mysql_query($query_getTransDevice, $echoloyalty) or die(mysql_error());
        $row_getTransDevice = mysql_fetch_assoc($getTransDevice);
        $getTransDeviceRows  = mysql_num_rows($getTransDevice);

        if($colname_getUbill < $row_getInst['org_risk_summ']) {

          if($getTransDeviceRows == 0) {

              // waiter transaction times
              $query_getTransWaiter = "SELECT * FROM transactions WHERE trans_waiterid = '".$colname_getWaiterId."' && trans_when > '".$when3m."'";
              $getTransWaiter = mysql_query($query_getTransWaiter, $echoloyalty) or die(mysql_error());
              $row_getTransWaiter = mysql_fetch_assoc($getTransWaiter);
              $getTransWaiterRows  = mysql_num_rows($getTransWaiter);

              if($getTransWaiterRows == 0) {

                  // time difference
                  $timeDifference = $colname_getWaiterDateTime - $colname_getUdatetime;
                  if(abs($timeDifference) < 600) {

                      // user transaction times
                      $query_getWallet = "SELECT * FROM wallet WHERE wallet_user = '".$row_getUserDevice['user_id']."' LIMIT 1";
                      $getWallet = mysql_query($query_getWallet, $echoloyalty) or die(mysql_error());
                      $row_getWallet = mysql_fetch_assoc($getWallet);
                      $getWalletRows  = mysql_num_rows($getWallet);

                      if($getWalletRows > 0) {

                          // ADD POINTS TO PROMOCODE INVOLVED DEVICE (SCANNED)
                          if($row_getInst['org_promo_points_scan_involved'] > 0 && $row_getUserDevice['user_promo'] > 1) {
                            $newWallet = $row_getWallet['wallet_total'] + $gotPoints + $row_getInst['org_promo_points_scan_involved'];
                          }
                          // NOT ADD POINTS TO PROMOCODE INVOLVED DEVICE (SCANNED)
                          else {
                            $newWallet = $row_getWallet['wallet_total'] + $gotPoints;
                          }

                          $updWallet = "UPDATE wallet SET wallet_total='".$newWallet."', wallet_when='".$when."' WHERE wallet_user='".$row_getUserDevice['user_id']."'";
                          mysql_query($updWallet, $echoloyalty) or die(mysql_error());

                          // ADD POINTS TO PROMOCODE OWNERS DEVICE (NOT SCANNED, JUST SHARED PROMO)
                          if($row_getInst['org_promo_points_scan_owner'] > 0 && $row_getUserDevice['user_promo'] > 1) {
            
                            $query_getPromoUser = "SELECT * FROM users WHERE user_id = '".$row_getUserDevice['user_promo']."' && user_del = '0' LIMIT 1";
                            $getPromoUser = mysql_query($query_getPromoUser, $echoloyalty) or die(mysql_error());
                            $row_getPromoUser = mysql_fetch_assoc($getPromoUser);
                            $getPromoUserRows  = mysql_num_rows($getPromoUser);

                            if($getPromoUserRows > 0) {

                              if($row_getPromoUser['user_work_pos'] == 0) {

                              $query_getWallet2 = "SELECT * FROM wallet WHERE wallet_user = '".$row_getPromoUser['user_id']."' LIMIT 1";
                              $getWallet2 = mysql_query($query_getWallet2, $echoloyalty) or die(mysql_error());
                              $row_getWallet2 = mysql_fetch_assoc($getWallet2);
                              $getWallet2Rows  = mysql_num_rows($getWallet2);

                              $newWallet2 = $row_getWallet2['wallet_total'] + $row_getInst['org_promo_points_scan_owner'];

                              $updWallet2 = "UPDATE wallet SET wallet_total='".$newWallet2."', wallet_when='".$when."' WHERE wallet_user='".$row_getPromoUser['user_id']."'";
                              mysql_query($updWallet2, $echoloyalty) or die(mysql_error());

                              }

                            }
          
                          }

                          $pointsProofed = 1;

                      }

                  }
                  else {
                      // timedifference between user waiter more than 10m
                      $pointsComment = 1;
                  }

              }
              else {
                  // 2 or more transactions from same waiter within 3m
                  $pointsComment = 2;
              }

          }
          else {
              // 2 or more transactions on same device within 12h
              $pointsComment = 3;
          }

        }
        else {
            // more than risk summ
            $pointsComment = 4;
        }

        $insTrans = "INSERT INTO transactions (trans_usrid, trans_udevice, trans_umobile, trans_udatetime, trans_usecure, trans_ubill, trans_waiterid, trans_waiterdevice, trans_waitersum, trans_waitercheck, trans_waiterdatetime, trans_waitersecure, trans_institution, trans_when) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getDeviceId."', '".$row_getUserDevice['user_mob']."', '".$colname_getUdatetime."', '".$colname_getUsecure."', '".$colname_getUbill."', '".$colname_getWaiterId."', '".$colname_getWaiterdevice."', '".$colname_getWaitersum."', '".$colname_getWaitercheck."', '".$colname_getWaiterDateTime."', '".$colname_getWaitersecure."', '".$colname_getUser2."', '".$when."')";
        mysql_query($insTrans, $echoloyalty) or die(mysql_error());

        $insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_waitertime, points_usertime, points_when, points_time) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getUbill."', '0', '".$gotPoints."', '0', '".$colname_getWaiterId."', '".$colname_getUser2."', '".$colname_getOffice."', '0', '".$pointsComment."', '".$pointsProofed."', '".$colname_getWaiterDateTime."', '".$colname_getUdatetime."', '".$when."', '".$when."')";
        mysql_query($insPoints, $echoloyalty) or die(mysql_error());

        // ADD POINTS TO PROMOCODE OWNERS DEVICE (NOT SCANNED, JUST SHARED PROMO)
        if($row_getInst['org_promo_points_scan_owner'] > 0 && $row_getUserDevice['user_promo'] > 1) {
  
          $query_getPromoUser = "SELECT * FROM users WHERE user_id = '".$row_getUserDevice['user_promo']."' && user_del = '0' LIMIT 1";
          $getPromoUser = mysql_query($query_getPromoUser, $echoloyalty) or die(mysql_error());
          $row_getPromoUser = mysql_fetch_assoc($getPromoUser);
          $getPromoUserRows  = mysql_num_rows($getPromoUser);

          if($getPromoUserRows > 0) {

            if($row_getPromoUser['user_work_pos'] == 0) {

              $pointsProofed2 = 0;
              if($pointsProofed == 1) {
                $pointsProofed2 = 1;
              }

              $insPoints2 = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_waitertime, points_usertime, points_when, points_time) VALUES ('".$row_getUserDevice['user_promo']."', '".$colname_getUbill."', '0', '".$row_getInst['org_promo_points_scan_owner']."', '0', '".$colname_getWaiterId."', '".$colname_getUser2."', '".$colname_getOffice."', '0', '8', '".$pointsProofed2."', '".$colname_getWaiterDateTime."', '".$colname_getUdatetime."', '".$when."', '".$when."')";
              mysql_query($insPoints2, $echoloyalty) or die(mysql_error());

            }

          }

        }

        // ADD POINTS TO PROMOCODE INVOLVED DEVICE (SCANNED)
        if($row_getInst['org_promo_points_scan_involved'] > 0 && $row_getUserDevice['user_promo'] > 1) {

          $pointsProofed2 = 0;
          if($pointsProofed == 1) {
            $pointsProofed2 = 1;
          }

          $insPoints2 = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_waitertime, points_usertime, points_when, points_time) VALUES ('".$row_getUserDevice['user_promo']."', '".$colname_getUbill."', '0', '".$row_getInst['org_promo_points_scan_involved']."', '0', '".$colname_getWaiterId."', '".$colname_getUser2."', '".$colname_getOffice."', '0', '7', '".$pointsProofed2."', '".$colname_getWaiterDateTime."', '".$colname_getUdatetime."', '".$when."', '".$when."')";
          mysql_query($insPoints2, $echoloyalty) or die(mysql_error());

          $updPromoUsr = "UPDATE users SET user_promo='1', user_upd='".$when."' WHERE user_id='".$row_getUserDevice['user_promo']."'";
          mysql_query($updPromoUsr, $echoloyalty) or die(mysql_error());

        }

    }
    else {
        // already used securecode
        $pointsComment = 100;

        if(isset($colname_getDiscount) && $colname_getDiscount != '' && $colname_getDiscount > 0) {

            $discountComment = 100;

            $insTrans = "INSERT INTO transactions (trans_usrid, trans_udevice, trans_umobile, trans_udatetime, trans_usecure, trans_ubill, trans_waiterid, trans_waiterdevice, trans_waitersum, trans_waitercheck, trans_waiterdatetime, trans_waitersecure, trans_institution, trans_when) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getDeviceId."', '".$row_getUserDevice['user_mob']."', '".$colname_getUdatetime."', '".$colname_getUsecure."', '".$colname_getUbill."', '".$colname_getWaiterId."', '".$colname_getWaiterdevice."', '".$colname_getWaitersum."', '".$colname_getWaitercheck."', '".$colname_getWaiterDateTime."', '".$colname_getWaitersecure."', '".$colname_getUser2."', '".$when."')";
                mysql_query($insTrans, $echoloyalty) or die(mysql_error());

            $insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_status, points_comment, points_proofed, points_waitertime, points_usertime, points_when, points_time) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getUbill."', '5', '0', '0', '".$colname_getWaiterId."', '".$colname_getUser2."', '2', '".$pointsComment."', '1', '".$colname_getWaiterDateTime."', '".$colname_getUdatetime."', '".$when."', '".$when."')";
            mysql_query($insPoints, $echoloyalty) or die(mysql_error());

        }
					else {
						
						$insTrans = "INSERT INTO transactions (trans_usrid, trans_udevice, trans_umobile, trans_udatetime, trans_usecure, trans_ubill, trans_waiterid, trans_waiterdevice, trans_waitersum, trans_waitercheck, trans_waiterdatetime, trans_waitersecure, trans_institution, trans_when) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getDeviceId."', '".$row_getUserDevice['user_mob']."', '".$colname_getUdatetime."', '".$colname_getUsecure."', '".$colname_getUbill."', '".$colname_getWaiterId."', '".$colname_getWaiterdevice."', '".$colname_getWaitersum."', 'used check', '".$colname_getWaiterDateTime."', '".$colname_getWaitersecure."', '".$colname_getUser2."', '".$when."')";
						mysql_query($insTrans, $echoloyalty) or die(mysql_error());

						$insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_status, points_comment, points_proofed, points_waitertime, points_usertime, points_when, points_time) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getUbill."', '0', '0', '0', '".$colname_getWaiterId."', '".$colname_getUser2."', '0', '".$pointsComment."', '".$pointsProofed."', '".$colname_getWaiterDateTime."', '".$colname_getUdatetime."', '".$when."', '".$when."')";
						mysql_query($insPoints, $echoloyalty) or die(mysql_error());
						
					}
        
    }

    // get transaction id
    $query_getTSecure = "SELECT * FROM transactions WHERE trans_usecure = '".$colname_getUsecure."' LIMIT 1";
    $getTSecure = mysql_query($query_getTSecure, $echoloyalty) or die(mysql_error());
    $row_getTSecure = mysql_fetch_assoc($getTSecure);
    $getTSecureRows  = mysql_num_rows($getTSecure);

    $newarrmes = array("scan" => '1', "pointsComment" => $pointsComment, "discountComment" => $discountComment, "trans_when" => $when, "trans_id" => $row_getTSecure['trans_id'], "giftState" => $giftState, "giftName" => $giftName, "giftPic" => $giftPic);
    array_push($gotdata, $newarrmes);

?>