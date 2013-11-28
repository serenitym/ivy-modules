<?php
/**
 * furnizeaza un array cu tagurile pentru autocomplete
 * deci probabil pentru editMODE
 */
$DB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

//var_dump($_POST);

$records = array();
$res = $DB->query(
    "SELECT title AS label, idRecord AS value
     FROM blogRecords WHERE title LIKE '%".$_POST['searchTerm']."%' ");
while($row = $res->fetch_assoc()){

    array_push($records, $row);

}

print json_encode($records);
