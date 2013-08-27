<?php
/**
 * furnizeaza un array cu tagurile pentru autocomplete
 * deci probabil pentru editMODE
 */
$DB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

//var_dump($_POST);

$tags = array();
$res = $DB->query("SELECT tagName FROM blogTags WHERE tagName LIKE '%".$_POST['searchTerm']."%' ");
while($row = $res->fetch_assoc()){

    array_push($tags, $row['tagName']);

}

print json_encode($tags);
