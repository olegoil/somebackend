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
        $themsg = json_decode(urldecode(ltrim($postdata, '=')), true);
    }
    else {
        // Android
        $themsg = json_decode($postdata, true);
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

    $hostname_echoloyalty = "";
    $database_echoloyalty = "";
    $username_echoloyalty = "";
    $password_echoloyalty = "";

    $echoloyalty = mysql_pconnect(
    $hostname_echoloyalty, 
    $username_echoloyalty, 
    // $password_echoloyalty);
    $password_echoloyalty) or trigger_error(mysql_error(),E_USER_ERROR);

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
      
      include 'incs/_app_usrorgdata.php';

      include 'incs/_app_endingwarn.php';

      include 'incs/_app_admin_mess.php';

    }

    $gotdata = array();

    // APPLICATION
    if (isset($colname_getUser6) && $colname_getUser6 != '' && $colname_getUser6 != -1) {

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

            include 'incs/_app_newusr_old.php';

        }
        else if($getUserDeviceRows == 0 && $colname_getUser6 == 'newusr' && $echoloyalty) {

            include 'incs/_app_newusr_new.php';

        }
        else if($getUserDeviceRows > 0 && $colname_getUser6 == 'check' && $echoloyalty) {

            include 'incs/_app_check.php';

        }
        else if($colname_getUser6 == 'check' && !$echoloyalty) {

          include 'incs/_app_check_no.php';

        }
        else if($getUserDeviceRows > 0 && $colname_getUser6 == 'upd' && $echoloyalty) {

            include 'incs/_app_upd.php';

        }
        else if($getUserDeviceRows > 0 && $colname_getUser6 == 'gcmreg' && $echoloyalty) {

            include 'incs/_app_gcmreg.php';

        }
        else if($colname_getUser6 == 'scan' && $echoloyalty) {
    
            include 'incs/_app_scan.php';

        }
        else if($colname_getUser6 == 'rate' && $echoloyalty) {
            
            include 'incs/_app_rate.php';

        }
        else if($colname_getUser6 == 'asks' && $echoloyalty) {
            
            include 'incs/_app_asks.php';

        }
        else if($colname_getUser6 == 'discount' && $echoloyalty) {
            
            include 'incs/_app_discount.php';

        }
        else if($getUserDeviceRows > 0 && $colname_getUser6 == 'sms' && $echoloyalty) {
            
            include 'incs/_app_sms.php';

        }
        else if($getUserDeviceRows > 0 && $colname_getUser6 == 'chat' && $echoloyalty) {
            
            include 'incs/_app_chat.php';

        }
  			else if(isset($colname_getUser6) && $colname_getUser6 == 'waiter' && $getInstRows > 0 && $echoloyalty) {
  				
  				include 'incs/_app_waiter.php';
  				
  			}
        else if($getUserDeviceRows > 0 && $colname_getUser6 == 'share' && $echoloyalty) {
		
  				include 'incs/_app_share.php';
  				
  			}
        else if($getUserDeviceRows > 0 && $colname_getUser6 == 'promo' && $echoloyalty) {

          include 'incs/_app_promo.php';

        }
        else if($getUserDeviceRows > 0 && $colname_getUser6 == 'calender' && $echoloyalty) {

          include 'incs/_app_calender.php';
          
        }
        else if($getUserDeviceRows > 0 && $colname_getUser6 == 'pushreceive' && $echoloyalty) {

          include 'incs/_app_pushreceive.php';
  
        }
	
        echo json_encode($gotdata, JSON_UNESCAPED_UNICODE);

    }

}

?>