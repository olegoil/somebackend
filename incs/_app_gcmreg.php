<?php

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

?>