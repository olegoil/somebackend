<?php

	// GET USER
  $query_getUser = "SELECT * FROM users WHERE user_work_pos >= '2' && user_pwd != '' && user_pwd != '0' && user_institution = '".$colname_getUser2."'";
  $getUser = mysql_query($query_getUser, $echoloyalty) or die(mysql_error());
  $row_getUser = mysql_fetch_assoc($getUser);
  $getUserRows  = mysql_num_rows($getUser);
			
			$usrArr = array();
			if($getUserRows > 0) {
				
				do {
					
					array_push($usrArr, array("user_id" => $row_getUser['user_id'], "user_name" => $row_getUser['user_name'], "user_surname" => $row_getUser['user_surname'], "user_middlename" => $row_getUser['user_middlename'], "user_pwd" => $row_getUser['user_pwd'], "user_mob" => $row_getUser['user_mob'], "user_work_pos" => $row_getUser['user_work_pos'], "user_menue_exe" => $row_getUser['user_menue_exe'], "user_pic" => $row_getUser['user_pic'], "user_gender" => $row_getUser['user_gender'], "user_institution" => $row_getUser['user_institution'], "user_upd" => $row_getUser['user_upd'], "user_reg" => $row_getUser['user_reg']));
					
				} while ($row_getUser = mysql_fetch_assoc($getUser));
				
			}

  // GET PROFESSIONS
  $query_getProf = "SELECT * FROM professions WHERE prof_when > '1' && (prof_institution = '".$colname_getUser2."' OR prof_institution = '0')";
  $getProf = mysql_query($query_getProf, $echoloyalty) or die(mysql_error());
  $row_getProf = mysql_fetch_assoc($getProf);
  $getProfRows  = mysql_num_rows($getProf);
  
  $profArr = array();
  if($getProfRows > 0) {
    
    do {
      
      array_push($profArr, array("prof_id" => $row_getProf['prof_id'], "prof_name" => $row_getProf['prof_name'], "prof_desc" => $row_getProf['prof_desc'], "prof_institution" => $row_getProf['prof_institution'], "prof_when" => $row_getProf['prof_when']));
      
    } while ($row_getProf = mysql_fetch_assoc($getProf));
    
  }

  // GET ORGANIZATION OFFICE
  $query_getOffice = "SELECT * FROM organizations_office WHERE office_reg > '1' && office_institution = '".$colname_getUser2."'";
  $getOffice = mysql_query($query_getOffice, $echoloyalty) or die(mysql_error());
  $row_getOffice = mysql_fetch_assoc($getOffice);
  $getOfficeRows  = mysql_num_rows($getOffice);
  
  $offArr = array();
  if($getOfficeRows > 0) {
    
    do {
      
      array_push($offArr, array("office_id" => $row_getOffice['office_id'], "office_name" => $row_getOffice['office_name'], "office_start" => $row_getOffice['office_start'], "office_stop" => $row_getOffice['office_stop'], "office_country" => $row_getOffice['office_country'], "office_city" => $row_getOffice['office_city'], "office_adress" => $row_getOffice['office_adress'], "office_timezone" => $row_getOffice['office_timezone'], "office_tel" => $row_getOffice['office_tel'], "office_fax" => $row_getOffice['office_fax'], "office_mob" => $row_getOffice['office_mob'], "office_email" => $row_getOffice['office_email'], "office_skype" => $row_getOffice['office_skype'], "office_site" => $row_getOffice['office_site'], "office_logo" => $row_getOffice['office_logo'], "office_institution" => $row_getOffice['office_institution']));
      
    } while ($row_getOffice = mysql_fetch_assoc($getOffice));
    
  }

  // GET SCHEDULE
  $query_getSchedule = "SELECT * FROM schedule WHERE (schedule_start > '".$when."' && schedule_institution = '".$colname_getUser2."') || (schedule_start < '".$when."' && schedule_stop > '".$when."' && schedule_institution = '".$colname_getUser2."')";
  $getSchedule = mysql_query($query_getSchedule, $echoloyalty) or die(mysql_error());
  $row_getSchedule = mysql_fetch_assoc($getSchedule);
  $getScheduleRows  = mysql_num_rows($getSchedule);
  
  $schArr = array();
  if($getScheduleRows > 0) {
    
    do {
      
      array_push($schArr, array("schedule_id" => $row_getSchedule['schedule_id'], "schedule_employee" => $row_getSchedule['schedule_employee'], "schedule_menue" => $row_getSchedule['schedule_menue'], "schedule_office" => $row_getSchedule['schedule_office'], "schedule_start" => $row_getSchedule['schedule_start'], "schedule_stop" => $row_getSchedule['schedule_stop'], "schedule_institution" => $row_getSchedule['schedule_institution'], "schedule_when" => $row_getSchedule['schedule_when']));
      
    } while ($row_getSchedule = mysql_fetch_assoc($getSchedule));
    
  }
			
			$newarrmes = array("waiters" => '1', "usrArr" => $usrArr, "profArr" => $profArr, "offArr" => $offArr, "schArr" => $schArr, "appVers" => $row_getInst['org_appvers'], "appUrl" => $row_getInst['org_appurl']);
  array_push($gotdata, $newarrmes);

?>