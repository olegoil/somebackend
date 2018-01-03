<?php

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

                array_push($catArr, array("cat_id" => $row_getCat['cat_id'], "cat_name" => $row_getCat['cat_name'], "cat_desc" => $row_getCat['cat_desc'], "cat_pic" => $row_getCat['cat_pic'], "cat_ingr" => $row_getCat['cat_ingr'], "cat_order" => $row_getCat['cat_order'], "cat_institution" => $row_getCat['cat_institution'], "cat_when" => $row_getCat['cat_when']));
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
                array_push($menueArr, array("menue_id" => $row_getMenue['menue_id'], "menue_cat" => $row_getMenue['menue_cat'], "menue_name" => $row_getMenue['menue_name'], "menue_desc" => $row_getMenue['menue_desc'], "menue_size" => $row_getMenue['menue_size'], "menue_cost" => $row_getMenue['menue_cost'], "menue_costs" => $row_getMenue['menue_costs'], "menue_ingr" => $row_getMenue['menue_ingr'], "menue_weight" => $row_getMenue['menue_weight'], "menue_interval" => $row_getMenue['menue_interval'], "menue_discount" => $row_getMenue['menue_discount'], "menue_action" => $row_getMenue['menue_action'], "menue_code" => $row_getMenue['menue_code'], "menue_pic" => $row_getMenue['menue_pic'], "menue_institution" => $row_getMenue['menue_institution'], "menue_when" => $row_getMenue['menue_when']));
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

?>