<?php

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

        $query_getLastChat = "SELECT * FROM chat WHERE chat_to = '".$row_getUserDevice['user_id']."' && chat_institution = '".$colname_getInst."' && chat_when > '".$colname_getLast."' && chat_del = '0'";
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
		
		$query_getGCM = "SELECT * FROM users WHERE user_institution = '".$colname_getUser2."' && user_gcm != '' && user_gcm != '0' && user_gcm != 'testingdevice' && user_device != '' && user_device != '0' && user_id = '".$row_getInst['org_admin']."'";
		$getGCM = mysql_query($query_getGCM, $echoloyalty) or die(mysql_error());
		$row_getGCM = mysql_fetch_assoc($getGCM);
		$getGCMRows  = mysql_num_rows($getGCM);

		if($getGCMRows > 0) {

		  $apiKey =  urldecode($row_getInst['org_key']);

			$title = urldecode("Техподдержка:");
			$messageand = urldecode('Новое сообщение!');
			
			$messageios = "Техподдержка: Новое сообщение!";

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

    $newarrmes = array("chat" => '1', "chatOK" => $chatOK, "chatID" => $chatID, "chatArr" => $chatArr);
    array_push($gotdata, $newarrmes);

?>