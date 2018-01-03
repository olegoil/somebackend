<?php

	$push_push = -1;
	  if (isset($themsg['push_push'])) {
	    $push_push = $protect($themsg['push_push']);
	  }

	  $push_rec = -1;
	  if (isset($themsg['push_rec'])) {
	    $push_rec = $protect($themsg['push_rec']);

	    $query_getPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_id='".$push_push."' LIMIT 1";
	    $getPushed = mysql_query($query_getPushed, $echoloyalty) or die(mysql_error());
	    $row_getPushed = mysql_fetch_assoc($getPushed);
	    $getPushedRows  = mysql_num_rows($getPushed);

	    if($getPushedRows > 0) {

	      $updPush = "UPDATE pushreceive SET push_rec_received='".$when."' WHERE push_rec_user='".$row_getUserDevice['user_id']."' && push_rec_push='".$push_push."' && push_rec_institution='".$colname_getUser2."'";
	      mysql_query($updPush, $echoloyalty) or die(mysql_error());

	    }
	    else {

	      $insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$push_push."', '".$row_getUserDevice['user_id']."', '".$when."', '0', '".$colname_getUser2."', '".$when."')";
	      mysql_query($insPush, $echoloyalty) or die(mysql_error());

	    }

	  }

	  $push_open = -1;
	  if (isset($themsg['push_open'])) {
	    $push_open = $protect($themsg['push_open']);

	    $query_getPushed = "SELECT * FROM pushmessages WHERE push_institution = '".$colname_getUser2."' && push_id='".$push_push."' LIMIT 1";
	    $getPushed = mysql_query($query_getPushed, $echoloyalty) or die(mysql_error());
	    $row_getPushed = mysql_fetch_assoc($getPushed);
	    $getPushedRows  = mysql_num_rows($getPushed);

	    if($getPushedRows > 0) {

	      $updPush = "UPDATE pushreceive SET push_rec_opened='".$when."' WHERE push_rec_user='".$row_getUserDevice['user_id']."' && push_rec_push='".$push_push."' && push_rec_institution='".$colname_getUser2."'";
	      mysql_query($updPush, $echoloyalty) or die(mysql_error());

	    }
	    else {

	      $insPush = "INSERT INTO pushreceive (push_rec_push, push_rec_user, push_rec_received, push_rec_opened, push_rec_institution, push_rec_when) VALUES ('".$push_push."', '".$row_getUserDevice['user_id']."', '".$when."', '".$when."', '".$colname_getUser2."', '".$when."')";
	      mysql_query($insPush, $echoloyalty) or die(mysql_error());

	    }

	  }

?>