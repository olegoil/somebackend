<?php

	$colname_getSMS = -1;
    if (isset($themsg['sms'])) {
      $colname_getSMS = $themsg['sms'];
    }

    // last 5 minutes
    $when5m = $when - 300;

    $smsOK = 0;

    $oldUsr = array();
    
    if($colname_getSMS == '0') {

        if($row_getUserDevice['user_mob_confirm'] == '1') {
            $smsOK = 1;
        }
        else if($row_getUserDevice['user_conf_req'] > $when5m) {
            $smsOK = 2;
        }
        else if (isset($row_getUserDevice['user_mob']) && $row_getUserDevice['user_mob'] != '0' && $row_getUserDevice['user_mob'] != '') {

          $sms_login = "Tsvirko";
          $sms_pwd = "5n5iCSZC";
          $sms_sender = "Proverka";

          if(isset($row_getInst['org_sms_login']) && $row_getInst['org_sms_login'] != '0' && isset($row_getInst['org_sms_pwd']) && $row_getInst['org_sms_pwd'] != '0' && isset($row_getInst['org_sms_sender']) && $row_getInst['org_sms_sender'] != '0') {
            $sms_login = $row_getInst['org_sms_login'];
            $sms_pwd = $row_getInst['org_sms_pwd'];
            $sms_sender = $row_getInst['org_sms_sender'];
          }

            $characters = '0123456789';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 5; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";

            $ch = curl_init("https://userarea.sms-assistent.by/api/v1/xml");

            curl_setopt($ch, CURLOPT_URL, "https://userarea.sms-assistent.by/api/v1/xml");
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)");  // useragent
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '<?xml version="1.0" encoding="utf-8" ?><package login="'.$sms_login.'" password="'.$sms_pwd.'"><message><msg recipient="'.$row_getUserDevice['user_mob'].'" sender="'.$sms_sender.'" validity_period="86400">Ваш код: '.$randomString.'</msg></message></package>');
            curl_setopt($ch, CURLOPT_TIMEOUT, 1200);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
            $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
            $header[] = "Content-Type: text/xml";
            $header[] = "Cache-Control: max-age=0";
            $header[] = "Connection: keep-alive";
            $header[] = "Keep-Alive: 300";
            $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
            $header[] = "Accept-Language: en-us,en;q=0.5";
            $header[] = "Pragma: "; // browsers keep this blank.
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            $content = curl_exec( $ch );
            $err     = curl_errno( $ch );
            $errmsg  = curl_error( $ch );
            $header  = curl_getinfo( $ch );
            curl_close( $ch );

            $header['errno']   = $err;
            $header['errmsg']  = $errmsg;
            $header['content'] = $content;

            // var_dump($curl_result['content']); // здесь результат выполнения

            $usrUpd = "UPDATE users SET user_mob_confirm='".$randomString."', user_conf_req='".$when."', user_upd='".$when."' WHERE user_id = '".$row_getUserDevice['user_id']."' && user_institution = '".$colname_getUser2."'";
            mysql_query($usrUpd, $echoloyalty) or die(mysql_error());

            $smsOK = 3;
        }
        else {
            $smsOK = 4;
        }

    }
    else if($colname_getSMS != '0') {

        $query_getSMS = "SELECT * FROM users WHERE user_id = '".$row_getUserDevice['user_id']."' && user_institution = '".$colname_getUser2."' && user_mob_confirm = '".$colname_getSMS."' LIMIT 1";
        $getSMS = mysql_query($query_getSMS, $echoloyalty) or die(mysql_error());
        $row_getSMS = mysql_fetch_assoc($getSMS);
        $getSMSRows  = mysql_num_rows($getSMS);

        if($getSMSRows > 0) {

          // PROOF WORKING POSITION
          $query_getUsrNumber = "SELECT * FROM users WHERE user_id != '".$row_getUserDevice['user_id']."' && user_mob = '".$row_getSMS['user_mob']."' && user_institution = '".$colname_getUser2."' && user_mob_confirm = '1' ORDER BY user_id DESC LIMIT 1";
          $getUsrNumber = mysql_query($query_getUsrNumber, $echoloyalty) or die(mysql_error());
          $row_getUsrNumber= mysql_fetch_assoc($getUsrNumber);
          $getUsrNumberRows  = mysql_num_rows($getUsrNumber);

          if($getUsrNumberRows > 0) {

              $usrUpd = "UPDATE users SET user_name = '".$row_getUsrNumber['user_name']."', user_surname = '".$row_getUsrNumber['user_surname']."', user_middlename = '".$row_getUsrNumber['user_middlename']."', user_email = '".$row_getUsrNumber['user_email']."', user_email_confirm = '".$row_getUsrNumber['user_email_confirm']."', user_pwd = '".$row_getUsrNumber['user_pwd']."', user_tel = '".$row_getUsrNumber['user_tel']."', user_work_pos = '".$row_getUsrNumber['user_work_pos']."', user_institution = '".$row_getUsrNumber['user_institution']."', user_pic = '".$row_getUsrNumber['user_pic']."', user_gender = '".$row_getUsrNumber['user_gender']."', user_mob_confirm='1', user_birthday = '".$row_getUsrNumber['user_birthday']."', user_country = '".$row_getUsrNumber['user_country']."', user_region = '".$row_getUsrNumber['user_region']."', user_city = '".$row_getUsrNumber['user_city']."', user_adress = '".$row_getUsrNumber['user_adress']."', user_install_where = '".$row_getUsrNumber['user_install_where']."', user_log_key = '".$row_getUsrNumber['user_log_key']."', user_discount = '".$row_getUsrNumber['user_discount']."', user_promo = '".$row_getUsrNumber['user_promo']."', user_upd='".$when."' WHERE user_id = '".$row_getUserDevice['user_id']."' && user_institution = '".$colname_getUser2."'";
              mysql_query($usrUpd, $echoloyalty) or die(mysql_error());

              $usrUpdOld = "UPDATE users SET user_discount = '0', user_upd='".$when."' WHERE user_id = '".$row_getUsrNumber['user_id']."' && user_institution = '".$colname_getUser2."'";
              mysql_query($usrUpdOld, $echoloyalty) or die(mysql_error());

              // GET USER
              $usrData = array("user_id" => $row_getUsrNumber['user_id'], "user_name" => $row_getUsrNumber['user_name'], "user_surname" => $row_getUsrNumber['user_surname'], "user_middlename" => $row_getUsrNumber['user_middlename'], "user_email" => $row_getUsrNumber['user_email'], "user_email_confirm" => $row_getUsrNumber['user_email_confirm'], "user_pwd" => $row_getUsrNumber['user_pwd'], "user_tel" => $row_getUsrNumber['user_tel'], "user_mob" => $row_getUsrNumber['user_mob'], "user_mob_confirm" => $row_getUsrNumber['user_mob_confirm'], "user_work_pos" => $row_getUsrNumber['user_work_pos'], "user_institution" => $row_getUsrNumber['user_institution'], "user_pic" => $row_getUsrNumber['user_pic'], "user_gender" => $row_getUsrNumber['user_gender'], "user_birthday" => $row_getUsrNumber['user_birthday'], "user_country" => $row_getUsrNumber['user_country'], "user_region" => $row_getUsrNumber['user_region'], "user_city" => $row_getUsrNumber['user_city'], "user_adress" => $row_getUsrNumber['user_adress'], "user_install_where" => $row_getUsrNumber['user_install_where'], "user_log_key" => $row_getUsrNumber['user_log_key'], "user_gcm" => $row_getUsrNumber['user_gcm'], "user_device" => $row_getUsrNumber['user_device'], "user_device_id" => $row_getUsrNumber['user_device_id'], "user_device_version" => $row_getUsrNumber['user_device_version'], "user_device_os" => $row_getUsrNumber['user_device_os'], "user_discount" => $row_getUsrNumber['user_discount'], "user_promo" => $row_getUsrNumber['user_promo'], "user_log" => $row_getUsrNumber['user_log'], "user_upd" => $row_getUsrNumber['user_upd'], "user_reg" => $row_getUsrNumber['user_reg']);

              // GET POINTS
              $query_getPoints = "SELECT * FROM points WHERE points_user = '".$row_getUsrNumber['user_id']."' LIMIT 1";
              $getPoints = mysql_query($query_getPoints, $echoloyalty) or die(mysql_error());
              $row_getPoints = mysql_fetch_assoc($getPoints);
              $getPointsRows  = mysql_num_rows($getPoints);

              $pointsArr = array();

              if($getPointsRows > 0) {

                do {

                  array_push($pointsArr, array("points_id" => $row_getPoints['points_id'], "points_user" => $row_getPoints['points_user'], "points_bill" => $row_getPoints['points_bill'], "points_discount" => $row_getPoints['points_discount'], "points_points" => $row_getPoints['points_points'], "points_got_spend" => $row_getPoints['points_got_spend'], "points_waiter" => $row_getPoints['points_waiter'], "points_institution" => $row_getPoints['points_institution'], "points_status" => $row_getPoints['points_status'], "points_comment" => $row_getPoints['points_comment'], "points_proofed" => $row_getPoints['points_proofed'], "points_gift" => $row_getPoints['points_gift'], "points_when" => $row_getPoints['points_when']));

                } while ($row_getPoints = mysql_fetch_assoc($getPoints));

              }

              // GET WALLET
              $query_getWallet = "SELECT * FROM wallet WHERE wallet_user = '".$row_getUsrNumber['user_id']."' LIMIT 1";
              $getWallet = mysql_query($query_getWallet, $echoloyalty) or die(mysql_error());
              $row_getWallet = mysql_fetch_assoc($getWallet);
              $getWalletRows  = mysql_num_rows($getWallet);

              $walletArr = array();

              if($getWalletRows > 0) {

                array_push($walletArr, array("wallet_id" => $row_getWallet['wallet_id'], "wallet_user" => $row_getWallet['wallet_user'], "wallet_institution" => $row_getWallet['wallet_institution'], "wallet_total" => $row_getWallet['wallet_total'], "wallet_when" => $row_getWallet['wallet_when']));

                $updWallet = "UPDATE wallet SET wallet_total = '".$row_getWallet['wallet_total']."', wallet_warn = '".$row_getWallet['wallet_warn']."', wallet_when = '".$when."' WHERE wallet_user = '".$row_getUserDevice['user_id']."' && wallet_institution = '".$colname_getUser2."'";
                mysql_query($updWallet, $echoloyalty) or die(mysql_error());

                $emptyOldWallet = "UPDATE wallet SET wallet_total = '0', wallet_warn = '".$row_getWallet['wallet_warn']."', wallet_when = '".$when."' WHERE wallet_user = '".$row_getWallet['wallet_user']."' && wallet_institution = '".$colname_getUser2."'";
                mysql_query($emptyOldWallet, $echoloyalty) or die(mysql_error());

              }

              // GET CHAT
              $query_getChat = "SELECT * FROM chat WHERE chat_from = '".$row_getUsrNumber['user_id']."' && chat_institution = '".$row_getUsrNumber['user_institution']."' && chat_when > '1' OR chat_to = '".$row_getUsrNumber['user_id']."' && chat_institution = '".$row_getUsrNumber['user_institution']."' && chat_when > '1'";
              $getChat = mysql_query($query_getChat, $echoloyalty) or die(mysql_error());
              $row_getChat = mysql_fetch_assoc($getChat);
              $getChatRows  = mysql_num_rows($getChat);

              $chatArr = array();

              if($getChatRows > 0) {

                do {

                  array_push($chatArr, array("chat_id" => $row_getChat['chat_id'], "chat_from" => $row_getChat['chat_from'], "chat_to" => $row_getChat['chat_to'], "chat_name" => $row_getChat['chat_name'], "chat_message" => $row_getChat['chat_message'], "chat_read" => $row_getChat['chat_read'], "chat_institution" => $row_getChat['chat_institution'], "chat_answered" => $row_getChat['chat_answered'], "chat_when" => $row_getChat['chat_when']));

                } while ($row_getChat = mysql_fetch_assoc($getChat));

              }

              // GET ORDER
              $query_getOrder = "SELECT * FROM ordering WHERE order_institution = '".$row_getUsrNumber['user_institution']."' && order_user = '".$row_getUsrNumber['user_id']."' ORDER BY order_id DESC LIMIT 20";
              $getOrder = mysql_query($query_getOrder, $echoloyalty) or die(mysql_error());
              $row_getOrder = mysql_fetch_assoc($getOrder);
              $getOrderRows  = mysql_num_rows($getOrder);

              $orderArr = array();

              if($getOrderRows > 0) {

                do {

                array_push($orderArr, array("order_id" => $row_getOrder['order_id'], "order_user" => $row_getOrder['order_user'], "order_name" => $row_getOrder['order_name'], "order_desc" => $row_getOrder['order_desc'], "order_worker" => $row_getOrder['order_worker'], "order_institution" => $row_getOrder['order_institution'], "order_bill" => $row_getOrder['order_bill'], "order_order" => $row_getOrder['order_order'], "order_status" => $row_getOrder['order_status'], "order_year" => $row_getOrder['order_year'], "order_year_end" => $row_getOrder['order_year_end'], "order_month" => $row_getOrder['order_month'], "order_month_end" => $row_getOrder['order_month_end'], "order_day" => $row_getOrder['order_day'], "order_day_end" => $row_getOrder['order_day_end'], "order_hour" => $row_getOrder['order_hour'], "order_hour_end" => $row_getOrder['order_hour_end'], "order_min" => $row_getOrder['order_min'], "order_min_end" => $row_getOrder['order_min_end'], "order_allday" => $row_getOrder['order_allday'], "order_mobile" => $row_getOrder['order_mobile'], "order_when" => $row_getOrder['order_when']));

                } while ($row_getOrder = mysql_fetch_assoc($getOrder));

              }

              $oldUsr = array("usrData" => $usrData, "pointsArr" => $pointsArr, "walletArr" => $walletArr, "chatArr" => $chatArr, "orderArr" => $orderArr);

          }
          else {

            $usrUpd = "UPDATE users SET user_mob_confirm='1', user_upd='".$when."' WHERE user_id = '".$row_getUserDevice['user_id']."' && user_institution = '".$colname_getUser2."'";
            mysql_query($usrUpd, $echoloyalty) or die(mysql_error());

          }
	  
            $smsOK = 5;
        }
        else {
            $smsOK = 6;
        }

    }
    
    $newarrmes = array("sms" => '1', "smsOK" => $smsOK, "when" => $when, "oldUsr" => $oldUsr);
    array_push($gotdata, $newarrmes);

?>