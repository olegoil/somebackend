<?php

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
                    array_push($catArr, array("cat_id" => $row_getCat['cat_id'], "cat_name" => $row_getCat['cat_name'], "cat_desc" => $row_getCat['cat_desc'], "cat_pic" => $row_getCat['cat_pic'], "cat_ingr" => $row_getCat['cat_ingr'], "cat_order" => $row_getCat['cat_order'], "cat_institution" => $row_getCat['cat_institution'], "cat_when" => $row_getCat['cat_when']));
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
                    array_push($menueArr, array("menue_id" => $row_getMenue['menue_id'], "menue_cat" => $row_getMenue['menue_cat'], "menue_name" => $row_getMenue['menue_name'], "menue_desc" => $row_getMenue['menue_desc'], "menue_size" => $row_getMenue['menue_size'], "menue_cost" => $row_getMenue['menue_cost'], "menue_costs" => $row_getMenue['menue_costs'], "menue_ingr" => $row_getMenue['menue_ingr'], "menue_weight" => $row_getMenue['menue_weight'], "menue_interval" => $row_getMenue['menue_interval'], "menue_discount" => $row_getMenue['menue_discount'], "menue_action" => $row_getMenue['menue_action'], "menue_code" => $row_getMenue['menue_code'], "menue_pic" => $row_getMenue['menue_pic'], "menue_institution" => $row_getMenue['menue_institution'], "menue_when" => $row_getMenue['menue_when']));
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

    $newarrmes = array("check" => '1', "user_discount" => $row_getUserDevice['user_discount'], "user_work_pos" => $row_getUserDevice['user_work_pos'], "user_menue_exe" => $row_getUserDevice['user_menue_exe'], "user_log" => $when, "pointsArr" => $pointsArr, "walletArr" => $walletArr, "catArr" => $catArr, "menueArr" => $menueArr, "ingrArr" => $ingrArr, "newsArr" => $newsArr, "reviewsArr" => $reviewsArr, "asksArr" => $asksArr, "giftsArr" => $giftsArr, "chatArr" => $chatArr, "goodsArr" => $goodsArr, "orderArr" => $orderArr);
    array_push($gotdata, $newarrmes);

?>