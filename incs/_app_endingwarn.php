<?php

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

?>