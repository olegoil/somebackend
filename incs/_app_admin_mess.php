<?php
// CHECK NEW HANDLING MESSAGES FOR PANEL ADMIN
$h12 = 60*60*12;
if($row_getInst['org_log']+$h12 < $when && $row_getInst['org_admin'] > 1) {
  // SUPPORT COUNT
  $query_getSupportC = "SELECT COUNT(*) AS supcnt FROM chat WHERE chat_institution = '".$colname_getUser2."' AND chat_to = '1' AND chat_answered = '0'";
  $getSupportC = mysql_query($query_getSupportC, $echoloyalty) or die(mysql_error());
  $row_getSupportC = mysql_fetch_assoc($getSupportC);
  // ORDERING COUNT
  $query_getOrderC = "SELECT COUNT(*) AS ordercnt FROM ordering WHERE order_institution = '".$colname_getUser2."' AND order_del = '0' AND order_status = '0'";
  $getOrderC = mysql_query($query_getOrderC, $echoloyalty) or die(mysql_error());
  $row_getOrderC = mysql_fetch_assoc($getOrderC);
  // POINTS COUNT
  $query_getPointsC = "SELECT COUNT(*) AS pointcnt FROM points WHERE points_institution = '".$colname_getUser2."' AND points_proofed = '0'";
  $getPointsC = mysql_query($query_getPointsC, $echoloyalty) or die(mysql_error());
  $row_getPointsC = mysql_fetch_assoc($getPointsC);
  // REVIEWS COUNT
  $query_getReviewC = "SELECT COUNT(*) AS reviewcnt FROM reviews WHERE reviews_institution = '".$colname_getUser2."' AND reviews_opened = '0'";
  $getReviewC = mysql_query($query_getReviewC, $echoloyalty) or die(mysql_error());
  $row_getReviewC = mysql_fetch_assoc($getReviewC);
  
  if($row_getSupportC['supcnt'] > 0 || $row_getOrderC['ordercnt'] > 0 || $row_getPointsC['pointcnt'] > 0 || $row_getReviewC['reviewcnt'] > 0) {
    
    $instLog = "UPDATE organizations SET org_log='".$when."' WHERE org_id = '".$colname_getUser2."'";
              mysql_query($instLog, $echoloyalty) or die(mysql_error());
    
    $query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_id = '".$row_getInst['org_admin']."'";
    $getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
    $row_getGCM = mysql_fetch_assoc($getGCM);
    $getGCMRows  = mysql_num_rows($getGCM);

    if($getGCMRows > 0) {

      $apiKey =  urldecode($row_getInst['org_key']);

      $title = urldecode("Панель управления:");
      $messageand = urldecode('Замечена активность!');
      
      $messageios = "Панель управления: Замечена активность!";

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

      $rootLink = '/var/www/vhosts/xxx.com/httpdocs/src/MyApp/';

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
?>