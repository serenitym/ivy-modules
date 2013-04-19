<?php
class ACproducts extends Cproducts
{
    var $name ;
    var $small_desc ;
    var $big_desc;
    var $price ;
    var $IDpr;
    var $affectedMODULES = array('menuPROD','siteMap');


    function UPLOADimg()        {
      //  $poz  = $_REQUEST['p'];
        $size = 'L';//$_REQUEST['size'];

        $LG = $this->lang;

        if($_FILES['filename_DTpic_'.$LG]['name'])
        {
            /*echo 'INTRU AICI'.$_FILES['filename']['name'].'-a fost numele --';
            var_dump($_FILES);*/
            $id   = $_REQUEST['id'];
            $stamp = time();

            $base_img = fw_pubPath.'MODELS/products/RES/';


        //($sPhotoFileName, $width, $height, $path='.', $prefix='', $suffix='',$extension='jpg')
            $rsL = new Resize($_FILES['filename_DTpic_'.$LG], 274, 310, $base_img.'big_img','',$stamp);
            $rsS = new Resize($_FILES['filename_DTpic_'.$LG], 130, 107, $base_img.'small_img','',$stamp);


            $oldimgR = $this->DB->query("SELECT imagine FROM imagini WHERE id_produs='$id' ")->fetch_assoc();
            $oldimg = $oldimgR['imagine'];

                       unlink($base_img.'big_img/'.$oldimg);
                       unlink($base_img.'small_img/'.$oldimg);

            $rmimg= "DELETE FROM imagini WHERE id_produs = '$id' ;";
            $updateimg= "INSERT INTO imagini (imagine, id_produs)
                            VALUES ('".$rsL->sThumbnailFileName."', '$id');";


             $this->DB->query($rmimg);
             $this->DB->query($updateimg);
        }


    }
    function delete_product()   {

          $IDpr = $_POST['BLOCK_id'];
          $basePath_local = fw_pubPath.'MODELS/products/RES/';

        //_____________________________________________[ get  picutere ]___________________________________________________________
          $RES_pic = $this->DB->query("SELECT imagine FROM imagini where id_produs='".$IDpr."' ")->fetch_assoc();
          $pic_PATH = $RES_pic['imagine'];

        //______________________________________________________________________________________________________________
          if(is_file(fw_pubPath.'MODELS/products/RES/small_img/'.$pic_PATH))  unlink($basePath_local."small_img/".$pic_PATH);
          if(is_file(fw_pubPath.'MODELS/products/RES/big_img/'.$pic_PATH))    unlink($basePath_local."big_img/".$pic_PATH );

        //______________________________________________________________________________________________________________

         $query = "DELETE from products WHERE id = '{$IDpr}' ";


        $this->DB->query($query);
    }
    function save_addProduct()  {
        $LG = $this->lang;
        $name        = trim($_POST["name_prod_{$LG}"]);
        $small_desc  = trim($_POST["smallDESC_{$LG}"]);
        $price       = $_POST["price_{$LG}"];
        $Pid         = $this->C->idC;

      //____________________________________________________________

        #dupa ce vor fii adaugate restul de produse
/*        $query = "INSERT into products (Pid, name_ro, name_en, small_description_ro, small_description_en,  price, new, end_new  )
                  VALUES ('{$Pid}','{$name}','{$name}','{$small_desc}','{$small_desc}', '{$price}', 1, DATE_ADD(NOW(), INTERVAL 1 WEEK)) ";*/

        $query = "INSERT into products (Pid, name_ro, name_en, small_description_ro, small_description_en,  price )
                  VALUES ('{$Pid}','{$name}','{$name}','{$small_desc}','{$small_desc}', '{$price}') ";
        $this->DB->query($query);

        /**
         * la adaugare de produs va pune aceeasi denumire si descriere in engleza si romana;
         */
       // echo $query;
    }
    function save_SGproduct()   {
        $LG = $this->lang;
        $LG2 = ($LG=='ro' ? 'en' : 'ro');

        $name        = trim($_POST["DTname_{$LG}"]);
        $big_desc    = trim($_POST["DTdesc_{$LG}"]);
        $price       = $_POST["DTprice_{$LG}"];
        $IDpr        = $_GET['IDpr'];

        $RES_desc = $this->DB->query("SELECT small_description_{$LG} AS small_desc, description_{$LG2} AS big_desc2
                                                FROM products  WHERE id='{$IDpr}' ")->fetch_assoc();

        $small_desc = ($RES_desc['small_desc'] ? $RES_desc['small_desc'] : substr(strip_tags($big_desc),0,50) );
        $big_desc2   = ($RES_desc['big_desc2'] ? $RES_desc['big_desc2'] : $big_desc);
        /**
         *  Testeaza daca are sau nu un small_desc -> daca nu o seteaza din BIG_DESCRIPTION
         *  DACA - pentru limba2 nu exista big_description o va pune pe cea curenta
         */



      //________________________________________________________________________________________________________________

        $query = "UPDATE products set name_{$LG}='{$name}',
                                      small_description_{$LG} = '{$small_desc}',
                                      description_{$LG} = '{$big_desc}',
                                      description_{$LG2} = '{$big_desc2}',
                                      price = '{$price}'
                                 WHERE id = '{$IDpr}' ";

       $this->DB->query($query);
       // echo $query;

    }
    function save_EDITProduct() {
        $LG = $this->lang;
        $name        = trim($_POST["name_prod_{$LG}"]);
        $small_desc  = trim($_POST["smallDESC_{$LG}"]);
        $price       = $_POST["price_{$LG}"];
        $IDpr        = $_POST['BLOCK_id'];

     //_________________________________________________________________________________________-

        $query = "UPDATE products set name_{$LG}='{$name}',
                                      small_description_{$LG} = '{$small_desc}',
                                      price = '{$price}'
                                   WHERE id = '{$IDpr}' ";

        $this->DB->query($query);

       /* echo $query;*/

    }


    function _setINI()           {

        parent::_setINI();
        $myPOST = false;

        if(isset($_POST['save_addproduct']))    { $this->save_addProduct();    $myPOST=true; $affected=true;}
        elseif(isset($_POST['delete_product'])) { $this->delete_product();     $myPOST=true; $affected=true;}

        elseif(isset($_POST['save_product']))   { $this->save_EDITProduct();                    $myPOST=true;}
        elseif(isset($_POST['save_SGproduct'])) { $this->save_SGproduct(); $this->UPLOADimg();  $myPOST=true;}
        elseif(isset($_POST['UPLDimg']))        { $this->UPLOADimg(); $this->save_SGproduct();  $myPOST=true;}


   //_______________________________________________________________________________________________________
       if(isset($affected)) $this->C->solve_affectedMOD($this->affectedMODULES);

       if($myPOST)
        {
           unset($_POST);
           header("Location: ".$_SERVER['REQUEST_URI'].'#ANCORA');
           exit;
        }

    }
}