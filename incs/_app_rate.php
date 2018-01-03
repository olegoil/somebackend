<?php

	$colname_getRate = -1;
    if (isset($themsg['rating'])) {
      $colname_getRate = $protect($themsg['rating']);
    }
    $colname_getRateTxt = -1;
    if (isset($themsg['ratingtxt'])) {
      $colname_getRateTxt = $protect($themsg['ratingtxt']);
    }
        $colname_getRatePic = 'images/data.png';
    if (isset($themsg['pic']) && $themsg['pic'] != '0') {
      $colname_getRatePic = $themsg['pic'];
    }
    
    // last 12 hours
    $when12h = $when - 60*60*12;

    $reviewOK = 0;
    
    // secure code
    $query_getTransLastReview = "SELECT * FROM reviews WHERE reviews_from = '".$row_getUserDevice['user_id']."' ORDER BY reviews_id DESC LIMIT 1";
    $getTransLastReview = mysql_query($query_getTransLastReview, $echoloyalty) or die(mysql_error());
    $row_getTransLastReview = mysql_fetch_assoc($getTransLastReview);
    $getTransLastReviewRows  = mysql_num_rows($getTransLastReview);
    
    if($getTransLastReviewRows == 0) {

        $insReview = "INSERT INTO reviews (reviews_from, reviews_to, reviews_message, reviews_rate, reviews_pic, reviews_institution, reviews_when) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getInst."', '".$colname_getRateTxt."', '".$colname_getRate."', '".$colname_getRatePic."', '".$colname_getInst."', '".$when."')";
        mysql_query($insReview, $echoloyalty) or die(mysql_error());

        $reviewOK = 1;

    }
    else if($getTransLastReviewRows > 0) {

        if($row_getTransLastReview['reviews_when'] < $when12h) {

            $insReview = "INSERT INTO reviews (reviews_from, reviews_to, reviews_message, reviews_rate, reviews_pic, reviews_institution, reviews_when) VALUES ('".$row_getUserDevice['user_id']."', '".$colname_getInst."', '".$colname_getRateTxt."', '".$colname_getRate."', '".$colname_getRatePic."', '".$colname_getInst."', '".$when."')";
            mysql_query($insReview, $echoloyalty) or die(mysql_error());

            $reviewOK = 1;

        }
        else {
            $reviewOK = 2;
        }
        
    }

    $newarrmes = array("scan" => '1', "reviewOK" => $reviewOK, "reviews_when" => $when);
    array_push($gotdata, $newarrmes);

?>