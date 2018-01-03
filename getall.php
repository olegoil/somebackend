<?php


if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
    header('Content-Type: application/json');
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

$postdata = file_get_contents("php://input");
if (isset($postdata)) {

  if($postdata[0] == '=') {
        // iOS
        $getStr = json_decode(urldecode(ltrim($postdata, '=')), true);
    }
    else {
        // Android
        $getStr = json_decode($postdata, true);
    }


    function sendGCM($count, $array, $cert, $title, $mesg, $push_id, $push_inst) {

        $messageios = $title . " : " . $mesg;
      
        $badge = 1;
        $sound = 'default';
        $development = false;

        $payload = array();
        $payload['aps'] = array('alert' => html_entity_decode($messageios), 'badge' => intval($badge), 'sound' => $sound);
        $payload = json_encode($payload);

        $apns_url = NULL;
        $apns_cert = NULL;
        $apns_port = 2195;

        $rootLink = '';

        if($development)
        {
          $apns_url = 'gateway.sandbox.push.apple.com';
          $apns_cert = $rootLink . $cert;
        }
        else
        {
          $apns_url = 'gateway.push.apple.com';
          $apns_cert = $rootLink . $cert;
        }

        $stream_context = stream_context_create();
        stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert);

        $apns = stream_socket_client('ssl://' . $apns_url . ':' . $apns_port, $error, $error_string, 2, STREAM_CLIENT_CONNECT, $stream_context);
        stream_set_blocking($apns, 0);

        if($apns) {

          $apple_expiry = time() + (90 * 24 * 60 * 60); //Keep push alive (waiting for delivery) for 90 days

          $sendingGCM($count, $array, $apns, $payload, $apple_expiry, $cert, $title, $mesg, $push_id, $push_inst);

        }

    }

    function sendingGCM($count, $array, $apns, $payload, $apple_expiry, $cert, $title, $mesg, $push_id, $push_inst) {

      $iosIDslength = count($array) - 1;

      if($count <= $iosIDslength) {

        $when = time();

        $apn_token = $array[$count];

        $apns_message = pack("C", 1) . pack("N", $push_id) . pack("N", $apple_expiry) . pack("n", 32) . pack('H*', str_replace(' ', '', $apn_token)) . pack("n", strlen($payload)) . $payload;

        fwrite($apns, $apns_message);
        usleep(500000);

        if($checkAppleErrorResponse($apns)) {

          $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$push_inst."' && user_gcm = '".$apn_token."'";
          $getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
          $row_getGCM = mysql_fetch_assoc($getGCM);
          $getGCMRows  = mysql_num_rows($getGCM);

          // TO WHOM IS MESSAGE SENDING
          $insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$push_id."', '".$row_getGCM['user_id']."', '2', '0', '".$push_inst."', '".$when."')";
          mysql_query($insPush, $echoloyalty) or die(mysql_error());

          $sendGCM($count+1, $array, $cert, $title, $mesg, $push_id, $push_inst);

        }
        else {

          $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$push_inst."' && user_gcm = '".$apn_token."'";
          $getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
          $row_getGCM = mysql_fetch_assoc($getGCM);
          $getGCMRows  = mysql_num_rows($getGCM);

          // TO WHOM IS MESSAGE SENDING
          $insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$push_id."', '".$row_getGCM['user_id']."', '1', '0', '".$push_inst."', '".$when."')";
          mysql_query($insPush, $echoloyalty) or die(mysql_error());

          $sendingGCM($count+1, $array, $apns, $payload, $apple_expiry, $cert, $title, $mesg, $push_id, $push_inst);

        }

      }

    }

    function sendGCMOrg($count, $array, $iosOrg, $title, $mesg) {

        $messageios = $title . " : " . $mesg;
      
        $badge = 1;
        $sound = 'default';
        $development = false;

        $payload = array();
        $payload['aps'] = array('alert' => html_entity_decode($messageios), 'badge' => intval($badge), 'sound' => $sound);
        $payload = json_encode($payload);

        $apns_url = NULL;
        $apns_cert = NULL;
        $apns_port = 2195;

        // $rootLink = '/var/www/vhosts/xxx.com/httpdocs/src/MyApp/';
        $rootLink = '';

        if($development)
        {
          $apns_url = 'gateway.sandbox.push.apple.com';
          $apns_cert = $rootLink . $iosOrg[$count];
        }
        else
        {
          $apns_url = 'gateway.push.apple.com';
          $apns_cert = $rootLink . $iosOrg[$count];
        }

        $stream_context = stream_context_create();
        stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert);

        $apns = stream_socket_client('ssl://' . $apns_url . ':' . $apns_port, $error, $error_string, 2, STREAM_CLIENT_CONNECT, $stream_context);
        stream_set_blocking($apns, 0);

        if($apns) {

          $apple_expiry = time() + (90 * 24 * 60 * 60); //Keep push alive (waiting for delivery) for 90 days

          $sendingGCMOrg($count, $array, $iosOrg, $title, $mesg, $apple_expiry, $payload);

        }

    }

    function sendingGCMOrg($count, $array, $iosOrg, $title, $mesg, $apple_expiry, $payload) {

      $iosIDslength = count($array) - 1;

      if($count <= $iosIDslength) {

        $when = time();

        $apn_token = $array[$count];

        $apns_message = pack("C", 1) . pack("N", $count) . pack("N", $apple_expiry) . pack("n", 32) . pack('H*', str_replace(' ', '', $apn_token)) . pack("n", strlen($payload)) . $payload;

        fwrite($apns, $apns_message);
        usleep(500000);

        if($checkAppleErrorResponse($apns)) {
          $sendGCMOrg($count+1, $array, $iosOrg, $title, $mesg);
        }
        else {
          $sendingGCMOrg($count+1, $array, $iosOrg, $title, $mesg, $apple_expiry, $payload);
        }

      }

    }

    function checkAppleErrorResponse($apns) {

       //byte1=always 8, byte2=StatusCode, bytes3,4,5,6=identifier(rowID). Should return nothing if OK.
       $apple_error_response = fread($apns, 6);
       //NOTE: Make sure you set stream_set_blocking($apns, 0) or else fread will pause your script and wait forever when there is no response to be sent.

       if ($apple_error_response) {
            //unpack the error response (first byte 'command" should always be 8)
            $error_response = unpack('Ccommand/Cstatus_code/Nidentifier', $apple_error_response);

            // if ($error_response['status_code'] == '0') {
            //     $error_response['status_code'] = '0-No errors encountered';
            // } else if ($error_response['status_code'] == '1') {
            //     $error_response['status_code'] = '1-Processing error';
            // } else if ($error_response['status_code'] == '2') {
            //     $error_response['status_code'] = '2-Missing device token';
            // } else if ($error_response['status_code'] == '3') {
            //     $error_response['status_code'] = '3-Missing topic';
            // } else if ($error_response['status_code'] == '4') {
            //     $error_response['status_code'] = '4-Missing payload';
            // } else if ($error_response['status_code'] == '5') {
            //     $error_response['status_code'] = '5-Invalid token size';
            // } else if ($error_response['status_code'] == '6') {
            //     $error_response['status_code'] = '6-Invalid topic size';
            // } else if ($error_response['status_code'] == '7') {
            //     $error_response['status_code'] = '7-Invalid payload size';
            // } else if ($error_response['status_code'] == '8') {
            //     $error_response['status_code'] = '8-Invalid token';
            // } else if ($error_response['status_code'] == '255') {
            //     $error_response['status_code'] = '255-None (unknown)';
            // } else {
            //     $error_response['status_code'] = $error_response['status_code'] . '-Not listed';
            // }

            // echo '<br><b>+ + + + + + ERROR</b> Response Command:<b>' . $error_response['command'] . '</b>&nbsp;&nbsp;&nbsp;Identifier:<b>' . $error_response['identifier'] . '</b>&nbsp;&nbsp;&nbsp;Status:<b>' . $error_response['status_code'] . '</b><br>';
            // echo 'Identifier is the rowID (index) in the database that caused the problem, and Apple will disconnect you from server. To continue sending Push Notifications, just start at the next rowID after this Identifier.<br>';

            return true;
       }
       return false;

    }

    function protect($v) {
      $v = trim($v);
      $v = stripslashes($v);
      $v = htmlentities($v, ENT_QUOTES);
      $v = mysql_real_escape_string($v);
      
      return $v;

    }

    function onMessage(ConnectionInterface $from, $msg) {

        $themsg = json_decode($msg);

        $hostname_echoloyalty = "";
        $database_echoloyalty = "";
        $username_echoloyalty = "";
        $password_echoloyalty = "";

        $echoloyalty = mysql_pconnect(
        $hostname_echoloyalty, 
        $username_echoloyalty, 
        $password_echoloyalty);
        // $password_echoloyalty) or trigger_error(mysql_error(),E_USER_ERROR);

        $when = time();
        $thetime = $when - 60*30;

        $colname_getUser = "%";
        if (isset($themsg['mem_id'])) {
          $colname_getUser = $themsg['mem_id'];
        }
        $colname_getUser2 = "%";
        if (isset($themsg['inst_id'])) {
          $colname_getUser2 = $themsg['inst_id'];
        }
        $colname_getUser3 = "%";
        if (isset($themsg['mem_key'])) {
          $colname_getUser3 = $themsg['mem_key'];
        }
        $colname_getUser4 = "%";
        if (isset($themsg['site_id'])) {
          $colname_getUser4 = $themsg['site_id'];
        }
        $colname_getUser5 = "%";
        if (isset($themsg['prof'])) {
          $colname_getUser5 = $themsg['prof'];
        }
        $colname_getUser6 = "-1";
        if (isset($themsg['newusr'])) {
          $colname_getUser6 = $themsg['newusr'];
        }

        if($echoloyalty) {

          mysql_query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
          // GET USER
          mysql_select_db($database_echoloyalty, $echoloyalty);
          $query_getUser = "SELECT * FROM users WHERE user_id = '".$colname_getUser."' && user_institution = '".$colname_getUser2."' && user_log_key = '".$colname_getUser3."'";
          $getUser = mysql_query($query_getUser, $echoloyalty) or die(mysql_error());
          $row_getUser = mysql_fetch_assoc($getUser);
          $artNumRows  = mysql_num_rows($getUser);
          // GET ORGANIZATION
          $query_getInst = "SELECT * FROM organizations WHERE org_id = '".$colname_getUser2."'";
          $getInst = mysql_query($query_getInst, $echoloyalty) or die(mysql_error());
          $row_getInst = mysql_fetch_assoc($getInst);
          $getInstRows  = mysql_num_rows($getInst);
          // GET CITY
          $orgCity = $row_getInst['org_city'];
          $orgCountry = $row_getInst['org_country'];
          $query_getCity = "SELECT name FROM city WHERE id_city = '".$orgCity."' && id_country = '".$orgCountry."'";
          $getCity = mysql_query($query_getCity, $echoloyalty) or die(mysql_error());
          $row_getCity = mysql_fetch_assoc($getCity);
          $getCityRows  = mysql_num_rows($getCity);
          // EVENTS GRATULATE
          $query_getEventsGrat = "SELECT * FROM events WHERE event_auto = '1' && event_date <= '".$when."' && event_when > '1'";
          $getEventsGrat = mysql_query($query_getEventsGrat, $echoloyalty) or die(mysql_error());
          $row_getEventsGrat = mysql_fetch_assoc($getEventsGrat);
          $getEventsGratRows  = mysql_num_rows($getEventsGrat);

          if($getEventsGratRows > 0) {

              do {

                  $query_getGCMevent = "SELECT * FROM users WHERE user_institution = '".$row_getEventsGrat['event_institution']."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0'";
                  $getGCMevent = mysql_query($query_getGCMevent, $echoloyalty) or die(mysql_error());
                  $row_getGCMevent = mysql_fetch_assoc($getGCMevent);
                  $getGCMeventRows  = mysql_num_rows($getGCMevent);

                  $query_getInstEvent = "SELECT * FROM organizations WHERE org_id = '".$row_getEventsGrat['event_institution']."'";
                  $getInstEvent = mysql_query($query_getInstEvent, $echoloyalty) or die(mysql_error());
                  $row_getInstEvent = mysql_fetch_assoc($getInstEvent);
                  $getInstEventRows  = mysql_num_rows($getInstEvent);

                  if($getGCMeventRows > 0) {

                    $apiKey =  urldecode($row_getInstEvent['org_key']);
                    
                    $title = urldecode($row_getEventsGrat['event_name']);

                    $messageand = urldecode($row_getEventsGrat['event_desc']);

                    // iOS SETTINGS
                    $iosIDs = array();

                    // SENDING
                    do {

                      if($row_getGCMevent['user_device_os'] == 'Android') {

                          // ANDROID PUSH
                          $registrationId = urldecode($row_getGCMevent['user_gcm']);

                          // ANDROID SETTINGS
                          $headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
                          $data = array(
                              'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title)),
                              'registration_ids' => array($registrationId)
                          );

                          $ch = curl_init();

                          curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
                          curl_setopt( $ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
                          curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                          curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                          curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                          curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );

                          $response = curl_exec($ch);
                          curl_close($ch);

                      }
                      elseif($row_getGCMevent['user_device_os'] == 'iOS') {

                        array_push($iosIDs, $row_getGCMevent['user_gcm']);

                      }

                      if($row_getEventsGrat['event_points'] > 0) {

                          $newwhen = time();

                          // ADD POINTS TO WALLET
                          $insPoints = "INSERT INTO points (points_user, points_points, points_institution, points_status, points_proofed, points_when, points_time) VALUES ('".$row_getGCMevent['user_id']."', '".$row_getEventsGrat['event_points']."', '".$row_getEventsGrat['event_institution']."', '2', '1', '".$newwhen."', '".$newwhen."')";
                          mysql_query($insPoints, $echoloyalty) or die(mysql_error());

                          $query_getWalletevent = "SELECT * FROM wallet WHERE wallet_user = '".$row_getGCMevent['user_id']."' && wallet_institution = '".$row_getEventsGrat['event_institution']."'";
                          $getWalletevent = mysql_query($query_getWalletevent, $echoloyalty) or die(mysql_error());
                          $row_getWalletevent = mysql_fetch_assoc($getWalletevent);
                          $getWalleteventRows  = mysql_num_rows($getWalletevent);

                          if($getWalleteventRows > 0) {

                              $newwallet = $row_getWalletevent['wallet_total'] + $row_getEventsGrat['event_points'];

                              $updWallet = "UPDATE wallet SET wallet_total = '".$newwallet."',  wallet_when = '".$newwhen."' WHERE wallet_id = '".$row_getWalletevent['wallet_id']."'";
                              mysql_query($updWallet, $echoloyalty) or die(mysql_error());

                          }

                      }

                    } while ($row_getGCMevent = mysql_fetch_assoc($getGCMevent));

                    $sendGCM(0, $iosIDs, $row_getInstEvent['org_cert'], $row_getEventsGrat['event_name'], $row_getEventsGrat['event_desc'], 0, 0);

                  }

              } while ($row_getEventsGrat = mysql_fetch_assoc($getEventsGrat));

              $eventFuture = $when + 60*60*24*365;

              $updEvents = "UPDATE events SET event_date = '".$eventFuture."' WHERE event_id = '".$row_getEventsGrat['event_id']."'";
              mysql_query($updEvents, $echoloyalty) or die(mysql_error());

          }
          // POINTS ENDING MESSAGE
          $lastDateWarn = time() - 60*60*24*150;

          $query_getWalletLast = "SELECT * FROM wallet WHERE wallet_total > '0' && wallet_when <= '".$lastDateWarn."' && wallet_warn = '0'";
          $getWalletLast = mysql_query($query_getWalletLast, $echoloyalty) or die(mysql_error());
          $row_getWalletLast = mysql_fetch_assoc($getWalletLast);
          $getWalletLastRows  = mysql_num_rows($getWalletLast);

          if($getWalletLastRows > 0) {

              // iOS SETTINGS
              $iosIDs = array();
              $iosOrg = array();

              do {

                  $query_getGCMWallet = "SELECT * FROM users WHERE user_id = '".$row_getWalletLast['wallet_user']."' && user_institution = '".$row_getWalletLast['wallet_institution']."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0'";
                  $getGCMWallet = mysql_query($query_getGCMWallet, $echoloyalty) or die(mysql_error());
                  $row_getGCMWallet = mysql_fetch_assoc($getGCMWallet);
                  $getGCMWalletRows  = mysql_num_rows($getGCMWallet);

                  $query_getInstWallet = "SELECT * FROM organizations WHERE org_id = '".$row_getWalletLast['wallet_institution']."'";
                  $getInstWallet = mysql_query($query_getInstWallet, $echoloyalty) or die(mysql_error());
                  $row_getInstWallet = mysql_fetch_assoc($getInstWallet);
                  $getInstWalletRows  = mysql_num_rows($getInstWallet);

                  $updWalletWarn = "UPDATE wallet SET wallet_warn = '1' WHERE wallet_id = '".$row_getWalletLast['wallet_id']."'";
                   mysql_query($updWalletWarn, $echoloyalty) or die(mysql_error());

                  if($getGCMWalletRows > 0) {

                    $apiKey =  urldecode($row_getInstWallet['org_key']);
                    
                    $title = urldecode('Истечение срока баллов');

                    $messageand = urldecode('До конца срока действия Ваших баллов осталось 30 дней!');

                    // SENDING
                    if($row_getGCMWallet['user_device_os'] == 'Android') {

                        // ANDROID PUSH
                        $registrationId = urldecode($row_getGCMWallet['user_gcm']);

                        // ANDROID SETTINGS
                        $headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
                        $data = array(
                            'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title)),
                            'registration_ids' => array($registrationId)
                        );

                        $ch = curl_init();

                        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
                        curl_setopt( $ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
                        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );

                        $response = curl_exec($ch);
                        curl_close($ch);

                    }
                    elseif($row_getGCMWallet['user_device_os'] == 'iOS') {

                      array_push($iosIDs, $row_getGCMWallet['user_gcm']);
                      array_push($iosOrg, $row_getInstWallet['org_cert']);

                    }

                  }

              } while ($row_getWalletLast = mysql_fetch_assoc($getWalletLast));

              $sendGCMOrg(0, $iosIDs, $iosOrg, 'Истечение срока баллов', 'До конца срока действия Ваших баллов осталось 30 дней!');

          }

          // POINTS ENDING DELETION
          $lastDateDel = time() - 60*60*24*180;

          $query_getWalletDel = "SELECT * FROM wallet WHERE wallet_total > '0' && wallet_when <= '".$lastDateDel."' && wallet_warn = '1'";
          $getWalletDel = mysql_query($query_getWalletDel, $echoloyalty) or die(mysql_error());
          $row_getWalletDel = mysql_fetch_assoc($getWalletDel);
          $getWalletDelRows  = mysql_num_rows($getWalletDel);

          if($getWalletDelRows > 0) {
              $updWalletDel = "UPDATE wallet SET wallet_warn = '0', wallet_total = '0' WHERE wallet_id = '".$row_getWalletDel['wallet_id']."'";
              mysql_query($updWalletDel, $echoloyalty) or die(mysql_error());
          }

        }

        $gotdata = array();

        // CONTROLL PANEL
        if($artNumRows > 0 && $echoloyalty) {

            if($colname_getUser4 == 'main') {

                if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                  $colname_me = "-1";
                  if (isset($themsg['me'])) {
                    $colname_me = $protect($themsg['me']);
                  }
                  $colname_points_cost = "-1";
                  if (isset($themsg['points_cost'])) {
                    $colname_points_cost = $protect($themsg['points_cost']);
                  }
                  $colname_starting_points = "-1";
                  if (isset($themsg['starting_points'])) {
                    $colname_starting_points = $protect($themsg['starting_points']);
                  }
                  $colname_money_percent = "-1";
                  if (isset($themsg['money_percent'])) {
                    $colname_money_percent = $protect($themsg['money_percent']);
                  }
                  $colname_risk_summ = "-1";
                  if (isset($themsg['risk_summ'])) {
                    $colname_risk_summ = $protect($themsg['risk_summ']);
                  }
                        
                  if($colname_getUser5 == 'set') {

                    $updOrg = "UPDATE organizations SET org_money_points='".$colname_points_cost."', org_starting_points='".$colname_starting_points."', org_money_percent='".$colname_money_percent."', org_risk_summ='".$colname_risk_summ."' WHERE org_id = '".$colname_getUser2."'";
                    mysql_query($updOrg, $echoloyalty) or die(mysql_error());

                    $newarrmes = array("requests" => '1', "orgUpd" => '1');
                    array_push($gotdata, $newarrmes);

                  }

                }
                else {

                    $query_getNewsC = "SELECT news_id FROM news WHERE news_institution = '".$colname_getUser2."' AND news_state > '0' AND news_when > '1'";
                    $getNewsC = mysql_query($query_getNewsC, $echoloyalty) or die(mysql_error());
                    $row_getNewsC = mysql_fetch_assoc($getNewsC);
                    $getNewsCRows  = mysql_num_rows($getNewsC);

                    $query_getEventsC = "SELECT event_id FROM events WHERE event_institution = '".$colname_getUser2."' AND event_when > '1'";
                    $getEventsC = mysql_query($query_getEventsC, $echoloyalty) or die(mysql_error());
                    $row_getEventsC = mysql_fetch_assoc($getEventsC);
                    $getEventsCRows  = mysql_num_rows($getEventsC);
            
                    $query_getPointsC = "SELECT points_id FROM points WHERE points_institution = '".$colname_getUser2."'";
                    $getPointsC = mysql_query($query_getPointsC, $echoloyalty) or die(mysql_error());
                    $row_getPointsC = mysql_fetch_assoc($getPointsC);
                    $getPointsCRows  = mysql_num_rows($getPointsC);
            
                    $query_getPushC = "SELECT push_id FROM pushmessages WHERE push_institution = '".$colname_getUser2."'";
                    $getPushC = mysql_query($query_getPushC, $echoloyalty) or die(mysql_error());
                    $row_getPushC = mysql_fetch_assoc($getPushC);
                    $getPushCRows  = mysql_num_rows($getPushC);
            
                    $query_getInstallsC = "SELECT user_id FROM users WHERE user_institution = '".$colname_getUser2."'";
                    $getInstallsC = mysql_query($query_getInstallsC, $echoloyalty) or die(mysql_error());
                    $row_getInstallsC = mysql_fetch_assoc($getInstallsC);
                    $getInstallsCRows  = mysql_num_rows($getInstallsC);
            
                    // POINTS
                    $query_getRevC = "SELECT SUM(points_bill) AS TotalBills FROM points WHERE points_institution = '".$colname_getUser2."' && points_proofed='1' && points_status='0'";
                    $getRevC = mysql_query($query_getRevC, $echoloyalty) or die(mysql_error());
                    $row_getRevC = mysql_fetch_assoc($getRevC);
                    $getRevCRows  = mysql_num_rows($getRevC);
                    
                    // SHARES ALL
                    $query_getShareAC = "SELECT share_id FROM shares WHERE share_institution = '".$colname_getUser2."'";
                    $getShareAC = mysql_query($query_getShareAC, $echoloyalty) or die(mysql_error());
                    $row_getShareAC = mysql_fetch_assoc($getShareAC);
                    $getShareACRows  = mysql_num_rows($getShareAC);
                    
                    $usramountarr = array();
                    $revarr = array();
                    $loyalusr = array();
                    $shareusr = array();
                    
                    for ($x = 0; $x <= 30; $x++) {
                      // LAST 30 DAYS
                      $timeamount = time() - $x*86400;
                      $afterday = $timeamount + 86400;
                      // USERS
                      $query_getUsers = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_reg < '".$timeamount."' && user_work_pos = '0'";
                      $getUsers = mysql_query($query_getUsers, $echoloyalty) or die(mysql_error());
                      $row_getUsers = mysql_fetch_assoc($getUsers);
                      $getUsersRows  = mysql_num_rows($getUsers);
                      
                      // TIME - PEOPLE ADD TO ARRAY
                      $usramountarr[$timeamount] = $getUsersRows;
                      
                      if($x < 20) {
                        // POINTS
                        $query_getBillsC = "SELECT SUM(points_bill) AS TotalBill FROM points WHERE points_institution = '".$colname_getUser2."' && points_when > '".$timeamount."' && points_when < '".$afterday."' && points_comment != '100'";
                        $getBillsC = mysql_query($query_getBillsC, $echoloyalty) or die(mysql_error());
                        $row_getBillsC = mysql_fetch_assoc($getBillsC);
                        $getBillsCRows  = mysql_num_rows($getBillsC);
                        
                        // BILLS ADD TO ARRAY
                        if($getBillsCRows > 0 && !empty($row_getBillsC['TotalBill'])) {
                          array_push($revarr, $row_getBillsC['TotalBill']);
                        }
                        else {
                          array_push($revarr, 0);
                        }
                        
                        // LOYAL USER
                        $query_getLoyalC = "SELECT points_user, count(points_user) AS cnt FROM points WHERE points_institution = '".$colname_getUser2."' && points_when > '".$timeamount."' && points_when < '".$afterday."' GROUP BY points_user HAVING COUNT(points_user)>1";
                        $getLoyalC = mysql_query($query_getLoyalC, $echoloyalty) or die(mysql_error());
                        $row_getLoyalC = mysql_fetch_assoc($getLoyalC);
                        $getLoyalCRows  = mysql_num_rows($getLoyalC);
                        
                        if($row_getLoyalC['cnt'] === NULL) {
                          array_push($loyalusr, "0");
                        }
                        else {
                          array_push($loyalusr, $row_getLoyalC['cnt']);
                        }
                        
                        // SHARES
                        $query_getShareC = "SELECT share_id FROM shares WHERE share_institution = '".$colname_getUser2."' && share_when > '".$timeamount."' && share_when < '".$afterday."'";
                        $getShareC = mysql_query($query_getShareC, $echoloyalty) or die(mysql_error());
                        $row_getShareC = mysql_fetch_assoc($getShareC);
                        $getShareCRows  = mysql_num_rows($getShareC);
                        
                        // SHARES ADD TO ARRAY
                        if($getShareCRows > 0 && !empty($row_getShareC['share_id'])) {
                          array_push($shareusr, $getShareCRows);
                        }
                        else {
                          array_push($shareusr, 0);
                        }
                      
                      }
                      
                    }
            
                    $newarrmes = array("newsC" => $getNewsCRows, "eventsC" => $getEventsCRows, "pointsC" => $getPointsCRows, "pushC" => $getPushCRows, "revC" => $row_getRevC['TotalBills'], "shareAC" => $getShareACRows, "shareC" => $shareusr, "billC" => $revarr, "loyalC" => $loyalusr, "usrC" => $usramountarr, "instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "org_points_cost" => $row_getInst['org_money_points'], "org_starting_points" => $row_getInst['org_starting_points'], "org_money_percent" => $row_getInst['org_money_percent'], "org_risk_summ" => $row_getInst['org_risk_summ']);
                    array_push($gotdata, $newarrmes);

                }

            }
            else if($colname_getUser4 == 'news') {
        
              if(isset($colname_getUser5) && $colname_getUser5 != '%') {


                $colname_newsid = "-1";
                if (isset($themsg['newsid'])) {
                  $colname_newsid = $protect($themsg['newsid']);
                }
                $colname_chid = "-1";
                if (isset($themsg['chid'])) {
                  $colname_chid = $protect($themsg['chid']);
                }
                $colname_newtitle = "-1";
                if (isset($themsg['newtitle'])) {
                  $colname_newtitle = $protect($themsg['newtitle']);
                }
                $colname_newmessage = "-1";
                if (isset($themsg['newmessage'])) {
                  $colname_newmessage = $protect($themsg['newmessage']);
                }
                
                if($colname_getUser5 == 'del') {

                    $query_getNewsChng = "SELECT * FROM news WHERE news_institution = '".$colname_getUser2."' && news_id = '".$colname_newsid."' LIMIT 1";
                    $getNewsChng = mysql_query($query_getNewsChng, $echoloyalty) or die(mysql_error());
                    $row_getNewsChng = mysql_fetch_assoc($getNewsChng);
                    $getNewsChngRows  = mysql_num_rows($getNewsChng);

                    if($getNewsChngRows > 0) {

                        $delNews = "UPDATE news SET news_when='1', news_state='0' WHERE news_institution = '".$colname_getUser2."' && news_id='".$colname_newsid."'";
                        mysql_query($delNews, $echoloyalty) or die(mysql_error());

                        // $rootLink = '/httpdocs/admin/';

                        // if(unlink($rootLink.'img/news/'.$colname_getUser2.'/slide/'.$row_getNewsChng['news_pic']) && unlink($rootLink.'img/news/'.$colname_getUser2.'/pic/'.$row_getNewsChng['news_pic']) && unlink($rootLink.'img/news/'.$colname_getUser2.'/th/'.$row_getNewsChng['news_pic'])) {

                        //     $delNews = "DELETE FROM news WHERE news_institution = '".$colname_getUser2."' && news_id='".$colname_newsid."'";
                        //     mysql_query($delNews, $echoloyalty) or die(mysql_error());

                        // }

                        $newarrmes = array("requests" => '1', "newsId" => $colname_newsid, "newsUpd" => '2');
                        array_push($gotdata, $newarrmes);

                    }

                }
                else if($colname_getUser5 == 'change') {

                    $query_getNewsChng = "SELECT * FROM news WHERE news_institution = '".$colname_getUser2."' && news_id = '".$colname_chid."' LIMIT 1";
                    $getNewsChng = mysql_query($query_getNewsChng, $echoloyalty) or die(mysql_error());
                    $row_getNewsChng = mysql_fetch_assoc($getNewsChng);
                    $getNewsChngRows  = mysql_num_rows($getNewsChng);

                    if($getNewsChngRows > 0) {

                        $chngNews = "UPDATE news SET news_name='".$colname_newtitle."', news_message='".$colname_newmessage."', news_when='".$when."' WHERE news_institution = '".$colname_getUser2."' && news_id='".$colname_chid."'";
                        mysql_query($chngNews, $echoloyalty) or die(mysql_error());

                        $newarrmes = array("requests" => '1', "newsId" => $colname_chid, "newsUpd" => '3');
                        array_push($gotdata, $newarrmes);

                    }

                }
                else {

                    $query_getNewsChng = "SELECT * FROM news WHERE news_institution = '".$colname_getUser2."' && news_id = '".$colname_getUser5."' LIMIT 1";
                    $getNewsChng = mysql_query($query_getNewsChng, $echoloyalty) or die(mysql_error());
                    $row_getNewsChng = mysql_fetch_assoc($getNewsChng);
                    $getNewsChngRows  = mysql_num_rows($getNewsChng);
                    
                    if($getNewsChngRows > 0) {
                        if($row_getNewsChng['news_state'] == '0') {
                            $updNews = "UPDATE news SET news_state='1', news_when='".$when."' WHERE news_id='".$colname_getUser5."'";
                            mysql_query($updNews, $echoloyalty) or die(mysql_error());
                            $newarrmes = array("requests" => '1', "newsId" => $colname_getUser5, "newsUpd" => '1');
                            array_push($gotdata, $newarrmes);
                        }
                        else {
                            $updNews = "UPDATE news SET news_state='0', news_when='".$when."' WHERE news_id='".$colname_getUser5."'";
                            mysql_query($updNews, $echoloyalty) or die(mysql_error());
                            $newarrmes = array("requests" => '1', "newsId" => $colname_getUser5, "newsUpd" => '0');
                            array_push($gotdata, $newarrmes);
                        }
                    }

                }
                

              }
              else {

                  $query_getNewsC = "SELECT * FROM news WHERE news_institution = '".$colname_getUser2."' && news_when > '1'";
                  $getNewsC = mysql_query($query_getNewsC, $echoloyalty) or die(mysql_error());
                  $row_getNewsC = mysql_fetch_assoc($getNewsC);
                  $getNewsCRows  = mysql_num_rows($getNewsC);
                  
                  $newsarr = array();
                  if($getNewsCRows > 0) {

                    do {
                      
                      array_push($newsarr, array($row_getNewsC['news_id'], $row_getNewsC['news_name'], $row_getNewsC['news_message'], $row_getNewsC['news_pic'], $row_getNewsC['news_institution'], $row_getNewsC['news_state'], $row_getNewsC['news_when']));
                      
                    } while ($row_getNewsC = mysql_fetch_assoc($getNewsC));

                  }
          
                  $newarrmes = array("newsC" => $getNewsCRows, "instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "newsAll" => $newsarr);
                  array_push($gotdata, $newarrmes);
              
              }
      
            }
            else if($colname_getUser4 == 'gifts') {
        
              if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                $colname_giftid = "-1";
                if (isset($themsg['giftid'])) {
                  $colname_giftid = $protect($themsg['giftid']);
                }
                $colname_chid = "-1";
                if (isset($themsg['chid'])) {
                  $colname_chid = $protect($themsg['chid']);
                }
                $colname_newtitle = "-1";
                if (isset($themsg['newtitle'])) {
                  $colname_newtitle = $protect($themsg['newtitle']);
                }
                $colname_newmessage = "-1";
                if (isset($themsg['newmessage'])) {
                  $colname_newmessage = $protect($themsg['newmessage']);
                }
                $colname_newpoints = "-1";
                if (isset($themsg['newpoints'])) {
                  $colname_newpoints = $protect($themsg['newpoints']);
                }
                              
                if($colname_getUser5 == 'del') {

                    $query_getGiftsChng = "SELECT * FROM gifts WHERE gifts_institution = '".$colname_getUser2."' && gifts_id = '".$colname_giftid."' LIMIT 1";
                    $getGiftsChng = mysql_query($query_getGiftsChng, $echoloyalty) or die(mysql_error());
                    $row_getGiftsChng = mysql_fetch_assoc($getGiftsChng);
                    $getGiftsChngRows  = mysql_num_rows($getGiftsChng);

                    if($getGiftsChngRows > 0) {

                        $delGifts = "UPDATE gifts SET gifts_when='1' WHERE gifts_institution = '".$colname_getUser2."' && gifts_id='".$colname_giftid."'";
                        mysql_query($delGifts, $echoloyalty) or die(mysql_error());

                        // $rootLink = '/httpdocs/admin/';

                        // if(unlink($rootLink.'img/gifts/'.$colname_getUser2.'/slide/'.$row_getGiftsChng['gifts_pic']) && unlink($rootLink.'img/gifts/'.$colname_getUser2.'/pic/'.$row_getGiftsChng['gifts_pic']) && unlink($rootLink.'img/gifts/'.$colname_getUser2.'/th/'.$row_getGiftsChng['gifts_pic'])) {

                        //     $delGifts = "DELETE FROM gifts WHERE gifts_institution = '".$colname_getUser2."' && gifts_id='".$colname_giftid."'";
                        //     mysql_query($delGifts, $echoloyalty) or die(mysql_error());

                        // }

                        $newarrmes = array("requests" => '1', "giftsId" => $colname_giftid, "giftsUpd" => '2');
                        array_push($gotdata, $newarrmes);

                    }

                }
                else if($colname_getUser5 == 'first') {

                    $query_getGiftsChng = "SELECT * FROM gifts WHERE gifts_institution = '".$colname_getUser2."' && gifts_id = '".$colname_giftid."' LIMIT 1";
                    $getGiftsChng = mysql_query($query_getGiftsChng, $echoloyalty) or die(mysql_error());
                    $row_getGiftsChng = mysql_fetch_assoc($getGiftsChng);
                    $getGiftsChngRows  = mysql_num_rows($getGiftsChng);

                    if($getGiftsChngRows > 0) {

                       // $rootLink = '/var/www/vhosts/xxx.com/httpdocs/admin/';
                       $rootLink = '';

                        if($row_getGiftsChng['gifts_when'] > '2') {

                            $updGifts = "UPDATE gifts SET gifts_when = '2' WHERE gifts_institution = '".$colname_getUser2."' && gifts_id='".$colname_giftid."'";
                            mysql_query($updGifts, $echoloyalty) or die(mysql_error());

                            $newarrmes = array("requests" => '1', "giftsId" => $colname_giftid, "giftsUpd" => '4');
                        array_push($gotdata, $newarrmes);

                        }
                        else if($row_getGiftsChng['gifts_when'] == '2') {

                            $updGifts = "UPDATE gifts SET gifts_when = '".$when."' WHERE gifts_institution = '".$colname_getUser2."' && gifts_id='".$colname_giftid."'";
                            mysql_query($updGifts, $echoloyalty) or die(mysql_error());

                            $newarrmes = array("requests" => '1', "giftsId" => $colname_giftid, "giftsUpd" => '3');
                        array_push($gotdata, $newarrmes);

                        }

                    }

                }
                else if($colname_getUser5 == 'change') {

                    $query_getGiftsChng = "SELECT * FROM gifts WHERE gifts_institution = '".$colname_getUser2."' && gifts_id = '".$colname_chid."' LIMIT 1";
                    $getGiftsChng = mysql_query($query_getGiftsChng, $echoloyalty) or die(mysql_error());
                    $row_getGiftsChng = mysql_fetch_assoc($getGiftsChng);
                    $getGiftsChngRows  = mysql_num_rows($getGiftsChng);

                    if($getGiftsChngRows > 0) {

                        $chngGifts = "UPDATE gifts SET gifts_name='".$colname_newtitle."', gifts_desc='".$colname_newmessage."', gifts_points='".$colname_newpoints."', gifts_when='".$when."' WHERE gifts_institution = '".$colname_getUser2."' && gifts_id='".$colname_chid."'";
                        mysql_query($chngGifts, $echoloyalty) or die(mysql_error());

                        $newarrmes = array("requests" => '1', "giftsId" => $colname_chid, "giftsUpd" => '5');
                        array_push($gotdata, $newarrmes);

                    }

                }
                else {

                  $query_getGiftsChng = "SELECT * FROM gifts WHERE gifts_institution = '".$colname_getUser2."' && gifts_id = '".$colname_getUser5."' LIMIT 1";
                  $getGiftsChng = mysql_query($query_getGiftsChng, $echoloyalty) or die(mysql_error());
                  $row_getGiftsChng = mysql_fetch_assoc($getGiftsChng);
                  $getGiftsChngRows  = mysql_num_rows($getGiftsChng);
                  
                  if($getGiftsChngRows > 0) {
                      if($row_getGiftsChng['gifts_when'] == '1') {
                          $updGifts = "UPDATE gifts SET gifts_when='".$when."' WHERE gifts_id='".$colname_getUser5."'";
                          mysql_query($updGifts, $echoloyalty) or die(mysql_error());
                          $newarrmes = array("requests" => '1', "giftsId" => $colname_getUser5, "giftsUpd" => "0");
                          array_push($gotdata, $newarrmes);
                      }
                      else {
                          $updGifts = "UPDATE gifts SET gifts_when='1' WHERE gifts_id='".$colname_getUser5."'";
                          mysql_query($updGifts, $echoloyalty) or die(mysql_error());
                          $newarrmes = array("requests" => '1', "giftsId" => $colname_getUser5, "giftsUpd" => '1');
                          array_push($gotdata, $newarrmes);
                      }
                  }

                }
                
              }
              else {

                  $query_getGiftsC = "SELECT * FROM gifts WHERE gifts_institution = '".$colname_getUser2."' && gifts_when > '1'";
                  $getGiftsC = mysql_query($query_getGiftsC, $echoloyalty) or die(mysql_error());
                  $row_getGiftsC = mysql_fetch_assoc($getGiftsC);
                  $getGiftsCRows  = mysql_num_rows($getGiftsC);
          
                  $giftsarr = array();
                  if($getGiftsCRows > 0) {

                    do {
                      
                      array_push($giftsarr, array($row_getGiftsC['gifts_id'], $row_getGiftsC['gifts_name'], $row_getGiftsC['gifts_desc'], $row_getGiftsC['gifts_points'], $row_getGiftsC['gifts_pic'], $row_getGiftsC['gifts_institution'], $row_getGiftsC['gifts_when']));
                      
                    } while ($row_getGiftsC = mysql_fetch_assoc($getGiftsC));

                  }
          
                  $newarrmes = array("giftsC" => $getGiftsCRows, "instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "giftsAll" => $giftsarr);
                  array_push($gotdata, $newarrmes);
              
              }
      
            }
            else if($colname_getUser4 == 'event') {

                if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                    $colname_eventid = "-1";
                    if (isset($themsg['eventid'])) {
                      $colname_eventid = $protect($themsg['eventid']);
                    }
                    $colname_chid = "-1";
                    if (isset($themsg['chid'])) {
                      $colname_chid = $protect($themsg['chid']);
                    }
                    $colname_newtitle = "-1";
                    if (isset($themsg['newtitle'])) {
                      $colname_newtitle = $protect($themsg['newtitle']);
                    }
                    $colname_newmessage = "-1";
                    if (isset($themsg['newmessage'])) {
                      $colname_newmessage = $protect($themsg['newmessage']);
                    }
                    $colname_newdate = "-1";
                    if (isset($themsg['newdate'])) {
                      $colname_newdate = $protect($themsg['newdate']);
                    }
                    $colname_newautomatic = "-1";
                    if (isset($themsg['newautomatic'])) {
                      $colname_newautomatic = $protect($themsg['newautomatic']);
                    }
                    $colname_newpoints = "-1";
                    if (isset($themsg['newpoints'])) {
                      $colname_newpoints = $protect($themsg['newpoints']);
                    }
                    $colname_newdiscount = "-1";
                    if (isset($themsg['newdiscount'])) {
                      $colname_newdiscount = $protect($themsg['newdiscount']);
                    }
                        
                    if($colname_getUser5 == 'del') {

                        $query_getEventChng = "SELECT * FROM events WHERE event_institution = '".$colname_getUser2."' && event_id = '".$colname_eventid."' LIMIT 1";
                        $getEventChng = mysql_query($query_getEventChng, $echoloyalty) or die(mysql_error());
                        $row_getEventChng = mysql_fetch_assoc($getEventChng);
                        $getEventChngRows  = mysql_num_rows($getEventChng);

                        if($getEventChngRows > 0) {

                            $delEvents = "UPDATE events SET event_when='1', event_status='0' WHERE event_institution = '".$colname_getUser2."' && event_id='".$colname_eventid."'";
                            mysql_query($delEvents, $echoloyalty) or die(mysql_error());

                            // $rootLink = '/httpdocs/admin/';

                            // if(unlink($rootLink.'img/event/'.$colname_getUser2.'/slide/'.$row_getEventChng['event_pic']) && unlink($rootLink.'img/event/'.$colname_getUser2.'/pic/'.$row_getEventChng['event_pic']) && unlink($rootLink.'img/event/'.$colname_getUser2.'/th/'.$row_getEventChng['event_pic'])) {

                            //     $delEvent = "DELETE FROM events WHERE event_institution = '".$colname_getUser2."' && event_id='".$colname_eventid."'";
                            //     mysql_query($delEvent, $echoloyalty) or die(mysql_error());

                            // }

                            $newarrmes = array("requests" => '1', "eventId" => $colname_eventid, "eventUpd" => '2');
                            array_push($gotdata, $newarrmes);

                        }

                    }
                    else if($colname_getUser5 == 'change') {

                        $query_getEventChng = "SELECT * FROM events WHERE event_institution = '".$colname_getUser2."' && event_id = '".$colname_chid."' LIMIT 1";
                        $getEventChng = mysql_query($query_getEventChng, $echoloyalty) or die(mysql_error());
                        $row_getEventChng = mysql_fetch_assoc($getEventChng);
                        $getEventChngRows  = mysql_num_rows($getEventChng);

                        if($getEventChngRows > 0) {

                            $newDate = strtotime($colname_newdate);

                            $chngEvents = "UPDATE events SET event_name='".$colname_newtitle."', event_desc='".$colname_newmessage."', event_auto='".$colname_newautomatic."', event_date='".$newDate."', event_bill='".$colname_newdiscount."', event_points='".$colname_newpoints."', event_when='".$when."' WHERE event_institution = '".$colname_getUser2."' && event_id='".$colname_chid."'";
                            mysql_query($chngEvents, $echoloyalty) or die(mysql_error());

                            $newarrmes = array("requests" => '1', "eventId" => $colname_chid, "eventUpd" => '3');
                            array_push($gotdata, $newarrmes);

                        }

                    }
                    else {

                        $query_getEventChng = "SELECT * FROM events WHERE event_institution = '".$colname_getUser2."' && event_id = '".$colname_getUser5."' LIMIT 1";
                        $getEventChng = mysql_query($query_getEventChng, $echoloyalty) or die(mysql_error());
                        $row_getEventChng = mysql_fetch_assoc($getEventChng);
                        $getEventChngRows  = mysql_num_rows($getEventChng);
                        
                        if($getEventChngRows > 0) {
                            if($row_getEventChng['event_auto'] == '0') {
                                $updEvent = "UPDATE events SET event_auto='1' WHERE event_id='".$colname_getUser5."'";
                                mysql_query($updEvent, $echoloyalty) or die(mysql_error());
                                $newarrmes = array("requests" => '1', "eventId" => $colname_getUser5, "eventUpd" => '1');
                                array_push($gotdata, $newarrmes);
                            }
                            else {
                                $updEvent = "UPDATE events SET event_auto='0' WHERE event_id='".$colname_getUser5."'";
                                mysql_query($updEvent, $echoloyalty) or die(mysql_error());
                                $newarrmes = array("requests" => '1', "eventId" => $colname_getUser5, "eventUpd" => '0');
                                array_push($gotdata, $newarrmes);
                            }
                        }

                    }
                    
                }
                else {
            
                    $query_getEventsC = "SELECT * FROM events WHERE event_institution = '".$colname_getUser2."' && event_when > '1'";
                    $getEventsC = mysql_query($query_getEventsC, $echoloyalty) or die(mysql_error());
                    $row_getEventsC = mysql_fetch_assoc($getEventsC);
                    $getEventsCRows  = mysql_num_rows($getEventsC);
            
                    $eventsarr = array();
                    if($getEventsCRows > 0) {
                      
                      do {
                        
                        array_push($eventsarr, array($row_getEventsC['event_id'], $row_getEventsC['event_name'], $row_getEventsC['event_desc'], $row_getEventsC['event_pic'], $row_getEventsC['event_auto'], $row_getEventsC['event_date'], $row_getEventsC['event_institution'], $row_getEventsC['event_bill'], $row_getEventsC['event_points'], $row_getEventsC['event_status'], $row_getEventsC['event_when']));
                        
                      } while ($row_getEventsC = mysql_fetch_assoc($getEventsC));
                      
                    }
            
                    $eventsarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "eventsAll" => $eventsarr);
                    array_push($gotdata, $eventsarrmes);

                }
      
            }
            else if($colname_getUser4 == 'points') {

              $when = time();

              $sendingok = true;

              if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                $colname_val = "-1";
                if (isset($themsg['val'])) {
                  $colname_val = $protect($themsg['val']);
                }
                $colname_pointid = "-1";
                if (isset($themsg['pointid'])) {
                  $colname_pointid = $protect($themsg['pointid']);
                }
                
                if($colname_getUser5 == 'proofed') {

                  $query_getPointsChng = "SELECT * FROM points WHERE points_institution = '".$colname_getUser2."' && points_id = '".$colname_pointid."' LIMIT 1";
                  $getPointsChng = mysql_query($query_getPointsChng, $echoloyalty) or die(mysql_error());
                  $row_getPointsChng = mysql_fetch_assoc($getPointsChng);
                  $getPointsChngRows  = mysql_num_rows($getPointsChng);

                  if($getPointsChngRows > 0) {

                      $updPoints = "UPDATE points SET points_status='".$colname_val."', points_proofed='1', points_when='".$when."' WHERE points_institution = '".$colname_getUser2."' && points_id='".$colname_pointid."'";
                      mysql_query($updPoints, $echoloyalty) or die(mysql_error());

                      if($colname_val == 0) {

                        $query_getWallet = "SELECT * FROM wallet WHERE wallet_institution = '".$colname_getUser2."' && wallet_user = '".$row_getPointsChng['points_user']."' LIMIT 1";
                        $getWallet = mysql_query($query_getWallet, $echoloyalty) or die(mysql_error());
                        $row_getWallet = mysql_fetch_assoc($getWallet);
                        $getWalletRows  = mysql_num_rows($getWallet);

                        if($getWalletRows > 0) {

                          $wallet_old = $row_getWallet['wallet_total'];
                          $wallet_new = $wallet_old + $row_getPointsChng['points_points'];

                          $updWallet = "UPDATE wallet SET wallet_total='".$wallet_new."', wallet_when='".$when."' WHERE wallet_institution = '".$colname_getUser2."' && wallet_user='".$row_getPointsChng['points_user']."'";
                          mysql_query($updWallet, $echoloyalty) or die(mysql_error());

                        }
                        else {

                          $wallet_old = $row_getWallet['wallet_total'];
                          $wallet_new = $wallet_old + $row_getPointsChng['points_points'];

                          $insrtWallet = "INSERT INTO wallet (wallet_user, wallet_institution, wallet_total, wallet_when) VALUES ('".$row_getPointsChng['points_user']."', '".$colname_getUser2."', '".$wallet_new."', '".$when."')";
                          mysql_query($insrtWallet, $echoloyalty) or die(mysql_error());

                        }

                      }

                      if($getPointsChngRows > 0 && $sendingok) {

                        $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_id = '".$row_getPointsChng['points_user']."'";
                        $getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
                        $row_getGCM = mysql_fetch_assoc($getGCM);
                        $getGCMRows  = mysql_num_rows($getGCM);

                        if($getGCMRows > 0) {

                          $apiKey =  urldecode($row_getInst['org_key']);

                          if($colname_val == 0) {

                            $title = urldecode("Баллы зачисленны!");
                            $messageios = "Баллы зачисленны! : " . $row_getPointsChng['points_points'];

                            $messageand = urldecode($row_getPointsChng['points_points']);

                            // iOS SETTINGS
                            
                            $badge = 1;
                            $sound = 'default';
                            $development = false;

                            $payload = array();
                            $payload['aps'] = array('alert' => html_entity_decode($messageios), 'badge' => intval($badge), 'sound' => $sound);
                            $payload = json_encode($payload);

                            $apns_url = NULL;
                            $apns_cert = NULL;
                            $apns_port = 2195;

                            // $rootLink = '/var/www/vhosts/xxx.com/httpdocs/src/MyApp/';
                            $rootLink = '';

                            if($development)
                            {
                              $apns_url = 'gateway.sandbox.push.apple.com';
                              $apns_cert = $rootLink . $row_getInst['org_cert'];
                            }
                            else
                            {
                              $apns_url = 'gateway.push.apple.com';
                              $apns_cert = $rootLink . $row_getInst['org_cert'];
                            }

                            $stream_context = stream_context_create();
                            stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert);

                            $apns = stream_socket_client('ssl://' . $apns_url . ':' . $apns_port, $error, $error_string, 2, STREAM_CLIENT_CONNECT, $stream_context);

                            // SENDING
                            if($row_getGCM['user_device_os'] == 'Android') {

                                // ANDROID PUSH
                                $registrationId = urldecode($row_getGCM['user_gcm']);

                                // ANDROID SETTINGS
                                $headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
                                $data = array(
                                    'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title)),
                                    'registration_ids' => array($registrationId)
                                );

                                $ch = curl_init();

                                curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
                                curl_setopt( $ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
                                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                                curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );

                                $response = curl_exec($ch);
                                curl_close($ch);

                            }
                            elseif($row_getGCM['user_device_os'] == 'iOS') {

                              $apn_token = $row_getGCM['user_gcm'];

                              $apns_message = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $apn_token)) . chr(0) . chr(strlen($payload)) . $payload;
                              fwrite($apns, $apns_message);

                              @socket_close($apns);
                              @fclose($apns);

                            }

                          }

                        }

                      }

                      $newarrmes = array("requests" => '1', "pointsId" => $colname_pointid, "pointsUpd" => $colname_val);
                      array_push($gotdata, $newarrmes);

                  }

                }

              }
              else {

                  $query_getPointsC = "SELECT * FROM points WHERE points_institution = '".$colname_getUser2."'";
                  $getPointsC = mysql_query($query_getPointsC, $echoloyalty) or die(mysql_error());
                  $row_getPointsC = mysql_fetch_assoc($getPointsC);
                  $getPointsCRows  = mysql_num_rows($getPointsC);
                  
                  $pointarr = array();
              
                  if($getPointsCRows > 0) {
                    
                    do {
                      
                      // GET ORGANIZATION
                      $query_getOrg = "SELECT * FROM organizations WHERE org_id = '".$row_getPointsC['points_institution']."'";
                      $getOrg = mysql_query($query_getOrg, $echoloyalty) or die(mysql_error());
                      $row_getOrg = mysql_fetch_assoc($getOrg);
                      $getOrgRows  = mysql_num_rows($getOrg);
                      // GET USER DATA
                      $query_getMem = "SELECT * FROM users WHERE user_id = '".$row_getPointsC['points_user']."'";
                      $getMem = mysql_query($query_getMem, $echoloyalty) or die(mysql_error());
                      $row_getMem = mysql_fetch_assoc($getMem);
                      $getMemRows  = mysql_num_rows($getMem);
					  
          					  $timediff = 0;
          					  $waitertime = 0;
          					  $usertime = 0;
        					  
        							if($row_getPointsC['points_waitertime'] > 0 && $row_getPointsC['points_usertime'] > 0) {
        								
          							$waitertime = $row_getPointsC['points_waitertime'];
          							$usertime = $row_getPointsC['points_usertime'];
        								
        							  if($row_getPointsC['points_usertime'] > $row_getPointsC['points_waitertime']) {
        								  $timediff = $row_getPointsC['points_usertime'] - $row_getPointsC['points_waitertime'];
        							  }
        							  else if ($row_getPointsC['points_usertime'] < $row_getPointsC['points_waitertime']) {
        								  $timediff = $row_getPointsC['points_waitertime'] - $row_getPointsC['points_usertime'];
        							  }

        							}
        							else {
        												  
            					  // GET TRANSACTION DATA
            					  $query_getTransC = "SELECT * FROM transactions WHERE trans_when = '".$row_getPointsC['points_time']."' && trans_institution = '".$row_getPointsC['points_institution']."' && trans_waiterid = '".$row_getPointsC['points_waiter']."' && trans_ubill = '".$row_getPointsC['points_bill']."'";
            					  $getTransC = mysql_query($query_getTransC, $echoloyalty) or die(mysql_error());
            					  $row_getTransC = mysql_fetch_assoc($getTransC);
            					  $getTransCRows  = mysql_num_rows($getTransC);
            					  
            					  if($getTransCRows > 0) {
        					  
        									$waitertime = $row_getTransC['trans_waiterdatetime'];
        									$usertime = $row_getTransC['trans_udatetime'];
        					
            						  if($row_getTransC['trans_udatetime'] > $row_getTransC['trans_waiterdatetime']) {
            							$timediff = $row_getTransC['trans_udatetime'] - $row_getTransC['trans_waiterdatetime'];
            						  }
            						  else if ($row_getTransC['trans_udatetime'] < $row_getTransC['trans_waiterdatetime']) {
            							  $timediff = $row_getTransC['trans_waiterdatetime'] - $row_getTransC['trans_udatetime'];
            						  }

            					  }

        							}
							
                      $giftName = 0;
                      $giftPic = 0;

                      if($row_getMem['user_surname'] != '0') {
                        $userIdent = $row_getMem['user_surname'];
                      }
                      else if($row_getMem['user_mob'] != '0') {
                        $userIdent = '+'.$row_getMem['user_mob'];
                      }
                      else if($row_getMem['user_id'] != '0') {
                        $userIdent = $row_getMem['user_id'];
                      }

                      if(isset($row_getPointsC['points_gift']) && $row_getPointsC['points_gift'] > '0') {

                        $query_getCheckGift = "SELECT * FROM gifts WHERE gifts_id = '".$row_getPointsC['points_gift']."' LIMIT 1";
                        $getCheckGift = mysql_query($query_getCheckGift, $echoloyalty) or die(mysql_error());
                        $row_getCheckGift = mysql_fetch_assoc($getCheckGift);
                        $getCheckGiftRows  = mysql_num_rows($getCheckGift);

                        if($getCheckGiftRows > 0) {

                          $giftName = $row_getCheckGift['gifts_name'];

                          $giftPic = $row_getCheckGift['gifts_pic'];

                        }

                      }

                      // GET OFFICE DATA
                      $query_getOffice = "SELECT * FROM organizations_office WHERE office_id = '".$row_getPointsC['points_office']."'";
                      $getOffice = mysql_query($query_getOffice, $echoloyalty) or die(mysql_error());
                      $row_getOffice = mysql_fetch_assoc($getOffice);
                      $getOfficeRows  = mysql_num_rows($getOffice);

                      $office = $row_getPointsC['points_office'];
                      if($getOfficeRows > 0) {
                        $office = $row_getOffice['office_name'];
                      }
                      
                      array_push($pointarr, array($row_getPointsC['points_id'], $userIdent, $row_getPointsC['points_bill'], $row_getPointsC['points_discount'], $row_getPointsC['points_points'], $row_getPointsC['points_got_spend'], $row_getPointsC['points_waiter'], $row_getOrg['org_name'], $row_getPointsC['points_status'], $row_getPointsC['points_proofed'], $row_getPointsC['points_when'], $row_getPointsC['points_user'], $row_getPointsC['points_comment'], $giftName, $giftPic, $office, $timediff, $waitertime, $usertime));
                      
                    } while ($row_getPointsC = mysql_fetch_assoc($getPointsC));
                  
                  }
                  
                  $newarrmes = array("newsC" => $getPointsCRows, "instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "pointsAll" => $pointarr);
                  array_push($gotdata, $newarrmes);

              }
        
            }
            else if($colname_getUser4 == 'push') {

              // iOS SETTINGS
              $iosIDs = array();

              $when = time();
              $sendingok = true;

              if(isset($colname_getUser5) && $colname_getUser5 != '%') {
                  
                $colname_fullname = "-1";
                if (isset($themsg['fullname'])) {
                  $colname_fullname = $protect($themsg['fullname']);
                }
                $colname_message = "-1";
                if (isset($themsg['message'])) {
                  $colname_message = $protect($themsg['message']);
                }
                $colname_selrec = "-1";
                if (isset($themsg['selrec'])) {
                  $colname_selrec = $protect($themsg['selrec']);
                }
                
                if($colname_getUser5 == 'send') {

                    $rece = (int)$colname_selrec;

                    if($rece > 8) {

                        $insertPush = "INSERT INTO pushmessages (push_name, push_message, push_status, push_receiver, push_institution, push_when) VALUES ('".$colname_fullname."', '".$colname_message."', '1', '".$rece."', '".$colname_getUser2."', '".$when."')";
                        mysql_query($insertPush, $echoloyalty) or die(mysql_error());

                        $query_getPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_when='".$when."' ORDER BY push_id DESC LIMIT 1";
                        $getPushed = mysql_query($query_getPushed, $echoloyalty) or die(mysql_error());
                        $row_getPushed = mysql_fetch_assoc($getPushed);
                        $getPushedRows  = mysql_num_rows($getPushed);
                        
                        $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_id = '".$rece."'";
                        $getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
                        $row_getGCM = mysql_fetch_assoc($getGCM);
                        $getGCMRows  = mysql_num_rows($getGCM);

                        if($getGCMRows > 0 && $sendingok) {

                          $apiKey =  urldecode($row_getInst['org_key']);
                          
                          $title = urldecode($colname_fullname);

                          $messageand = urldecode($colname_message);

                          // SENDING
                          do {

                            if($row_getGCM['user_device_os'] == 'Android') {

                                // ANDROID PUSH
                                $registrationId = urldecode($row_getGCM['user_gcm']);

                                // ANDROID SETTINGS
                                $headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
                                $data = array(
                                    'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title), 'push_id' => $row_getPushed['push_id']),
                                    'registration_ids' => array($registrationId)
                                );

                                $ch = curl_init();

                                curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
                                curl_setopt( $ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
                                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                                curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );

                                $response = curl_exec($ch);
                                curl_close($ch);

                                $respsuc = get_object_vars(json_decode($response));

                                $recsuccess = $respsuc['success'];
                                if($respsuc['success'] == 0) {
                                  $recsuccess = 2;
                                }

                                // TO WHOM IS MESSAGE SENDING
                                $insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$row_getPushed['push_id']."', '".$row_getGCM['user_id']."', '".$recsuccess."', '0', '".$colname_getUser2."', '".$when."')";
                                mysql_query($insPush, $echoloyalty) or die(mysql_error());

                            }
                            elseif($row_getGCM['user_device_os'] == 'iOS') {

                              array_push($iosIDs, $row_getGCM['user_gcm']);

                            }

                          } while ($row_getGCM = mysql_fetch_assoc($getGCM));

                          $sendGCM(0, $iosIDs, $row_getInst['org_cert'], $colname_fullname, $colname_message, $row_getPushed['push_id'], $colname_getUser2);

                        }

                        $newarrmes = array("requests" => '1', "pushId" => $row_getPushed['push_id'], "pushUpd" => '1', "pushTime" => $row_getPushed['push_when']);
                        array_push($gotdata, $newarrmes);

                    }
                    else if ($rece == 4 || $rece == 5  || $rece == 6 || $rece == 7 || $rece == 8) {

                        $insertPush = "INSERT INTO pushmessages (push_name, push_message, push_status, push_receiver, push_institution, push_when) VALUES ('".$colname_fullname."', '".$colname_message."', '1', '".$rece."', '".$colname_getUser2."', '".$when."')";
                        mysql_query($insertPush, $echoloyalty) or die(mysql_error());

                        $query_getPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_when='".$when."' ORDER BY push_id DESC LIMIT 1";
                        $getPushed = mysql_query($query_getPushed, $echoloyalty) or die(mysql_error());
                        $row_getPushed = mysql_fetch_assoc($getPushed);
                        $getPushedRows  = mysql_num_rows($getPushed);

                        $query_getGCM = '';
                        
                        if($rece == 4) {
                          $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_device_os = 'iOS'";
                        }
                        else if ($rece == 5) {
                          $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_device_os = 'Android'";
                        }
                        else if ($rece == 6) {
                          $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_gender = '1'";
                        }
                        else if ($rece == 7) {
                          $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_gender = '2'";
                        }
                        else if ($rece == 8) {
                          $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_gender != '1' && user_gender != '2'";
                        }

                        
                        $getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
                        $row_getGCM = mysql_fetch_assoc($getGCM);
                        $getGCMRows  = mysql_num_rows($getGCM);

                        if($getGCMRows > 0 && $sendingok) {

                          $apiKey =  urldecode($row_getInst['org_key']);
                          
                          $title = urldecode($colname_fullname);

                          $messageand = urldecode($colname_message);

                          // SENDING
                          do {

                            if($row_getGCM['user_device_os'] == 'Android') {

                                // ANDROID PUSH
                                $registrationId = urldecode($row_getGCM['user_gcm']);

                                // ANDROID SETTINGS
                                $headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
                                $data = array(
                                    'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title), 'push_id' => $row_getPushed['push_id']),
                                    'registration_ids' => array($registrationId)
                                );

                                $ch = curl_init();

                                curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
                                curl_setopt( $ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
                                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                                curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );

                                $response = curl_exec($ch);
                                curl_close($ch);

                                $respsuc = get_object_vars(json_decode($response));

                                $recsuccess = $respsuc['success'];
                                if($respsuc['success'] == 0) {
                                  $recsuccess = 2;
                                }

                                // TO WHOM IS MESSAGE SENDING
                                $insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$row_getPushed['push_id']."', '".$row_getGCM['user_id']."', '".$recsuccess."', '0', '".$colname_getUser2."', '".$when."')";
                                mysql_query($insPush, $echoloyalty) or die(mysql_error());

                            }
                            elseif($row_getGCM['user_device_os'] == 'iOS') {

                              array_push($iosIDs, $row_getGCM['user_gcm']);

                            }

                          } while ($row_getGCM = mysql_fetch_assoc($getGCM));

                          $sendGCM(0, $iosIDs, $row_getInst['org_cert'], $colname_fullname, $colname_message, $row_getPushed['push_id'], $colname_getUser2);

                        }

                        $newarrmes = array("requests" => '1', "pushId" => $row_getPushed['push_id'], "pushUpd" => '1', "pushTime" => $row_getPushed['push_when']);
                        array_push($gotdata, $newarrmes);

                    }
                    else if ($rece == 2) {

                        $then = $when - 60*60*24*14; // 14 days ago

                        $insertPush = "INSERT INTO pushmessages (push_name, push_message, push_status, push_receiver, push_institution, push_when) VALUES ('".$colname_fullname."', '".$colname_message."', '1', '".$rece."', '".$colname_getUser2."', '".$when."')";
                        mysql_query($insertPush, $echoloyalty) or die(mysql_error());

                        $query_getPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_when='".$when."' ORDER BY push_id DESC LIMIT 1";
                        $getPushed = mysql_query($query_getPushed, $echoloyalty) or die(mysql_error());
                        $row_getPushed = mysql_fetch_assoc($getPushed);
                        $getPushedRows  = mysql_num_rows($getPushed);
                        
                        $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_log < '".$then."'";
                        $getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
                        $row_getGCM = mysql_fetch_assoc($getGCM);
                        $getGCMRows  = mysql_num_rows($getGCM);

                        if($getGCMRows > 0 && $sendingok) {

                          $apiKey =  urldecode($row_getInst['org_key']);
                          
                          $title = urldecode($colname_fullname);

                          $messageand = urldecode($colname_message);

                          // SENDING
                          do {

                            if($row_getGCM['user_device_os'] == 'Android') {

                                // ANDROID PUSH
                                $registrationId = urldecode($row_getGCM['user_gcm']);

                                // ANDROID SETTINGS
                                $headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
                                $data = array(
                                    'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title), 'push_id' => $row_getPushed['push_id']),
                                    'registration_ids' => array($registrationId)
                                );

                                $ch = curl_init();

                                curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
                                curl_setopt( $ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
                                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                                curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );

                                $response = curl_exec($ch);
                                curl_close($ch);

                                $respsuc = get_object_vars(json_decode($response));

                                $recsuccess = $respsuc['success'];
                                if($respsuc['success'] == 0) {
                                  $recsuccess = 2;
                                }

                                // TO WHOM IS MESSAGE SENDING
                                $insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$row_getPushed['push_id']."', '".$row_getGCM['user_id']."', '".$recsuccess."', '0', '".$colname_getUser2."', '".$when."')";
                                mysql_query($insPush, $echoloyalty) or die(mysql_error());

                            }
                            elseif($row_getGCM['user_device_os'] == 'iOS') {

                              array_push($iosIDs, $row_getGCM['user_gcm']);

                            }

                          } while ($row_getGCM = mysql_fetch_assoc($getGCM));

                          $sendGCM(0, $iosIDs, $row_getInst['org_cert'], $colname_fullname, $colname_message, $row_getPushed['push_id'], $colname_getUser2);

                        }

                        $newarrmes = array("requests" => '1', "pushId" => $row_getPushed['push_id'], "pushUpd" => '1', "pushTime" => $row_getPushed['push_when']);
                        array_push($gotdata, $newarrmes);

                    }
                    else if ($rece == 1) {

                        $insertPush = "INSERT INTO pushmessages (push_name, push_message, push_status, push_receiver, push_institution, push_when) VALUES ('".$colname_fullname."', '".$colname_message."', '1', '".$rece."', '".$colname_getUser2."', '".$when."')";
                        mysql_query($insertPush, $echoloyalty) or die(mysql_error());

                        $query_getPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_when='".$when."' ORDER BY push_id DESC LIMIT 1";
                        $getPushed = mysql_query($query_getPushed, $echoloyalty) or die(mysql_error());
                        $row_getPushed = mysql_fetch_assoc($getPushed);
                        $getPushedRows  = mysql_num_rows($getPushed);
                        
                        $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0'";
                        $getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
                        $row_getGCM = mysql_fetch_assoc($getGCM);
                        $getGCMRows  = mysql_num_rows($getGCM);

                        if($getGCMRows > 0 && $sendingok) {

                          $apiKey =  urldecode($row_getInst['org_key']);
                          
                          $title = urldecode($colname_fullname);

                          $messageand = urldecode($colname_message);

                          // SENDING
                          do {

                            if($row_getGCM['user_device_os'] == 'Android') {

                                // ANDROID PUSH
                                $registrationId = urldecode($row_getGCM['user_gcm']);

                                // ANDROID SETTINGS
                                $headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
                                $data = array(
                                    'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title), 'push_id' => $row_getPushed['push_id']),
                                    'registration_ids' => array($registrationId)
                                );

                                $ch = curl_init();

                                curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
                                curl_setopt( $ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
                                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                                curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );

                                $response = curl_exec($ch);
                                curl_close($ch);

                                $respsuc = get_object_vars(json_decode($response));

                                $recsuccess = $respsuc['success'];
                                if($respsuc['success'] == 0) {
                                  $recsuccess = 2;
                                }

                                // TO WHOM IS MESSAGE SENDING
                                $insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$row_getPushed['push_id']."', '".$row_getGCM['user_id']."', '".$recsuccess."', '0', '".$colname_getUser2."', '".$when."')";
                                mysql_query($insPush, $echoloyalty) or die(mysql_error());

                            }
                            elseif($row_getGCM['user_device_os'] == 'iOS') {

                              array_push($iosIDs, $row_getGCM['user_gcm']);

                            }

                          } while ($row_getGCM = mysql_fetch_assoc($getGCM));

                          $sendGCM(0, $iosIDs, $row_getInst['org_cert'], $colname_fullname, $colname_message, $row_getPushed['push_id'], $colname_getUser2);

                        }

                        $newarrmes = array("requests" => '1', "pushId" => $row_getPushed['push_id'], "pushUpd" => '1', "pushTime" => $row_getPushed['push_when']);
                        array_push($gotdata, $newarrmes);

                    }

                }
                else {

                    $query_getPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_id='".$colname_getUser5."'";
                    $getPushed = mysql_query($query_getPushed, $echoloyalty) or die(mysql_error());
                    $row_getPushed = mysql_fetch_assoc($getPushed);
                    $getPushedRows  = mysql_num_rows($getPushed);

                    $insertPush = "INSERT INTO pushmessages (push_name, push_message, push_status, push_institution, push_when) VALUES ('".$row_getPushed['push_name']."', '".$row_getPushed['push_message']."', '1', '".$colname_getUser2."', '".$when."')";
                    mysql_query($insertPush, $echoloyalty) or die(mysql_error());
        
                    $query_getLastPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' ORDER BY push_id DESC LIMIT 1";
                    $getLastPushed = mysql_query($query_getLastPushed, $echoloyalty) or die(mysql_error());
                    $row_getLastPushed = mysql_fetch_assoc($getLastPushed);
                    $getLastPushedRows  = mysql_num_rows($getLastPushed);
        
                    $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0'";
                    $getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
                    $row_getGCM = mysql_fetch_assoc($getGCM);
                    $getGCMRows  = mysql_num_rows($getGCM);

                    if($getGCMRows > 0 && $sendingok) {

                      $apiKey =  urldecode($row_getInst['org_key']);
                      
                      $title = urldecode($row_getPushed['push_name']);

                      $messageand = urldecode($row_getPushed['push_message']);

                      // SENDING
                      do {

                        if($row_getGCM['user_device'] == 'Android') {

                          // ANDROID PUSH
                          $registrationId = urldecode($row_getGCM['user_gcm']);

                          // ANDROID SETTINGS
                          $headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
                          $data = array(
                              'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title), 'push_id' => $row_getPushed['push_id']),
                              'registration_ids' => array($registrationId)
                          );

                          $ch = curl_init();

                          curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
                          curl_setopt( $ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
                          curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                          curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                          curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                          curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );

                          $response = curl_exec($ch);
                          curl_close($ch);

                          $respsuc = get_object_vars(json_decode($response));

                          $recsuccess = $respsuc['success'];
                          if($respsuc['success'] == 0) {
                            $recsuccess = 2;
                          }

                          // TO WHOM IS MESSAGE SENDING
                          $insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$row_getLastPushed['push_id']."', '".$row_getGCM['user_id']."', '".$recsuccess."', '0', '".$colname_getUser2."', '".$when."')";
                          mysql_query($insPush, $echoloyalty) or die(mysql_error());

                        }
                        elseif($row_getGCM['user_device'] == 'iOS') {

                          array_push($iosIDs, $row_getGCM['user_gcm']);

                        }

                      } while ($row_getGCM = mysql_fetch_assoc($getGCM));

                      $sendGCM(0, $iosIDs, $row_getInst['org_cert'], $row_getPushed['push_name'], $row_getPushed['push_message'], $row_getLastPushed['push_id'], $colname_getUser2);

                    }

                    $newarrmes = array("requests" => '1', "pushId" => $row_getLastPushed['push_id'], "pushUpd" => '2', "pushTime" => $when, "pushName" => $row_getPushed['push_name'], "pushMessage" => $row_getPushed['push_message']);
                    array_push($gotdata, $newarrmes);

                }

              }
              else {

                $query_getPushC = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."'";
                $getPushC = mysql_query($query_getPushC, $echoloyalty) or die(mysql_error());
                $row_getPushC = mysql_fetch_assoc($getPushC);
                $getPushCRows  = mysql_num_rows($getPushC);
                
                $pusharr = array();
                if($getPushCRows > 0) {
                  
                  do {
                    
                    array_push($pusharr, array($row_getPushC['push_id'], $row_getPushC['push_name'], $row_getPushC['push_message'], $row_getPushC['push_status'], $row_getPushC['push_institution'], $row_getPushC['push_when']));
                    
                  } while ($row_getPushC = mysql_fetch_assoc($getPushC));
                  
                }
        
                $newarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "pushAll" => $pusharr);
                array_push($gotdata, $newarrmes);

              }
      
            }
            else if($colname_getUser4 == 'profile') {

              if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                $colname_profid= "-1";
                if (isset($themsg['profid'])) {
                  $colname_profid = $protect($themsg['profid']);
                }
                $colname_usrname2 = "-1";
                if (isset($themsg['usrname2'])) {
                  $colname_usrname2 = $protect($themsg['usrname2']);
                }
                $colname_usrsurname = "-1";
                if (isset($themsg['usrsurname'])) {
                  $colname_usrsurname = $protect($themsg['usrsurname']);
                }
                $colname_usremail = "-1";
                if (isset($themsg['usremail'])) {
                  $colname_usremail = $protect($themsg['usremail']);
                }
                $colname_usrmob = "-1";
                if (isset($themsg['usrmob'])) {
                  $colname_usrmob = $protect($themsg['usrmob']);
                }
                $colname_usrtel = "-1";
                if (isset($themsg['usrtel'])) {
                  $colname_usrtel = $protect($themsg['usrtel']);
                }
                $colname_usradress = "-1";
                if (isset($themsg['usradress'])) {
                  $colname_usradress = $protect($themsg['usradress']);
                }
                $colname_usrc = "-1";
                if (isset($themsg['usrc'])) {
                  $colname_usrc = $protect($themsg['usrc']);
                }
                $colname_usrr = "-1";
                if (isset($themsg['usrr'])) {
                  $colname_usrr = $protect($themsg['usrr']);
                }
                $colname_usrs = "-1";
                if (isset($themsg['usrs'])) {
                  $colname_usrs = $protect($themsg['usrs']);
                }
                $colname_usrdiscount = "-1";
                if (isset($themsg['usrdiscount'])) {
                  $colname_usrdiscount = $protect($themsg['usrdiscount']);
                }
                $colname_pswd = "-1";
                if (isset($themsg['pswd'])) {
                  $colname_pswd = $protect($themsg['pswd']);
                }
                $colname_usrdis = "-1";
                if (isset($themsg['usrdis'])) {
                  $colname_usrdis = $protect($themsg['usrdis']);
                }
                $colname_usrworkpos = "-1";
                if (isset($themsg['usrworkpos'])) {
                  $colname_usrworkpos = $protect($themsg['usrworkpos']);
                }
                $colname_usrmenueexe = "-1";
                if (isset($themsg['usrmenueexe'])) {
                  $colname_usrmenueexe = $protect($themsg['usrmenueexe']);
                }
				        $colname_usrwallet = "-1";
                if (isset($themsg['usrwallet'])) {
                  $colname_usrwallet = $protect($themsg['usrwallet']);
                }

                if($colname_getUser5 == 'send') {

                  $profUpd = 0;
				          $pointsComment = 6;

                  // GET MEMBER
                  $query_getMember = "SELECT * FROM users WHERE user_id = '".$colname_profid."' LIMIT 1";
                  $getMember = mysql_query($query_getMember, $echoloyalty) or die(mysql_error());
                  $row_getMember = mysql_fetch_assoc($getMember);
                  $getMemberRows  = mysql_num_rows($getMember);
				  
				          // GET WALLET
                  $query_getMemsWallet = "SELECT * FROM wallet WHERE wallet_user = '".$colname_profid."' LIMIT 1";
                  $getMemsWallet = mysql_query($query_getMemsWallet, $echoloyalty) or die(mysql_error());
                  $row_getMemsWallet = mysql_fetch_assoc($getMemsWallet);
                  $getMemsWalletRows  = mysql_num_rows($getMemsWallet);

                  if($getMemberRows > 0 && $colname_getUser == $row_getMember['user_id']) {

                    $colname_getUsrDiscount = $row_getMember['user_discount'];
                    if (isset($colname_usrdiscount) && $colname_usrdiscount != '0') {
                      $colname_getUsrDiscount = $colname_usrdiscount;
                    }
                    $colname_getUsrMob = $row_getMember['user_mob'];
                    if (isset($colname_usrmob) && $colname_usrmob != '0') {
                      $colname_getUsrMob = $colname_usrmob;
                    }
                    $colname_getUsrTel = $row_getMember['user_tel'];
                    if (isset($colname_usrtel) && $colname_usrtel != '0') {
                      $colname_getUsrTel = $colname_usrtel;
                    }
                    $colname_getUsrPwd = $row_getMember['user_pwd'];
                    if (isset($colname_pswd) && $colname_pswd != '0' && $colname_pswd != '') {

                      $salt = "dockbox";
                      $pwdmd5 = md5($colname_pswd) . $salt;
                      $pwdhash = sha1($pwdmd5);
                      $colname_getUsrPwd = $pwdhash;

                    }
                    $colname_getUsrAdress = $row_getMember['user_adress'];
                    if (isset($colname_usradress) && $colname_usradress != '0') {
                      $colname_getUsrAdress = $colname_usradress;
                    }
                    $colname_getUsrCity = $row_getMember['user_city'];
                    if (isset($colname_usrs) && $colname_usrs != '0') {
                      $colname_getUsrCity = $colname_usrs;
                    }
                    $colname_getUsrRegion = $row_getMember['user_region'];
                    if (isset($colname_usrr) && $colname_usrr != '0') {
                      $colname_getUsrRegion = $colname_usrr;
                    }
                    $colname_getUsrCountry = $row_getMember['user_country'];
                    if (isset($colname_usrc) && $colname_usrc != '0') {
                      $colname_getUsrCountry = $colname_usrc;
                    }
                    $colname_getUsrEmail = $row_getMember['user_email'];
                    if (isset($colname_usremail) && $colname_usremail != '0') {
                      $colname_getUsrEmail = $colname_usremail;
                    }
                    $colname_getUsrSur = $row_getMember['user_surname'];
                    if (isset($colname_usrsurname) && $colname_usrsurname != '0') {
                      $colname_getUsrSur = $colname_usrsurname;
                    }
                    $colname_getUsrName = $row_getMember['user_name'];
                    if (isset($colname_usrname2) && $colname_usrname2 != '0') {
                      $colname_getUsrName = $colname_usrname2;
                    }
                    $colname_getUsrEx = $row_getMember['user_menue_exe'];
                    if (isset($colname_usrmenueexe) && $colname_usrmenueexe != '0') {
                      $colname_getUsrEx = $colname_usrmenueexe;
                    }
					
                    if (isset($colname_usrwallet) && $colname_usrwallet != $row_getMemsWallet['wallet_total']) {
                      if($colname_usrwallet > $row_getMemsWallet['wallet_total']) {
          							$pointsadd = $colname_usrwallet - $row_getMemsWallet['wallet_total'];
          							// UPDATE WALLET
          							$updWallet = "UPDATE wallet SET wallet_total='".$colname_usrwallet."', wallet_when='".$when."' WHERE wallet_user='".$row_getMember['user_id']."'";
          							mysql_query($updWallet, $echoloyalty) or die(mysql_error());
          							// INSERT INTO POINTS
          							$insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_gift, points_when, points_time) VALUES ('".$row_getMember['user_id']."', '0', '0', '".$pointsadd."', '0', '".$colname_getUser."', '".$colname_getUser2."', '0', '0', '".$pointsComment."', '1', '0', '".$when."', '".$when."')";
                                      mysql_query($insPoints, $echoloyalty) or die(mysql_error());
          					  }
          					  else if($colname_usrwallet < $row_getMemsWallet['wallet_total']) {
          							$pointssub = $row_getMemsWallet['wallet_total'] - $colname_usrwallet;
          							// UPDATE WALLET
          							$updWallet = "UPDATE wallet SET wallet_total='".$colname_usrwallet."', wallet_when='".$when."' WHERE wallet_user='".$row_getMember['user_id']."'";
          							mysql_query($updWallet, $echoloyalty) or die(mysql_error());
          							// INSERT INTO POINTS
          							$insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_gift, points_when, points_time) VALUES ('".$row_getMember['user_id']."', '0', '0', '".$pointssub."', '1', '".$colname_getUser."', '".$colname_getUser2."', '0', '0', '".$pointsComment."', '1', '0', '".$when."', '".$when."')";
                                      mysql_query($insPoints, $echoloyalty) or die(mysql_error());
          					  }
                    }

                    $updMember = "UPDATE users SET user_name='".$colname_getUsrName."', user_surname='".$colname_getUsrSur."', user_email='".$colname_getUsrEmail."', user_menue_exe='".$colname_getUsrEx."', user_pwd='".$colname_getUsrPwd."', user_mob='".$colname_getUsrMob."', user_tel='".$colname_getUsrTel."', user_country='".$colname_getUsrCountry."', user_region='".$colname_getUsrRegion."', user_city='".$colname_getUsrCity."', user_adress='".$colname_getUsrAdress."', user_discount='".$colname_getUsrDiscount."', user_upd='".$when."' WHERE user_id='".$row_getMember['user_id']."'";
                    mysql_query($updMember, $echoloyalty) or die(mysql_error());

                    $profUpd = '1';

                  }
                  else if($getMemberRows > 0 && $colname_getUser != $row_getMember['user_id']) {

                    $colname_getUsrSur = $row_getMember['user_surname'];
                    if (isset($colname_usrsurname) && $colname_usrsurname != '0') {
                      $colname_getUsrSur = $colname_usrsurname;
                    }
                    $colname_getUsrName = $row_getMember['user_name'];
                    if (isset($colname_usrname2) && $colname_usrname2 != '0') {
                      $colname_getUsrName = $colname_usrname2;
                    }
                    $colname_getUsrDiscount = $row_getMember['user_discount'];
                    if (isset( $colname_usrdis) &&  $colname_usrdis != '0') {
                      $colname_getUsrDiscount =  $colname_usrdis;
                    }
                    $colname_getUsrWorkPos = $row_getMember['user_work_pos'];
                    if (isset($colname_usrworkpos) && $colname_usrworkpos != '0') {
                      $colname_getUsrWorkPos = $colname_usrworkpos;
                    }
                    $colname_getUsrMob = $row_getMember['user_mob'];
                    if (isset($colname_usrmob) && $colname_usrmob != '0') {
                      $colname_getUsrMob = $colname_usrmob;
                    }
                    $colname_getUsrTel = $row_getMember['user_tel'];
                    if (isset($colname_usrtel) && $colname_usrtel != '0') {
                      $colname_getUsrTel = $colname_usrtel;
                    }
                    $colname_getUsrPwd = $row_getMember['user_pwd'];
                    if (isset($colname_pswd) && $colname_pswd != '0') {
                      $colname_getUsrPwd = $colname_pswd;
                    }
                    $colname_getUsrEx = $row_getMember['user_menue_exe'];
                    if (isset($colname_usrmenueexe)) {
                      $colname_getUsrEx = $colname_usrmenueexe;
                    }
					
          					if (isset($colname_usrwallet) && $colname_usrwallet != $row_getMemsWallet['wallet_total']) {
          					  if($colname_usrwallet > $row_getMemsWallet['wallet_total']) {
          							$pointsadd = $colname_usrwallet - $row_getMemsWallet['wallet_total'];
          							// UPDATE WALLET
          							$updWallet = "UPDATE wallet SET wallet_total='".$colname_usrwallet."', wallet_when='".$when."' WHERE wallet_user='".$row_getMember['user_id']."'";
          							mysql_query($updWallet, $echoloyalty) or die(mysql_error());
          							// INSERT INTO POINTS
          							$insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_gift, points_when, points_time) VALUES ('".$row_getMember['user_id']."', '0', '0', '".$pointsadd."', '0', '".$colname_getUser."', '".$colname_getUser2."', '0', '0', '".$pointsComment."', '1', '0', '".$when."', '".$when."')";
          							mysql_query($insPoints, $echoloyalty) or die(mysql_error());
          					  }
          					  else if($colname_usrwallet < $row_getMemsWallet['wallet_total']) {
          							$pointssub = $row_getMemsWallet['wallet_total'] - $colname_usrwallet;
          							// UPDATE WALLET
          							$updWallet = "UPDATE wallet SET wallet_total='".$colname_usrwallet."', wallet_when='".$when."' WHERE wallet_user='".$row_getMember['user_id']."'";
          							mysql_query($updWallet, $echoloyalty) or die(mysql_error());
          							// INSERT INTO POINTS
          							$insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_office, points_status, points_comment, points_proofed, points_gift, points_when, points_time) VALUES ('".$row_getMember['user_id']."', '0', '0', '".$pointssub."', '1', '".$colname_getUser."', '".$colname_getUser2."', '0', '0', '".$pointsComment."', '1', '0', '".$when."', '".$when."')";
          							mysql_query($insPoints, $echoloyalty) or die(mysql_error());
          					  }
          					}

                    $updMember = "UPDATE users SET user_name='".$colname_getUsrName."', user_surname='".$colname_getUsrSur."', user_menue_exe='".$colname_getUsrEx."', user_pwd='".$colname_getUsrPwd."', user_tel='".$colname_getUsrTel."', user_mob='".$colname_getUsrMob."', user_work_pos='".$colname_getUsrWorkPos."', user_discount='".$colname_getUsrDiscount."', user_upd='".$when."' WHERE user_id='".$row_getMember['user_id']."'";
                    mysql_query($updMember, $echoloyalty) or die(mysql_error());

                    $profUpd = '1';

                  }

                  $newarrmes = array("requests" => '1', "profId" => $colname_profid, "profUpd" => $profUpd);
                  array_push($gotdata, $newarrmes);

                }
                else {

                    $usrArr = array();
                    // GET MEMBER
                    $query_getMember = "SELECT * FROM users WHERE user_id = '".$colname_getUser5."'";
                    $getMember = mysql_query($query_getMember, $echoloyalty) or die(mysql_error());
                    $row_getMember = mysql_fetch_assoc($getMember);
                    $getMemberRows  = mysql_num_rows($getMember);

                    // GET PROFESSIONS
                    $professionArr = array();
                    $query_getProf = "SELECT * FROM professions WHERE prof_when > '2' && (prof_institution = '".$colname_getUser2."' OR prof_institution = '0') ORDER BY prof_id ASC";
                    $getProf = mysql_query($query_getProf, $echoloyalty) or die(mysql_error());
                    $row_getProf = mysql_fetch_assoc($getProf);
                    $getProfRows  = mysql_num_rows($getProf);

                    if($getProfRows > 0) {

                        do {

                            array_push($professionArr, array($row_getProf['prof_id'], $row_getProf['prof_name'], $row_getProf['prof_desc'], $row_getProf['prof_institution'], $row_getProf['prof_when']));

                        } while ($row_getProf = mysql_fetch_assoc($getProf));

                    }

                    // GET COUNTRY
                    $query_getCountry = "SELECT * FROM country WHERE id_country = '".$row_getMember['user_country']."'";
                    $getCountry = mysql_query($query_getCountry, $echoloyalty) or die(mysql_error());
                    $row_getCountry = mysql_fetch_assoc($getCountry);
                    $getCountryRows  = mysql_num_rows($getCountry);

                    // GET REGION
                    $query_getRegion = "SELECT * FROM region WHERE id_region = '".$row_getMember['user_region']."'";
                    $getRegion = mysql_query($query_getRegion, $echoloyalty) or die(mysql_error());
                    $row_getRegion = mysql_fetch_assoc($getRegion);
                    $getRegionRows  = mysql_num_rows($getRegion);

                    // GET CITY
                    $query_getCity = "SELECT * FROM city WHERE id_city = '".$row_getMember['user_city']."'";
                    $getCity = mysql_query($query_getCity, $echoloyalty) or die(mysql_error());
                    $row_getCity = mysql_fetch_assoc($getCity);
                    $getCityRows  = mysql_num_rows($getCity);

                    // GET CLICKS
                    $clicksArr = array();
                    $query_getClicks = "SELECT *, COUNT(*) AS clickCount FROM clicks WHERE clicks_user = '".$colname_getUser5."' && clicks_institution = '".$colname_getUser2."' GROUP BY clicks_what DESC";
                    $getClicks = mysql_query($query_getClicks, $echoloyalty) or die(mysql_error());
                    $row_getClicks = mysql_fetch_assoc($getClicks);
                    $getClicksRows  = mysql_num_rows($getClicks);

                    if($getClicksRows > 0) {

                        do {

                            // GET CLICKSNAME
                            $query_getClicksN = "SELECT * FROM clicks_name WHERE clicks_name_id = '".$row_getClicks['clicks_what']."'";
                            $getClicksN = mysql_query($query_getClicksN, $echoloyalty) or die(mysql_error());
                            $row_getClicksN = mysql_fetch_assoc($getClicksN);
                            $getClicksNRows  = mysql_num_rows($getClicksN);

                            array_push($clicksArr, array($row_getClicks['clickCount'], $row_getClicksN['clicks_name_n'], $row_getClicks['clicks_when']));

                        } while ($row_getClicks = mysql_fetch_assoc($getClicks));

                    }

                    // GET POINTS
                    $pointsArr = array();
                    $query_getPoints = "SELECT * FROM points WHERE points_user = '".$row_getMember['user_id']."' && points_institution = '".$colname_getUser2."'";
                    $getPoints = mysql_query($query_getPoints, $echoloyalty) or die(mysql_error());
                    $row_getPoints = mysql_fetch_assoc($getPoints);
                    $getPointsRows  = mysql_num_rows($getPoints);

                    if($getPointsRows > 0) {

                        $query_getOrg = "SELECT * FROM organizations WHERE org_id = '".$row_getPoints['points_institution']."'";
                        $getOrg = mysql_query($query_getOrg, $echoloyalty) or die(mysql_error());
                        $row_getOrg = mysql_fetch_assoc($getOrg);
                        $getOrgRows  = mysql_num_rows($getOrg);

                        do{

                            array_push($pointsArr, array($row_getPoints['points_bill'], $row_getPoints['points_discount'], $row_getPoints['points_points'], $row_getPoints['points_got_spend'], $row_getPoints['points_waiter'], $row_getOrg['org_name'], $row_getPoints['points_status'], $row_getPoints['points_proofed'], $row_getPoints['points_when'], $row_getPoints['points_id']));

                        } while ($row_getPoints = mysql_fetch_assoc($getPoints));

                    }

                    // ASSOCIATED TO WORKER MENUE
                    $query_getMenueC = "SELECT * FROM menue WHERE menue_institution = '".$colname_getUser2."' && menue_when > '1'";
                    $getMenueC = mysql_query($query_getMenueC, $echoloyalty) or die(mysql_error());
                    $row_getMenueC = mysql_fetch_assoc($getMenueC);
                    $getMenueCRows  = mysql_num_rows($getMenueC);
                    
                    $menuearr = array();
                    if($getMenueCRows > 0) {

                      do {

                        $query_getCatChng = "SELECT * FROM categories WHERE cat_institution = '".$colname_getUser2."' && cat_id = '".$row_getMenueC['menue_cat']."' LIMIT 1";
                        $getCatChng = mysql_query($query_getCatChng, $echoloyalty) or die(mysql_error());
                        $row_getCatChng = mysql_fetch_assoc($getCatChng);
                        $getCatChngRows  = mysql_num_rows($getCatChng);
                        
                        array_push($menuearr, array($row_getMenueC['menue_id'], $row_getMenueC['menue_name'], $row_getMenueC['menue_desc'], $row_getMenueC['menue_pic'], $row_getMenueC['menue_institution'], $row_getMenueC['menue_when'], $row_getCatChng['cat_name'], $row_getMenueC['menue_size'], $row_getMenueC['menue_cost'], $row_getMenueC['menue_weight'], $row_getMenueC['menue_discount'], $row_getMenueC['menue_action'], $row_getMenueC['menue_code'], $row_getCatChng['cat_ingr'], $row_getMenueC['menue_cat']));
                          
                      } while ($row_getMenueC = mysql_fetch_assoc($getMenueC));

                    }

                    // GET WALLET
                    $walletArr = array();
                    $query_getWallet = "SELECT * FROM wallet WHERE wallet_user = '".$row_getMember['user_id']."' && wallet_institution = '".$colname_getUser2."'";
                    $getWallet = mysql_query($query_getWallet, $echoloyalty) or die(mysql_error());
                    $row_getWallet = mysql_fetch_assoc($getWallet);
                    $getWalletRows  = mysql_num_rows($getWallet);

                    if($getWalletRows > 0) {

                        array_push($walletArr, array($row_getWallet['wallet_institution'], $row_getWallet['wallet_total'], $row_getWallet['wallet_when']));

                    }

                    if($row_getMember['user_gender'] == '1') {
                        $memGender = 'Мужской';
                    }
                    else if($row_getMember['user_gender'] == '2') {
                        $memGender = 'Женский';
                    }
                    else {
                        $memGender = 'Не указано';
                    }

                    if($row_getMember['user_id'] == $row_getUser['user_id']) {$memPWD = '';} else {$memPWD = $row_getMember['user_pwd'];}

                    array_push($usrArr, array("mem_id" => $row_getMember['user_id'], "mem_name" => $row_getMember['user_name'], "mem_surname" => $row_getMember['user_surname'], "mem_middlename" => $row_getMember['user_middlename'], "mem_email" => $row_getMember['user_email'], "mem_tel" => $row_getMember['user_tel'], "mem_mob" => $row_getMember['user_mob'], "mem_work_pos" => $row_getMember['user_work_pos'], "mem_menue_exe" => $row_getMember['user_menue_exe'], "mem_institution" => $row_getMember['user_institution'], "mem_pic" => 'img/user/'.$colname_getUser2.'/pic/'.$row_getMember['user_pic'], "mem_gender" => $memGender, "mem_birthday" => $row_getMember['user_birthday'], "mem_country_id" => $row_getCountry['id_country'], "mem_country" => $row_getCountry['name'], "mem_region_id" => $row_getRegion['id_region'], "mem_region" => $row_getRegion['name'], "mem_city_id" => $row_getCity['id_city'], "mem_city" => $row_getCity['name'], "mem_adress" => $row_getMember['user_adress'], "mem_install_where" => $row_getMember['user_install_where'], "user_discount" => $row_getMember['user_discount'], "mem_log" => $row_getMember['user_log'], "mem_reg" => $row_getMember['user_reg'], "mem_clicks" => $clicksArr, "mem_points" => $pointsArr, "mem_wallet" => $walletArr, "mem_pwd" => $memPWD, "professionArr" => $professionArr, "menueArr" => $menuearr));

                }

              }

              $asksarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "usrArr" => $usrArr);
              array_push($gotdata, $asksarrmes);

            }
            else if($colname_getUser4 == 'asks') {

              $when = time();
              $sendingok = true;

              if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                  if ($colname_getUser5 == 'send') {

                    $colname_Fullname = "-1";
                    if (isset($themsg['fullname'])) {
                      $colname_Fullname = $protect($themsg['fullname']);
                    }
                    $colname_Message = "-1";
                    if (isset($themsg['message'])) {
                      $colname_Message = $protect($themsg['message']);
                    }

                    $insertAsks = "INSERT INTO asks (asks_name, asks_message, asks_institution, asks_when) VALUES ('".$colname_Fullname."', '".$colname_Message."', '".$colname_getUser2."', '".$when."')";
                    mysql_query($insertAsks, $echoloyalty) or die(mysql_error());

                    $query_getAsks = "SELECT * FROM asks WHERE asks_institution = '".$colname_getUser2."' && asks_when='".$when."' ORDER BY asks_id DESC LIMIT 1";
                    $getAsks = mysql_query($query_getAsks, $echoloyalty) or die(mysql_error());
                    $row_getAsks = mysql_fetch_assoc($getAsks);
                    $getAsksRows  = mysql_num_rows($getAsks);

                    $newarrmes = array("requests" => '1', "asksId" => $row_getAsks['asks_id'], "asksUpd" => '1', "asksTime" => $row_getAsks['asks_when']);
                    array_push($gotdata, $newarrmes);

                  }
                  else if ($colname_getUser5 == 'del') {

                    $colname_asksid = "-1";
                    if (isset($themsg['asksid'])) {
                      $colname_asksid = $protect($themsg['asksid']);
                    }

                    $updateAsks = "UPDATE asks SET asks_when='1' WHERE asks_institution = '".$colname_getUser2."' && asks_id = '".$colname_asksid."'";
                    mysql_query($updateAsks, $echoloyalty) or die(mysql_error());

                    $newarrmes = array("requests" => '1', "asksId" => $colname_asksid, "asksUpd" => '3', "asksTime" => $when);
                    array_push($gotdata, $newarrmes);

                  }
                  else {

                    $query_getAsks = "SELECT * FROM asks WHERE asks_institution = '".$colname_getUser2."' && asks_id='".$colname_getUser5."'";
                    $getAsks = mysql_query($query_getAsks, $echoloyalty) or die(mysql_error());
                    $row_getAsks = mysql_fetch_assoc($getAsks);
                    $getAsksRows  = mysql_num_rows($getAsks);

                    $insertAsks = "INSERT INTO asks (asks_name, asks_message, asks_institution, asks_when) VALUES ('".$row_getAsks['asks_name']."', '".$row_getAsks['asks_message']."', '".$colname_getUser2."', '".$when."')";
                    mysql_query($insertAsks, $echoloyalty) or die(mysql_error());
                    
                    $query_getLastAsks = "SELECT * FROM asks WHERE asks_institution = '".$colname_getUser2."' ORDER BY asks_id DESC LIMIT 1";
                    $getLastAsks = mysql_query($query_getLastAsks, $echoloyalty) or die(mysql_error());
                    $row_getLastAsks = mysql_fetch_assoc($getLastAsks);
                    $getLastAsksRows  = mysql_num_rows($getLastAsks);

                    $newarrmes = array("requests" => '1', "asksId" => $row_getLastAsks['asks_id'], "asksUpd" => '2', "asksTime" => $when, "asksName" => $row_getAsks['asks_name'], "asksMessage" => $row_getAsks['asks_message']);
                    array_push($gotdata, $newarrmes);

                  }

              }
              else {
      
                $query_getAsksC = "SELECT * FROM asks WHERE asks_institution = '".$colname_getUser2."'";
                $getAsksC = mysql_query($query_getAsksC, $echoloyalty) or die(mysql_error());
                $row_getAsksC = mysql_fetch_assoc($getAsksC);
                $getAsksCRows  = mysql_num_rows($getAsksC);
        
                $asksarr = array();
                if($getAsksCRows > 0) {
                
                  do {

                    $asksYes = 0;
                    if(isset($row_getAsksC['asks_yes'])) {
                      $asksYes = $row_getAsksC['asks_yes'];
                    }
                    $asksNo = 0;
                    if(isset($row_getAsksC['asks_no'])) {
                      $asksNo = $row_getAsksC['asks_no'];
                    }
                    $todevide = $asksYes + $asksNo;
                    if($todevide == 0) {
                      $todevide = 1;
                    }
                    
                    $askPercent = 100 * $asksYes / $todevide;
                    $askPercentR = round($askPercent, 2);
                    
                    array_push($asksarr, array($row_getAsksC['asks_id'], $row_getAsksC['asks_name'], $row_getAsksC['asks_message'], $askPercentR, $row_getAsksC['asks_institution'], $row_getAsksC['asks_when']));
                    
                  } while ($row_getAsksC = mysql_fetch_assoc($getAsksC));
                  
                }
      
                $asksarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "asksAll" => $asksarr);
                array_push($gotdata, $asksarrmes);

              }
      
            }
            else if($colname_getUser4 == 'clients') {
        
              $query_getUsersC = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_work_pos = '0'";
              $getUsersC = mysql_query($query_getUsersC, $echoloyalty) or die(mysql_error());
              $row_getUsersC = mysql_fetch_assoc($getUsersC);
              $getUsersCRows  = mysql_num_rows($getUsersC);
      
              $usrsarr = array();
              if($getUsersCRows > 0) {
                
                do {
                  
                  // $query_getCityName = "SELECT * FROM city WHERE id_city = '".$row_getUsersC['user_city']."' && id_country = '".$row_getUsersC['user_country']."'";
                  // $getCityName = mysql_query($query_getCityName, $echoloyalty) or die(mysql_error());
                  // $row_getCityName = mysql_fetch_assoc($getCityName);
                  // $getCityNameRows  = mysql_num_rows($getCityName);
                  
                  $query_getLastBuy = "SELECT * FROM points WHERE points_institution = '".$colname_getUser2."' && points_user = '".$row_getUsersC['user_id']."' ORDER BY points_id DESC LIMIT 1";
                  $getLastBuy = mysql_query($query_getLastBuy, $echoloyalty) or die(mysql_error());
                  $row_getLastBuy = mysql_fetch_assoc($getLastBuy);
                  $getLastBuyRows  = mysql_num_rows($getLastBuy);
                  
                  $query_getAllBuy = "SELECT SUM(points_bill) AS SumBills FROM points WHERE points_institution = '".$colname_getUser2."' && points_user = '".$row_getUsersC['user_id']."'";
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
                  
                  array_push($usrsarr, array($row_getUsersC['user_id'], $row_getUsersC['user_name'], $row_getUsersC['user_surname'], $row_getUsersC['user_email'], $row_getUsersC['user_tel'], $row_getUsersC['user_mob'], $row_getUsersC['user_gender'], $row_getUsersC['user_birthday'], $row_getUsersC['user_adress'], $row_getUsersC['user_reg'], $lastBuy, $allBuy, $row_getUsersC['user_pic']));
                  
                } while ($row_getUsersC = mysql_fetch_assoc($getUsersC));
              
              }
      
              $asksarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "clientsAll" => $usrsarr);
              array_push($gotdata, $asksarrmes);
      
            }
      			else if($colname_getUser4 == 'personal') {
      				
      				if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                $colname_name = "-1";
                if (isset($themsg['name'])) {
                  $colname_name = $protect($themsg['name']);
                }
                $colname_surname = "-1";
                if (isset($themsg['surname'])) {
                  $colname_surname = $protect($themsg['surname']);
                }
                $colname_patronymics = "-1";
                if (isset($themsg['patronymics'])) {
                  $colname_patronymics = $protect($themsg['patronymics']);
                }
                $colname_email = "-1";
                if (isset($themsg['email'])) {
                  $colname_email = $protect($themsg['email']);
                }
                $colname_pwd = "-1";
                if (isset($themsg['pwd'])) {
                  $colname_pwd = $protect($themsg['pwd']);
                }
                $colname_phone2 = "-1";
                if (isset($themsg['phone2'])) {
                  $colname_phone2 = $protect($themsg['phone2']);
                }
                $colname_phone1 = "-1";
                if (isset($themsg['phone1'])) {
                  $colname_phone1 = $protect($themsg['phone1']);
                }
                $colname_working_pos = "-1";
                if (isset($themsg['working_pos'])) {
                  $colname_working_pos = $protect($themsg['working_pos']);
                }
                $colname_office = "-1";
                if (isset($themsg['office'])) {
                  $colname_office = $protect($themsg['office']);
                }
                $colname_gender = "-1";
                if (isset($themsg['gender'])) {
                  $colname_gender = $protect($themsg['gender']);
                }
                
                if($colname_getUser5 == 'send') {
			
    							$query_getUsersProof = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_mob = '".$colname_phone1."' OR user_institution = '".$colname_getUser2."' && user_mob = '".$colname_phone2."'";
    							$getUsersProof = mysql_query($query_getUsersProof, $echoloyalty) or die(mysql_error());
    							$row_getUsersProof = mysql_fetch_assoc($getUsersProof);
    							$getUsersProofRows  = mysql_num_rows($getUsersProof);
    							
    							if($getUsersProofRows == 0) {

    								$insrtUsr = "INSERT INTO users (user_name, user_surname, user_middlename, user_email, user_pwd, user_tel, user_mob, user_mob_confirm, user_work_pos, user_office, user_institution, user_gender, user_promo, user_upd, user_reg) VALUES ('".$colname_name."', '".$colname_surname."', '".$colname_patronymics."', '".$colname_email."', '".$colname_pwd."', '".$colname_phone2."', '".$colname_phone1."', '1', '".$colname_working_pos."', '".$colname_office."', '".$colname_getUser2."', '".$colname_gender."', '1', '".$when."', '".$when."')";
    								mysql_query($insrtUsr, $echoloyalty) or die(mysql_error());

    								$query_getNewPersonal = "SELECT * FROM users WHERE user_reg = '".$when."' && user_institution = '".$colname_getUser2."' LIMIT 1";
    								$getNewPersonal = mysql_query($query_getNewPersonal, $echoloyalty) or die(mysql_error());
    								$row_getNewPersonal = mysql_fetch_assoc($getNewPersonal);
    								$getNewPersonalRows  = mysql_num_rows($getNewPersonal);

    								$startwallet = 0;

    								$insrtWallet = "INSERT INTO wallet (wallet_user, wallet_institution, wallet_total, wallet_when) VALUES ('".$row_getNewPersonal['user_id']."', '".$colname_getUser2."', '".$startwallet."', '".$when."')";
    								mysql_query($insrtWallet, $echoloyalty) or die(mysql_error());

    								$newarrmes = array("requests" => '1', "usrId" => $row_getNewPersonal['user_id'], "usrReg" => $when);
    								array_push($gotdata, $newarrmes);
    								
    							}
    							else {
    								$newarrmes = array("requests" => '2');
    								array_push($gotdata, $newarrmes);
    							}

                }
				
              }
      				else {
      		      // WORKERS
      				  $query_getUsersC = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_work_pos >= '2'";
      				  $getUsersC = mysql_query($query_getUsersC, $echoloyalty) or die(mysql_error());
      				  $row_getUsersC = mysql_fetch_assoc($getUsersC);
      				  $getUsersCRows  = mysql_num_rows($getUsersC);
      		  
      				  $usrsarr = array();
      				  if($getUsersCRows > 0) {
      					
        					do {
        					  
        					  $query_getCityName = "SELECT * FROM city WHERE id_city = '".$row_getUsersC['user_city']."' && id_country = '".$row_getUsersC['user_country']."'";
        					  $getCityName = mysql_query($query_getCityName, $echoloyalty) or die(mysql_error());
        					  $row_getCityName = mysql_fetch_assoc($getCityName);
        					  $getCityNameRows  = mysql_num_rows($getCityName);
        					  
        					  $query_getLastBuy = "SELECT * FROM points WHERE points_institution = '".$colname_getUser2."' && points_user = '".$row_getUsersC['user_id']."' ORDER BY points_id DESC LIMIT 1";
        					  $getLastBuy = mysql_query($query_getLastBuy, $echoloyalty) or die(mysql_error());
        					  $row_getLastBuy = mysql_fetch_assoc($getLastBuy);
        					  $getLastBuyRows  = mysql_num_rows($getLastBuy);
        					  
        					  $query_getAllBuy = "SELECT SUM(points_bill) AS SumBills FROM points WHERE points_institution = '".$colname_getUser2."' && points_user = '".$row_getUsersC['user_id']."'";
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
        					  
        					  array_push($usrsarr, array($row_getUsersC['user_id'], $row_getUsersC['user_name'], $row_getUsersC['user_surname'], $row_getUsersC['user_email'], $row_getUsersC['user_tel'], $row_getUsersC['user_mob'], $row_getUsersC['user_gender'], $row_getUsersC['user_birthday'], $row_getUsersC['user_adress'], $row_getUsersC['user_reg'], $lastBuy, $allBuy, $row_getUsersC['user_pic'], $row_getUsersC['user_work_pos'], $row_getUsersC['user_office']));
        					  
        					} while ($row_getUsersC = mysql_fetch_assoc($getUsersC));
      				  
      				  }
                // PROFESSIONS
                $query_getProfs = "SELECT * FROM professions WHERE (prof_institution = '".$colname_getUser2."' OR prof_institution = '0') && prof_when > '2'";
                $getProfs = mysql_query($query_getProfs, $echoloyalty) or die(mysql_error());
                $row_getProfs = mysql_fetch_assoc($getProfs);
                $getProfsRows  = mysql_num_rows($getProfs);

                $profAll = array();
                if($getProfsRows > 0) {

                  do {

                    array_push($profAll, array($row_getProfs['prof_id'], $row_getProfs['prof_name'], $row_getProfs['prof_desc'], $row_getProfs['prof_institution'], $row_getProfs['prof_when']));

                  } while ($row_getProfs = mysql_fetch_assoc($getProfs));

                }
                // OFFICES
                $query_getOffice = "SELECT * FROM organizations_office WHERE office_institution = '".$colname_getUser2."' && office_reg > '2'";
                $getOffice = mysql_query($query_getOffice, $echoloyalty) or die(mysql_error());
                $row_getOffice = mysql_fetch_assoc($getOffice);
                $getOfficeRows = mysql_num_rows($getOffice);
            
                $officearr = array();
                if($getOfficeRows > 0) {
                
                  do {
                    
                    array_push($officearr, array($row_getOffice['office_id'], $row_getOffice['office_name'], $row_getOffice['office_start'], $row_getOffice['office_stop'], $row_getOffice['office_logo'], $row_getOffice['office_institution']));
                    
                  } while ($row_getOffice = mysql_fetch_assoc($getOffice));
                
                }

      				  $asksarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "clientsAll" => $usrsarr, "profAll" => $profAll, "officeAll" => $officearr);
      				  array_push($gotdata, $asksarrmes);
      				  
      				}
      		
      			}
      			else if($colname_getUser4 == 'reviews') {
              
                $query_getReview = "SELECT * FROM reviews WHERE reviews_institution = '".$colname_getUser2."'";
                $getReview = mysql_query($query_getReview, $echoloyalty) or die(mysql_error());
                $row_getReview = mysql_fetch_assoc($getReview);
                $getReviewRows  = mysql_num_rows($getReview);
        
                $reviewarr = array();
                if($getReviewRows > 0) {
                
                  do {
                    
                    $query_getUsersC = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_id = '".$row_getReview['reviews_from']."'";
                    $getUsersC = mysql_query($query_getUsersC, $echoloyalty) or die(mysql_error());
                    $row_getUsersC = mysql_fetch_assoc($getUsersC);
                    $getUsersCRows  = mysql_num_rows($getUsersC);
                              
                    array_push($reviewarr, array($row_getReview['reviews_id'], $row_getUsersC['user_surname'], $row_getReview['reviews_from'], $row_getReview['reviews_message'], $row_getReview['reviews_pic'], $row_getReview['reviews_answered'], $row_getReview['reviews_when'], $row_getReview['reviews_rate']));
                    
                  } while ($row_getReview = mysql_fetch_assoc($getReview));
                
                
                }
                $reviewarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "revsAll" => $reviewarr);
                array_push($gotdata, $reviewarrmes);
      
            }
            else if($colname_getUser4 == 'statistics') {

              if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                if($colname_getUser5 == 'bestwaiter') {

                  $colname_getTmFrom = time();
                  if (isset($themsg['tmfrom'])) {
                    $colname_getTmFrom = $protect($themsg['tmfrom']);
                  }
                  $colname_getTmTo = time();
                  if (isset($themsg['tmto'])) {
                    $colname_getTmTo = $protect($themsg['tmto']);
                  }

                  // PROMOCODE USE
                  $query_getWorkerPoints = "SELECT *, COUNT(*) AS PromoUsed FROM promo RIGHT JOIN users ON users.user_id = promo.promo_from AND users.user_work_pos >= '2' WHERE promo.promo_institution = '".$colname_getUser2."' && promo.promo_when BETWEEN '".$colname_getTmFrom."' AND '".$colname_getTmTo."' GROUP BY promo.promo_from ORDER BY PromoUsed DESC LIMIT 5";
                  $getWorkerPoints = mysql_query($query_getWorkerPoints, $echoloyalty) or die(mysql_error());
                  $row_getWorkerPoints = mysql_fetch_assoc($getWorkerPoints);
                  $getWorkerPointsRows  = mysql_num_rows($getWorkerPoints);

                  $workerPoints = array();

                  if($getWorkerPointsRows > 0) {

                    do {

                      $workerName = $row_getWorkerPoints['user_name'] . ' ' . $row_getWorkerPoints['user_surname'];
                      $workerPoints[$workerName] = $row_getWorkerPoints['PromoUsed'];

                    } while ($row_getWorkerPoints = mysql_fetch_assoc($getWorkerPoints));

                  }
                  else {
                      $workerPoints['никто'] = 1;
                  }

                  $newarrmes = array("requests" => '1', "workerPoints" => $workerPoints);
                  array_push($gotdata, $newarrmes);

                }

              }
              else {

                // GENDER MALE
                $query_getUsersMale = "SELECT user_id FROM users WHERE user_gender = '1' && user_institution = '".$colname_getUser2."'";
                $getUsersMale = mysql_query($query_getUsersMale, $echoloyalty) or die(mysql_error());
                $row_getUsersMale = mysql_fetch_assoc($getUsersMale);
                $getUsersMaleRows  = mysql_num_rows($getUsersMale);

                // GENDER FEMALE
                $query_getUsersFemale = "SELECT user_id FROM users WHERE user_gender = '2' && user_institution = '".$colname_getUser2."'";
                $getUsersFemale = mysql_query($query_getUsersFemale, $echoloyalty) or die(mysql_error());
                $row_getUsersFemale = mysql_fetch_assoc($getUsersFemale);
                $getUsersFemaleRows  = mysql_num_rows($getUsersFemale);

                // GENDER NO
                $query_getUsersNo = "SELECT user_id FROM users WHERE user_gender = '0' && user_institution = '".$colname_getUser2."'";
                $getUsersNo = mysql_query($query_getUsersNo, $echoloyalty) or die(mysql_error());
                $row_getUsersNo = mysql_fetch_assoc($getUsersNo);
                $getUsersNoRows  = mysql_num_rows($getUsersNo);

                $genderMale = round(100 * $getUsersMaleRows / ($getUsersMaleRows + $getUsersFemaleRows + $getUsersNoRows));
                $genderFemale = round(100 * $getUsersFemaleRows / ($getUsersMaleRows + $getUsersFemaleRows + $getUsersNoRows));
                $genderNo = round(100 * $getUsersNoRows / ($getUsersMaleRows + $getUsersFemaleRows + $getUsersNoRows));
        
                $age16 = date("Y-m-d ", time() - 31536000*16);
                $age20 = date("Y-m-d ", time() - 31536000*20);
                $age25 = date("Y-m-d ", time() - 31536000*25);
                $age30 = date("Y-m-d ", time() - 31536000*30);
                $age40 = date("Y-m-d ", time() - 31536000*40);
                
                $query_getMemAge15 = "SELECT * FROM users WHERE user_birthday < '".$age16."' && user_birthday > '".$age20."' && user_institution = '".$colname_getUser2."'";
                $getMemAge15 = mysql_query($query_getMemAge15, $echoloyalty) or die(mysql_error());
                $row_getMemAge15 = mysql_fetch_assoc($getMemAge15);
                $totalRows_getMemAge15 = mysql_num_rows($getMemAge15);

                $query_getMemAge20 = "SELECT * FROM users WHERE user_birthday < '".$age20."' && user_birthday > '".$age25."' && user_institution = '".$colname_getUser2."'";
                $getMemAge20 = mysql_query($query_getMemAge20, $echoloyalty) or die(mysql_error());
                $row_getMemAge20 = mysql_fetch_assoc($getMemAge20);
                $totalRows_getMemAge20 = mysql_num_rows($getMemAge20);

                $query_getMemAge25 = "SELECT * FROM users WHERE user_birthday < '".$age25."' && user_birthday > '".$age30."' && user_institution = '".$colname_getUser2."'";
                $getMemAge25 = mysql_query($query_getMemAge25, $echoloyalty) or die(mysql_error());
                $row_getMemAge25 = mysql_fetch_assoc($getMemAge25);
                $totalRows_getMemAge25 = mysql_num_rows($getMemAge25);

                $query_getMemAge30 = "SELECT * FROM users WHERE user_birthday < '".$age30."' && user_birthday > '".$age40."' && user_institution = '".$colname_getUser2."'";
                $getMemAge30 = mysql_query($query_getMemAge30, $echoloyalty) or die(mysql_error());
                $row_getMemAge30 = mysql_fetch_assoc($getMemAge30);
                $totalRows_getMemAge30 = mysql_num_rows($getMemAge30);

                $query_getMemAge40 = "SELECT * FROM users WHERE user_birthday < '".$age40."' && user_institution = '".$colname_getUser2."'";
                $getMemAge40 = mysql_query($query_getMemAge40, $echoloyalty) or die(mysql_error());
                $row_getMemAge40 = mysql_fetch_assoc($getMemAge40);
                $totalRows_getMemAge40 = mysql_num_rows($getMemAge40);
                
                // INSTALLED FROM
                $query_getInstFrom = "SELECT user_install_where, COUNT(*) AS CountInst FROM users WHERE user_institution = '".$colname_getUser2."' GROUP BY user_install_where";
                $getInstFrom = mysql_query($query_getInstFrom, $echoloyalty) or die(mysql_error());
                $row_getInstFrom = mysql_fetch_assoc($getInstFrom);
                $getInstFromRows  = mysql_num_rows($getInstFrom);

                $inst0 = 0;
                $inst1 = 0;
                $inst2 = 0;
                $inst3 = 0;
                $inst4 = 0;
                $inst5 = 0;

                if($getInstFromRows > 0) {
                  
                  do {

                      if(isset($row_getInstFrom['user_install_where'])) {

                          switch($row_getInstFrom['user_install_where']) {
                          case '0':
                              $inst0 = $row_getInstFrom['CountInst'];
                              break;
                          case '1':
                              $inst1 = $row_getInstFrom['CountInst'];
                              break;
                          case '2':
                              $inst2 = $row_getInstFrom['CountInst'];
                              break;
                          case '3':
                              $inst3 = $row_getInstFrom['CountInst'];
                              break;
                          case '4':
                              $inst4 = $row_getInstFrom['CountInst'];
                              break;
                          case '5':
                              $inst5 = $row_getInstFrom['CountInst'];
                              break;
                          }

                      }

                  } while ($row_getInstFrom = mysql_fetch_assoc($getInstFrom));
                
                }

                // PREFERRED
                // $query_getInstClicks = "SELECT clicks_what, COUNT(*) AS CountClicks FROM clicks WHERE clicks_institution = '".$colname_getUser2."' GROUP BY clicks_what ORDER BY 2 DESC LIMIT 5";
                // $getInstClicks = mysql_query($query_getInstClicks, $echoloyalty) or die(mysql_error());
                // $row_getInstClicks = mysql_fetch_assoc($getInstClicks);
                // $getInstClicksRows  = mysql_num_rows($getInstClicks);

                $clickarr = array();
                // if($getInstClicksRows > 0) {
          
                //   do {
                //       if(isset($row_getInstClicks['clicks_what'])) {

                //           // PREFERRED NAME
                //           $query_getInstClicksN = "SELECT clicks_name_n FROM clicks_name WHERE clicks_name_id = '".$row_getInstClicks['clicks_what']."' && clicks_name_institution = '".$colname_getUser2."' LIMIT 1";
                //           $getInstClicksN = mysql_query($query_getInstClicksN, $echoloyalty) or die(mysql_error());
                //           $row_getInstClicksN = mysql_fetch_assoc($getInstClicksN);
                //           $getInstClicksNRows  = mysql_num_rows($getInstClicksN);

                //           $clickarr[$row_getInstClicksN['clicks_name_n']] = $row_getInstClicks['CountClicks'];

                //       }
                //   } while ($row_getInstClicks = mysql_fetch_assoc($getInstClicks));
        
                // }
        
                $pointaddarr = array();
                $pointsubarr = array();
                $pointmiddlearr = array();
                $revallsarr = array();

                for ($x = 0; $x <= 7; $x++) {
                    // LAST 8 DAYS
                    $tmamount = time() - $x*86400;
                    $aftday = $tmamount + 86400;

                    // POINTS ADDED
                    $query_getPoitsAdded = "SELECT SUM(points_points) AS SumPoints FROM points WHERE points_got_spend = '1' && points_proofed = '1' && points_institution = '".$colname_getUser2."' && points_when > '".$tmamount."' && points_when < '".$aftday."'";
                    $getPoitsAdded = mysql_query($query_getPoitsAdded, $echoloyalty) or die(mysql_error());
                    $row_getPoitsAdded = mysql_fetch_assoc($getPoitsAdded);
                    $getPoitsAddedRows  = mysql_num_rows($getPoitsAdded);

                    // POINTS SPENT
                    $query_getPoitsSub = "SELECT SUM(points_points) AS SubPoints FROM points WHERE points_got_spend = '0' && points_proofed = '1' && points_institution = '".$colname_getUser2."' && points_when > '".$tmamount."' && points_when < '".$aftday."'";
                    $getPoitsSub = mysql_query($query_getPoitsSub, $echoloyalty) or die(mysql_error());
                    $row_getPoitsSub = mysql_fetch_assoc($getPoitsSub);
                    $getPoitsSubRows  = mysql_num_rows($getPoitsSub);
                    
                    // POINTS TIME ADD TO ARRAY
                    if(isset($row_getPoitsAdded['SumPoints'])) {
                        $pointaddarr[$tmamount] = $row_getPoitsAdded['SumPoints'];
                    }
                    else {
                        $pointaddarr[$tmamount] = 0;
                    }
                    // POINTS TIME ADD TO ARRAY
                    if(isset($row_getPoitsSub['SubPoints'])) {
                        $pointsubarr[$tmamount] = $row_getPoitsSub['SubPoints'];
                    }
                    else {
                        $pointsubarr[$tmamount] = 0;
                    }

                    // POINTS TIME MIDDLE ADD TO ARRAY
                    $pointsum = $row_getPoitsAdded['SumPoints'] + $row_getPoitsSub['SubPoints'];
                    if($row_getPoitsAdded['SumPoints'] > 0 && $row_getPoitsSub['SubPoints'] > 0) {
                        $pointmiddlearr[$tmamount] = $pointsum / 2;
                    }
                    else {
                        $pointmiddlearr[$tmamount] = $pointsum;
                    }

                    // ДОХОД
                    $query_getRevA = "SELECT SUM(points_bill) AS TotalBills FROM points WHERE points_institution = '".$colname_getUser2."' && points_when > '".$tmamount."' && points_when < '".$aftday."'";
                    $getRevA = mysql_query($query_getRevA, $echoloyalty) or die(mysql_error());
                    $row_getRevA = mysql_fetch_assoc($getRevA);
                    $getRevARows  = mysql_num_rows($getRevA);

                    // REVENUE ADD TO ARRAY
                    if(isset($row_getRevA['TotalBills'])) {
                        $revallsarr[$tmamount] = $row_getRevA['TotalBills'];
                    }
                    else {
                        $revallsarr[$tmamount] = 0;
                    }

                }
      
                $usramountarr = array();
                $pointsarr = array();
                
                for ($x = 0; $x <= 30; $x++) {
                  // LAST 30 DAYS
                  $timeamount = time() - $x*86400;
                            if($x == 0) {
                                $afterday = $timeamount - 86400;
                            }
                            else {
                                $afterday = $timeamount - ($x+1)*86400;
                            }
                  // USERS
                  $query_getUsers = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_reg < '".$timeamount."' && user_reg > '".$afterday."'";
                  $getUsers = mysql_query($query_getUsers, $echoloyalty) or die(mysql_error());
                  $row_getUsers = mysql_fetch_assoc($getUsers);
                  $getUsersRows  = mysql_num_rows($getUsers);
                  
                  // TIME - PEOPLE ADD TO ARRAY
                  $usramountarr[$timeamount] = $getUsersRows;
                  
                  // USING SYSTEM
                  $query_getPoints = "SELECT * FROM points WHERE points_institution = '".$colname_getUser2."' && points_when < '".$timeamount."' && points_when > '".$afterday."' GROUP BY points_user";
                  $getPoints = mysql_query($query_getPoints, $echoloyalty) or die(mysql_error());
                  $row_getPoints = mysql_fetch_assoc($getPoints);
                  $getPointsRows  = mysql_num_rows($getPoints);
                  
                  // TIME - USING ADD TO ARRAY
                  $pointsarr[$timeamount] = $getPointsRows;
                  
                }

                // PROMOCODE USE
                $nowY = date('Y', time());
                $nowM = date('m', time());
                $lastDay = date('t', time());
                $monthbegin = strtotime(date($nowY.'-'.$nowM.'-'.'01'.' '.'00:00:00'));
                $monthend = strtotime(date($nowY.'-'.$nowM.'-'.$lastDay.' '.'23:59:59'));

                $query_getWorkerPoints = "SELECT *, COUNT(*) AS PromoUsed FROM promo RIGHT JOIN users ON users.user_id = promo.promo_from AND users.user_work_pos >= '2' WHERE promo.promo_institution = '".$colname_getUser2."' && promo.promo_when BETWEEN '".$monthbegin."' AND '".$monthend."' GROUP BY promo.promo_from ORDER BY PromoUsed DESC LIMIT 5";
                $getWorkerPoints = mysql_query($query_getWorkerPoints, $echoloyalty) or die(mysql_error());
                $row_getWorkerPoints = mysql_fetch_assoc($getWorkerPoints);
                $getWorkerPointsRows  = mysql_num_rows($getWorkerPoints);

                $workerPoints = array();

                if($getWorkerPointsRows > 0) {

                  do {

                    $workerName = $row_getWorkerPoints['user_name'] . ' ' . $row_getWorkerPoints['user_surname'];
                    $workerPoints[$workerName] = $row_getWorkerPoints['PromoUsed'];

                  } while ($row_getWorkerPoints = mysql_fetch_assoc($getWorkerPoints));

                }
                else {
                    $workerPoints['никто'] = 1;
                }

                $thetime = time();
        
                $newarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "usrAll" => $usramountarr, "pointsAll" => $pointsarr, "gendM" => $genderMale, "gendF" => $genderFemale, "gendN" => $genderNo, "age16" => $totalRows_getMemAge15, "age20" => $totalRows_getMemAge20, "age25" => $totalRows_getMemAge25, "age30" => $totalRows_getMemAge30, "age40" => $totalRows_getMemAge40, "inst0" => $inst0, "inst1" => $inst1, "inst2" => $inst2, "inst3" => $inst3, "inst4" => $inst4, "inst5" => $inst5, "clickarr" => $clickarr, "pointsAdd" => $pointaddarr, "pointsSub" => $pointsubarr, "pointsMid" => $pointmiddlearr, "revAll" => $revallsarr, "workerPoints" => $workerPoints, "time" => $thetime);
                array_push($gotdata, $newarrmes);

              }
        
            }
            else if($colname_getUser4 == 'country') {

                // GET COUNTRY
                $query_getCountry = "SELECT * FROM country";
                $getCountry = mysql_query($query_getCountry, $echoloyalty) or die(mysql_error());
                $row_getCountry = mysql_fetch_assoc($getCountry);
                $getCountryRows  = mysql_num_rows($getCountry);

                $countryArr = array();

                do{

                    array_push($countryArr, array("country" => '1', "id_country" => $row_getCountry['id_country'], "country_name" => $row_getCountry['name']));

                } while($row_getCountry = mysql_fetch_assoc($getCountry));

                $countryArray = array("requests" => "1", "countryArr" => $countryArr);

                array_push($gotdata, $countryArray);

            }
            else if($colname_getUser4 == 'region') {

              if(isset($colname_getUser5) && $colname_getUser5 != '%') {
                  
                $colname_country = "-1";
                if (isset($themsg['country'])) {
                  $colname_country = $protect($themsg['country']);
                }

                if($colname_getUser5 == 'send') {

                  // GET REGION
                  $query_getRegion = "SELECT * FROM region WHERE id_country = '".$colname_country."'";
                  $getRegion = mysql_query($query_getRegion, $echoloyalty) or die(mysql_error());
                  $row_getRegion = mysql_fetch_assoc($getRegion);
                  $getRegionRows  = mysql_num_rows($getRegion);

                  $regArr = array();

                  if($getRegionRows > 0) {

                      do {

                          array_push($regArr, array("region" => '1', "id_region" => $row_getRegion['id_region'], "id_country" => $row_getRegion['id_country'], "region_name" => $row_getRegion['name']));

                      } while($row_getRegion = mysql_fetch_assoc($getRegion));

                      $regionArray = array("requests" => "1", "regionArr" => $regArr);

                      array_push($gotdata, $regionArray);

                  }

                }

              }
                
            }
            else if($colname_getUser4 == 'city') {

              if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                $colname_region = "-1";
                if (isset($themsg['region'])) {
                  $colname_region = $protect($themsg['region']);
                }
                  
                if($colname_getUser5 == 'send') {

                  // GET CITY
                  $query_getCity = "SELECT * FROM city WHERE id_region = '".$colname_region."'";
                  $getCity = mysql_query($query_getCity, $echoloyalty) or die(mysql_error());
                  $row_getCity = mysql_fetch_assoc($getCity);
                  $getCityRows  = mysql_num_rows($getCity);

                  $cityArr = array();

                  if($getCityRows > 0) {

                    do{

                        array_push($cityArr, array("city" => '1', "id_city" => $row_getCity['id_city'], "id_region" => $row_getCity['id_region'], "id_country" => $row_getCity['id_country'], "city_name" => $row_getCity['name']));

                    } while($row_getCity = mysql_fetch_assoc($getCity));

                    $cityArray = array("requests" => "1", "cityArr" => $cityArr);

                    array_push($gotdata, $cityArray);

                  }

                }

              }

            }
            else if($colname_getUser4 == 'categories') {
                
              if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                $colname_chid = "-1";
                if (isset($themsg['chid'])) {
                  $colname_chid = $protect($themsg['chid']);
                }
                $colname_newtitle = "-1";
                if (isset($themsg['newtitle'])) {
                  $colname_newtitle = $protect($themsg['newtitle']);
                }
                $colname_newmessage = "-1";
                if (isset($themsg['newmessage'])) {
                  $colname_newmessage = $protect($themsg['newmessage']);
                }
                $colname_newgood = "-1";
                if (isset($themsg['newgood'])) {
                  $colname_newgood = $protect($themsg['newgood']);
                }
                      
                if($colname_getUser5 == 'del') {

                  $query_getCatChng = "SELECT * FROM categories WHERE cat_institution = '".$colname_getUser2."' && cat_id = '".$colname_chid."' LIMIT 1";
                  $getCatChng = mysql_query($query_getCatChng, $echoloyalty) or die(mysql_error());
                  $row_getCatChng = mysql_fetch_assoc($getCatChng);
                  $getCatChngRows  = mysql_num_rows($getCatChng);

                  if($getCatChngRows > 0) {

                    $delCat = "UPDATE categories SET cat_when='1' WHERE cat_institution = '".$colname_getUser2."' && cat_id='".$colname_chid."'";
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

                      $updCat = "UPDATE categories SET cat_name='".$colname_newtitle."', cat_desc='".$colname_newmessage."', cat_ingr='".$colname_newgood."', cat_when='".$when."' WHERE cat_institution = '".$colname_getUser2."' && cat_id='".$colname_chid."'";
                      mysql_query($updCat, $echoloyalty) or die(mysql_error());

                      $newarrmes = array("requests" => '1', "catId" => $colname_chid, "catUpd" => '1');
                      array_push($gotdata, $newarrmes);

                  }

                }
                  
              }
              else {

                  $query_getCatC = "SELECT * FROM categories WHERE cat_institution = '".$colname_getUser2."' && cat_when > '1'";
                  $getCatC = mysql_query($query_getCatC, $echoloyalty) or die(mysql_error());
                  $row_getCatC = mysql_fetch_assoc($getCatC);
                  $getCatCRows  = mysql_num_rows($getCatC);
                  
                  $catarr = array();
                  if($getCatCRows > 0) {

                    do {

                      $query_getGoodsN = "SELECT goods_name FROM goods WHERE goods_id = '".$row_getCatC['cat_ingr']."' && goods_institution = '".$colname_getUser2."' && goods_when > '1'";
                      $getGoodsN = mysql_query($query_getGoodsN, $echoloyalty) or die(mysql_error());
                      $row_getGoodsN = mysql_fetch_assoc($getGoodsN);
                      $getGoodsNRows  = mysql_num_rows($getGoodsN);

                      $goodsName = 'Без группы';
                      if($getGoodsNRows > 0) {
                        $goodsName = $row_getGoodsN['goods_name'];
                      }
                      
                      array_push($catarr, array($row_getCatC['cat_id'], $row_getCatC['cat_name'], $row_getCatC['cat_desc'], $row_getCatC['cat_pic'], $goodsName, $row_getCatC['cat_institution'], $row_getCatC['cat_when'], $row_getCatC['cat_ingr']));
                        
                    } while ($row_getCatC = mysql_fetch_assoc($getCatC));

                  }

                  $query_getGoodsC = "SELECT * FROM goods WHERE goods_institution = '".$colname_getUser2."' && goods_when > '1'";
                  $getGoodsC = mysql_query($query_getGoodsC, $echoloyalty) or die(mysql_error());
                  $row_getGoodsC = mysql_fetch_assoc($getGoodsC);
                  $getGoodsCRows  = mysql_num_rows($getGoodsC);
                  
                  $goodsarr = array();
                  if($getGoodsCRows > 0) {

                    do {
                        
                      array_push($goodsarr, array("goods_id" => $row_getGoodsC['goods_id'], "goods_name" => $row_getGoodsC['goods_name'], "goods_desc" => $row_getGoodsC['goods_desc'], "goods_pic" => $row_getGoodsC['goods_pic'], "goods_inst" => $row_getGoodsC['goods_institution'], "goods_when" => $row_getGoodsC['goods_when']));
                        
                    } while ($row_getGoodsC = mysql_fetch_assoc($getGoodsC));

                  }
                  
                  $catarrmes = array("catC" => $getCatCRows, "instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "catAll" => $catarr, "goodsAll" => $goodsarr);
                  array_push($gotdata, $catarrmes);
              
              }
            
            }
            else if($colname_getUser4 == 'goods') {
                
              if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                $colname_catid= "-1";
                if (isset($themsg['catid'])) {
                  $colname_catid = $protect($themsg['catid']);
                }
                $colname_chid = "-1";
                if (isset($themsg['chid'])) {
                  $colname_chid = $protect($themsg['chid']);
                }
                $colname_newtitle = "-1";
                if (isset($themsg['newtitle'])) {
                  $colname_newtitle = $protect($themsg['newtitle']);
                }
                $colname_newmessage = "-1";
                if (isset($themsg['newmessage'])) {
                  $colname_newmessage = $protect($themsg['newmessage']);
                }

                if($colname_getUser5 == 'del') {

                    $query_getGoodChng = "SELECT * FROM goods WHERE goods_institution = '".$colname_getUser2."' && goods_id = '".$colname_catid."' LIMIT 1";
                    $getGoodChng = mysql_query($query_getGoodChng, $echoloyalty) or die(mysql_error());
                    $row_getGoodChng = mysql_fetch_assoc($getGoodChng);
                    $getGoodChngRows  = mysql_num_rows($getGoodChng);

                    if($getGoodChngRows > 0) {

                      $delGood = "UPDATE goods SET goods_when='1' WHERE goods_institution = '".$colname_getUser2."' && goods_id='".$colname_catid."'";
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

                $query_getGoodsC = "SELECT * FROM goods WHERE goods_institution = '".$colname_getUser2."' && goods_when > '1'";
                $getGoodsC = mysql_query($query_getGoodsC, $echoloyalty) or die(mysql_error());
                $row_getGoodsC = mysql_fetch_assoc($getGoodsC);
                $getGoodsCRows  = mysql_num_rows($getGoodsC);
                
                $goodsarr = array();
                if($getGoodsCRows > 0) {

                  do {
                      
                      array_push($goodsarr, array($row_getGoodsC['goods_id'], $row_getGoodsC['goods_name'], $row_getGoodsC['goods_desc'], $row_getGoodsC['goods_pic'], $row_getGoodsC['goods_institution'], $row_getGoodsC['goods_when']));
                      
                  } while ($row_getGoodsC = mysql_fetch_assoc($getGoodsC));

                }
                
                $goodsarrmes = array("catC" => $getGoodsCRows, "instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "goodsAll" => $goodsarr);
                array_push($gotdata, $goodsarrmes);
              
              }
            
            }
            else if($colname_getUser4 == 'menue') {
                
              if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                $colname_menueid = "-1";
                if (isset($themsg['menueid'])) {
                  $colname_menueid = $protect($themsg['menueid']);
                }
                $colname_chid = "-1";
                if (isset($themsg['chid'])) {
                  $colname_chid = $protect($themsg['chid']);
                }
                $colname_newtitle = "-1";
                if (isset($themsg['newtitle'])) {
                  $colname_newtitle = $protect($themsg['newtitle']);
                }
                $colname_newmessage = "-1";
                if (isset($themsg['newmessage'])) {
                  $colname_newmessage = $protect($themsg['newmessage']);
                }
                $colname_newmenusize = "-1";
                if (isset($themsg['newmenusize'])) {
                  $colname_newmenusize = $protect($themsg['newmenusize']);
                }
                $colname_newmenucost = "-1";
                if (isset($themsg['newmenucost'])) {
                  $colname_newmenucost = $protect($themsg['newmenucost']);
                }
                $colname_newmenuweight = "-1";
                if (isset($themsg['newmenuweight'])) {
                  $colname_newmenuweight = $protect($themsg['newmenuweight']);
                }
                $colname_newmenuinterval = "-1";
                if (isset($themsg['newmenuinterval'])) {
                  $colname_newmenuinterval = $protect($themsg['newmenuinterval']);
                }
                $colname_newmenudiscount = "-1";
                if (isset($themsg['newmenudiscount'])) {
                  $colname_newmenudiscount = $protect($themsg['newmenudiscount']);
                }
                $colname_newmenuaction = "-1";
                if (isset($themsg['newmenuaction'])) {
                  $colname_newmenuaction = $protect($themsg['newmenuaction']);
                }
                $colname_newmenucode = "-1";
                if (isset($themsg['newmenucode'])) {
                  $colname_newmenucode = $protect($themsg['newmenucode']);
                }
                $colname_newcat = "-1";
                if (isset($themsg['newcat'])) {
                  $colname_newcat = $protect($themsg['newcat']);
                }

                if($colname_getUser5 == 'del') {

                    $query_getMenuChng = "SELECT * FROM menue WHERE menue_institution = '".$colname_getUser2."' && menue_id = '".$colname_menueid."' LIMIT 1";
                    $getMenuChng = mysql_query($query_getMenuChng, $echoloyalty) or die(mysql_error());
                    $row_getMenuChng = mysql_fetch_assoc($getMenuChng);
                    $getMenuChngRows  = mysql_num_rows($getMenuChng);

                    if($getMenuChngRows > 0) {

                        $delMenue = "UPDATE menue SET menue_when='1' WHERE menue_institution = '".$colname_getUser2."' && menue_id='".$colname_menueid."'";
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

                      $updMenue = "UPDATE menue SET menue_cat='".$colname_newcat."', menue_name='".$colname_newtitle."', menue_desc='".$colname_newmessage."', menue_size='".$colname_newmenusize."', menue_cost='".$colname_newmenucost."', menue_weight='".$colname_newmenuweight."', menue_interval='".$colname_newmenuinterval."', menue_discount='".$colname_newmenudiscount."', menue_action='".$colname_newmenuaction."', menue_code='".$colname_newmenucode."', menue_when='".$when."' WHERE menue_institution = '".$colname_getUser2."' && menue_id='".$colname_chid."'";
                      mysql_query($updMenue, $echoloyalty) or die(mysql_error());

                      $menuearrmes = array("requests" => '1', "menueId" => $colname_chid, "menueUpd" => '2');
                      array_push($gotdata, $menuearrmes);

                  }

                }
                  
              }
              else {

                  $query_getMenueC = "SELECT * FROM menue WHERE menue_institution = '".$colname_getUser2."' && menue_when > '1'";
                  $getMenueC = mysql_query($query_getMenueC, $echoloyalty) or die(mysql_error());
                  $row_getMenueC = mysql_fetch_assoc($getMenueC);
                  $getMenueCRows  = mysql_num_rows($getMenueC);
                  
                  $menuearr = array();
                  if($getMenueCRows > 0) {

                    do {

                      $query_getCatChng = "SELECT * FROM categories WHERE cat_institution = '".$colname_getUser2."' && cat_id = '".$row_getMenueC['menue_cat']."' LIMIT 1";
                      $getCatChng = mysql_query($query_getCatChng, $echoloyalty) or die(mysql_error());
                      $row_getCatChng = mysql_fetch_assoc($getCatChng);
                      $getCatChngRows  = mysql_num_rows($getCatChng);
                      
                      array_push($menuearr, array($row_getMenueC['menue_id'], $row_getMenueC['menue_name'], $row_getMenueC['menue_desc'], $row_getMenueC['menue_pic'], $row_getMenueC['menue_institution'], $row_getMenueC['menue_when'], $row_getCatChng['cat_name'], $row_getMenueC['menue_size'], $row_getMenueC['menue_cost'], $row_getMenueC['menue_weight'], $row_getMenueC['menue_discount'], $row_getMenueC['menue_action'], $row_getMenueC['menue_code'], $row_getCatChng['cat_ingr'], $row_getMenueC['menue_cat'], $row_getMenueC['menue_interval']));
                        
                    } while ($row_getMenueC = mysql_fetch_assoc($getMenueC));

                  }

                  $query_getCatC = "SELECT * FROM categories WHERE cat_institution = '".$colname_getUser2."' && cat_when > '1'";
                  $getCatC = mysql_query($query_getCatC, $echoloyalty) or die(mysql_error());
                  $row_getCatC = mysql_fetch_assoc($getCatC);
                  $getCatCRows  = mysql_num_rows($getCatC);
                  
                  $catarr = array();
                  if($getCatCRows > 0) {

                      do {
                          
                          array_push($catarr, array("cat_id" => $row_getCatC['cat_id'], "cat_name" => $row_getCatC['cat_name'], "cat_desc" => $row_getCatC['cat_desc'], "cat_pic" => $row_getCatC['cat_pic'], "cat_inst" => $row_getCatC['cat_institution'], "cat_when" => $row_getCatC['cat_when']));
                          
                      } while ($row_getCatC = mysql_fetch_assoc($getCatC));

                  }
                  
                  $catarrmes = array("menueC" => $getMenueCRows, "instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "menueAll" => $menuearr, "catArr" => $catarr);
                  array_push($gotdata, $catarrmes);
              
              }
            
            }
            else if($colname_getUser4 == 'support') {

                $when = time();
                $sendingok = true;

                if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                    $colname_userid = "-1";
                    if (isset($themsg['userid'])) {
                      $colname_userid = $protect($themsg['userid']);
                    }
                    $colname_lastmes = "-1";
                    if (isset($themsg['lastmes'])) {
                      $colname_lastmes = $protect($themsg['lastmes']);
                    }
                    $colname_message = "-1";
                    if (isset($themsg['message'])) {
                      $colname_message = $protect($themsg['message']);
                    }
                        
                    if($colname_getUser5 == 'send') {

                        $insrtMessage = "INSERT INTO chat (chat_from, chat_to, chat_name, chat_message, chat_institution, chat_answered, chat_when) VALUES ('1', '".$colname_userid."', 'support', '".$colname_message."', '".$colname_getUser2."', '1', '".$when."')";
                        mysql_query($insrtMessage, $echoloyalty) or die(mysql_error());

                        $query_getSupportC = "SELECT * FROM chat WHERE chat_institution = '".$colname_getUser2."' && chat_to = '".$colname_userid."' && chat_from = '1' ORDER BY chat_id DESC LIMIT 1";
                        $getSupportC = mysql_query($query_getSupportC, $echoloyalty) or die(mysql_error());
                        $row_getSupportC = mysql_fetch_assoc($getSupportC);
                        $getSupportCRows  = mysql_num_rows($getSupportC);
                        
                        $supportarr = array();
                        if($getSupportCRows > 0) {
                                
                            array_push($supportarr, array($row_getSupportC['chat_id'], $row_getSupportC['chat_from'], $row_getSupportC['chat_to'], $row_getSupportC['chat_name'], $row_getSupportC['chat_message'], $row_getSupportC['chat_read'], $row_getSupportC['chat_institution'], $row_getSupportC['chat_answered'], $row_getSupportC['chat_when']));
                                
                        }

                        $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_id = '".$colname_userid."'";
                        $getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
                        $row_getGCM = mysql_fetch_assoc($getGCM);
                        $getGCMRows  = mysql_num_rows($getGCM);

                        if($getGCMRows > 0) {

                          $apiKey =  urldecode($row_getInst['org_key']);
                          
                          $title = urldecode("Техподдержка: ");

                          $messageand = urldecode($colname_message);

                          // SENDING
                          do {

                            if($row_getGCM['user_device_os'] == 'Android') {

                                // ANDROID PUSH
                                $registrationId = urldecode($row_getGCM['user_gcm']);

                                // ANDROID SETTINGS
                                $headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
                                $data = array(
                                    'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title)),
                                    'registration_ids' => array($registrationId)
                                );

                                $ch = curl_init();

                                curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
                                curl_setopt( $ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
                                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                                curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );

                                $response = curl_exec($ch);
                                curl_close($ch);

                            }
                            elseif($row_getGCM['user_device_os'] == 'iOS') {

                              array_push($iosIDs, $row_getGCM['user_gcm']);

                            }

                          } while ($row_getGCM = mysql_fetch_assoc($getGCM));

                          $sendGCM(0, $iosIDs, $row_getInst['org_cert'], "Техподдержка: ", $colname_message, 0, 0);

                        }

                        $newarrmes = array("requests" => '1', "supportArr" => $supportarr);
                            array_push($gotdata, $newarrmes);

                    }
                    else if($colname_getUser5 == 'get') {

                        if($colname_lastmes == '0') {

                            $query_getSupportC = "SELECT * FROM chat WHERE chat_institution = '".$colname_getUser2."' && chat_to = '1' && chat_from = '".$colname_userid."' OR chat_institution = '".$colname_getUser2."' && chat_to = '".$colname_userid."' && chat_from = '1' ORDER BY chat_id DESC";
                            $getSupportC = mysql_query($query_getSupportC, $echoloyalty) or die(mysql_error());
                            $row_getSupportC = mysql_fetch_assoc($getSupportC);
                            $getSupportCRows  = mysql_num_rows($getSupportC);
                            
                            $supportarr = array();
                            if($getSupportCRows > 0) {

                                do {
                                    
                                    array_push($supportarr, array($row_getSupportC['chat_id'], $row_getSupportC['chat_from'], $row_getSupportC['chat_to'], $row_getSupportC['chat_name'], $row_getSupportC['chat_message'], $row_getSupportC['chat_read'], $row_getSupportC['chat_institution'], $row_getSupportC['chat_answered'], $row_getSupportC['chat_when']));

                                    $updMessage = "UPDATE chat SET chat_answered = '1' WHERE chat_id = '".$row_getSupportC['chat_id']."' && chat_institution = '".$colname_getUser2."'";
                                    mysql_query($updMessage, $echoloyalty) or die(mysql_error());
                                    
                                } while ($row_getSupportC = mysql_fetch_assoc($getSupportC));

                            }

                            $newarrmes = array("requests" => '1', "supportArr" => $supportarr);
                            array_push($gotdata, $newarrmes);

                        }
                        else if($colname_lastmes > '0') {

                            $query_getSupportC = "SELECT * FROM chat WHERE chat_id > '".$colname_lastmes."' && chat_institution = '".$colname_getUser2."' && chat_to = '1' && chat_from = '".$colname_userid."' OR chat_id > '".$colname_lastmes."' && chat_institution = '".$colname_getUser2."' && chat_to = '".$colname_userid."' && chat_from = '1' ORDER BY chat_id DESC";
                            $getSupportC = mysql_query($query_getSupportC, $echoloyalty) or die(mysql_error());
                            $row_getSupportC = mysql_fetch_assoc($getSupportC);
                            $getSupportCRows  = mysql_num_rows($getSupportC);
                            
                            $supportarr = array();
                            if($getSupportCRows > 0) {

                                do {
                                    
                                    array_push($supportarr, array($row_getSupportC['chat_id'], $row_getSupportC['chat_from'], $row_getSupportC['chat_to'], $row_getSupportC['chat_name'], $row_getSupportC['chat_message'], $row_getSupportC['chat_read'], $row_getSupportC['chat_institution'], $row_getSupportC['chat_answered'], $row_getSupportC['chat_when']));

                                    $updMessage = "UPDATE chat SET chat_answered = '1' WHERE chat_id = '".$row_getSupportC['chat_id']."' && chat_institution = '".$colname_getUser2."'";
                                    mysql_query($updMessage, $echoloyalty) or die(mysql_error());
                                    
                                } while ($row_getSupportC = mysql_fetch_assoc($getSupportC));

                            }

                            $newarrmes = array("requests" => '1', "supportArr" => $supportarr);
                            array_push($gotdata, $newarrmes);

                        }

                    }
                    else {

                        $query_getSupportC = "SELECT * FROM chat WHERE chat_institution = '".$colname_getUser2."' && chat_to = '1' && chat_from = '".$colname_getUser5."' OR chat_institution = '".$colname_getUser2."' && chat_to = '".$colname_getUser5."' && chat_from = '1' ORDER BY chat_id DESC";
                        $getSupportC = mysql_query($query_getSupportC, $echoloyalty) or die(mysql_error());
                        $row_getSupportC = mysql_fetch_assoc($getSupportC);
                        $getSupportCRows  = mysql_num_rows($getSupportC);
                        
                        $supportarr = array();
                        if($getSupportCRows > 0) {

                            do {
                                
                                array_push($supportarr, array($row_getSupportC['chat_id'], $row_getSupportC['chat_from'], $row_getSupportC['chat_to'], $row_getSupportC['chat_name'], $row_getSupportC['chat_message'], $row_getSupportC['chat_read'], $row_getSupportC['chat_institution'], $row_getSupportC['chat_answered'], $row_getSupportC['chat_when']));

                                $updMessage = "UPDATE chat SET chat_answered = '1' WHERE chat_id = '".$row_getSupportC['chat_id']."' && chat_institution = '".$colname_getUser2."'";
                                mysql_query($updMessage, $echoloyalty) or die(mysql_error());
                                
                            } while ($row_getSupportC = mysql_fetch_assoc($getSupportC));

                        }

                        $newarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "supportAll" => $supportarr);
                        array_push($gotdata, $newarrmes);

                    }

                }
                else {

                    $query_getSupportC = "SELECT * FROM chat p WHERE chat_id = (SELECT max(chat_id) FROM chat p2 WHERE p2.chat_from = p.chat_from && chat_institution = '".$colname_getUser2."' && chat_to = '1' && chat_from != '1' && chat_answered = '0') && chat_institution = '".$colname_getUser2."' && chat_to = '1' && chat_from != '1' && chat_answered = '0' GROUP BY chat_from ORDER BY chat_id DESC";
                    $getSupportC = mysql_query($query_getSupportC, $echoloyalty) or die(mysql_error());
                    $row_getSupportC = mysql_fetch_assoc($getSupportC);
                    $getSupportCRows  = mysql_num_rows($getSupportC);
                    
                    $supportarr = array();
                    if($getSupportCRows > 0) {

                        do {
                            
                            array_push($supportarr, array($row_getSupportC['chat_id'], $row_getSupportC['chat_from'], $row_getSupportC['chat_to'], $row_getSupportC['chat_name'], $row_getSupportC['chat_message'], $row_getSupportC['chat_read'], $row_getSupportC['chat_institution'], $row_getSupportC['chat_answered'], $row_getSupportC['chat_when']));
                            
                        } while ($row_getSupportC = mysql_fetch_assoc($getSupportC));

                    }
                    
                    $newarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "supportAll" => $supportarr);
                    array_push($gotdata, $newarrmes);

                }
            
            }
		        else if($colname_getUser4 == 'calendar') {
        
              if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                  $colname_sorderid = "-1";
                  if (isset($themsg['sorderid'])) {
                    $colname_sorderid = $protect($themsg['sorderid']);
                  }
                  $colname_suser = "-1";
                  if (isset($themsg['suser'])) {
                    $colname_suser = $protect($themsg['suser']);
                  }
                  $colname_stitle = "-1";
                  if (isset($themsg['stitle'])) {
                    $colname_stitle = $protect($themsg['stitle']);
                  }
                  $colname_sdescr = "-1";
                  if (isset($themsg['sdescr'])) {
                    $colname_sdescr = $protect($themsg['sdescr']);
                  }
                  $colname_soffice = "-1";
                  if (isset($themsg['soffice'])) {
                    $colname_soffice = $protect($themsg['soffice']);
                  }
                  $colname_sgoods = "-1";
                  if (isset($themsg['sgoods'])) {
                    $colname_sgoods = $protect($themsg['sgoods']);
                  }
                  $colname_scats = "-1";
                  if (isset($themsg['scats'])) {
                    $colname_scats = $protect($themsg['scats']);
                  }
                  $colname_smenue = "-1";
                  if (isset($themsg['smenue'])) {
                    $colname_smenue = $protect($themsg['smenue']);
                  }
                  $colname_sworkerid = "-1";
                  if (isset($themsg['sworkerid'])) {
                    $colname_sworkerid = $protect($themsg['sworkerid']);
                  }
                  $colname_sbill = "-1";
                  if (isset($themsg['sbill'])) {
                    $colname_sbill = $protect($themsg['sbill']);
                  }
                  $colname_sstatus = "-1";
                  if (isset($themsg['sstatus'])) {
                    $colname_sstatus = $protect($themsg['sstatus']);
                  }
                  $colname_start = "-1";
                  if (isset($themsg['start'])) {
                    $colname_start = $protect($themsg['start']);
                  }
                  $colname_stop = "-1";
                  if (isset($themsg['stop'])) {
                    $colname_stop = $protect($themsg['stop']);
                  }
                  $colname_sallday = "-1";
                  if (isset($themsg['sallday'])) {
                    $colname_sallday = $protect($themsg['sallday']);
                  }
                    
                  if($colname_getUser5 == 'del') {

                      $query_getOrderChng = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."' && order_id = '".$colname_sorderid."' LIMIT 1";
                      $getOrderChng = mysql_query($query_getOrderChng, $echoloyalty) or die(mysql_error());
                      $row_getOrderChng = mysql_fetch_assoc($getOrderChng);
                      $getOrderChngRows  = mysql_num_rows($getOrderChng);

                      if($getOrderChngRows > 0) {

                          $delOrder = "UPDATE ordering SET order_when='1', order_state='0' WHERE order_institution = '".$colname_getUser2."' && order_id='".$colname_sorderid."'";
                          mysql_query($delOrder, $echoloyalty) or die(mysql_error());

                          $newarrmes = array("requests" => '1', "orderId" => $colname_sorderid, "orderUpd" => '2');
                          array_push($gotdata, $newarrmes);

                      }

                  }
        					else if($colname_getUser5 == 'create') {

                    if($colname_sorderid == '0') {

                      $insOrder = "INSERT INTO ordering (order_user, order_name, order_desc, order_worker, order_institution, order_office, order_bill, order_goods, order_cats, order_order, order_status, order_start, order_end, order_allday, order_mobile, order_when) VALUES ('".$colname_suser."', '".$colname_stitle."', '".$colname_sdescr."', '".$colname_sworkerid."', '".$colname_getUser2."', '".$colname_soffice."', '".$colname_sbill."', '".$colname_sgoods."', '".$colname_scats."', '".$colname_smenue."', '".$colname_sstatus."', '".$colname_start."', '".$colname_stop."', '".$colname_sallday."', '0', '".$when."')";
                      mysql_query($insOrder, $echoloyalty) or die(mysql_error());

                      $query_getOrderChng = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."' && order_user = '".$colname_suser."' && order_when = '".$when."' ORDER BY order_id DESC LIMIT 1";
                      $getOrderChng = mysql_query($query_getOrderChng, $echoloyalty) or die(mysql_error());
                      $row_getOrderChng = mysql_fetch_assoc($getOrderChng);
                      $getOrderChngRows  = mysql_num_rows($getOrderChng);

                      if($getOrderChngRows > 0) {

                        $newarrmes = array("requests" => '1', "orderId" => $row_getOrderChng['order_id'], "orderIns" => '1');
                        array_push($gotdata, $newarrmes);

                      }

                    }

        					}
        					else if($colname_getUser5 == 'change') {

                    if($colname_sorderid != '0') {
        						
          						$query_getOrderChng = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."' && order_id = '".$colname_sorderid."' LIMIT 1";
                      $getOrderChng = mysql_query($query_getOrderChng, $echoloyalty) or die(mysql_error());
                      $row_getOrderChng = mysql_fetch_assoc($getOrderChng);
                      $getOrderChngRows  = mysql_num_rows($getOrderChng);

                      if($getOrderChngRows > 0) {

                          $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_id = '".$colname_suser."'";
                          $getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
                          $row_getGCM = mysql_fetch_assoc($getGCM);
                          $getGCMRows  = mysql_num_rows($getGCM);

                          if($getGCMRows > 0) {

                            $apiKey =  urldecode($row_getInst['org_key']);
                            
                            $title = urldecode("Ваша запись!");

                            $messageand = urldecode("Загляните в личный кабинет");

                            // SENDING
                            do {

                              if($row_getGCM['user_device_os'] == 'Android') {

                                  // ANDROID PUSH
                                  $registrationId = urldecode($row_getGCM['user_gcm']);

                                  // ANDROID SETTINGS
                                  $headers = array("Content-Type: application/json", "Authorization: key=" . $apiKey);
                                  $data = array(
                                      'data' => array('message' => html_entity_decode($messageand), 'title' => html_entity_decode($title)),
                                      'registration_ids' => array($registrationId)
                                  );

                                  $ch = curl_init();

                                  curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
                                  curl_setopt( $ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
                                  curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                                  curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                                  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                                  curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );

                                  $response = curl_exec($ch);
                                  curl_close($ch);

                              }
                              elseif($row_getGCM['user_device_os'] == 'iOS') {

                                array_push($iosIDs, $row_getGCM['user_gcm']);

                              }

                            } while ($row_getGCM = mysql_fetch_assoc($getGCM));

                            $sendGCM(0, $iosIDs, $row_getInst['org_cert'], "Ваша запись!", "Загляните в личный кабинет", 0, 0);

                          }

                          $updOrder = "UPDATE ordering SET order_user='".$colname_suser."', order_name='".$colname_stitle."', order_desc='".$colname_sdescr."', order_worker='".$colname_sworkerid."', order_institution='".$colname_getUser2."', order_office='".$colname_soffice."', order_bill='".$colname_sbill."', order_goods='".$colname_sgoods."', order_cats='".$colname_scats."', order_order='".$colname_smenue."', order_status='".$colname_sstatus."', order_start='".$colname_start."', order_end='".$colname_stop."', order_allday='".$colname_sallday."', order_when='".$when."' WHERE order_id = '".$colname_sorderid."'";
						               mysql_query($updOrder, $echoloyalty) or die(mysql_error());

                          $newarrmes = array("requests" => '1', "orderId" => $colname_sorderid, "orderUpd" => '1');
                          array_push($gotdata, $newarrmes);

                      }

                    }
        						
        					}
                
              }
              else {
                  // ORDERS
                  $query_getOrder = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."'";
        				  $getOrder = mysql_query($query_getOrder, $echoloyalty) or die(mysql_error());
        				  $row_getOrder = mysql_fetch_assoc($getOrder);
        				  $getOrderRows = mysql_num_rows($getOrder);
                          
                  $orderarr = array();
        				  if($getOrderRows > 0) {

        					  do {

                      // USER DATA
                      $query_getUsrData = "SELECT * FROM users WHERE user_id = '".$row_getOrder['order_user']."'";
                      $getUsrData = mysql_query($query_getUsrData, $echoloyalty) or die(mysql_error());
                      $row_getUsrData = mysql_fetch_assoc($getUsrData);
                      $getUsrDataRows  = mysql_num_rows($getUsrData);

                      $usrpic = '';
                      $usrmob = '';
                      $usrname = $row_getOrder['order_user'];

                      if($getUsrDataRows > 0) {

                        if(isset($row_getOrder['order_mobile']) && $row_getOrder['order_mobile'] == '1') {
                          $usrpic = $row_getUsrData['user_pic'];
                          $usrname = $row_getUsrData['user_name'];
                          $usrmob = $row_getUsrData['user_mob'];
                        }

                      }

                      $org_office = $row_getInst['org_name'];
                      $org_office_id = $row_getInst['org_id'];

                      $query_getOfficeData = "SELECT * FROM organizations_office WHERE office_id = '".$row_getOrder['order_office']."'";
                      $getOfficeData = mysql_query($query_getOfficeData, $echoloyalty) or die(mysql_error());
                      $row_getOfficeData = mysql_fetch_assoc($getOfficeData);
                      $getOfficeDataRows  = mysql_num_rows($getOfficeData);

                      if($getOfficeDataRows > 0) {
                        $org_office = $row_getOfficeData['office_name'];
                        $org_office_id = $row_getOfficeData['office_id'];
                      }
          						
          						array_push($orderarr, array("order_id" => $row_getOrder['order_id'], "user_mob" => $usrmob, "order_user" => $row_getOrder['order_user'], "order_user_pic" => $usrpic, "order_user_name" => $usrname, "order_name" => $row_getOrder['order_name'], "order_desc" => $row_getOrder['order_desc'], "order_worker" => $row_getOrder['order_worker'], "order_institution" => $row_getOrder['order_institution'], "order_office_name" => $org_office, "order_office_id" => $org_office_id, "order_bill" => $row_getOrder['order_bill'], "order_goods" => $row_getOrder['order_goods'], "order_cats" => $row_getOrder['order_cats'], "order_order" => $row_getOrder['order_order'], "order_status" => $row_getOrder['order_status'], "order_start" => $row_getOrder['order_start'], "order_end" => $row_getOrder['order_end'], "order_allday" => $row_getOrder['order_allday'], "order_mobile" => $row_getOrder['order_mobile'], "order_when" => $row_getOrder['order_when']));
        						
        					  } while ($row_getOrder = mysql_fetch_assoc($getOrder));

        				  }
                  // WORKERS
                  $query_getUsersC = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_work_pos >= '2'";
                  $getUsersC = mysql_query($query_getUsersC, $echoloyalty) or die(mysql_error());
                  $row_getUsersC = mysql_fetch_assoc($getUsersC);
                  $getUsersCRows  = mysql_num_rows($getUsersC);
          
                  $usrsarr = array();
                  if($getUsersCRows > 0) {

                    $workers = array();
                    do {

                      // GET PROFESSION
                      $query_getProf = "SELECT * FROM professions WHERE prof_id = '".$row_getUsersC['user_work_pos']."' && prof_when > '2' && (prof_institution = '0' OR prof_institution = '".$colname_getUser2."')";
                      $getProf = mysql_query($query_getProf, $echoloyalty) or die(mysql_error());
                      $row_getUProf = mysql_fetch_assoc($getProf);
                      $getProfRows  = mysql_num_rows($getProf);

                      $prof = '';
                      if($getProfRows > 0) {
                        $prof = $row_getUProf['prof_name'];
                      }

                      array_push($usrsarr, array("user_id" => $row_getUsersC['user_id'], "user_name" => $row_getUsersC['user_name'], "user_surname" => $row_getUsersC['user_surname'], "user_email" => $row_getUsersC['user_email'], "user_tel" => $row_getUsersC['user_tel'], "user_mob" => $row_getUsersC['user_mob'], "user_gender" => $row_getUsersC['user_gender'], "user_adress" => $row_getUsersC['user_adress'], "user_reg" => $row_getUsersC['user_reg'], "user_pic" => $row_getUsersC['user_pic'], "user_work_pos" => $row_getUsersC['user_work_pos'], "user_menue_exe" => $row_getUsersC['user_menue_exe'], "user_institution" => $row_getUsersC['user_institution'], "user_office" => $row_getUsersC['user_office'], "user_profession" => $prof));

                    } while ($row_getUsersC = mysql_fetch_assoc($getUsersC));

                    array_push($usrsarr, $workers);

                  }
                  // OFFICES
                  $query_getOffice = "SELECT * FROM organizations_office WHERE office_institution = '".$colname_getUser2."' && office_reg > '2'";
                  $getOffice = mysql_query($query_getOffice, $echoloyalty) or die(mysql_error());
                  $row_getOffice = mysql_fetch_assoc($getOffice);
                  $getOfficeRows = mysql_num_rows($getOffice);
              
                  $officearr = array();
                  if($getOfficeRows > 0) {
                  
                    do {
                      
                      array_push($officearr, array($row_getOffice['office_id'], $row_getOffice['office_name'], $row_getOffice['office_start'], $row_getOffice['office_stop'], $row_getOffice['office_country'], $row_getOffice['office_city'], $row_getOffice['office_adress'], $row_getOffice['office_timezone'], $row_getOffice['office_tel'], $row_getOffice['office_fax'], $row_getOffice['office_mob'], $row_getOffice['office_email'], $row_getOffice['office_pwd'], $row_getOffice['office_skype'], $row_getOffice['office_site'], $row_getOffice['office_tax_id'], $row_getOffice['office_logo'], $row_getOffice['office_institution'], $row_getOffice['office_log'], $row_getOffice['office_reg']));
                      
                    } while ($row_getOffice = mysql_fetch_assoc($getOffice));
                  
                  }
                  // MENUE
                  $query_getMenueC = "SELECT * FROM menue WHERE menue_institution = '".$colname_getUser2."' && menue_when > '1'";
                  $getMenueC = mysql_query($query_getMenueC, $echoloyalty) or die(mysql_error());
                  $row_getMenueC = mysql_fetch_assoc($getMenueC);
                  $getMenueCRows  = mysql_num_rows($getMenueC);
                  
                  $menuearr = array();
                  if($getMenueCRows > 0) {

                    do {

                      $query_getCatChng = "SELECT * FROM categories WHERE cat_institution = '".$colname_getUser2."' && cat_id = '".$row_getMenueC['menue_cat']."' LIMIT 1";
                      $getCatChng = mysql_query($query_getCatChng, $echoloyalty) or die(mysql_error());
                      $row_getCatChng = mysql_fetch_assoc($getCatChng);
                      $getCatChngRows  = mysql_num_rows($getCatChng);
                      
                      array_push($menuearr, array($row_getMenueC['menue_id'], $row_getMenueC['menue_name'], $row_getMenueC['menue_desc'], $row_getMenueC['menue_pic'], $row_getMenueC['menue_institution'], $row_getMenueC['menue_when'], $row_getCatChng['cat_name'], $row_getMenueC['menue_size'], $row_getMenueC['menue_cost'], $row_getMenueC['menue_weight'], $row_getMenueC['menue_discount'], $row_getMenueC['menue_action'], $row_getMenueC['menue_code'], $row_getCatChng['cat_ingr'], $row_getMenueC['menue_cat']));
                        
                    } while ($row_getMenueC = mysql_fetch_assoc($getMenueC));

                  }
                  // CATEGORIES
                  $query_getCatC = "SELECT * FROM categories WHERE cat_institution = '".$colname_getUser2."' && cat_when > '1'";
                  $getCatC = mysql_query($query_getCatC, $echoloyalty) or die(mysql_error());
                  $row_getCatC = mysql_fetch_assoc($getCatC);
                  $getCatCRows  = mysql_num_rows($getCatC);
                  
                  $catarr = array();
                  if($getCatCRows > 0) {

                      do {
                          
                          array_push($catarr, array("cat_id" => $row_getCatC['cat_id'], "cat_name" => $row_getCatC['cat_name'], "cat_desc" => $row_getCatC['cat_desc'], "cat_pic" => $row_getCatC['cat_pic'], "cat_ingr" => $row_getCatC['cat_ingr'], "cat_inst" => $row_getCatC['cat_institution'], "cat_when" => $row_getCatC['cat_when']));
                          
                      } while ($row_getCatC = mysql_fetch_assoc($getCatC));

                  }
                  // GOODS
                  $query_getGroupC = "SELECT * FROM goods WHERE goods_institution = '".$colname_getUser2."' && goods_when > '1'";
                  $getGroupC = mysql_query($query_getGroupC, $echoloyalty) or die(mysql_error());
                  $row_getGroupC = mysql_fetch_assoc($getGroupC);
                  $getGroupCRows  = mysql_num_rows($getGroupC);
                  
                  $grouparr = array();
                  if($getGroupCRows > 0) {

                      do {
                          
                          array_push($grouparr, array("goods_id" => $row_getGroupC['goods_id'], "goods_name" => $row_getGroupC['goods_name'], "goods_desc" => $row_getGroupC['goods_desc'], "goods_pic" => $row_getGroupC['goods_pic'], "goods_institution" => $row_getGroupC['goods_institution'], "goods_when" => $row_getGroupC['goods_when']));
                          
                      } while ($row_getGroupC = mysql_fetch_assoc($getGroupC));

                  }
                  // SCHEDULE
                  $query_getSchedule = "SELECT * FROM schedule WHERE schedule_institution = '".$colname_getUser2."' && schedule_when > '2'";
                  $getSchedule = mysql_query($query_getSchedule, $echoloyalty) or die(mysql_error());
                  $row_getSchedule = mysql_fetch_assoc($getSchedule);
                  $getScheduleRows = mysql_num_rows($getSchedule);
                          
                  $schedulearr = array();
                  if($getScheduleRows > 0) {

                    do {
                      
                      array_push($schedulearr, array("schedule_id" => $row_getSchedule['schedule_id'], "schedule_employee" => $row_getSchedule['schedule_employee'], "schedule_menue" => $row_getSchedule['schedule_menue'], "schedule_office" => $row_getSchedule['schedule_office'], "schedule_start" => $row_getSchedule['schedule_start'], "schedule_stop" => $row_getSchedule['schedule_stop'], "schedule_institution" => $row_getSchedule['schedule_institution'], "schedule_when" => $row_getSchedule['schedule_when']));
                    
                    } while ($row_getSchedule = mysql_fetch_assoc($getSchedule));

                  }
        		  
        				  $newarrmes = array("orderC" => $getOrderRows, "instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "orderAll" => $orderarr, "workersAll" => $usrsarr, "officeAll" => $officearr, "menueAll" => $menuearr, "catsAll" => $catarr, "goodsAll" => $grouparr, "scheduleAll" => $schedulearr);
        				  array_push($gotdata, $newarrmes);
              
              }
      
            }
            else if($colname_getUser4 == 'schedule') {
        
              if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                  $colname_scheduleid = "-1";
                  if (isset($themsg['scheduleid'])) {
                    $colname_scheduleid = $protect($themsg['scheduleid']);
                  }
                  $colname_scheduleemployee = "-1";
                  if (isset($themsg['scheduleemployee'])) {
                    $colname_scheduleemployee = $protect($themsg['scheduleemployee']);
                  }
                  $colname_schedulemenue = "-1";
                  if (isset($themsg['schedulemenue'])) {
                    $colname_schedulemenue = $protect($themsg['schedulemenue']);
                  }
                  $colname_scheduleoffice = "-1";
                  if (isset($themsg['scheduleoffice'])) {
                    $colname_scheduleoffice = $protect($themsg['scheduleoffice']);
                  }
                  $colname_schedulestart = "-1";
                  if (isset($themsg['schedulestart'])) {
                    $colname_schedulestart = $protect($themsg['schedulestart']);
                  }
                  $colname_schedulestop = "-1";
                  if (isset($themsg['schedulestop'])) {
                    $colname_schedulestop = $protect($themsg['schedulestop']);
                  }
                    
                  if($colname_getUser5 == 'del') {

                      $query_getScheduleChng = "SELECT * FROM schedule WHERE schedule_institution = '".$colname_getUser2."' && schedule_id = '".$colname_scheduleid."' LIMIT 1";
                      $getScheduleChng = mysql_query($query_getScheduleChng, $echoloyalty) or die(mysql_error());
                      $row_getScheduleChng = mysql_fetch_assoc($getScheduleChng);
                      $getScheduleChngRows  = mysql_num_rows($getScheduleChng);

                      if($getScheduleChngRows > 0) {

                          $delSchedule = "UPDATE schedule SET schedule_when='1' WHERE schedule_institution = '".$colname_getUser2."' && schedule_id='".$colname_scheduleid."'";
                          mysql_query($delSchedule, $echoloyalty) or die(mysql_error());

                          $newarrmes = array("requests" => '1', "scheduleId" => $colname_scheduleid, "scheduleDel" => '2');
                          array_push($gotdata, $newarrmes);

                      }

                  }
                  else if($colname_getUser5 == 'create') {

                    if($colname_scheduleid == '0') {

                      $insSchedule = "INSERT INTO schedule (schedule_employee, schedule_menue, schedule_office, schedule_start, schedule_stop, schedule_institution, schedule_when) VALUES ('".$colname_scheduleemployee."', '".$colname_schedulemenue."', '".$colname_scheduleoffice."', '".$colname_schedulestart."', '".$colname_schedulestop."', '".$colname_getUser2."', '".$when."')";
                      mysql_query($insSchedule, $echoloyalty) or die(mysql_error());

                      $query_getScheduleChng = "SELECT * FROM schedule WHERE schedule_institution = '".$colname_getUser2."' && schedule_when = '".$when."' ORDER BY schedule_id DESC LIMIT 1";
                      $getScheduleChng = mysql_query($query_getScheduleChng, $echoloyalty) or die(mysql_error());
                      $row_getScheduleChng = mysql_fetch_assoc($getScheduleChng);
                      $getScheduleChngRows  = mysql_num_rows($getScheduleChng);

                      if($getScheduleChngRows > 0) {

                        $newarrmes = array("requests" => '1', "orderId" => $row_getScheduleChng['schedule_id'], "orderIns" => '1', 'when' => $when);
                        array_push($gotdata, $newarrmes);

                      }

                    }

                  }
                  else if($colname_getUser5 == 'change') {

                    if($colname_scheduleid != '0') {
                    
                      $query_getScheduleChng = "SELECT * FROM schedule WHERE schedule_institution = '".$colname_getUser2."' && schedule_id = '".$colname_scheduleid."' ORDER BY schedule_id DESC LIMIT 1";
                      $getScheduleChng = mysql_query($query_getScheduleChng, $echoloyalty) or die(mysql_error());
                      $row_getScheduleChng = mysql_fetch_assoc($getScheduleChng);
                      $getScheduleChngRows  = mysql_num_rows($getScheduleChng);

                      if($getScheduleChngRows > 0) {

                          $updSchedule = "UPDATE schedule SET schedule_employee='".$colname_scheduleemployee."', schedule_menue='".$colname_schedulemenue."', schedule_office='".$colname_scheduleoffice."', schedule_start='".$colname_schedulestart."', schedule_stop='".$colname_schedulestop."', schedule_when='".$when."' WHERE schedule_id = '".$colname_scheduleid."'";
                           mysql_query($updSchedule, $echoloyalty) or die(mysql_error());

                          $newarrmes = array("requests" => '1', "orderId" => $colname_scheduleid, "orderUpd" => '1', "when" => $when);
                          array_push($gotdata, $newarrmes);

                      }

                    }
                    
                  }
                
              }
              else {

                  $query_getSchedule = "SELECT * FROM schedule WHERE schedule_institution = '".$colname_getUser2."' && schedule_when > '2'";
                  $getSchedule = mysql_query($query_getSchedule, $echoloyalty) or die(mysql_error());
                  $row_getSchedule = mysql_fetch_assoc($getSchedule);
                  $getScheduleRows = mysql_num_rows($getSchedule);
                          
                  $schedulearr = array();
                  if($getScheduleRows > 0) {

                    do {

                      $org_office = $row_getInst['org_name'];
                      $org_office_id = $row_getInst['org_id'];

                      $query_getOfficeData = "SELECT * FROM organizations_office WHERE office_id = '".$row_getSchedule['schedule_office']."'";
                      $getOfficeData = mysql_query($query_getOfficeData, $echoloyalty) or die(mysql_error());
                      $row_getOfficeData = mysql_fetch_assoc($getOfficeData);
                      $getOfficeDataRows  = mysql_num_rows($getOfficeData);

                      if($getOfficeDataRows > 0) {
                        $org_office = $row_getOfficeData['office_name'];
                        $org_office_id = $row_getOfficeData['office_id'];
                      }
                      
                      array_push($schedulearr, array("schedule_id" => $row_getSchedule['schedule_id'], "schedule_employee" => $row_getSchedule['schedule_employee'], "schedule_menue" => $row_getSchedule['schedule_menue'], "schedule_start" => $row_getSchedule['schedule_start'], "schedule_stop" => $row_getSchedule['schedule_stop'], "schedule_institution" => $row_getSchedule['schedule_institution'], "schedule_when" => $row_getSchedule['schedule_when']));
                    
                    } while ($row_getSchedule = mysql_fetch_assoc($getSchedule));

                  }

                  $query_getUsersC = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_work_pos >= '2'";
                  $getUsersC = mysql_query($query_getUsersC, $echoloyalty) or die(mysql_error());
                  $row_getUsersC = mysql_fetch_assoc($getUsersC);
                  $getUsersCRows  = mysql_num_rows($getUsersC);
          
                  $usrsarr = array();
                  if($getUsersCRows > 0) {

                    do {

                      // GET PROFESSION
                      $query_getProf = "SELECT * FROM professions WHERE prof_id = '".$row_getUsersC['user_work_pos']."' && prof_when > '2' && (prof_institution = '0' OR prof_institution = '".$colname_getUser2."')";
                      $getProf = mysql_query($query_getProf, $echoloyalty) or die(mysql_error());
                      $row_getUProf = mysql_fetch_assoc($getProf);
                      $getProfRows  = mysql_num_rows($getProf);

                      $prof = '';
                      if($getProfRows > 0) {
                        $prof = $row_getUProf['prof_name'];
                      }

                      array_push($usrsarr, array("user_id" => $row_getUsersC['user_id'], "user_name" => $row_getUsersC['user_name'], "user_surname" => $row_getUsersC['user_surname'], "user_middlename" => $row_getUsersC['user_middlename'], "user_mob" => $row_getUsersC['user_mob'], "user_institution" => $row_getUsersC['user_institution'], "user_pic" => $row_getUsersC['user_pic'], "user_profession" => $prof));

                    } while ($row_getUsersC = mysql_fetch_assoc($getUsersC));

                  }
              
                  $newarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "scheduleAll" => $schedulearr, "workersAll" => $usrsarr);
                  array_push($gotdata, $newarrmes);
              
              }
      
            }
            else if($colname_getUser4 == 'professions') {
              
              if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                $colname_title = "-1";
                if (isset($themsg['title'])) {
                  $colname_title = $protect($themsg['title']);
                }
                $colname_description = "-1";
                if (isset($themsg['description'])) {
                  $colname_description = $protect($themsg['description']);
                }
                $colname_profid = "-1";
                if (isset($themsg['profid'])) {
                  $colname_profid = $protect($themsg['profid']);
                }
                
                if($colname_getUser5 == 'del') {

                    $query_getProfChng = "SELECT * FROM professions WHERE prof_institution = '".$colname_getUser2."' && prof_id = '".$colname_profid."' LIMIT 1";
                    $getProfChng = mysql_query($query_getProfChng, $echoloyalty) or die(mysql_error());
                    $row_getProfChng = mysql_fetch_assoc($getProfChng);
                    $getProfChngRows  = mysql_num_rows($getProfChng);

                    if($getProfChngRows > 0) {

                        $delProf = "UPDATE professions SET prof_when='1' WHERE prof_institution = '".$colname_getUser2."' && prof_id='".$colname_profid."'";
                        mysql_query($delProf, $echoloyalty) or die(mysql_error());

                        $newarrmes = array("requests" => '1', "profId" => $colname_profid, "profDel" => '1');
                        array_push($gotdata, $newarrmes);

                    }

                }
                else if($colname_getUser5 == 'create') {

                  if($colname_profid == '0') {

                    $insProf = "INSERT INTO professions (prof_name, prof_desc, prof_institution, prof_when) VALUES ('".$colname_title."', '".$colname_description."', '".$colname_getUser2."', '".$when."')";
                    mysql_query($insProf, $echoloyalty) or die(mysql_error());

                    $query_getProfChng = "SELECT * FROM professions WHERE prof_institution = '".$colname_getUser2."' && prof_when = '".$when."' ORDER BY prof_id DESC LIMIT 1";
                    $getProfChng = mysql_query($query_getProfChng, $echoloyalty) or die(mysql_error());
                    $row_getProfChng = mysql_fetch_assoc($getProfChng);
                    $getProfChngRows  = mysql_num_rows($getProfChng);

                    if($getProfChngRows > 0) {

                      $newarrmes = array("requests" => '1', "profId" => $row_getProfChng['prof_id'], "profIns" => '1', 'when' => $when);
                      array_push($gotdata, $newarrmes);

                    }

                  }

                }
                else if($colname_getUser5 == 'change') {

                  if($colname_profid != '0') {
                  
                    $query_getProfChng = "SELECT * FROM professions WHERE prof_institution = '".$colname_getUser2."' && prof_id = '".$colname_profid."' ORDER BY prof_id DESC LIMIT 1";
                    $getProfChng = mysql_query($query_getProfChng, $echoloyalty) or die(mysql_error());
                    $row_getProfChng = mysql_fetch_assoc($getProfChng);
                    $getProfChngRows  = mysql_num_rows($getProfChng);

                    if($getProfChngRows > 0) {

                        $updProf = "UPDATE professions SET prof_name = '".$colname_title."', prof_desc = '".$colname_description."', prof_when = '".$when."'";
                         mysql_query($updProf, $echoloyalty) or die(mysql_error());

                        $newarrmes = array("requests" => '1', "profId" => $colname_profid, "profUpd" => '1', "when" => $when);
                        array_push($gotdata, $newarrmes);

                    }

                  }
                  
                }
        
              }
              else {
            
                $query_getProfC = "SELECT * FROM professions WHERE (prof_institution = '".$colname_getUser2."' OR prof_institution = '0') && prof_when > '2'";
                $getProfC = mysql_query($query_getProfC, $echoloyalty) or die(mysql_error());
                $row_getProfC = mysql_fetch_assoc($getProfC);
                $getProfCRows  = mysql_num_rows($getProfC);
            
                $profarr = array();
                if($getProfCRows > 0) {
                
                  do {
                    
                    array_push($profarr, array($row_getProfC['prof_id'], $row_getProfC['prof_name'], $row_getProfC['prof_desc'], $row_getProfC['prof_institution'], $row_getProfC['prof_when']));
                    
                  } while ($row_getProfC = mysql_fetch_assoc($getProfC));
                
                }
            
                $profarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "profAll" => $profarr);
                array_push($gotdata, $profarrmes);
                
              }
          
            }
            else if($colname_getUser4 == 'office') {
              
              if(isset($colname_getUser5) && $colname_getUser5 != '%') {

                $colname_office_id = "-1";
                if (isset($themsg['officeid'])) {
                  $colname_office_id = $protect($themsg['officeid']);
                }
                $colname_name = "-1";
                if (isset($themsg['name'])) {
                  $colname_name = $protect($themsg['name']);
                }
                $colname_start = "-1";
                if (isset($themsg['start'])) {
                  $colname_start = $protect($themsg['start']);
                }
                $colname_stop = "-1";
                if (isset($themsg['stop'])) {
                  $colname_stop = $protect($themsg['stop']);
                }
                $colname_country = "-1";
                if (isset($themsg['country'])) {
                  $colname_country = $protect($themsg['country']);
                }
                $colname_city = "-1";
                if (isset($themsg['city'])) {
                  $colname_city = $protect($themsg['city']);
                }
                $colname_adress = "-1";
                if (isset($themsg['adress'])) {
                  $colname_adress = $protect($themsg['adress']);
                }
                $colname_timezone = "-1";
                if (isset($themsg['timezone'])) {
                  $colname_timezone = $protect($themsg['timezone']);
                }
                $colname_tel = "-1";
                if (isset($themsg['tel'])) {
                  $colname_tel = $protect($themsg['tel']);
                }
                $colname_fax = "-1";
                if (isset($themsg['fax'])) {
                  $colname_fax = $protect($themsg['fax']);
                }
                $colname_mob = "-1";
                if (isset($themsg['mob'])) {
                  $colname_mob = $protect($themsg['mob']);
                }
                $colname_email = "-1";
                if (isset($themsg['email'])) {
                  $colname_email = $protect($themsg['email']);
                }
                $colname_pwd = "-1";
                if (isset($themsg['pwd'])) {
                  $colname_pwd = $protect($themsg['pwd']);
                }
                $colname_skype = "-1";
                if (isset($themsg['skype'])) {
                  $colname_skype = $protect($themsg['skype']);
                }
                $colname_site = "-1";
                if (isset($themsg['site'])) {
                  $colname_site = $protect($themsg['site']);
                }
                $colname_tax_id = "-1";
                if (isset($themsg['tax_id'])) {
                  $colname_tax_id = $protect($themsg['tax_id']);
                }
                
                if($colname_getUser5 == 'del') {

                  if($colname_office_id > 0) {

                    $query_getOffice = "SELECT * FROM organizations_office WHERE office_institution = '".$colname_getUser2."' ORDER BY office_id DESC LIMIT 1";
                    $getOffice = mysql_query($query_getOffice, $echoloyalty) or die(mysql_error());
                    $row_getOffice = mysql_fetch_assoc($getOffice);
                    $getOfficeRows  = mysql_num_rows($getOffice);

                    if($getOfficeRows > 0) {

                        $updOffice = "UPDATE organizations_office SET office_log='".$when."', office_reg='1' WHERE office_institution='".$colname_getUser2."' && office_id='".$colname_office_id."'";
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
            
                $query_getOffice = "SELECT * FROM organizations_office WHERE office_institution = '".$colname_getUser2."' && office_reg > '2'";
                $getOffice = mysql_query($query_getOffice, $echoloyalty) or die(mysql_error());
                $row_getOffice = mysql_fetch_assoc($getOffice);
                $getOfficeRows = mysql_num_rows($getOffice);
            
                $officearr = array();
                if($getOfficeRows > 0) {
                
                  do {
                    
                    array_push($officearr, array($row_getOffice['office_id'], $row_getOffice['office_name'], $row_getOffice['office_start'], $row_getOffice['office_stop'], $row_getOffice['office_country'], $row_getOffice['office_city'], $row_getOffice['office_adress'], $row_getOffice['office_timezone'], $row_getOffice['office_tel'], $row_getOffice['office_fax'], $row_getOffice['office_mob'], $row_getOffice['office_email'], $row_getOffice['office_pwd'], $row_getOffice['office_skype'], $row_getOffice['office_site'], $row_getOffice['office_tax_id'], $row_getOffice['office_logo'], $row_getOffice['office_institution'], $row_getOffice['office_log'], $row_getOffice['office_reg']));
                    
                  } while ($row_getOffice = mysql_fetch_assoc($getOffice));
                
                }
            
                $profarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "officeAll" => $officearr);
                array_push($gotdata, $profarrmes);
                
              }
          
            }

            // SELECT RECEIVER AND SEND
            $numRecv = count($clients) - 1;
            // echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

            foreach ($clients as $client) {
                if ($from === $client) {
                    // The sender is not the receiver, send to each client connected
                    $client->send(json_encode($gotdata));
                }
            }

        }
        // APPLICATION
        else if (isset($colname_getUser6) && $colname_getUser6 != '' && $colname_getUser6 != -1) {

            $colname_getGCM = "-1";
            if (isset($themsg['gcm'])) {
              $colname_getGCM = $themsg['gcm'];
            }
            $colname_getDevice = "-1";
            if (isset($themsg['device'])) {
              $colname_getDevice = $themsg['device'];
            }
            $colname_getDeviceId = "-1";
            if (isset($themsg['device_id'])) {
              $colname_getDeviceId = $themsg['device_id'];
            }
            $colname_getDeviceVersion = "-1";
            if (isset($themsg['device_version'])) {
              $colname_getDeviceVersion = $themsg['device_version'];
            }
            $colname_getDeviceOS = "-1";
            if (isset($themsg['device_os'])) {
              $colname_getDeviceOS = $themsg['device_os'];
            }
            $colname_getInst = "-1";
            if (isset($themsg['inst_id'])) {
              $colname_getInst = $themsg['inst_id'];
            }

            if($echoloyalty) {

              $query_getUserDevice = "SELECT * FROM users WHERE user_device_id = '".$colname_getDeviceId."' && user_institution = '".$colname_getInst."' LIMIT 1";
              $getUserDevice = mysql_query($query_getUserDevice, $echoloyalty) or die(mysql_error());
              $row_getUserDevice = mysql_fetch_assoc($getUserDevice);
              $getUserDeviceRows  = mysql_num_rows($getUserDevice);

            }

            if($getUserDeviceRows > 0 && $colname_getUser6 == 'newusr' && $echoloyalty) {

                $query_getNewUser = "SELECT * FROM users WHERE user_device_id = '".$colname_getDeviceId."' && user_institution = '".$colname_getInst."' LIMIT 1";
                $getNewUser = mysql_query($query_getNewUser, $echoloyalty) or die(mysql_error());
                $row_getNewUser = mysql_fetch_assoc($getNewUser);
                $getNewUserRows  = mysql_num_rows($getNewUser);

                // GET USER
                $usrData = array("user_id" => $row_getNewUser['user_id'], "user_name" => $row_getNewUser['user_name'], "user_surname" => $row_getNewUser['user_surname'], "user_middlename" => $row_getNewUser['user_middlename'], "user_email" => $row_getNewUser['user_email'], "user_email_confirm" => $row_getNewUser['user_email_confirm'], "user_pwd" => $row_getNewUser['user_pwd'], "user_tel" => $row_getNewUser['user_tel'], "user_mob" => $row_getNewUser['user_mob'], "user_mob_confirm" => $row_getNewUser['user_mob_confirm'], "user_work_pos" => $row_getNewUser['user_work_pos'], "user_menue_exe" => $row_getNewUser['user_menue_exe'], "user_institution" => $row_getNewUser['user_institution'], "user_pic" => $row_getNewUser['user_pic'], "user_gender" => $row_getNewUser['user_gender'], "user_birthday" => $row_getNewUser['user_birthday'], "user_country" => $row_getNewUser['user_country'], "user_region" => $row_getNewUser['user_region'], "user_city" => $row_getNewUser['user_city'], "user_adress" => $row_getNewUser['user_adress'], "user_install_where" => $row_getNewUser['user_install_where'], "user_log_key" => $row_getNewUser['user_log_key'], "user_gcm" => $row_getNewUser['user_gcm'], "user_device" => $row_getNewUser['user_device'], "user_device_id" => $row_getNewUser['user_device_id'], "user_device_version" => $row_getNewUser['user_device_version'], "user_device_os" => $row_getNewUser['user_device_os'], "user_discount" => $row_getNewUser['user_discount'], "user_promo" => $row_getNewUser['user_promo'], "user_log" => $row_getNewUser['user_log'], "user_upd" => $row_getNewUser['user_upd'], "user_reg" => $row_getNewUser['user_reg']);

                // GET POINTS
                $query_getPoints = "SELECT * FROM points WHERE points_user = '".$row_getNewUser['user_id']."' LIMIT 1";
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
                $query_getWallet = "SELECT * FROM wallet WHERE wallet_user = '".$row_getNewUser['user_id']."' LIMIT 1";
                $getWallet = mysql_query($query_getWallet, $echoloyalty) or die(mysql_error());
                $row_getWallet = mysql_fetch_assoc($getWallet);
                $getWalletRows  = mysql_num_rows($getWallet);

                $walletArr = array();

                if($getWalletRows > 0) {

                    array_push($walletArr, array("wallet_id" => $row_getWallet['wallet_id'], "wallet_user" => $row_getWallet['wallet_user'], "wallet_institution" => $row_getWallet['wallet_institution'], "wallet_total" => $row_getWallet['wallet_total'], "wallet_when" => $row_getWallet['wallet_when']));

                }

                // GET GOODS
                $query_getGoods = "SELECT * FROM goods WHERE goods_institution = '".$row_getNewUser['user_institution']."'";
                $getGoods = mysql_query($query_getGoods, $echoloyalty) or die(mysql_error());
                $row_getGoods = mysql_fetch_assoc($getGoods);
                $getGoodsRows  = mysql_num_rows($getGoods);

                $goodsArr = array();

                if($getGoodsRows > 0) {

                    do {

                        if($row_getGoods['goods_when'] == '1') {
                            array_push($goodsArr, array("goods_id" => $row_getGoods['goods_id'], "goods_when" => $row_getGoods['goods_when']));
                        }
                        else {

                            array_push($goodsArr, array("goods_id" => $row_getGoods['goods_id'], "goods_name" => $row_getGoods['goods_name'], "goods_desc" => $row_getGoods['goods_desc'], "goods_pic" => $row_getGoods['goods_pic'], "goods_institution" => $row_getGoods['goods_institution'], "goods_when" => $row_getGoods['goods_when']));
                        }

                    } while ($row_getGoods = mysql_fetch_assoc($getGoods));

                }

                // GET CATEGORIES
                $query_getCat = "SELECT * FROM categories WHERE cat_institution = '".$row_getNewUser['user_institution']."'";
                $getCat = mysql_query($query_getCat, $echoloyalty) or die(mysql_error());
                $row_getCat = mysql_fetch_assoc($getCat);
                $getCatRows  = mysql_num_rows($getCat);

                $catArr = array();

                if($getCatRows > 0) {

                    do {

                        if($row_getCat['cat_when'] == '1') {
                            array_push($catArr, array("cat_id" => $row_getCat['cat_id'], "cat_when" => $row_getCat['cat_when']));
                        }
                        else {

                            array_push($catArr, array("cat_id" => $row_getCat['cat_id'], "cat_name" => $row_getCat['cat_name'], "cat_desc" => $row_getCat['cat_desc'], "cat_pic" => $row_getCat['cat_pic'], "cat_ingr" => $row_getCat['cat_ingr'], "cat_institution" => $row_getCat['cat_institution'], "cat_when" => $row_getCat['cat_when']));
                        }

                    } while ($row_getCat = mysql_fetch_assoc($getCat));

                }

                // GET MENUE
                $query_getMenue = "SELECT * FROM menue WHERE menue_institution = '".$row_getNewUser['user_institution']."'";
                $getMenue = mysql_query($query_getMenue, $echoloyalty) or die(mysql_error());
                $row_getMenue = mysql_fetch_assoc($getMenue);
                $getMenueRows  = mysql_num_rows($getMenue);

                $menueArr = array();

                if($getMenueRows > 0) {

                    do {

                        if($row_getMenue['menue_when'] == '1') {
                            array_push($menueArr, array("menue_id" => $row_getMenue['menue_id'], "menue_when" => $row_getMenue['menue_when']));
                        }
                        else {
                            array_push($menueArr, array("menue_id" => $row_getMenue['menue_id'], "menue_cat" => $row_getMenue['menue_cat'], "menue_name" => $row_getMenue['menue_name'], "menue_desc" => $row_getMenue['menue_desc'], "menue_size" => $row_getMenue['menue_size'], "menue_cost" => $row_getMenue['menue_cost'], "menue_ingr" => $row_getMenue['menue_ingr'], "menue_weight" => $row_getMenue['menue_weight'], "menue_interval" => $row_getMenue['menue_interval'], "menue_discount" => $row_getMenue['menue_discount'], "menue_action" => $row_getMenue['menue_action'], "menue_code" => $row_getMenue['menue_code'], "menue_pic" => $row_getMenue['menue_pic'], "menue_institution" => $row_getMenue['menue_institution'], "menue_when" => $row_getMenue['menue_when']));
                        }

                    } while ($row_getMenue = mysql_fetch_assoc($getMenue));

                }
        
                // GET GIFTS
                $query_getGifts = "SELECT * FROM gifts WHERE gifts_institution = '".$row_getNewUser['user_institution']."'";
                $getGifts = mysql_query($query_getGifts, $echoloyalty) or die(mysql_error());
                $row_getGifts = mysql_fetch_assoc($getGifts);
                $getGiftsRows  = mysql_num_rows($getGifts);

                $giftsArr = array();

                if($getGiftsRows > 0) {

                    // IF FIRST GIFT IS USED OR NOT
                    $waiterCheck = 'First Gift';
                    $query_getTransWaiter = "SELECT * FROM transactions WHERE trans_waitercheck = '".$waiterCheck."' && trans_usrid = '".$row_getUserDevice['user_id']."'";
                    $getTransWaiter = mysql_query($query_getTransWaiter, $echoloyalty) or die(mysql_error());
                    $row_getTransWaiter = mysql_fetch_assoc($getTransWaiter);
                    $getTransWaiterRows  = mysql_num_rows($getTransWaiter);

                    $firstGift = false;
                    if($getTransWaiterRows > 0) {
                        // FIRST GIFT USED
                        $firstGift = true;
                    }

                    do {

                        if($row_getMenue['menue_when'] == '1') {
                            array_push($giftsArr, array("menue_id" => $row_getMenue['menue_id'], "menue_when" => $row_getMenue['menue_when']));
                        }
                        else {
                            // IF FIRST GIFT USED
                            $giftTime = $row_getGifts['gifts_when'];
                            if($row_getGifts['gifts_when'] == '2' && $firstGift) {
                                $giftTime = $when;
                            }

                            array_push($giftsArr, array("gifts_id" => $row_getGifts['gifts_id'], "gifts_name" => $row_getGifts['gifts_name'], "gifts_desc" => $row_getGifts['gifts_desc'], "gifts_points" => $row_getGifts['gifts_points'], "gifts_pic" => $row_getGifts['gifts_pic'], "gifts_institution" => $row_getGifts['gifts_institution'], "gifts_when" => $giftTime));
                        }

                    } while ($row_getGifts = mysql_fetch_assoc($getGifts));

                }
        
                // GET INGREDIENTS
                $query_getIngr = "SELECT * FROM ingredients WHERE ingr_institution = '".$row_getNewUser['user_institution']."'";
                $getIngr = mysql_query($query_getIngr, $echoloyalty) or die(mysql_error());
                $row_getIngr = mysql_fetch_assoc($getIngr);
                $getIngrRows  = mysql_num_rows($getIngr);

                $ingrArr = array();

                if($getIngrRows > 0) {

                    do {

                        if($row_getIngr['ingr_when'] == '1') {
                            array_push($ingrArr, array("ingr_id" => $row_getIngr['ingr_id'], "ingr_when" => $row_getIngr['ingr_when']));
                        }
                        else {
                            array_push($ingrArr, array("ingr_id" => $row_getIngr['ingr_id'], "ingr_name" => $row_getIngr['ingr_name'], "ingr_desc" => $row_getIngr['ingr_desc'], "ingr_cat" => $row_getIngr['ingr_cat'], "ingr_pic" => $row_getIngr['ingr_pic'], "ingr_cost" => $row_getIngr['ingr_cost'], "ingr_institution" => $row_getIngr['ingr_institution'], "ingr_when" => $row_getIngr['ingr_when']));
                        }

                    } while ($row_getIngr = mysql_fetch_assoc($getIngr));

                }

                // GET NEWS
                $query_getNews = "SELECT * FROM news WHERE news_institution = '".$row_getNewUser['user_institution']."' && news_state = '1'";
                $getNews = mysql_query($query_getNews, $echoloyalty) or die(mysql_error());
                $row_getNews = mysql_fetch_assoc($getNews);
                $getNewsRows  = mysql_num_rows($getNews);

                $newsArr = array();

                if($getNewsRows > 0) {

                    do {

                        if($row_getNews['news_state'] == '0') {
                            array_push($newsArr, array("news_id" => $row_getNews['news_id'], "news_state" => $row_getNews['news_state'], "news_when" => $row_getNews['news_when']));
                        }
                        else {
                            array_push($newsArr, array("news_id" => $row_getNews['news_id'], "news_name" => $row_getNews['news_name'], "news_message" => $row_getNews['news_message'], "news_pic" => $row_getNews['news_pic'], "news_institution" => $row_getNews['news_institution'], "news_state" => $row_getNews['news_state'], "news_when" => $row_getNews['news_when']));
                        }

                    } while ($row_getNews = mysql_fetch_assoc($getNews));

                }

                // GET REVIEWS
                $query_getReviews = "SELECT * FROM reviews WHERE reviews_institution = '".$row_getNewUser['user_institution']."' && reviews_when > '1' && reviews_from = '".$row_getNewUser['user_id']."' OR reviews_institution = '".$row_getNewUser['user_institution']."' && reviews_when > '1' && reviews_to = '".$row_getNewUser['user_id']."'";
                $getReviews = mysql_query($query_getReviews, $echoloyalty) or die(mysql_error());
                $row_getReviews = mysql_fetch_assoc($getReviews);
                $getReviewsRows  = mysql_num_rows($getReviews);

                $reviewsArr = array();

                if($getReviewsRows > 0) {

                    do {

                        array_push($reviewsArr, array("reviews_id" => $row_getReviews['reviews_id'], "reviews_from" => $row_getReviews['reviews_from'], "reviews_to" => $row_getReviews['reviews_to'], "reivews_message" => $row_getReviews['reivews_message'], "reviews_pic" => $row_getReviews['reviews_pic'], "reviews_institution" => $row_getReviews['reviews_institution'], "reviews_answered" => $row_getReviews['reviews_answered'], "reviews_when" => $row_getReviews['reviews_when']));

                    } while ($row_getReviews = mysql_fetch_assoc($getReviews));

                }

                // GET ASKS
                $query_getAsks = "SELECT * FROM asks WHERE asks_institution = '".$row_getNewUser['user_institution']."' && asks_when > '1' ORDER BY asks_id DESC LIMIT 1";
                $getAsks = mysql_query($query_getAsks, $echoloyalty) or die(mysql_error());
                $row_getAsks = mysql_fetch_assoc($getAsks);
                $getAsksRows  = mysql_num_rows($getAsks);

                $asksArr = array();

                if($getAsksRows > 0) {

                    array_push($asksArr, array("asks_id" => $row_getAsks['asks_id'], "asks_name" => $row_getAsks['asks_name'], "asks_message" => $row_getAsks['asks_message'], "asks_yes" => $row_getAsks['asks_yes'], "asks_no" => $row_getAsks['asks_no'], "asks_institution" => $row_getAsks['asks_institution'], "asks_when" => $row_getAsks['asks_when']));

                }

                // GET CHAT
                $query_getChat = "SELECT * FROM chat WHERE chat_from = '".$row_getNewUser['user_id']."' && chat_institution = '".$row_getNewUser['user_institution']."' && chat_when > '1' OR chat_to = '".$row_getNewUser['user_id']."' && chat_institution = '".$row_getNewUser['user_institution']."' && chat_when > '1'";
                $getChat = mysql_query($query_getChat, $echoloyalty) or die(mysql_error());
                $row_getChat = mysql_fetch_assoc($getChat);
                $getChatRows  = mysql_num_rows($getChat);

                $chatArr = array();

                if($getChatRows > 0) {

                    do {

                        array_push($chatArr, array("chat_id" => $row_getChat['chat_id'], "chat_from" => $row_getChat['chat_from'], "chat_to" => $row_getChat['chat_to'], "chat_name" => $row_getChat['chat_name'], "chat_message" => $row_getChat['chat_message'], "chat_read" => $row_getChat['chat_read'], "chat_institution" => $row_getChat['chat_institution'], "chat_answered" => $row_getChat['chat_answered'], "chat_when" => $row_getChat['chat_when']));

                    } while ($row_getChat = mysql_fetch_assoc($getChat));

                }

                // GET ORDER (DEPRECATED)
                $orderArr = array();

                $newarrmes = array("newusr" => '1', "usrData" => $usrData, "pointsArr" => $pointsArr, "walletArr" => $walletArr, "catArr" => $catArr, "menueArr" => $menueArr, "ingrArr" => $ingrArr, "newsArr" => $newsArr, "reviewsArr" => $reviewsArr, "asksArr" => $asksArr, "giftsArr" => $giftsArr, "chatArr" => $chatArr, "goodsArr" => $goodsArr, "orderArr" => $orderArr);
                array_push($gotdata, $newarrmes);

            }
            else if($getUserDeviceRows == 0 && $colname_getUser6 == 'newusr' && $echoloyalty) {

                $insrtUsr = "INSERT INTO users (user_institution, user_gcm, user_device, user_device_id, user_device_version, user_device_os, user_log, user_upd, user_reg) VALUES ('".$colname_getInst."', '0', '".$colname_getDevice."', '".$colname_getDeviceId."', '".$colname_getDeviceVersion."', '".$colname_getDeviceOS."', '".$when."', '".$when."', '".$when."')";
                mysql_query($insrtUsr, $echoloyalty) or die(mysql_error());

                $query_getNewUser = "SELECT * FROM users WHERE user_device_id = '".$colname_getDeviceId."' && user_institution = '".$colname_getInst."' LIMIT 1";
                $getNewUser = mysql_query($query_getNewUser, $echoloyalty) or die(mysql_error());
                $row_getNewUser = mysql_fetch_assoc($getNewUser);
                $getNewUserRows  = mysql_num_rows($getNewUser);

                $startwallet = 100;

                if(isset($row_getInst['org_starting_points'])) {$startwallet = $row_getInst['org_starting_points'];}

                $insrtWallet = "INSERT INTO wallet (wallet_user, wallet_institution, wallet_total, wallet_when) VALUES ('".$row_getNewUser['user_id']."', '".$colname_getInst."', '".$startwallet."', '".$when."')";
                mysql_query($insrtWallet, $echoloyalty) or die(mysql_error());

                // GET USER
                $usrData = array("user_id" => $row_getNewUser['user_id'], "user_name" => $row_getNewUser['user_name'], "user_surname" => $row_getNewUser['user_surname'], "user_middlename" => $row_getNewUser['user_middlename'], "user_email" => $row_getNewUser['user_email'], "user_email_confirm" => $row_getNewUser['user_email_confirm'], "user_pwd" => $row_getNewUser['user_pwd'], "user_tel" => $row_getNewUser['user_tel'], "user_mob" => $row_getNewUser['user_mob'], "user_mob_confirm" => $row_getNewUser['user_mob_confirm'], "user_work_pos" => $row_getNewUser['user_work_pos'], "user_menue_exe" => $row_getNewUser['user_menue_exe'], "user_institution" => $row_getNewUser['user_institution'], "user_pic" => $row_getNewUser['user_pic'], "user_gender" => $row_getNewUser['user_gender'], "user_birthday" => $row_getNewUser['user_birthday'], "user_country" => $row_getNewUser['user_country'], "user_region" => $row_getNewUser['user_region'], "user_city" => $row_getNewUser['user_city'], "user_adress" => $row_getNewUser['user_adress'], "user_install_where" => $row_getNewUser['user_install_where'], "user_log_key" => $row_getNewUser['user_log_key'], "user_gcm" => $row_getNewUser['user_gcm'], "user_device" => $row_getNewUser['user_device'], "user_device_id" => $row_getNewUser['user_device_id'], "user_device_version" => $row_getNewUser['user_device_version'], "user_device_os" => $row_getNewUser['user_device_os'], "user_discount" => $row_getNewUser['user_discount'], "user_promo" => $row_getNewUser['user_promo'], "user_log" => $row_getNewUser['user_log'], "user_upd" => $row_getNewUser['user_upd'], "user_reg" => $row_getNewUser['user_reg']);

                // GET POINTS
                $query_getPoints = "SELECT * FROM points WHERE points_user = '".$row_getNewUser['user_id']."' LIMIT 1";
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
                $query_getWallet = "SELECT * FROM wallet WHERE wallet_user = '".$row_getNewUser['user_id']."' LIMIT 1";
                $getWallet = mysql_query($query_getWallet, $echoloyalty) or die(mysql_error());
                $row_getWallet = mysql_fetch_assoc($getWallet);
                $getWalletRows  = mysql_num_rows($getWallet);

                $walletArr = array();

                if($getWalletRows > 0) {

                    array_push($walletArr, array("wallet_id" => $row_getWallet['wallet_id'], "wallet_user" => $row_getWallet['wallet_user'], "wallet_institution" => $row_getWallet['wallet_institution'], "wallet_total" => $row_getWallet['wallet_total'], "wallet_when" => $row_getWallet['wallet_when']));

                }

                // GET GOODS
                $query_getGoods = "SELECT * FROM goods WHERE goods_institution = '".$row_getNewUser['user_institution']."'";
                $getGoods = mysql_query($query_getGoods, $echoloyalty) or die(mysql_error());
                $row_getGoods = mysql_fetch_assoc($getGoods);
                $getGoodsRows  = mysql_num_rows($getGoods);

                $goodsArr = array();

                if($getGoodsRows > 0) {

                    do {

                        if($row_getGoods['goods_when'] == '1') {
                            array_push($goodsArr, array("goods_id" => $row_getGoods['goods_id'], "goods_when" => $row_getGoods['goods_when']));
                        }
                        else {

                            array_push($goodsArr, array("goods_id" => $row_getGoods['goods_id'], "goods_name" => $row_getGoods['goods_name'], "goods_desc" => $row_getGoods['goods_desc'], "goods_pic" => $row_getGoods['goods_pic'], "goods_institution" => $row_getGoods['goods_institution'], "goods_when" => $row_getGoods['goods_when']));
                        }

                    } while ($row_getGoods = mysql_fetch_assoc($getGoods));

                }

                // GET CATEGORIES
                $query_getCat = "SELECT * FROM categories WHERE cat_institution = '".$row_getNewUser['user_institution']."'";
                $getCat = mysql_query($query_getCat, $echoloyalty) or die(mysql_error());
                $row_getCat = mysql_fetch_assoc($getCat);
                $getCatRows  = mysql_num_rows($getCat);

                $catArr = array();

                if($getCatRows > 0) {

                    do {

                        if($row_getCat['cat_when'] == '1') {
                            array_push($catArr, array("cat_id" => $row_getCat['cat_id'], "cat_when" => $row_getCat['cat_when']));
                        }
                        else {

                            array_push($catArr, array("cat_id" => $row_getCat['cat_id'], "cat_name" => $row_getCat['cat_name'], "cat_desc" => $row_getCat['cat_desc'], "cat_pic" => $row_getCat['cat_pic'], "cat_ingr" => $row_getCat['cat_ingr'], "cat_institution" => $row_getCat['cat_institution'], "cat_when" => $row_getCat['cat_when']));
                        }

                    } while ($row_getCat = mysql_fetch_assoc($getCat));

                }

                // GET MENUE
                $query_getMenue = "SELECT * FROM menue WHERE menue_institution = '".$row_getNewUser['user_institution']."'";
                $getMenue = mysql_query($query_getMenue, $echoloyalty) or die(mysql_error());
                $row_getMenue = mysql_fetch_assoc($getMenue);
                $getMenueRows  = mysql_num_rows($getMenue);

                $menueArr = array();

                if($getMenueRows > 0) {

                    do {

                        if($row_getMenue['menue_when'] == '1') {
                            array_push($menueArr, array("menue_id" => $row_getMenue['menue_id'], "menue_when" => $row_getMenue['menue_when']));
                        }
                        else {
                            array_push($menueArr, array("menue_id" => $row_getMenue['menue_id'], "menue_cat" => $row_getMenue['menue_cat'], "menue_name" => $row_getMenue['menue_name'], "menue_desc" => $row_getMenue['menue_desc'], "menue_size" => $row_getMenue['menue_size'], "menue_cost" => $row_getMenue['menue_cost'], "menue_ingr" => $row_getMenue['menue_ingr'], "menue_weight" => $row_getMenue['menue_weight'], "menue_interval" => $row_getMenue['menue_interval'], "menue_discount" => $row_getMenue['menue_discount'], "menue_action" => $row_getMenue['menue_action'], "menue_code" => $row_getMenue['menue_code'], "menue_pic" => $row_getMenue['menue_pic'], "menue_institution" => $row_getMenue['menue_institution'], "menue_when" => $row_getMenue['menue_when']));
                        }

                    } while ($row_getMenue = mysql_fetch_assoc($getMenue));

                }

                // GET GIFTS
                $query_getGifts = "SELECT * FROM gifts WHERE gifts_institution = '".$row_getNewUser['user_institution']."'";
                $getGifts = mysql_query($query_getGifts, $echoloyalty) or die(mysql_error());
                $row_getGifts = mysql_fetch_assoc($getGifts);
                $getGiftsRows  = mysql_num_rows($getGifts);

                $giftsArr = array();

                if($getGiftsRows > 0) {

                    do {

                        if($row_getMenue['menue_when'] == '1') {
                            array_push($giftsArr, array("menue_id" => $row_getMenue['menue_id'], "menue_when" => $row_getMenue['menue_when']));
                        }
                        else {
                            array_push($giftsArr, array("gifts_id" => $row_getGifts['gifts_id'], "gifts_name" => $row_getGifts['gifts_name'], "gifts_desc" => $row_getGifts['gifts_desc'], "gifts_points" => $row_getGifts['gifts_points'], "gifts_pic" => $row_getGifts['gifts_pic'], "gifts_institution" => $row_getGifts['gifts_institution'], "gifts_when" => $row_getGifts['gifts_when']));
                        }

                    } while ($row_getGifts = mysql_fetch_assoc($getGifts));

                }

                // GET INGREDIENTS
                $query_getIngr = "SELECT * FROM ingredients WHERE ingr_institution = '".$row_getNewUser['user_institution']."'";
                $getIngr = mysql_query($query_getIngr, $echoloyalty) or die(mysql_error());
                $row_getIngr = mysql_fetch_assoc($getIngr);
                $getIngrRows  = mysql_num_rows($getIngr);

                $ingrArr = array();

                if($getIngrRows > 0) {

                    do {

                        if($row_getIngr['ingr_when'] == '1') {
                            array_push($ingrArr, array("ingr_id" => $row_getIngr['ingr_id'], "ingr_when" => $row_getIngr['ingr_when']));
                        }
                        else {
                            array_push($ingrArr, array("ingr_id" => $row_getIngr['ingr_id'], "ingr_name" => $row_getIngr['ingr_name'], "ingr_desc" => $row_getIngr['ingr_desc'], "ingr_cat" => $row_getIngr['ingr_cat'], "ingr_pic" => $row_getIngr['ingr_pic'], "ingr_cost" => $row_getIngr['ingr_cost'], "ingr_institution" => $row_getIngr['ingr_institution'], "ingr_when" => $row_getIngr['ingr_when']));
                        }

                    } while ($row_getIngr = mysql_fetch_assoc($getIngr));

                }

                // GET NEWS
                $query_getNews = "SELECT * FROM news WHERE news_institution = '".$row_getNewUser['user_institution']."' && news_state = '1'";
                $getNews = mysql_query($query_getNews, $echoloyalty) or die(mysql_error());
                $row_getNews = mysql_fetch_assoc($getNews);
                $getNewsRows  = mysql_num_rows($getNews);

                $newsArr = array();

                if($getNewsRows > 0) {

                    do {

                        if($row_getNews['news_state'] == '0') {
                            array_push($newsArr, array("news_id" => $row_getNews['news_id'], "news_state" => $row_getNews['news_state'], "news_when" => $row_getNews['news_when']));
                        }
                        else {
                            array_push($newsArr, array("news_id" => $row_getNews['news_id'], "news_name" => $row_getNews['news_name'], "news_message" => $row_getNews['news_message'], "news_pic" => $row_getNews['news_pic'], "news_institution" => $row_getNews['news_institution'], "news_state" => $row_getNews['news_state'], "news_when" => $row_getNews['news_when']));
                        }

                    } while ($row_getNews = mysql_fetch_assoc($getNews));

                }

                // GET REVIEWS
                $query_getReviews = "SELECT * FROM reviews WHERE reviews_institution = '".$row_getNewUser['user_institution']."' && reviews_when > '1' && reviews_from = '".$row_getNewUser['user_id']."' OR reviews_institution = '".$row_getNewUser['user_institution']."' && reviews_when > '1' && reviews_to = '".$row_getNewUser['user_id']."'";
                $getReviews = mysql_query($query_getReviews, $echoloyalty) or die(mysql_error());
                $row_getReviews = mysql_fetch_assoc($getReviews);
                $getReviewsRows  = mysql_num_rows($getReviews);

                $reviewsArr = array();

                if($getReviewsRows > 0) {

                    do {

                        array_push($reviewsArr, array("reviews_id" => $row_getReviews['reviews_id'], "reviews_from" => $row_getReviews['reviews_from'], "reviews_to" => $row_getReviews['reviews_to'], "reivews_message" => $row_getReviews['reivews_message'], "reviews_pic" => $row_getReviews['reviews_pic'], "reviews_institution" => $row_getReviews['reviews_institution'], "reviews_answered" => $row_getReviews['reviews_answered'], "reviews_when" => $row_getReviews['reviews_when']));

                    } while ($row_getReviews = mysql_fetch_assoc($getReviews));

                }

                // GET ASKS
                $query_getAsks = "SELECT * FROM asks WHERE asks_institution = '".$row_getNewUser['user_institution']."' && asks_when > '1' ORDER BY asks_id DESC LIMIT 1";
                $getAsks = mysql_query($query_getAsks, $echoloyalty) or die(mysql_error());
                $row_getAsks = mysql_fetch_assoc($getAsks);
                $getAsksRows  = mysql_num_rows($getAsks);

                $asksArr = array();

                if($getAsksRows > 0) {

                    array_push($asksArr, array("asks_id" => $row_getAsks['asks_id'], "asks_name" => $row_getAsks['asks_name'], "asks_message" => $row_getAsks['asks_message'], "asks_yes" => $row_getAsks['asks_yes'], "asks_no" => $row_getAsks['asks_no'], "asks_institution" => $row_getAsks['asks_institution'], "asks_when" => $row_getAsks['asks_when']));

                }

                // GET CHAT
                $query_getChat = "SELECT * FROM chat WHERE chat_from = '".$row_getNewUser['user_id']."' && chat_institution = '".$row_getNewUser['user_institution']."' && chat_when > '1' OR chat_to = '".$row_getNewUser['user_id']."' && chat_institution = '".$row_getNewUser['user_institution']."' && chat_when > '1'";
                $getChat = mysql_query($query_getChat, $echoloyalty) or die(mysql_error());
                $row_getChat = mysql_fetch_assoc($getChat);
                $getChatRows  = mysql_num_rows($getChat);

                $chatArr = array();

                if($getChatRows > 0) {

                    do {

                        array_push($chatArr, array("chat_id" => $row_getChat['chat_id'], "chat_from" => $row_getChat['chat_from'], "chat_to" => $row_getChat['chat_to'], "chat_name" => $row_getChat['chat_name'], "chat_message" => $row_getChat['chat_message'], "chat_read" => $row_getChat['chat_read'], "chat_institution" => $row_getChat['chat_institution'], "chat_answered" => $row_getChat['chat_answered'], "chat_when" => $row_getChat['chat_when']));

                    } while ($row_getChat = mysql_fetch_assoc($getChat));

                }

                // GET ORDER (DEPRECATED)
                $orderArr = array();

                $newarrmes = array("newusr" => '1', "usrData" => $usrData, "pointsArr" => $pointsArr, "walletArr" => $walletArr, "catArr" => $catArr, "menueArr" => $menueArr, "ingrArr" => $ingrArr, "newsArr" => $newsArr, "reviewsArr" => $reviewsArr, "asksArr" => $asksArr, "giftsArr" => $giftsArr, "chatArr" => $chatArr, "goodsArr" => $goodsArr, "orderArr" => $orderArr);
                array_push($gotdata, $newarrmes);

            }
            else if($getUserDeviceRows > 0 && $colname_getUser6 == 'check' && $echoloyalty) {

                $colname_getLastPoints = "-1";
                if (isset($themsg['points'])) {
                  $colname_getLastPoints = $themsg['points'];
                }
                $colname_getLastWallet = "-1";
                if (isset($themsg['wallet'])) {
                  $colname_getLastWallet = $themsg['wallet'];
                }
                $colname_getLastGoods = "-1";
                if (isset($themsg['goods'])) {
                  $colname_getLastGoods = $themsg['goods'];
                }
                $colname_getLastCat = "-1";
                if (isset($themsg['cat'])) {
                  $colname_getLastCat = $themsg['cat'];
                }
                $colname_getLastMenue = "-1";
                if (isset($themsg['menue'])) {
                  $colname_getLastMenue = $themsg['menue'];
                }
                $colname_getLastIngrs = "-1";
                if (isset($themsg['ingrs'])) {
                  $colname_getLastIngrs = $themsg['ingrs'];
                }
                $colname_getLastNews = "-1";
                if (isset($themsg['news'])) {
                  $colname_getLastNews = $themsg['news'];
                }
                $colname_getLastRevs = "-1";
                if (isset($themsg['revs'])) {
                  $colname_getLastRevs = $themsg['revs'];
                }
                $colname_getLastAsks = "-1";
                if (isset($themsg['asks'])) {
                  $colname_getLastAsks = $themsg['asks'];
                }
                $colname_getLastGifts = "-1";
                if (isset($themsg['gifts'])) {
                  $colname_getLastGifts = $themsg['gifts'];
                }
                $colname_getLastChat = "-1";
                if (isset($themsg['chat'])) {
                  $colname_getLastChat = $themsg['chat'];
                }
                $colname_getLastOrder = "-1";
                if (isset($themsg['order'])) {
                  $colname_getLastOrder = $themsg['order'];
                }

                $pointsArr = array();
                $walletArr = array();
                $goodsArr = array();
                $catArr = array();
                $menueArr = array();
                $giftsArr = array();
                $ingrArr = array();
                $newsArr = array();
                $reviewsArr = array();
                $asksArr = array();
                $chatArr = array();
                // GET ORDER (DEPRECATED)
                $orderArr = array();

                  // GET POINTS
                  $query_getPoints = "SELECT * FROM points WHERE points_user = '".$row_getUserDevice['user_id']."' && points_when > '".$colname_getLastPoints."' OR points_user = '".$row_getUserDevice['user_id']."' && points_when = '1' ORDER BY points_id DESC";
                  $getPoints = mysql_query($query_getPoints, $echoloyalty) or die(mysql_error());
                  $row_getPoints = mysql_fetch_assoc($getPoints);
                  $getPointsRows  = mysql_num_rows($getPoints);

                  if($getPointsRows > 0) {

                      do {

                          array_push($pointsArr, array("points_id" => $row_getPoints['points_id'], "points_user" => $row_getPoints['points_user'], "points_bill" => $row_getPoints['points_bill'], "points_discount" => $row_getPoints['points_discount'], "points_points" => $row_getPoints['points_points'], "points_got_spend" => $row_getPoints['points_got_spend'], "points_waiter" => $row_getPoints['points_waiter'], "points_institution" => $row_getPoints['points_institution'], "points_status" => $row_getPoints['points_status'], "points_comment" => $row_getPoints['points_comment'], "points_proofed" => $row_getPoints['points_proofed'], "points_gift" => $row_getPoints['points_gift'], "points_when" => $row_getPoints['points_when']));

                      } while ($row_getPoints = mysql_fetch_assoc($getPoints));

                  }

                  // GET WALLET
                  $query_getWallet = "SELECT * FROM wallet WHERE wallet_user = '".$row_getUserDevice['user_id']."' LIMIT 1";
                  $getWallet = mysql_query($query_getWallet, $echoloyalty) or die(mysql_error());
                  $row_getWallet = mysql_fetch_assoc($getWallet);
                  $getWalletRows  = mysql_num_rows($getWallet);

                  if($getWalletRows > 0) {

                      array_push($walletArr, array("wallet_id" => $row_getWallet['wallet_id'], "wallet_user" => $row_getWallet['wallet_user'], "wallet_institution" => $row_getWallet['wallet_institution'], "wallet_total" => $row_getWallet['wallet_total'], "wallet_when" => $row_getWallet['wallet_when']));

                  }

                  // if($row_getUserDevice['user_log'] < $when-60*60*24) {

                    // GET GOODS
                    $query_getGoods = "SELECT * FROM goods WHERE goods_institution = '".$row_getUserDevice['user_institution']."' && goods_when > '".$colname_getLastGoods."' OR goods_institution = '".$row_getUserDevice['user_institution']."' && goods_when = '1'";
                    $getGoods = mysql_query($query_getGoods, $echoloyalty) or die(mysql_error());
                    $row_getGoods = mysql_fetch_assoc($getGoods);
                    $getGoodsRows  = mysql_num_rows($getGoods);

                    if($getGoodsRows > 0) {

                        do {

                            if($row_getGoods['goods_when'] == '1') {
                                array_push($goodsArr, array("goods_id" => $row_getGoods['goods_id'], "goods_when" => $row_getGoods['goods_when']));
                            }
                            else {

                                array_push($goodsArr, array("goods_id" => $row_getGoods['goods_id'], "goods_name" => $row_getGoods['goods_name'], "goods_desc" => $row_getGoods['goods_desc'], "goods_pic" => $row_getGoods['goods_pic'], "goods_institution" => $row_getGoods['goods_institution'], "goods_when" => $row_getGoods['goods_when']));
                            }

                        } while ($row_getGoods = mysql_fetch_assoc($getGoods));

                    }

                    // GET CATEGORIES
                    $query_getCat = "SELECT * FROM categories WHERE cat_institution = '".$row_getUserDevice['user_institution']."' && cat_when > '".$colname_getLastCat."' OR cat_institution = '".$row_getUserDevice['user_institution']."' && cat_when = '1'";
                    $getCat = mysql_query($query_getCat, $echoloyalty) or die(mysql_error());
                    $row_getCat = mysql_fetch_assoc($getCat);
                    $getCatRows  = mysql_num_rows($getCat);

                    if($getCatRows > 0) {

                        do {

                            if($row_getCat['cat_when'] == '1') {
                                array_push($catArr, array("cat_id" => $row_getCat['cat_id'], "cat_when" => $row_getCat['cat_when']));
                            }
                            else {
                                array_push($catArr, array("cat_id" => $row_getCat['cat_id'], "cat_name" => $row_getCat['cat_name'], "cat_desc" => $row_getCat['cat_desc'], "cat_pic" => $row_getCat['cat_pic'], "cat_ingr" => $row_getCat['cat_ingr'], "cat_institution" => $row_getCat['cat_institution'], "cat_when" => $row_getCat['cat_when']));
                            }

                        } while ($row_getCat = mysql_fetch_assoc($getCat));

                    }

                    // GET MENUE
                    $query_getMenue = "SELECT * FROM menue WHERE menue_institution = '".$row_getUserDevice['user_institution']."' && menue_when > '".$colname_getLastMenue."' OR menue_institution = '".$row_getUserDevice['user_institution']."' && menue_when = '1'";
                    $getMenue = mysql_query($query_getMenue, $echoloyalty) or die(mysql_error());
                    $row_getMenue = mysql_fetch_assoc($getMenue);
                    $getMenueRows  = mysql_num_rows($getMenue);

                    if($getMenueRows > 0) {

                        do {

                            if($row_getMenue['menue_when'] == '1') {
                                array_push($menueArr, array("menue_id" => $row_getMenue['menue_id'], "menue_when" => $row_getMenue['menue_when']));
                            }
                            else {
                                array_push($menueArr, array("menue_id" => $row_getMenue['menue_id'], "menue_cat" => $row_getMenue['menue_cat'], "menue_name" => $row_getMenue['menue_name'], "menue_desc" => $row_getMenue['menue_desc'], "menue_size" => $row_getMenue['menue_size'], "menue_cost" => $row_getMenue['menue_cost'], "menue_ingr" => $row_getMenue['menue_ingr'], "menue_weight" => $row_getMenue['menue_weight'], "menue_interval" => $row_getMenue['menue_interval'], "menue_discount" => $row_getMenue['menue_discount'], "menue_action" => $row_getMenue['menue_action'], "menue_code" => $row_getMenue['menue_code'], "menue_pic" => $row_getMenue['menue_pic'], "menue_institution" => $row_getMenue['menue_institution'], "menue_when" => $row_getMenue['menue_when']));
                            }

                        } while ($row_getMenue = mysql_fetch_assoc($getMenue));

                    }

                    // GET GIFTS
                    $query_getGifts = "SELECT * FROM gifts WHERE gifts_institution = '".$row_getUserDevice['user_institution']."' && gifts_when > '".$colname_getLastGifts."' OR gifts_institution = '".$row_getUserDevice['user_institution']."' && gifts_when <= '2'";
                    $getGifts = mysql_query($query_getGifts, $echoloyalty) or die(mysql_error());
                    $row_getGifts = mysql_fetch_assoc($getGifts);
                    $getGiftsRows  = mysql_num_rows($getGifts);

                    if($getGiftsRows > 0) {

                        // IF FIRST GIFT IS USED OR NOT
                        $waiterCheck = 'First Gift';
                        $query_getTransWaiter = "SELECT * FROM transactions WHERE trans_waitercheck = '".$waiterCheck."' && trans_usrid = '".$row_getUserDevice['user_id']."'";
                        $getTransWaiter = mysql_query($query_getTransWaiter, $echoloyalty) or die(mysql_error());
                        $row_getTransWaiter = mysql_fetch_assoc($getTransWaiter);
                        $getTransWaiterRows  = mysql_num_rows($getTransWaiter);

                        $firstGift = false;
                        if($getTransWaiterRows > 0) {
                            // FIRST GIFT USED
                            $firstGift = true;
                        }

                        do {

                            if($row_getGifts['gifts_when'] == '1') {
                                array_push($giftsArr, array("gifts_id" => $row_getGifts['gifts_id'], "gifts_when" => $row_getGifts['gifts_when']));
                            }
                            else {
                                // IF FIRST GIFT USED
                                $giftTime = $row_getGifts['gifts_when'];
                                if($row_getGifts['gifts_when'] == '2' && $firstGift) {
                                    $giftTime = $when;
                                }

                                array_push($giftsArr, array("gifts_id" => $row_getGifts['gifts_id'], "gifts_name" => $row_getGifts['gifts_name'], "gifts_desc" => $row_getGifts['gifts_desc'], "gifts_points" => $row_getGifts['gifts_points'], "gifts_pic" => $row_getGifts['gifts_pic'], "gifts_institution" => $row_getGifts['gifts_institution'], "gifts_when" => $giftTime));
                            }

                        } while ($row_getGifts = mysql_fetch_assoc($getGifts));

                    }

                    // GET INGREDIENTS
                    $query_getIngr = "SELECT * FROM ingredients WHERE ingr_institution = '".$row_getUserDevice['user_institution']."' && ingr_when > '".$colname_getLastIngrs."' OR ingr_institution = '".$row_getUserDevice['user_institution']."' && ingr_when = '1'";
                    $getIngr = mysql_query($query_getIngr, $echoloyalty) or die(mysql_error());
                    $row_getIngr = mysql_fetch_assoc($getIngr);
                    $getIngrRows  = mysql_num_rows($getIngr);

                    if($getIngrRows > 0) {

                        do {

                            if($row_getIngr['ingr_when'] == '1') {
                                array_push($ingrArr, array("ingr_id" => $row_getIngr['ingr_id'], "ingr_when" => $row_getIngr['ingr_when']));
                            }
                            else {
                                array_push($ingrArr, array("ingr_id" => $row_getIngr['ingr_id'], "ingr_name" => $row_getIngr['ingr_name'], "ingr_desc" => $row_getIngr['ingr_desc'], "ingr_cat" => $row_getIngr['ingr_cat'], "ingr_pic" => $row_getIngr['ingr_pic'], "ingr_cost" => $row_getIngr['ingr_cost'], "ingr_institution" => $row_getIngr['ingr_institution'], "ingr_when" => $row_getIngr['ingr_when']));
                            }

                        } while ($row_getIngr = mysql_fetch_assoc($getIngr));

                    }

                    $updMember = "UPDATE users SET user_log='".$when."' WHERE user_id='".$row_getUserDevice['user_id']."'";
                    mysql_query($updMember, $echoloyalty) or die(mysql_error());

                  // }

                  // GET NEWS
                  $query_getNews = "SELECT * FROM news WHERE news_institution = '".$row_getUserDevice['user_institution']."' && news_when > '".$colname_getLastNews."' OR news_institution = '".$row_getUserDevice['user_institution']."' && news_when = '1'";
                  $getNews = mysql_query($query_getNews, $echoloyalty) or die(mysql_error());
                  $row_getNews = mysql_fetch_assoc($getNews);
                  $getNewsRows  = mysql_num_rows($getNews);

                  if($getNewsRows > 0) {

                      do {

                          if($row_getNews['news_state'] == '0') {
                              array_push($newsArr, array("news_id" => $row_getNews['news_id'], "news_state" => $row_getNews['news_state'], "news_when" => $row_getNews['news_when']));
                          }
                          else {
                              array_push($newsArr, array("news_id" => $row_getNews['news_id'], "news_name" => $row_getNews['news_name'], "news_message" => $row_getNews['news_message'], "news_pic" => $row_getNews['news_pic'], "news_institution" => $row_getNews['news_institution'], "news_state" => $row_getNews['news_state'], "news_when" => $row_getNews['news_when']));
                          }

                      } while ($row_getNews = mysql_fetch_assoc($getNews));

                  }

                  // GET REVIEWS
                  $query_getReviews = "SELECT * FROM reviews WHERE reviews_institution = '".$row_getUserDevice['user_institution']."' && reviews_when > '".$colname_getLastRevs."' && reviews_from = '".$row_getUserDevice['user_id']."' OR reviews_institution = '".$row_getUserDevice['user_institution']."' && reviews_when > '".$colname_getLastRevs."' && reviews_to = '".$row_getUserDevice['user_id']."'";
                  $getReviews = mysql_query($query_getReviews, $echoloyalty) or die(mysql_error());
                  $row_getReviews = mysql_fetch_assoc($getReviews);
                  $getReviewsRows  = mysql_num_rows($getReviews);

                  if($getReviewsRows > 0) {

                      do {

                          array_push($reviewsArr, array("reviews_id" => $row_getReviews['reviews_id'], "reviews_from" => $row_getReviews['reviews_from'], "reviews_to" => $row_getReviews['reviews_to'], "reivews_message" => $row_getReviews['reivews_message'], "reviews_pic" => $row_getReviews['reviews_pic'], "reviews_institution" => $row_getReviews['reviews_institution'], "reviews_answered" => $row_getReviews['reviews_answered'], "reviews_when" => $row_getReviews['reviews_when']));

                      } while ($row_getReviews = mysql_fetch_assoc($getReviews));

                  }

                  // GET ASKS
                  $query_getAsks = "SELECT * FROM asks WHERE asks_institution = '".$row_getUserDevice['user_institution']."' && asks_when > '".$colname_getLastAsks."' ORDER BY asks_id DESC LIMIT 1";
                  $getAsks = mysql_query($query_getAsks, $echoloyalty) or die(mysql_error());
                  $row_getAsks = mysql_fetch_assoc($getAsks);
                  $getAsksRows  = mysql_num_rows($getAsks);

                  if($getAsksRows > 0) {

                      array_push($asksArr, array("asks_id" => $row_getAsks['asks_id'], "asks_name" => $row_getAsks['asks_name'], "asks_message" => $row_getAsks['asks_message'], "asks_yes" => $row_getAsks['asks_yes'], "asks_no" => $row_getAsks['asks_no'], "asks_institution" => $row_getAsks['asks_institution'], "asks_when" => $row_getAsks['asks_when']));

                  }

                  // GET CHAT
                  $query_getChat = "SELECT * FROM chat WHERE chat_from = '".$row_getUserDevice['user_id']."' && chat_institution = '".$row_getUserDevice['user_institution']."' && chat_when > '".$colname_getLastChat."' OR chat_to = '".$row_getUserDevice['user_id']."' && chat_institution = '".$row_getUserDevice['user_institution']."' && chat_when > '".$colname_getLastChat."' ORDER BY chat_id DESC";
                  $getChat = mysql_query($query_getChat, $echoloyalty) or die(mysql_error());
                  $row_getChat = mysql_fetch_assoc($getChat);
                  $getChatRows  = mysql_num_rows($getChat);

                  if($getChatRows > 0) {

                      do {

                          array_push($chatArr, array("chat_id" => $row_getChat['chat_id'], "chat_from" => $row_getChat['chat_from'], "chat_to" => $row_getChat['chat_to'], "chat_name" => $row_getChat['chat_name'], "chat_message" => $row_getChat['chat_message'], "chat_read" => $row_getChat['chat_read'], "chat_institution" => $row_getChat['chat_institution'], "chat_answered" => $row_getChat['chat_answered'], "chat_when" => $row_getChat['chat_when']));

                      } while ($row_getChat = mysql_fetch_assoc($getChat));

                  }

                  if($row_getUserDevice['user_work_pos'] >= '2') {

                    $query_getWallet = "SELECT * FROM wallet WHERE wallet_user = '".$row_getUserDevice['user_id']."' && wallet_institution = '".$row_getUserDevice['user_institution']."' && wallet_total > '0' LIMIT 1";
                    $getWallet = mysql_query($query_getWallet, $echoloyalty) or die(mysql_error());
                    $row_getWallet = mysql_fetch_assoc($getWallet);
                    $getWalletRows  = mysql_num_rows($getWallet);

                    if($getWalletRows > 0) {

                      $updWallet = "UPDATE wallet SET wallet_total='0', wallet_when='".$when."' WHERE wallet_user='".$row_getUserDevice['user_id']."'";
                      mysql_query($updWallet, $echoloyalty) or die(mysql_error());

                    }

                  }

                $newarrmes = array("check" => '1', "user_discount" => $row_getUserDevice['user_discount'], "user_work_pos" => $row_getUserDevice['user_work_pos'], "user_menue_exe" => $row_getUserDevice['user_menue_exe'], "pointsArr" => $pointsArr, "walletArr" => $walletArr, "catArr" => $catArr, "menueArr" => $menueArr, "ingrArr" => $ingrArr, "newsArr" => $newsArr, "reviewsArr" => $reviewsArr, "asksArr" => $asksArr, "giftsArr" => $giftsArr, "chatArr" => $chatArr, "goodsArr" => $goodsArr, "orderArr" => $orderArr);
                array_push($gotdata, $newarrmes);

            }
            else if($colname_getUser6 == 'check' && !$echoloyalty) {

              $pointsArr = array();
              $walletArr = array();
              $catArr = array();
              $menueArr = array();
              $ingrArr = array();
              $newsArr = array();
              $reviewsArr = array();
              $asksArr = array();
              $giftsArr = array();
              $chatArr = array();
              $goodsArr = array();
              $orderArr = array();

              $newarrmes = array("check" => '1', "user_discount" => '0', "user_work_pos" => '1000', "user_menue_exe" => '0', "pointsArr" => $pointsArr, "walletArr" => $walletArr, "catArr" => $catArr, "menueArr" => $menueArr, "ingrArr" => $ingrArr, "newsArr" => $newsArr, "reviewsArr" => $reviewsArr, "asksArr" => $asksArr, "giftsArr" => $giftsArr, "chatArr" => $chatArr, "goodsArr" => $goodsArr, "orderArr" => $orderArr);
              array_push($gotdata, $newarrmes);

            }
            else if($getUserDeviceRows > 0 && $colname_getUser6 == 'upd' && $echoloyalty) {

                $colname_getUsrName = $row_getUserDevice['user_name'];
                if (isset($themsg['user_name']) && $themsg['user_name'] != '0') {
                  $colname_getUsrName = $themsg['user_name'];
                }
                $colname_getUsrSurname = $row_getUserDevice['user_surname'];
                if (isset($themsg['user_surname']) && $themsg['user_surname'] != '0') {
                  $colname_getUsrSurname = $themsg['user_surname'];
                }
                $colname_getUsrMiddlename = $row_getUserDevice['user_middlename'];
                if (isset($themsg['user_middlename']) && $themsg['user_middlename'] != '0') {
                  $colname_getUsrMiddlename = $themsg['user_middlename'];
                }
                $colname_getEmail = $row_getUserDevice['user_email'];
                if (isset($themsg['user_email']) && $themsg['user_email'] != '0') {
                  $colname_getEmail = $themsg['user_email'];
                }
                $colname_getTel = $row_getUserDevice['user_tel'];
                if (isset($themsg['user_tel']) && $themsg['user_tel'] != '0') {
                  $colname_getTel = $themsg['user_tel'];
                }
                $colname_getMob = $row_getUserDevice['user_mob'];
                if (isset($themsg['user_mob']) && $themsg['user_mob'] != '0') {
                  $colname_getMob = $themsg['user_mob'];
                }
                $colname_getGender = $row_getUserDevice['user_gender'];
                if (isset($themsg['user_gender']) && $themsg['user_gender'] != '0') {
                  $colname_getGender = $themsg['user_gender'];
                }
                $colname_getBirthday = $row_getUserDevice['user_birthday'];
                if (isset($themsg['user_birthday']) && $themsg['user_birthday'] != '0') {
                  $colname_getBirthday = strtotime($themsg['user_birthday']);
                  $colname_getBirthday = date("Y-m-d" ,$colname_getBirthday);
                }
                $colname_getCountry = $row_getUserDevice['user_country'];
                if (isset($themsg['user_country']) && $themsg['user_country'] != '0') {
                  $colname_getCountry = $themsg['user_country'];
                }
                $colname_getRegion = $row_getUserDevice['user_region'];
                if (isset($themsg['user_region']) && $themsg['user_region'] != '0') {
                  $colname_getRegion = $themsg['user_region'];
                }
                $colname_getCity = $row_getUserDevice['user_city'];
                if (isset($themsg['user_city']) && $themsg['user_city'] != '0') {
                  $colname_getCity = $themsg['user_city'];
                }
                $colname_getAdress = $row_getUserDevice['user_adress'];
                if (isset($themsg['user_adress']) && $themsg['user_adress'] != '0') {
                  $colname_getAdress = $themsg['user_adress'];
                }
                $colname_getInstWhere = $row_getUserDevice['user_install_where'];
                if (isset($themsg['user_install_where']) && $themsg['user_install_where'] != '0') {
                  $colname_getInstWhere = $themsg['user_install_where'];
                }
                $colname_getDevice = $row_getUserDevice['user_device'];
                if (isset($themsg['user_device']) && $themsg['user_device'] != '0') {
                  $colname_getDevice = $themsg['user_device'];
                }
                $colname_getDeviceId = $row_getUserDevice['user_device_id'];
                if (isset($themsg['user_device_id']) && $themsg['user_device_id'] != '0') {
                  $colname_getDeviceId = $themsg['user_device_id'];
                }
                $colname_getDeviceVersion = $row_getUserDevice['user_device_version'];
                if (isset($themsg['user_device_version']) && $themsg['user_device_version'] != '0') {
                  $colname_getDeviceVersion = $themsg['user_device_version'];
                }
                $colname_getDeviceOS = $row_getUserDevice['user_device_os'];
                if (isset($themsg['user_device_os']) && $themsg['user_device_os'] != '0') {
                  $colname_getDeviceOS = $themsg['user_device_os'];
                }
                $colname_getDiscount = $row_getUserDevice['user_discount'];
                if (isset($themsg['user_discount']) && $themsg['user_discount'] != '0') {
                  $colname_getDiscount = $themsg['user_discount'];
                }

                // UPDATE MEMBER
                $updMember = "UPDATE users SET user_name='".$colname_getUsrName."', user_surname='".$colname_getUsrSurname."', user_middlename='".$colname_getUsrMiddlename."', user_email='".$colname_getEmail."', user_mob='".$colname_getMob."', user_tel='".$colname_getTel."', user_gender='".$colname_getGender."', user_birthday='".$colname_getBirthday."', user_country='".$colname_getCountry."', user_region='".$colname_getRegion."', user_city='".$colname_getCity."', user_adress='".$colname_getAdress."', user_install_where='".$colname_getInstWhere."', user_device='".$colname_getDevice."', user_device_id='".$colname_getDeviceId."', user_device_version='".$colname_getDeviceVersion."', user_device_os='".$colname_getDeviceOS."', user_discount='".$colname_getDiscount."', user_upd='".$when."' WHERE user_id='".$row_getUserDevice['user_id']."'";
                mysql_query($updMember, $echoloyalty) or die(mysql_error());

                $newarrmes = array("upd" => '1', "when" => $when);
                array_push($gotdata, $newarrmes);

            }
            else if($getUserDeviceRows > 0 && $colname_getUser6 == 'gcmreg' && $echoloyalty) {

                $colname_getDevice = $row_getUserDevice['user_device'];
                if (isset($themsg['user_device']) && $themsg['user_device'] != '0') {
                  $colname_getDevice = $themsg['user_device'];
                }
                $colname_getDeviceId = $row_getUserDevice['user_device_id'];
                if (isset($themsg['user_device_id']) && $themsg['user_device_id'] != '0') {
                  $colname_getDeviceId = $themsg['user_device_id'];
                }
                $colname_getDeviceVersion = $row_getUserDevice['user_device_version'];
                if (isset($themsg['user_device_version']) && $themsg['user_device_version'] != '0') {
                  $colname_getDeviceVersion = $themsg['user_device_version'];
                }
                $colname_getDeviceOS = $row_getUserDevice['user_device_os'];
                if (isset($themsg['user_device_os']) && $themsg['user_device_os'] != '0') {
                  $colname_getDeviceOS = $themsg['user_device_os'];
                }
                $colname_getGCMnew = $row_getUserDevice['user_gcm'];
                if (isset($themsg['gcm']) && $themsg['gcm'] != '0') {
                  $colname_getGCMnew = $themsg['gcm'];
                }

                // UPDATE GCM
                $updMember = "UPDATE users SET user_device='".$colname_getDevice."', user_device_id='".$colname_getDeviceId."', user_device_version='".$colname_getDeviceVersion."', user_device_os='".$colname_getDeviceOS."', user_gcm='".$colname_getGCMnew."', user_upd='".$when."' WHERE user_id='".$row_getUserDevice['user_id']."'";
                mysql_query($updMember, $echoloyalty) or die(mysql_error());

                $newarrmes = array("gcmreg" => '1', "gcm" => $colname_getGCMnew, "when" => $when);
                array_push($gotdata, $newarrmes);

            }
            else if($colname_getUser6 == 'scan' && $echoloyalty) {
        
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

                                      $newWallet = $row_getWallet['wallet_total'] + $gotPoints;

                                      $updWallet = "UPDATE wallet SET wallet_total='".$newWallet."', wallet_when='".$when."' WHERE wallet_user='".$row_getUserDevice['user_id']."'";
                                      mysql_query($updWallet, $echoloyalty) or die(mysql_error());

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

            }
            else if($colname_getUser6 == 'rate' && $echoloyalty) {
                
                $colname_getRate = -1;
                if (isset($themsg['rating'])) {
                  $colname_getRate = $protect($themsg['rating']);
                }
                $colname_getRateTxt = -1;
                if (isset($themsg['ratingtxt'])) {
                  $colname_getRateTxt = $protect($themsg['ratingtxt']);
                }
				        $colname_getRatePic = 'images/data.png';
                if (isset($themsg['pic']) && $themsg['pic'] != '0') {
                  $colname_getRatePic = $themsg['pic'];
                }
                
                // last 12 hours
                $when12h = $when - 60*60*12;

                $reviewOK = 0;
                
                // secure code
                $query_getTransLastReview = "SELECT * FROM reviews WHERE reviews_from = '".$row_getUserDevice['user_id']."' ORDER BY reviews_id DESC LIMIT 1";
                $getTransLastReview = mysql_query($query_getTransLastReview, $echoloyalty) or die(mysql_error());
                $row_getTransLastReview = mysql_fetch_assoc($getTransLastReview);
                $getTransLastReviewRows  = mysql_num_rows($getTransLastReview);
                
                if($getTransLastReviewRows == 0) {

                    $insReview = "INSERT INTO reviews (reviews_from, reviews_to, reviews_message, reviews_rate, reviews_pic, reviews_institution, reviews_when) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getInst."', '".$colname_getRateTxt."', '".$colname_getRate."', '".$colname_getRatePic."', '".$colname_getInst."', '".$when."')";
                    mysql_query($insReview, $echoloyalty) or die(mysql_error());

                    $reviewOK = 1;

                }
                else if($getTransLastReviewRows > 0) {

                    if($row_getTransLastReview['reviews_when'] < $when12h) {

                        $insReview = "INSERT INTO reviews (reviews_from, reviews_to, reviews_message, reviews_rate, reviews_pic, reviews_institution, reviews_when) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getInst."', '".$colname_getRateTxt."', '".$colname_getRate."', '".$colname_getRatePic."', '".$colname_getInst."', '".$when."')";
                        mysql_query($insReview, $echoloyalty) or die(mysql_error());

                        $reviewOK = 1;

                    }
                    else {
                        $reviewOK = 2;
                    }
                    
                }

                $newarrmes = array("scan" => '1', "reviewOK" => $reviewOK, "reviews_when" => $when);
                array_push($gotdata, $newarrmes);

            }
            else if($colname_getUser6 == 'asks' && $echoloyalty) {
                
                $colname_getAsksId = -1;
                if (isset($themsg['asks_id'])) {
                  $colname_getAsksId = $themsg['asks_id'];
                }
                $colname_getAsksAnsw = -1;
                if (isset($themsg['asks_answ'])) {
                  $colname_getAsksAnsw = $themsg['asks_answ'];
                }

                $asksOK = 0;
                
                // secure code
                $query_getAsks = "SELECT * FROM asks WHERE asks_id = '".$colname_getAsksId."' && asks_institution = '".$colname_getUser2."' LIMIT 1";
                $getAsks = mysql_query($query_getAsks, $echoloyalty) or die(mysql_error());
                $row_getAsks = mysql_fetch_assoc($getAsks);
                $getAsksRows  = mysql_num_rows($getAsks);
                
                if($getAsksRows > 0) {

                    if($colname_getAsksAnsw == 1) {
                        $asksY = $row_getAsks['asks_yes'] + 1;
                        $asksUpd = "UPDATE asks SET asks_yes='".$asksY."', asks_when='".$when."' WHERE asks_id = '".$colname_getAsksId."'";
                        mysql_query($asksUpd, $echoloyalty) or die(mysql_error());
                        $asksOK = 1;
                    }
                    else if($colname_getAsksAnsw == 2) {
                        $asksN = $row_getAsks['asks_no'] + 1;
                        $asksUpd = "UPDATE asks SET asks_no='".$asksN."', asks_when='".$when."' WHERE asks_id = '".$colname_getAsksId."'";
                        mysql_query($asksUpd, $echoloyalty) or die(mysql_error());
                        $asksOK = 2;
                    }

                }

                $newarrmes = array("asks" => '1', "asksOK" => $asksOK, "asks_id" => $colname_getAsksId, "asks_when" => $when);
                array_push($gotdata, $newarrmes);

            }
            else if($colname_getUser6 == 'discount' && $echoloyalty) {
                
                $colname_getDiscount = -1;
                if (isset($themsg['discount'])) {
                  $colname_getDiscount = $themsg['discount'];
                }

                $discountOK = 0;

                if($colname_getDiscount != 'req') {
                
                    $query_getDiscount = "SELECT * FROM users WHERE user_discount = '".$colname_getDiscount."' && user_institution = '".$colname_getUser2."' LIMIT 1";
                    $getDiscount = mysql_query($query_getDiscount, $echoloyalty) or die(mysql_error());
                    $row_getDiscount = mysql_fetch_assoc($getDiscount);
                    $getDiscountRows  = mysql_num_rows($getDiscount);
                    
                    if($getDiscountRows == 0) {

                        $usrUpd = "UPDATE users SET user_discount='".$colname_getDiscount."', user_upd='".$when."' WHERE user_id = '".$row_getUserDevice['user_id']."'";
                        mysql_query($usrUpd, $echoloyalty) or die(mysql_error());
                        $discountOK = 1;

                    }
                    else if($getDiscountRows > 0) {
                        $discountOK = 2;
                    }

                }
                else if($colname_getDiscount == 'req') {

                    $query_getDiscountReq = "SELECT * FROM chat WHERE chat_from = '".$row_getUserDevice['user_id']."' && chat_name = 'Запрос' && chat_institution = '".$colname_getUser2."' LIMIT 1";
                    $getDiscountReq = mysql_query($query_getDiscountReq, $echoloyalty) or die(mysql_error());
                    $row_getDiscountReq = mysql_fetch_assoc($getDiscountReq);
                    $getDiscountReqRows  = mysql_num_rows($getDiscountReq);
                    
                    if($getDiscountReqRows == 0) {

                        $insrtRequest = "INSERT INTO chat (chat_from, chat_to, chat_name, chat_message, chat_institution, chat_when) VALUES ('".$row_getUserDevice['user_id']."', '1', 'Запрос', 'Скидочная карта', '".$colname_getUser2."', '".$when."')";
                        mysql_query($insrtRequest, $echoloyalty) or die(mysql_error());

                        $discountOK = 3;

                    }
                    else if($getDiscountReqRows > 0) {
                        $discountOK = 4;
                    }

                }

                $newarrmes = array("discount" => '1', "discountOK" => $discountOK, "discount_when" => $when);
                array_push($gotdata, $newarrmes);

            }
            else if($getUserDeviceRows > 0 && $colname_getUser6 == 'sms' && $echoloyalty) {
                
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

                            $updWallet = "UPDATE wallet SET wallet_total = '".$row_getWallet['wallet_total']."', wallet_warn = '".$row_getWallet['wallet_warn']."' WHERE wallet_user = '".$row_getUserDevice['user_id']."' && wallet_institution = '".$colname_getUser2."'";
                            mysql_query($updWallet, $echoloyalty) or die(mysql_error());

                            $emptyOldWallet = "UPDATE wallet SET wallet_total = '0', wallet_warn = '".$row_getWallet['wallet_warn']."' WHERE wallet_user = '".$row_getWallet['wallet_user']."' && wallet_institution = '".$colname_getUser2."'";
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

            }
            else if($getUserDeviceRows > 0 && $colname_getUser6 == 'chat' && $echoloyalty) {
                
                $colname_getAction = -1;
                if (isset($themsg['getsend'])) {
                  $colname_getAction = $themsg['getsend'];
                }
                $colname_getChat = -1;
                if (isset($themsg['name'])) {
                  $colname_getChat = $themsg['name'];
                }
                $colname_getChatTxt = -1;
                if (isset($themsg['txt'])) {
                  $colname_getChatTxt = $themsg['txt'];
                }
                $colname_getLast = -1;
                if (isset($themsg['lastchat'])) {
                  $colname_getLast = $themsg['lastchat'];
                }

                $chatOK = 0;

                $chatID = 0;

                $chatArr = array();
                
                if($colname_getAction == 'get') {

                    $query_getLastChat = "SELECT * FROM chat WHERE chat_to = '".$row_getUserDevice['user_id']."' && chat_institution = '".$colname_getInst."' && chat_when > '".$colname_getLast."'";
                    $getLastChat = mysql_query($query_getLastChat, $echoloyalty) or die(mysql_error());
                    $row_getLastChat = mysql_fetch_assoc($getLastChat);
                    $getLastChatRows  = mysql_num_rows($getLastChat);

                    if($getLastChatRows > 0) {

                        do {

                            array_push($chatArr, array("chat_id" => $row_getLastChat['chat_id'], "chat_from" => $row_getLastChat['chat_from'], "chat_to" => $row_getLastChat['chat_to'], "chat_name" => $row_getLastChat['chat_name'], "chat_message" => $row_getLastChat['chat_message'], "chat_read" => $row_getLastChat['chat_read'], "chat_institution" => $row_getLastChat['chat_institution'], "chat_answered" => $row_getLastChat['chat_answered'], "chat_when" => $row_getLastChat['chat_when']));

                        } while ($row_getLastChat = mysql_fetch_assoc($getLastChat));

                        $chatOK = 1;

                    }

                }
                else if($colname_getAction == 'send' && $colname_getChatTxt != '') {

                    $insChat = "INSERT INTO chat (chat_from, chat_to, chat_name, chat_message, chat_institution, chat_when) VALUES ('".$row_getUserDevice['user_id']."', '1', '".$colname_getChat."', '".$colname_getChatTxt."', '".$colname_getUser2."', '".$when."')";
                    mysql_query($insChat, $echoloyalty) or die(mysql_error());

                    $query_getLastMyChat = "SELECT * FROM chat WHERE chat_from = '".$row_getUserDevice['user_id']."' && chat_institution = '".$colname_getInst."' && chat_when = '".$when."'";
                    $getLastMyChat = mysql_query($query_getLastMyChat, $echoloyalty) or die(mysql_error());
                    $row_getLastMyChat = mysql_fetch_assoc($getLastMyChat);
                    $getLastMyChatRows  = mysql_num_rows($getLastMyChat);

                    $chatOK = $when;
                    $chatID = $row_getLastMyChat['chat_id'];

                }

                $newarrmes = array("chat" => '1', "chatOK" => $chatOK, "chatID" => $chatID, "chatArr" => $chatArr);
                array_push($gotdata, $newarrmes);

            }
      			else if(isset($colname_getUser6) && $colname_getUser6 == 'waiter' && $getInstRows > 0 && $echoloyalty) {
      				
      				// GET USER
              $query_getUser = "SELECT * FROM users WHERE user_work_pos >= '2' && user_pwd != '' && user_pwd != '0' && user_institution = '".$colname_getUser2."'";
              $getUser = mysql_query($query_getUser, $echoloyalty) or die(mysql_error());
              $row_getUser = mysql_fetch_assoc($getUser);
              $getUserRows  = mysql_num_rows($getUser);
      				
      				$usrArr = array();
      				if($getUserRows > 0) {
      					
      					do {
      						
      						array_push($usrArr, array("user_id" => $row_getUser['user_id'], "user_name" => $row_getUser['user_name'], "user_surname" => $row_getUser['user_surname'], "user_middlename" => $row_getUser['user_middlename'], "user_pwd" => $row_getUser['user_pwd'], "user_mob" => $row_getUser['user_mob'], "user_work_pos" => $row_getUser['user_work_pos'], "user_menue_exe" => $row_getUser['user_menue_exe'], "user_pic" => $row_getUser['user_pic'], "user_gender" => $row_getUser['user_gender'], "user_institution" => $row_getUser['user_institution'], "user_upd" => $row_getUser['user_upd'], "user_reg" => $row_getUser['user_reg']));
      						
      					} while ($row_getUser = mysql_fetch_assoc($getUser));
      					
      				}

              // GET PROFESSIONS
              $query_getProf = "SELECT * FROM professions WHERE prof_when > '1' && (prof_institution = '".$colname_getUser2."' OR prof_institution = '0')";
              $getProf = mysql_query($query_getProf, $echoloyalty) or die(mysql_error());
              $row_getProf = mysql_fetch_assoc($getProf);
              $getProfRows  = mysql_num_rows($getProf);
              
              $profArr = array();
              if($getProfRows > 0) {
                
                do {
                  
                  array_push($profArr, array("prof_id" => $row_getProf['prof_id'], "prof_name" => $row_getProf['prof_name'], "prof_desc" => $row_getProf['prof_desc'], "prof_institution" => $row_getProf['prof_institution'], "prof_when" => $row_getProf['prof_when']));
                  
                } while ($row_getProf = mysql_fetch_assoc($getProf));
                
              }

              // GET ORGANIZATION OFFICE
              $query_getOffice = "SELECT * FROM organizations_office WHERE office_reg > '1' && office_institution = '".$colname_getUser2."'";
              $getOffice = mysql_query($query_getOffice, $echoloyalty) or die(mysql_error());
              $row_getOffice = mysql_fetch_assoc($getOffice);
              $getOfficeRows  = mysql_num_rows($getOffice);
              
              $offArr = array();
              if($getOfficeRows > 0) {
                
                do {
                  
                  array_push($offArr, array("office_id" => $row_getOffice['office_id'], "office_name" => $row_getOffice['office_name'], "office_start" => $row_getOffice['office_start'], "office_stop" => $row_getOffice['office_stop'], "office_country" => $row_getOffice['office_country'], "office_city" => $row_getOffice['office_city'], "office_adress" => $row_getOffice['office_adress'], "office_timezone" => $row_getOffice['office_timezone'], "office_tel" => $row_getOffice['office_tel'], "office_fax" => $row_getOffice['office_fax'], "office_mob" => $row_getOffice['office_mob'], "office_email" => $row_getOffice['office_email'], "office_skype" => $row_getOffice['office_skype'], "office_site" => $row_getOffice['office_site'], "office_logo" => $row_getOffice['office_logo'], "office_institution" => $row_getOffice['office_institution']));
                  
                } while ($row_getOffice = mysql_fetch_assoc($getOffice));
                
              }

              // GET SCHEDULE
              $query_getSchedule = "SELECT * FROM schedule WHERE (schedule_start > '".$when."' && schedule_institution = '".$colname_getUser2."') || (schedule_start < '".$when."' && schedule_stop > '".$when."' && schedule_institution = '".$colname_getUser2."')";
              $getSchedule = mysql_query($query_getSchedule, $echoloyalty) or die(mysql_error());
              $row_getSchedule = mysql_fetch_assoc($getSchedule);
              $getScheduleRows  = mysql_num_rows($getSchedule);
              
              $schArr = array();
              if($getScheduleRows > 0) {
                
                do {
                  
                  array_push($schArr, array("schedule_id" => $row_getSchedule['schedule_id'], "schedule_employee" => $row_getSchedule['schedule_employee'], "schedule_menue" => $row_getSchedule['schedule_menue'], "schedule_office" => $row_getSchedule['schedule_office'], "schedule_start" => $row_getSchedule['schedule_start'], "schedule_stop" => $row_getSchedule['schedule_stop'], "schedule_institution" => $row_getSchedule['schedule_institution'], "schedule_when" => $row_getSchedule['schedule_when']));
                  
                } while ($row_getSchedule = mysql_fetch_assoc($getSchedule));
                
              }
      				
      				$newarrmes = array("waiters" => '1', "usrArr" => $usrArr, "profArr" => $profArr, "offArr" => $offArr, "schArr" => $schArr, "appVers" => $row_getInst['org_appvers'], "appUrl" => $row_getInst['org_appurl']);
              array_push($gotdata, $newarrmes);
      				
      			}
            else if($getUserDeviceRows > 0 && $colname_getUser6 == 'share' && $echoloyalty) {
				
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
      				
      			}
            else if($getUserDeviceRows > 0 && $colname_getUser6 == 'promo' && $echoloyalty) {

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
                    $promoOK = 1;

                    // POINTS GET INSERTER
                    $insPoints = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_status, points_comment, points_proofed, points_when, points_time) VALUES ('".$row_getUserDevice['user_id']."', '0', '0', '30', '0', '0', '".$colname_getUser2."', '0', '".$pointsComment."', '1', '".$when."', '".$when."')";
                    mysql_query($insPoints, $echoloyalty) or die(mysql_error());
                    
                    $query_getWallet = "SELECT * FROM wallet WHERE wallet_institution = '".$colname_getUser2."' && wallet_user = '".$row_getUserDevice['user_id']."' LIMIT 1";
                    $getWallet = mysql_query($query_getWallet, $echoloyalty) or die(mysql_error());
                    $row_getWallet = mysql_fetch_assoc($getWallet);
                    $getWalletRows  = mysql_num_rows($getWallet);

                    $newWallet = $row_getWallet['wallet_total'] + 30;

                    $updWallet = "UPDATE wallet SET wallet_total='".$newWallet."', wallet_when='".$when."' WHERE wallet_user='".$row_getUserDevice['user_id']."'";
                    mysql_query($updWallet, $echoloyalty) or die(mysql_error());

                    if($row_getPromo['user_work_pos'] == '0') {

                      // POINTS GET OWNER
                      $insPointsOWN = "INSERT INTO points (points_user, points_bill, points_discount, points_points, points_got_spend, points_waiter, points_institution, points_status, points_comment, points_proofed, points_when, points_time) VALUES ('".$colname_getPromo."', '0', '0', '10', '0', '0', '".$colname_getUser2."', '0', '".$pointsComment."', '1', '".$when."', '".$when."')";
                      mysql_query($insPointsOWN, $echoloyalty) or die(mysql_error());
                      
                      $query_getWalletOWN = "SELECT * FROM wallet WHERE wallet_institution = '".$colname_getUser2."' && wallet_user = '".$colname_getPromo."' LIMIT 1";
                      $getWalletOWN = mysql_query($query_getWalletOWN, $echoloyalty) or die(mysql_error());
                      $row_getWalletOWN = mysql_fetch_assoc($getWalletOWN);
                      $getWalletOWNRows  = mysql_num_rows($getWalletOWN);

                      $newWalletOWN = $row_getWalletOWN['wallet_total'] + 10;

                      $updWalletOWN = "UPDATE wallet SET wallet_total='".$newWalletOWN."', wallet_when='".$when."' WHERE wallet_user='".$colname_getPromo."'";
                      mysql_query($updWalletOWN, $echoloyalty) or die(mysql_error());

                    }

                    // UPDATE PROMOCODE USED
                    $updPromo = "UPDATE users SET user_promo='1' WHERE user_id='".$row_getUserDevice['user_id']."'";
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

            }
            else if($getUserDeviceRows > 0 && $colname_getUser6 == 'calender' && $echoloyalty) {


              $colname_getGetSet = "-1";
              if (isset($themsg['getset'])) {
                $colname_getGetSet = $themsg['getset'];
              }
              $colname_getOffice = 0;
              if (isset($themsg['ordoffice'])) {
                $colname_getOffice = $themsg['ordoffice'];
              }
              $colname_getGoods = "-1";
              if (isset($themsg['ordgood'])) {
                $colname_getGoods = $themsg['ordgood'];
              }
              $colname_getCats = "-1";
              if (isset($themsg['ordercats'])) {
                $colname_getCats = $themsg['ordercats'];
              }
              $colname_getOrder = "-1";
              if (isset($themsg['orderid'])) {
                $colname_getOrder = $themsg['orderid'];
              }
              $colname_getWorker = "-1";
              if (isset($themsg['worker'])) {
                $colname_getWorker = $themsg['worker'];
              }
              $colname_getStart = "-1";
              if (isset($themsg['start'])) {
                $colname_getStart = $themsg['start'];
              }
              $colname_getEnd = "-1";
              if (isset($themsg['end'])) {
                $colname_getEnd = $themsg['end'];
              }
              $colname_getText = "-1";
              if (isset($themsg['text'])) {
                $colname_getText = $themsg['text'];
              }

              if($colname_getGetSet == '1') {

                $orderOK = 0;

                $query_getMenueC = "SELECT * FROM menue WHERE menue_id = '".$colname_getOrder."' && menue_institution = '".$colname_getUser2."' && menue_when > '1'";
                $getMenueC = mysql_query($query_getMenueC, $echoloyalty) or die(mysql_error());
                $row_getMenueC = mysql_fetch_assoc($getMenueC);
                $getMenueCRows  = mysql_num_rows($getMenueC);

                if($getMenueCRows > 0) {

                  $query_getOrderChng = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."' && order_user = '".$row_getUserDevice['user_id']."' && order_order = '".$colname_getOrder."' && order_status = '0' ORDER BY order_id DESC LIMIT 1";
                  $getOrderChng = mysql_query($query_getOrderChng, $echoloyalty) or die(mysql_error());
                  $row_getOrderChng = mysql_fetch_assoc($getOrderChng);
                  $getOrderChngRows  = mysql_num_rows($getOrderChng);

                  if($getOrderChngRows == 0) {

                    $insOrder = "INSERT INTO ordering (order_user, order_name, order_desc, order_worker, order_institution, order_office, order_bill, order_goods, order_cats, order_order, order_status, order_start, order_end, order_allday, order_mobile, order_when) VALUES ('".$row_getUserDevice['user_id']."', '', '".$colname_getText."', '".$colname_getWorker."', '".$colname_getUser2."', '".$colname_getOffice."', '".$row_getMenueC['menue_cost']."', '".$row_getMenueC['menue_id']."', '".$colname_getGoods."', '".$colname_getCats."', '0', '".$colname_getStart."', '".$colname_getEnd."', '0', '1', '".$when."')";
                    mysql_query($insOrder, $echoloyalty) or die(mysql_error());

                    $orderOK = 1;

                    $query_getOrderNew = "SELECT * FROM ordering WHERE order_institution = '".$colname_getUser2."' && order_user = '".$row_getUserDevice['user_id']."' && order_order = '".$colname_getOrder."' && order_status = '0' && order_when = '".$when."' ORDER BY order_id DESC LIMIT 1";
                    $getOrderNew = mysql_query($query_getOrderNew, $echoloyalty) or die(mysql_error());
                    $row_getOrderNew = mysql_fetch_assoc($getOrderNew);
                    $getOrderNewRows  = mysql_num_rows($getOrderNew);

                    if($getOrderNewRows > 0) {

                      $newarrmes = array("requests" => '1', "orderId" => $colname_getOrder, "orderIns" => '1', "orderOK" => $orderOK, "order_id" => $row_getOrderNew['order_id'], "order_user" => $row_getOrderNew['order_user'], "order_name" => $row_getOrderNew['order_name'], "order_desc" => $row_getOrderNew['order_desc'], "order_worker" => $row_getOrderNew['order_worker'], "order_institution" => $row_getOrderNew['order_institution'], "order_office" => $row_getOrderNew['order_office'], "order_bill" => $row_getOrderNew['order_bill'], "order_goods" => $row_getOrderNew['order_goods'], "order_cats" => $row_getOrderNew['order_cats'], "order_order" => $row_getOrderNew['order_order'], "order_status" => $row_getOrderNew['order_status'], "order_allday" => $row_getOrderNew['order_allday'], "order_mobile" => $row_getOrderNew['order_mobile'], "order_when" => $row_getOrderNew['order_when']);
                      array_push($gotdata, $newarrmes);

                    }

                  }
                  else {

                    $orderOK = 2;

                  }

                }
                else {

                  $orderOK = 3;

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

                    array_push($orderArr, array("order_id" => $row_getOrdering['order_id'], "order_user" => $row_getOrdering['order_user'], "order_name" => $row_getOrdering['order_name'], "order_desc" => $row_getOrdering['order_desc'], "order_worker" => $row_getOrdering['order_worker'], "order_institution" => $row_getOrdering['order_institution'], "order_office" => $row_getOrdering['order_office'], "order_bill" => $row_getOrdering['order_bill'], "order_goods" => $row_getOrdering['order_goods'], "order_cats" => $row_getOrdering['order_cats'], "order_order" => $row_getOrdering['order_order'], "order_status" => $row_getOrdering['order_status'], "order_start" => $row_getOrdering['order_start'], "order_end" => $row_getOrdering['order_end'], "order_allday" => $row_getOrdering['order_allday'], "order_mobile" => $row_getOrdering['order_mobile'], "order_when" => $row_getOrdering['order_when']));

                  } while ($row_getOrdering = mysql_fetch_assoc($getOrdering));

                }

                $newarrmes = array("calender" => '1', "ordersArr" => $orderArr);
                array_push($gotdata, $newarrmes);

              }
              
            }
            else if($getUserDeviceRows > 0 && $colname_getUser6 == 'pushreceive' && $echoloyalty) {

              $push_push = -1;
              if (isset($themsg['push_push'])) {
                $push_push = $protect($themsg['push_push']);
              }

              $push_rec = -1;
              if (isset($themsg['push_rec'])) {
                $push_rec = $protect($themsg['push_rec']);

                $query_getPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_id='".$push_push."' LIMIT 1";
                $getPushed = mysql_query($query_getPushed, $echoloyalty) or die(mysql_error());
                $row_getPushed = mysql_fetch_assoc($getPushed);
                $getPushedRows  = mysql_num_rows($getPushed);

                if($getPushedRows > 0) {

                  $updPush = "UPDATE pushreceive SET push_rec_received='".$when."' WHERE push_rec_user='".$row_getUserDevice['user_id']."' && push_rec_push='".$push_push."' && push_rec_institution='".$colname_getUser2."'";
                  mysql_query($updPush, $echoloyalty) or die(mysql_error());

                }
                else {

                  $insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$push_push."', '".$row_getUserDevice['user_id']."', '".$when."', '0', '".$colname_getUser2."', '".$when."')";
                  mysql_query($insPush, $echoloyalty) or die(mysql_error());

                }

              }

              $push_open = -1;
              if (isset($themsg['push_open'])) {
                $push_open = $protect($themsg['push_open']);

                $query_getPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_id='".$push_push."' LIMIT 1";
                $getPushed = mysql_query($query_getPushed, $echoloyalty) or die(mysql_error());
                $row_getPushed = mysql_fetch_assoc($getPushed);
                $getPushedRows  = mysql_num_rows($getPushed);

                if($getPushedRows > 0) {

                  $updPush = "UPDATE pushreceive SET push_rec_opened='".$when."' WHERE push_rec_user='".$row_getUserDevice['user_id']."' && push_rec_push='".$push_push."' && push_rec_institution='".$colname_getUser2."'";
                  mysql_query($updPush, $echoloyalty) or die(mysql_error());

                }
                else {

                  $insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$push_push."', '".$row_getUserDevice['user_id']."', '".$when."', '".$when."', '".$colname_getUser2."', '".$when."')";
                  mysql_query($insPush, $echoloyalty) or die(mysql_error());

                }

              }
      
            }
			
            echo json_encode($gotdata, JSON_UNESCAPED_UNICODE);

        }
        // NO KEY -> AUTO LOGOUT
        else if($artNumRows == 0 && $echoloyalty) {

            $newarrmes = array("logout" => '1');
            array_push($gotdata, $newarrmes);

            json_encode($gotdata, JSON_UNESCAPED_UNICODE);
            
        }

    }

}

?>