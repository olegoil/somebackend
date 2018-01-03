<?php

$colname_getAsksId = -1;
if (isset($themsg['asks_id'])) {
  $colname_getAsksId = $themsg['asks_id'];
}
$colname_getAsksAnsw = -1;
if (isset($themsg['asks_answ'])) {
  $colname_getAsksAnsw = $themsg['asks_answ'];
}

$asksOK = 0;

// secure code
$query_getAsks = "SELECT * FROM asks WHERE asks_id = '".$colname_getAsksId."' && asks_institution = '".$colname_getUser2."' LIMIT 1";
$getAsks = mysql_query($query_getAsks, $echoloyalty) or die(mysql_error());
$row_getAsks = mysql_fetch_assoc($getAsks);
$getAsksRows  = mysql_num_rows($getAsks);

if($getAsksRows > 0) {

    if($colname_getAsksAnsw == 1) {
        $asksY = $row_getAsks['asks_yes'] + 1;
        $asksUpd = "UPDATE asks SET asks_yes='".$asksY."', asks_when='".$when."' WHERE asks_id = '".$colname_getAsksId."'";
        mysql_query($asksUpd, $echoloyalty) or die(mysql_error());
        $asksOK = 1;
    }
    else if($colname_getAsksAnsw == 2) {
        $asksN = $row_getAsks['asks_no'] + 1;
        $asksUpd = "UPDATE asks SET asks_no='".$asksN."', asks_when='".$when."' WHERE asks_id = '".$colname_getAsksId."'";
        mysql_query($asksUpd, $echoloyalty) or die(mysql_error());
        $asksOK = 2;
    }

}

$newarrmes = array("asks" => '1', "asksOK" => $asksOK, "asks_id" => $colname_getAsksId, "asks_when" => $when);
array_push($gotdata, $newarrmes);

?>