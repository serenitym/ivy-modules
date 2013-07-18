<?php
# pentru mai multe detalii vizualizeaza templateul ratingRecord.html


/**
     * Ce anume este  trimis in post?
     *
     * - starsNo        # votul curent al userului
     * - uStars         # votul userului in prealabil
     *
     * - nrRates           # numarul de voturi
     *
     * - totalRating    # ratingul total
     *
     * - uid            # ID user
     *
     *
     * - postFix_table     # postfixul tabelul de rating
     * - extKey_name    # numele coloanei care face legatura externa
     * - extKey_value   # valoarea cheii externe
     *
     */

/**
 *  Ce mai este nevoie
 *
 *  - stars
 *  - ustars
 *
 */

$DB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);



$starNo          = $_POST['starNo']      =  intval($_POST['starNo']);
$uStars          = $_POST['uStars']      =  intval($_POST['uStars']);
$nrRates         = $_POST['nrRates']     =  intval($_POST['nrRates']);
$totalRating     = $_POST['totalRating'] =  intval($_POST['totalRating']);

$uid             = $_POST['uid']         =  intval($_POST['uid']);
$postFix_table   = $_POST['postFix_table'];
$extKey_name     = $_POST['extKey_name'];
$extKey_value    = $_POST['extKey_value'];

#var_dump($_POST);

#===========Processing Post vars=====================================

if($uStars == 0) {

    $nrRates++;
    $totalRating += $starNo;

}
else{
    $totalRating = $totalRating - $uStars + $starNo;
}



$Rating = ceil($totalRating / $nrRates);

$stars  = array('-empty','-empty','-empty','-empty','-empty');
$ustars = array('-empty','-empty','-empty','-empty','-empty');

for($i=0; $i<$starNo;$i++)   $ustars[$i] = '';      #stele pentru user
for($i=0; $i<$Rating;$i++)   $stars[$i] = '';       #stele in general


#=================================================================================

if($uStars == 0){
    $query = "INSERT into rating_{$postFix_table}
                           (uid, {$extKey_name}, rating)
                           VALUES ('{$uid}','{$extKey_value}','{$starNo}')";

}
else{
    $query = "UPDATE  rating_{$postFix_table}
                      SET rating = '{$starNo}'
                      WHERE {$extKey_name} = '{$extKey_value}' AND uid = '{$uid} ' ";

}

$DB->query($query);


#===========================[ TESTE ]======================================================
#echo $query;

/*$_POST['nrRates']     = $nrRates;
$_POST['Rating']      = $Rating;
$_POST['totalRating'] = $totalRating ;*/

#var_dump($_POST);

#=================================================================================


$tmpl_content = file_get_contents(FW_PUB_PATH.'PLUGINS/rating/tmpl/ratingRecord_bigStar.html');

$display ='';
eval("\$display = \"$tmpl_content\";");

echo $display;




