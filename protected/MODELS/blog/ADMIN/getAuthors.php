<?php
$DB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
$DB->set_charset('utf8');
$q = $DB->real_escape_string($_GET["q"]);

$authors = array();
$query = "SELECT uid AS id , CONCAT( first_name , ' ' , last_name) AS name
         FROM auth_user_details
         WHERE first_name LIKE '%".$q."%' OR last_name LIKE '%".$q."%'
         ;
";
//error_log("************************** query  CAUTAT ESTE ".$query ) ;


$res = $DB->query($query);
while($row = $res->fetch_assoc()){

    array_push($authors, $row);

}

print json_encode($authors);
