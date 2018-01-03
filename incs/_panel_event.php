<?php
if(isset($colname_getUser5) && $colname_getUser5 != '%') {

	$colname_eventid = "-1";
	if (isset($themsg['eventid'])) {
	  $colname_eventid = protect($themsg['eventid']);
	}
	$colname_chid = "-1";
	if (isset($themsg['chid'])) {
	  $colname_chid = protect($themsg['chid']);
	}
	$colname_newtitle = "-1";
	if (isset($themsg['newtitle'])) {
	  $colname_newtitle = protect($themsg['newtitle']);
	}
	$colname_newmessage = "-1";
	if (isset($themsg['newmessage'])) {
	  $colname_newmessage = protect($themsg['newmessage']);
	}
	$colname_newdate = "-1";
	if (isset($themsg['newdate'])) {
	  $colname_newdate = protect($themsg['newdate']);
	}
	$colname_newautomatic = "-1";
	if (isset($themsg['newautomatic'])) {
	  $colname_newautomatic = protect($themsg['newautomatic']);
	}
	$colname_newpoints = "-1";
	if (isset($themsg['newpoints'])) {
	  $colname_newpoints = protect($themsg['newpoints']);
	}
	$colname_newdiscount = "-1";
	if (isset($themsg['newdiscount'])) {
	  $colname_newdiscount = protect($themsg['newdiscount']);
	}
		
	if($colname_getUser5 == 'del') {

		$query_getEventChng = "SELECT * FROM events WHERE event_institution = '".$colname_getUser2."' && event_id = '".$colname_eventid."' LIMIT 1";
		$getEventChng = mysql_query($query_getEventChng, $echoloyalty) or die(mysql_error());
		$row_getEventChng = mysql_fetch_assoc($getEventChng);
		$getEventChngRows  = mysql_num_rows($getEventChng);

		if($getEventChngRows > 0) {

			$delEvents = "UPDATE events SET event_when='1', event_status='0' WHERE event_institution = '".$colname_getUser2."' && event_id='".$colname_eventid."'";
			mysql_query($delEvents, $echoloyalty) or die(mysql_error());

			// $rootLink = '/httpdocs/admin/';

			// if(unlink($rootLink.'img/event/'.$colname_getUser2.'/slide/'.$row_getEventChng['event_pic']) && unlink($rootLink.'img/event/'.$colname_getUser2.'/pic/'.$row_getEventChng['event_pic']) && unlink($rootLink.'img/event/'.$colname_getUser2.'/th/'.$row_getEventChng['event_pic'])) {

			//     $delEvent = "DELETE FROM events WHERE event_institution = '".$colname_getUser2."' && event_id='".$colname_eventid."'";
			//     mysql_query($delEvent, $echoloyalty) or die(mysql_error());

			// }

			$newarrmes = array("requests" => '1', "eventId" => $colname_eventid, "eventUpd" => '2');
			array_push($gotdata, $newarrmes);

		}

	}
	else if($colname_getUser5 == 'change') {

		$query_getEventChng = "SELECT * FROM events WHERE event_institution = '".$colname_getUser2."' && event_id = '".$colname_chid."' LIMIT 1";
		$getEventChng = mysql_query($query_getEventChng, $echoloyalty) or die(mysql_error());
		$row_getEventChng = mysql_fetch_assoc($getEventChng);
		$getEventChngRows  = mysql_num_rows($getEventChng);

		if($getEventChngRows > 0) {

			$newDate = strtotime($colname_newdate);

			$chngEvents = "UPDATE events SET event_name='".$colname_newtitle."', event_desc='".$colname_newmessage."', event_auto='".$colname_newautomatic."', event_date='".$newDate."', event_bill='".$colname_newdiscount."', event_points='".$colname_newpoints."', event_when='".$when."' WHERE event_institution = '".$colname_getUser2."' && event_id='".$colname_chid."'";
			mysql_query($chngEvents, $echoloyalty) or die(mysql_error());

			$newarrmes = array("requests" => '1', "eventId" => $colname_chid, "eventUpd" => '3');
			array_push($gotdata, $newarrmes);

		}

	}
	else {

		$query_getEventChng = "SELECT * FROM events WHERE event_institution = '".$colname_getUser2."' && event_id = '".$colname_getUser5."' LIMIT 1";
		$getEventChng = mysql_query($query_getEventChng, $echoloyalty) or die(mysql_error());
		$row_getEventChng = mysql_fetch_assoc($getEventChng);
		$getEventChngRows  = mysql_num_rows($getEventChng);
		
		if($getEventChngRows > 0) {
			if($row_getEventChng['event_auto'] == '0') {
				$updEvent = "UPDATE events SET event_auto='1' WHERE event_id='".$colname_getUser5."'";
				mysql_query($updEvent, $echoloyalty) or die(mysql_error());
				$newarrmes = array("requests" => '1', "eventId" => $colname_getUser5, "eventUpd" => '1');
				array_push($gotdata, $newarrmes);
			}
			else {
				$updEvent = "UPDATE events SET event_auto='0' WHERE event_id='".$colname_getUser5."'";
				mysql_query($updEvent, $echoloyalty) or die(mysql_error());
				$newarrmes = array("requests" => '1', "eventId" => $colname_getUser5, "eventUpd" => '0');
				array_push($gotdata, $newarrmes);
			}
		}

	}
	
}
else {

	$query_getEventsC = "SELECT * FROM events WHERE event_institution = '".$colname_getUser2."' && event_when > '1'";
	$getEventsC = mysql_query($query_getEventsC, $echoloyalty) or die(mysql_error());
	$row_getEventsC = mysql_fetch_assoc($getEventsC);
	$getEventsCRows  = mysql_num_rows($getEventsC);

	$eventsarr = array();
	if($getEventsCRows > 0) {
	  
	  do {
		
		array_push($eventsarr, array($row_getEventsC['event_id'], $row_getEventsC['event_name'], $row_getEventsC['event_desc'], $row_getEventsC['event_pic'], $row_getEventsC['event_auto'], $row_getEventsC['event_date'], $row_getEventsC['event_institution'], $row_getEventsC['event_bill'], $row_getEventsC['event_points'], $row_getEventsC['event_status'], $row_getEventsC['event_when']));
		
	  } while ($row_getEventsC = mysql_fetch_assoc($getEventsC));
	  
	}

	$eventsarrmes = array("instN" => $row_getInst['org_name'], "my_id" => $row_getUser['user_id'], "usrN" => $row_getUser['user_name'], "usrSN" => $row_getUser['user_surname'], "usrWP" => $row_getUser['user_work_pos'], "usrPic" => $row_getUser['user_pic'], "instPic" => $row_getInst['org_logo'], "orgCity" => $row_getCity['name'], "eventsAll" => $eventsarr);
	array_push($gotdata, $eventsarrmes);

}

?>