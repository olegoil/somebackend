<?php

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

?>