<?php
/**
 * furnizeaza un array cu tagurile pentru autocomplete
 * deci probabil pentru editMODE
 */
$DB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);


$tags = array();
$res = $DB->query("SELECT tagName FROM blogTags");
while($row = $res->fetch_assoc()){

    array_push($tags, $row['tagName']);

}

print json_encode($tags);