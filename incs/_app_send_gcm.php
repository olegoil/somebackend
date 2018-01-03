<?php

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

  // $rootLink = '/var/www/vhosts/xxx.com/httpdocs/src/MyApp/';
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

?>