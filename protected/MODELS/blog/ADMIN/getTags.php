<?php
/**
 * furnizeaza un array cu tagurile pentru autocomplete
 * deci probabil pentru editMODE
 */
$DB = new mysqli(dbHost,dbUser,dbPass,dbName);


$tags = array();
$res = $DB->query("SELECT tagName FROM blogTags");
while($row = $res->fetch_assoc()){

    array_push($tags, $row['tagName']);

}

print json_encode($tags);