<?php

	$colname_getGetSet = "0";
  if (isset($themsg['getset'])) {
    $colname_getGetSet = $themsg['getset'];
  }
  $colname_getOffice = 0;
  if (isset($themsg['ordoffice'])) {
    $colname_getOffice = $themsg['ordoffice'];
  }
  $colname_getGoods = "0";
  if (isset($themsg['ordgood'])) {
    $colname_getGoods = $themsg['ordgood'];
  }
  $colname_getCats = "0";
  if (isset($themsg['ordercats'])) {
    $colname_getCats = $themsg['ordercats'];
  }
  $colname_getOrder = "0";
  if (isset($themsg['menueId'])) {
    $colname_getOrder = $themsg['menueId'];
  }
  

  $colname_getOrderName = "";
  if (isset($themsg['menueName'])) {
    $colname_getOrderName = $themsg['menueName'];
  }
  $colname_getOrderCost = "0";
  if (isset($themsg['menueCost'])) {
    $colname_getOrderCost = $themsg['menueCost'];
  }
  $colname_getOrderWorker = "0";
  if (isset($themsg['workerName'])) {
    $colname_getOrderWorker = $themsg['workerName'];
  }
  $colname_getOrderPic = "user.png";
  if (isset($themsg['workerPic'])) {
    $colname_getOrderPic = $themsg['workerPic'];
  }
  $colname_getOrderProfession = "-1";
  if (isset($themsg['workerProfession'])) {
    $colname_getOrderProfession = $themsg['workerProfession'];
  }
  $colname_getOrderStartName = "-1";
  if (isset($themsg['orderHourName'])) {
    $colname_getOrderStartName = $themsg['orderHourName'];
  }
  $colname_getOrderUser = "0";
  if (isset($themsg['name'])) {
    $colname_getOrderUser = $themsg['name'];
  }
  $colname_getOrderPhone = "0";
  if (isset($themsg['phone'])) {
    $colname_getOrderPhone = $themsg['phone'];
  }
  $colname_getOrderEmail = "0";
  if (isset($themsg['email'])) {
    $colname_getOrderEmail = $themsg['email'];
  }
  $colname_getOrderReminder = "0";
  if (isset($themsg['reminder'])) {
    $colname_getOrderReminder = $themsg['reminder'];
  }
  $colname_getSMSConf = "0";
  if (isset($themsg['smsconf'])) {
    $colname_getSMSConf = $themsg['smsconf'];
  }
  

  $colname_getWorker = "0";
  if (isset($themsg['workerId'])) {
    $colname_getWorker = $themsg['workerId'];
  }
  $colname_getStart = "0";
  if (isset($themsg['orderHour'])) {
    $colname_getStart = $themsg['orderHour'];
  }
  $colname_getText = "0";
  if (isset($themsg['comments'])) {
    $colname_getText = $themsg['comments'];
  }

  $colname_getOrderId = "0";
  if (isset($themsg['orderId'])) {
    $colname_getOrderId = $themsg['orderId'];
  }


  if($colname_getGetSet == '1') {

    $orderOK = 0;

    // last 5 minutes
    $when5m = $when - 300;

    $query_getMenueC = "SELECT * FROM menue WHERE menue_id = '".$colname_getOrder."' && menue_institution = '".$colname_getUser2."' && menue_when > '1'";
    $getMenueC = mysql_query($query_getMenueC, $echoloyalty) or die(mysql_error());
    $row_getMenueC = mysql_fetch_assoc($getMenueC);
    $getMenueCRows  = mysql_num_rows($getMenueC);

    if($getMenueCRows > 0) {

      $query_getOrderChng = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."' && order_user_phone_phone = '".$colname_getOrderPhone."' && order_order = '".$colname_getOrder."' && order_status = '0' && order_start = '".$colname_getStart."' ORDER BY order_id DESC LIMIT 1";
      $getOrderChng = mysql_query($query_getOrderChng, $echoloyalty) or die(mysql_error());
      $row_getOrderChng = mysql_fetch_assoc($getOrderChng);
      $getOrderChngRows  = mysql_num_rows($getOrderChng);

      if($getOrderChngRows == 0) {

          // $characters = '0123456789';
          // $charactersLength = strlen($characters);
          // $randomString = '';
          // for ($i = 0; $i < 5; $i++) {
          //     $randomString .= $characters[rand(0, $charactersLength - 1)];
          // }

          $randomString = 1;

          $colname_getEnd = "-1";
          if (isset($row_getMenueC['menue_interval'])) {
            $colname_getEnd = $colname_getStart + ($row_getMenueC['menue_interval']*60);
          }

          $insOrder = "INSERT INTO ordering (order_user, order_user_name_phone, order_name, order_name_phone, order_user_phone_phone, order_user_email_phone, order_desc, order_worker, order_worker_name_phone, order_worker_pic_phone, order_worker_profession_phone, order_reminder_phone, order_institution, order_office, order_bill, order_bill_phone, order_goods, order_cats, order_order, order_status, order_start, order_start_name_phone, order_end, order_allday, order_mobile, order_mobile_confirm, order_when) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getOrderUser."', '".$row_getMenueC['menue_name']."', '".$colname_getOrderName."', '".$colname_getOrderPhone."', '".$colname_getOrderEmail."', '".$colname_getText."', '".$colname_getWorker."', '".$colname_getOrderWorker."', '".$colname_getOrderPic."', '".$colname_getOrderProfession."', '".$colname_getOrderReminder."', '".$colname_getUser2."', '".$colname_getOffice."', '".$row_getMenueC['menue_cost']."', '".$colname_getOrderCost."', '".$colname_getGoods."', '".$colname_getCats."', '".$row_getMenueC['menue_id']."', '0', '".$colname_getStart."', '".$colname_getOrderStartName."', '".$colname_getEnd."', '0', '1', '".$randomString."', '".$when."')";
          mysql_query($insOrder, $echoloyalty) or die(mysql_error());

          $query_getOrderNew = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."' && order_user = '".$row_getUserDevice['user_id']."' && order_order = '".$colname_getOrder."' && order_status = '0' && order_when = '".$when."' ORDER BY order_id DESC LIMIT 1";
          $getOrderNew = mysql_query($query_getOrderNew, $echoloyalty) or die(mysql_error());
          $row_getOrderNew = mysql_fetch_assoc($getOrderNew);
          $getOrderNewRows  = mysql_num_rows($getOrderNew);

          if($getOrderNewRows > 0) {

            $orderOK = 1;

            // $sms_login = "Tsvirko";
            // $sms_pwd = "5n5iCSZC";
            // $sms_sender = "Proverka";

            // if(isset($row_getInst['org_sms_login']) && $row_getInst['org_sms_login'] != '0' && isset($row_getInst['org_sms_pwd']) && $row_getInst['org_sms_pwd'] != '0' && isset($row_getInst['org_sms_sender']) && $row_getInst['org_sms_sender'] != '0') {
            //   $sms_login = $row_getInst['org_sms_login'];
            //   $sms_pwd = $row_getInst['org_sms_pwd'];
            //   $sms_sender = $row_getInst['org_sms_sender'];
            // }

            // $uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";

            // $ch = curl_init("https://userarea.sms-assistent.by/api/v1/xml");

            // curl_setopt($ch, CURLOPT_URL, "https://userarea.sms-assistent.by/api/v1/xml");
            // curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)");  // useragent
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
            // curl_setopt($ch, CURLOPT_POST, 1);
            /* curl_setopt($ch, CURLOPT_POSTFIELDS, '<?xml version="1.0" encoding="utf-8" ?><package login="'.$sms_login.'" password="'.$sms_pwd.'"><message><msg recipient="'.$colname_getOrderPhone.'" sender="'.$sms_sender.'" validity_period="86400">Ваш код: '.$randomString.'</msg></message></package>'); */
            // curl_setopt($ch, CURLOPT_TIMEOUT, 1200);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
            // $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
            // $header[] = "Content-Type: text/xml";
            // $header[] = "Cache-Control: max-age=0";
            // $header[] = "Connection: keep-alive";
            // $header[] = "Keep-Alive: 300";
            // $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
            // $header[] = "Accept-Language: en-us,en;q=0.5";
            // $header[] = "Pragma: "; // browsers keep this blank.
            // curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            // $content = curl_exec( $ch );
            // $err     = curl_errno( $ch );
            // $errmsg  = curl_error( $ch );
            // $header  = curl_getinfo( $ch );
            // curl_close( $ch );

            // $header['errno']   = $err;
            // $header['errmsg']  = $errmsg;
            // $header['content'] = $content;

            // var_dump($curl_result['content']); // здесь результат выполнения

            $newarrmes = array("requests" => '1', "orderId" => $colname_getOrder, "orderIns" => '1', "orderOK" => $orderOK, "order_id" => $row_getOrderNew['order_id'], "order_user" => $row_getOrderNew['order_user'], "order_name" => $row_getOrderNew['order_name'], "order_desc" => $row_getOrderNew['order_desc'], "order_worker" => $row_getOrderNew['order_worker'], "order_institution" => $row_getOrderNew['order_institution'], "order_office" => $row_getOrderNew['order_office'], "order_bill" => $row_getOrderNew['order_bill'], "order_goods" => $row_getOrderNew['order_goods'], "order_cats" => $row_getOrderNew['order_cats'], "order_order" => $row_getOrderNew['order_order'], "order_status" => $row_getOrderNew['order_status'], "order_start" => $row_getOrderNew['order_start'], "order_end" => $row_getOrderNew['order_end'], "order_allday" => $row_getOrderNew['order_allday'], "order_mobile" => $row_getOrderNew['order_mobile'], "order_when" => $row_getOrderNew['order_when'], "order_del" => $row_getOrderNew['order_del']);
            array_push($gotdata, $newarrmes);

          }

      }
      else if($getOrderChngRows > 0) {

        if($row_getOrderChng['order_mobile_confirm'] == '1') {
            $orderOK = 2;
        }
        else if ($row_getOrderChng['order_mobile_confirm'] == $colname_getSMSConf) {

            $orderOK = 4;
            $ordUpd = "UPDATE ordering SET order_mobile_confirm='1', order_when='".$when."' WHERE order_id = '".$row_getOrderChng['order_id']."' && order_institution = '".$colname_getUser2."'";
            mysql_query($ordUpd, $echoloyalty) or die(mysql_error());

        }
        else if($row_getOrderChng['order_when'] > $when5m) {
            $orderOK = 3;
        }

      }

    }
    else {

      $orderOK = 5;

    }

    $newarrmes = array("requests" => '1', "orderId" => $colname_getOrder, "orderIns" => '0', "orderOK" => $orderOK);
    array_push($gotdata, $newarrmes);

  }
  else if($colname_getGetSet == '2') {

    $query_getOrderChng = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."' && order_id = '".$colname_getOrderId."' && order_start > '".$when."' ORDER BY order_id DESC LIMIT 1";
    $getOrderChng = mysql_query($query_getOrderChng, $echoloyalty) or die(mysql_error());
    $row_getOrderChng = mysql_fetch_assoc($getOrderChng);
    $getOrderChngRows  = mysql_num_rows($getOrderChng);

    if($getOrderChngRows > 0) {

      $orderOK = 6;
      $ordUpd = "UPDATE ordering SET order_status='4', order_when='".$when."' WHERE order_id = '".$row_getOrderChng['order_id']."' && order_institution = '".$colname_getUser2."'";
      mysql_query($ordUpd, $echoloyalty) or die(mysql_error());

    }
    else {
      $orderOK = 7;
    }

    $newarrmes = array("requests" => '1', "orderId" => $colname_getOrder, "orderIns" => '0', "orderOK" => $orderOK);
    array_push($gotdata, $newarrmes);

  }
  else {

    $query_getOrdering = "SELECT * FROM ordering WHERE (order_institution = '".$colname_getUser2."' && order_start >= '".$when."') OR (order_institution = '".$colname_getUser2."' && order_start <= '".$when."' && order_end >= '".$when."') ORDER BY order_id DESC";
    $getOrdering = mysql_query($query_getOrdering, $echoloyalty) or die(mysql_error());
    $row_getOrdering = mysql_fetch_assoc($getOrdering);
    $getOrderingRows  = mysql_num_rows($getOrdering);

    $orderArr = array();
    if($getOrderingRows > 0) {

      do {

        array_push($orderArr, array("order_id" => $row_getOrdering['order_id'], "order_user" => $row_getOrdering['order_user'], "order_name" => $row_getOrdering['order_name'], "order_desc" => $row_getOrdering['order_desc'], "order_worker" => $row_getOrdering['order_worker'], "order_institution" => $row_getOrdering['order_institution'], "order_office" => $row_getOrdering['order_office'], "order_bill" => $row_getOrdering['order_bill'], "order_goods" => $row_getOrdering['order_goods'], "order_cats" => $row_getOrdering['order_cats'], "order_order" => $row_getOrdering['order_order'], "order_status" => $row_getOrdering['order_status'], "order_start" => $row_getOrdering['order_start'], "order_end" => $row_getOrdering['order_end'], "order_allday" => $row_getOrdering['order_allday'], "order_mobile" => $row_getOrdering['order_mobile'], "order_when" => $row_getOrdering['order_when'], "order_del" => $row_getOrdering['order_del']));

      } while ($row_getOrdering = mysql_fetch_assoc($getOrdering));

    }

    $newarrmes = array("calender" => '1', "ordersArr" => $orderArr);
    array_push($gotdata, $newarrmes);

  }

?>