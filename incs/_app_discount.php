<?php

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

?>