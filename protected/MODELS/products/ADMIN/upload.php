<?php
session_start();
include './Resize.php';

require_once('../../../fw/GENERAL/core/config.php');
$DB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

$poz  = $_REQUEST['p'];
$size = 'L';//$_REQUEST['size'];
$id   = $_REQUEST['id'];
$filename = $_REQUEST['filename'];


//    unlink($_SESSION['rsl']);
//    unlink($_SESSION['rsm']);
//    unlink($_SESSION['rss']);
   //  unset($_SESSION['rsl']);
   //  unset($_SESSION['rsm']);
   //  unset($_SESSION['rss']);

	$stamp = time();

if($_REQUEST['action'] == 'delete') {
    $rmimg= "DELETE FROM imagini WHERE id_produs = '$id' ;";
    $DB->query($rmimg);


}
else {
    $base_img = FW_PUB_PATH.'MODELS/products/RES/';
    $URLbase_img = FW_PUB_URL.'MODELS/products/RES/';


    $rsL = new Resize($_FILES[$filename], 350, 350, $base_img.'big_img','',$stamp);
    $rsS = new Resize($_FILES[$filename], 121, 121, $base_img.'small_img','',$stamp);


    $oldimgR = $DB->query("SELECT imagine FROM imagini WHERE id_produs='$id' ")->fetch_assoc();
    $oldimg = $oldimgR['imagine'];

            unlink($base_img.'big_img/'.$oldimg);
            unlink($base_img.'small_img/'.$oldimg);

        $rmimg= "DELETE FROM imagini WHERE id_produs = '$id' AND pozitie='$poz';";
        $updateimg= "INSERT INTO imagini (imagine, id_produs)
                        VALUES ('".$rsL->sThumbnailFileName."', '$id');";


         $DB->query($rmimg);
         $DB->query($updateimg);

        //unset($_SESSION['picture_name']);

    /*
    echo $rsL->sThumbnailFileName.'<br/>';
    echo $_SESSION['picture_name'].'<br/>';
    echo $_POST['update'].'<br/>';
    echo $_SESSION['rsl'].'<br/>';
    echo $updateprodus.'<br/>';
    */
    unset($_REQUEST['action']);
    echo '<img src="'. $URLbase_img.'big_img/'.$rsL->sThumbnailFileName.'" border="0" />';
    //echo "<input type='hidden' name='update' value='1' />";

    file_put_contents('test.txt','Am ajuns pana in upload'.$rsL->sThumbnailFileName.' \n'.${'rs'.$size}->sFinalImage);
}