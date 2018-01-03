<?php

$themsg = $_POST;
if (isset($themsg)) {

    include 'incs/_app_send_gcm.php';

    include 'incs/_app_protect.php';

    $hostname_echoloyalty = "";
    $database_echoloyalty = "";
    $username_echoloyalty = "";
    $password_echoloyalty = "";

    $echoloyalty = mysql_pconnect(
    $hostname_echoloyalty, 
    $username_echoloyalty, 
    // $password_echoloyalty);
    $password_echoloyalty) or trigger_error(mysql_error(),E_USER_ERROR);

    $when = time();
    $thetime = $when - 60*30;

    $colname_getUser = "%";
    if (isset($themsg['mem_id'])) {
      $colname_getUser = $themsg['mem_id'];
    }
    $colname_getUser2 = "%";
    if (isset($themsg['inst_id'])) {
      $colname_getUser2 = $themsg['inst_id'];
    }
    $colname_getUser3 = "%";
    if (isset($themsg['mem_key'])) {
      $colname_getUser3 = $themsg['mem_key'];
    }
    $colname_getUser4 = "%";
    if (isset($themsg['site_id'])) {
      $colname_getUser4 = $themsg['site_id'];
    }
    $colname_getUser5 = "%";
    if (isset($themsg['prof'])) {
      $colname_getUser5 = $themsg['prof'];
    }
    $colname_getUser6 = "-1";
    if (isset($themsg['newusr'])) {
      $colname_getUser6 = $themsg['newusr'];
    }

    if($echoloyalty) {

      mysql_query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
      
      include 'incs/_app_usrorgdata.php';

      include 'incs/_app_endingwarn.php';

      include 'incs/_app_admin_mess.php';

    }

    $gotdata = array(
  		"sEcho" => 0,
  		"iTotalRecords" => 0,
  		"iTotalDisplayRecords" => 0,
  		"aaData" => array()
  	);

    // CONTROLL PANEL
    if($artNumRows > 0 && $echoloyalty) {

        if($colname_getUser4 == 'main') {

            include 'incs/_panel_main.php';
			
        }
        else if($colname_getUser4 == 'news') {
    
			include 'incs/_panel_news.php';
          
        }
        else if($colname_getUser4 == 'gifts') {
    
			include 'incs/_panel_gifts.php';
          
        }
        else if($colname_getUser4 == 'event') {

            include 'incs/_panel_event.php';
			
        }
        else if($colname_getUser4 == 'points') {

          include 'incs/_panel_points.php';
		  
        }
        else if($colname_getUser4 == 'push') {

          include 'incs/_panel_push.php';
		  
        }
        else if($colname_getUser4 == 'profile') {

          include 'incs/_panel_profile.php';

        }
        else if($colname_getUser4 == 'asks') {

			include 'incs/_panel_asks.php';
          
        }
        else if($colname_getUser4 == 'clients') {
    
			include 'incs/_panel_clients.php';
  
        }
  			else if($colname_getUser4 == 'personal') {
  				
				include 'incs/_panel_personal.php';
  				
  			}
  			else if($colname_getUser4 == 'reviews') {
          
				include 'incs/_panel_reviews.php';
  
        }
        else if($colname_getUser4 == 'statistics') {

			include 'incs/_panel_statistics.php';
          
        }
        else if($colname_getUser4 == 'country') {

            include 'incs/_panel_country.php';

        }
        else if($colname_getUser4 == 'region') {

			include 'incs/_panel_region.php';
           
        }
        else if($colname_getUser4 == 'city') {

			include 'incs/_panel_city.php';
          
        }
        else if($colname_getUser4 == 'categories') {
            
			include 'incs/_panel_categories.php';
          
        }
        else if($colname_getUser4 == 'goods') {
            
			include 'incs/_panel_goods.php';
          
        }
        else if($colname_getUser4 == 'menue') {
            
			include 'incs/_panel_menue.php';
          
        }
        else if($colname_getUser4 == 'support') {

			include 'incs/_panel_support.php';
            
        }
        else if($colname_getUser4 == 'calendar') {
    
			include 'incs/_panel_calendar.php';
          
        }
        else if($colname_getUser4 == 'schedule') {
    
			include 'incs/_panel_schedule.php';
		
        }
        else if($colname_getUser4 == 'professions') {
			
			include 'incs/_panel_professions.php';
          
        }
        else if($colname_getUser4 == 'office') {
          
			include 'incs/_panel_office.php';
		 
        }
        else if($colname_getUser4 == 'checkphone') {
          
			include 'incs/_panel_checkphone.php';
		 
        }

        echo json_encode($gotdata);

    }
    // NO KEY -> AUTO LOGOUT
    else if($artNumRows == 0 && $echoloyalty) {

        $newarrmes = array("logout" => $themsg);
        array_push($gotdata, $newarrmes);

        echo json_encode($gotdata);
        
    }


}

?>