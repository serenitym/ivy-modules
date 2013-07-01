<?php
class dummy{}
class Product
{


    var $img_small;
    var $img_big;
    var $ED = '';

  //_________[PRODUCT]___________________
    var $name ;
    var $small_desc ;
    var $big_desc;
    var $price ;
    var $IDpr;
    var $Pid;

    var $promo='';
    var $end_promo;
    var $new;
    var $end_new;

    var $statusPromo='';

}


class Cproducts extends Product
{
    var $C;
    var $DB;
    var $lang;
    var $tree;
    var $ENDpoints='';
    var $basePath;
    var $level;
  //____________________________________
    var $DEF_img_small   ;
    var $DEF_img_big   ;

    var $priceSTR;
    var $detailsSTR;

    var $DISPLAY_page;

    var $check_ProductDisplay = false;
    var $nrItems = 12;   #numarul de produse pe pagina, configurabil






    function RESET_DB($type)                  {

           $id=$this->IDpr;
           $this->DB->query("UPDATE products SET end_{$type}=NULL , {$type}=NULL WHERE id='{$id}' ");
       }

    function CHECK_valability($endDATE)       {
         //start_ , promo/new, id-ul produsului - daca nu mai este valabila elimina promotia sau NEWstatus din BD

           $TMSP_end = strtotime($endDATE);
           $TMSP_today = time();

           if($TMSP_end > $TMSP_today)  return true;
           else return false;
       }

    function CHECK_RES($res)    {



        #returned variables
        /**
         * name
         * id
         * Pid
         * small_desc
         * big_desc
         * price
         * promo
         * end_promo
         * new
         * end_new
         * imagine
         */

        $res['name'];
        $res['nameF']        = str_replace(' ','_',$res['name']);
        $id = $res['IDpr']         = $res['id'];
        $Pid = $res['Pid']  ;
        
        $res['small_desc']   = (isset($res['small_desc']) ? trim($res['small_desc']) : '');
        $res['big_desc']     = (isset($res['big_desc']) ?  trim($res['big_desc']) : '');


        $res['priceVal']    = $res['price'];
                              $priceARR = explode('.',strval($res['price']));
        $res['price']       = $priceARR[0]."<span style='font-size:11px;'>.".$priceARR[1]."</span>";


        $pic_PATH            = $res['imagine'];


        #________________________________________________________________________________________________________

              if($res['end_promo']  )
              {
                  if(!$this->CHECK_valability($res['end_promo']) )
                  {
                      $res['statusPROMO']='';
                      $this->RESET_DB('promo');
                  }
                  else
                      $res['statusPROMO'] ='promo';
              }
              if($res['new']   && !$this->CHECK_valability($res['end_new'])   )
              {
                  $res['new']='';
                  $this->RESET_DB('new');
              }


             $res['promo']           = ($res['promo'] ? 'Promo: '.$res['promo'].' lei' : '' );
             $res['new']             = ($res['new'] ? 'new' : '')       ;

        #________________________________________________________________________________________________________
               $res['img_small'] = $this->DEF_img_small;
               $res['img_big']   = $this->DEF_img_big  ;

               if(is_file(fw_pubPath.'MODELS/products/RES/small_img/'.$pic_PATH))
                   $res['img_small'] = $this->basePath.'small_img/'.$pic_PATH;

               if(is_file(fw_pubPath.'MODELS/products/RES/big_img/'.$pic_PATH))
                   $res['img_big']   = $this->basePath.'big_img/'.$pic_PATH;




        $idC = $this->C->idNode;
        $idT = $this->C->idTree;


        $res['priceSTR']   = $this->priceSTR;
        $res['detailsSTR'] = $this->detailsSTR;

        $res['LG']          = $this->lang;
        $res['ED']          = $this->ED = ($this->level==3 ? '' : 'n');

    //____________________________________________________________



        $idC = ($idC!=1 ? $Pid : $Pid);
        $res['href'] =  $href = "{$idT}-{$idC}-{$id}/".$this->C->history_TITLE.$res['nameF'];


        # daca suntem la displayul unui produs atunci trebuie sa setam SEO pt el
        if(isset($_GET['IDpr']) && $this->check_ProductDisplay)
        {
             #========================================== [ META'S] ==============================================================

                    $this->C->SEO->meta_DESCRIPTION .=   $res['small_desc'];
                    $this->C->SEO->meta_KEYWORDS    .=   $res['name'];


             #===================================================================================================================

               $res['IDpr'] = $IDpr = $_GET['IDpr'];

               $priceARR = explode('.',strval($res['priceVal']));
               $res['price']     = $priceARR[0]."<span style='font-size:19px;'>.".$priceARR[1]."</span>";

            #__________________________________________________________________________________________________________________


                $res['desc']       = ( $res['big_desc'] ? $res['big_desc'] : $res['small_desc']);
                $res['priceSTR']   = ( $res['LG']=='ro' ? 'Pret:' : 'Price:');
                $res['buySTR']     = ( $res['LG']=='ro' ? 'Cumpara' : 'Buy');
                $res['piecesSTR']  = ( $res['LG']=='ro' ? 'Cantitate:' : 'Pieces:');
                $res['nrITEMS']    = ($_SESSION['basket'][$IDpr] ? $_SESSION['basket'][$IDpr] : 1);


        }

        return $res;

    }


    function getENDpoints($id)  {
        $ch = $this->C->tree[$id]->children;
        if($ch)
            foreach($ch AS $id_ch) $this->getENDpoints($id_ch);
        else $this->ENDpoints .="'{$id}',";


    }  #pentru level1 DISPLAY


    function GET_PRODUCT()      {
        $LG = $this->lang;
        $IDpr = $_GET['IDpr'];
        $Pid  = $idC = $this->C->idNode;
                $idT = $this->C->idTree;





        $query = "SELECT id,Pid, name_{$LG} AS name, description_{$LG} AS big_desc, small_description_{$LG} AS small_desc, price,
               new, DATE_FORMAT(end_new,' %d-%m-%Y') AS end_new, promo, DATE_FORMAT(end_promo,' %d-%m-%Y') AS end_promo, imagine
                                                           FROM products
                                                      LEFT outer JOIN imagini ON(products.id = imagini.id_produs)
                                                    WHERE id='{$IDpr}'
";
        $querySIM = "SELECT  id,Pid, name_{$LG} AS name, small_description_{$LG} AS small_desc, price,
          new, DATE_FORMAT(end_new,' %d-%m-%Y') AS end_new, promo, DATE_FORMAT(end_promo,' %d-%m-%Y') AS end_promo, imagine
                                                      FROM products
                                                 LEFT outer  JOIN imagini ON(products.id = imagini.id_produs)
                                                 WHERE Pid='{$Pid}' ORDER BY rand()

";
    #=============================================== [ BACK HISTORY ] ==================================================


        $prod = new dummy();

        if($idT!=1 )
        {
            $prod->level        = $level       = (($idC==$idT) ? '1' : '3');
            $prod->backName     = $backName    = $this->C->tree[$idC]->name;
            $prod->history      = $history     = $this->C->history_TITLE;

            $prod->back         = $back        = "<a href='{$idT}-{$idC}-L{$level}/{$history}'> back to &nbsp;<b>$backName</b></a></div>";
            #old href:    index.php?idC={$idT}&idT={$idT}&level=nr
            #newFormat:   idT-idC-L{level} / backName;

        }

        if($_GET['idT']==1)
        {
               $prod->back       = $back        = "<a href='1-1/Home'> back to &nbsp;<b>Home</b></a></div>";   #old href :   index.php?idC=1&idT=1

        }

        $prod->historyHREF = $historyHREF = $this->C->history_HREF;
        $prod->home        = ($idT!=1 ? true : false );
     #==================================================================================================================

        $this->check_ProductDisplay = true;
        $query    =  $query." LIMIT 0,1 ";
        $product  =  $this->C->GET_objProperties($this,$query,'CHECK_RES');

        #echo '<b>product</b></br></br></br></br></br></br>';
       # var_dump($product);
       # echo '<b>pdALL</b></br></br></br></br></br></br>';

        $this->check_ProductDisplay = false;
        $query =  $querySIM." LIMIT 0,4 ";
        $prod->similarPROD  =  $this->C->GET_objProperties($this,$query,'CHECK_RES');



        $prodARR     = get_object_vars($prod);
        $prodALL     = array_merge($product[0],$prodARR);  #pentru ca vreau sa concatenez rezultatele

        #var_dump($prodALL);


        $disp = $this->C->getDisplay($prodALL,'MODELS','products',$this->template,'product');
        return $disp;


    }

    function GET_LEVEL()        {

               $LG = $this->lang;
               $idC = $this->C->idNode;
               $idT = $this->C->idTree;


               $this->level = $level = (isset($_GET['level']) ? $_GET['level'] : 0 );
               $this->ED = ($level==3 ? '' : 'n');

               $ENDpoints  = '';
               $startLIMIT =0;
               $pagination = '';
               $querys= array();
#=======================================================================================================================
               if($level == 1)
               {
                       $this->getENDpoints($idC);
                       $ENDpoints = '('.substr($this->ENDpoints,0,-1).')';
               }
               elseif($level == 3)
               {
                    $queryPag   = "SELECT id FROM products  WHERE Pid='{$idC}' ";
                    $page       = (isset($_GET['Pn']) ? $_GET['Pn'] : 1);
                    $startLIMIT = ($page-1)*$this->nrItems;                                     //ex : daca $page=2; => startLIMIT =12;


                   $GETargs    = array('idT'=>$idT, 'idC'=>$idC, 'level'=>$level);
                   $pagination = $this->C->GET_pagination($queryPag,$this->nrItems, $GETargs,$idC);
               }


              //"SELECT imagine FROM imagini where id_produs='".$this->IDpr."' "
#=======================================================================================================================
             //table: products -- id , Pid, name_[LG], description_[LG], small_description_[LG], price
               $querys[0] = "SELECT  id,Pid, name_{$LG} AS name, small_description_{$LG} AS small_desc, price,
                     new, DATE_FORMAT(end_new,' %d-%m-%Y') AS end_new, promo, DATE_FORMAT(end_promo,' %d-%m-%Y') AS end_promo, imagine
                                            FROM products
                                      LEFT outer JOIN imagini ON(products.id = imagini.id_produs)

                                            ORDER BY rand()

";             $querys[1] =
"                         SELECT id, Pid, name_{$LG} AS name, small_description_{$LG} AS small_desc, price,
        new, DATE_FORMAT(end_new,' %d-%m-%Y') AS end_new, promo, DATE_FORMAT(end_promo,' %d-%m-%Y') AS end_promo, imagine
                                                    FROM products
                                                  LEFT outer JOIN imagini ON(products.id = imagini.id_produs)

                                WHERE (end_promo > NOW() OR end_promo IS NULL) AND  Pid IN  {$ENDpoints} ORDER BY end_promo desc, id desc

";               $querys[3] =
"                         SELECT  id, Pid, name_{$LG} AS name, small_description_{$LG} AS small_desc, price,
           new, DATE_FORMAT(end_new,' %d-%m-%Y') AS end_new, promo, DATE_FORMAT(end_promo,' %d-%m-%Y') AS end_promo,  imagine
                                                       FROM products
                                            LEFT outer  JOIN imagini ON(products.id = imagini.id_produs)
                                                    WHERE Pid='{$idC}' ORDER BY id desc

";

         $query =  $querys[$level]." LIMIT {$startLIMIT},{$this->nrItems} ";
         #=============================================================================================================



           $prod = new dummy();
          # $prod->productItems      = $this->PROCESS_QUERY(false,$querys[$level],$startLIMIT,12);
           $prod->productItems      =$this->C->GET_objProperties($this,$query,'CHECK_RES');

           $prod->backName   = $backName   = $this->C->tree[$idT]->name;
           $prod->backNameF  = $backNameF  = str_replace(' ','_',$backName);
           $prod->backHREF   = $backHREF   = "{$backNameF}";              #pentru ca se intoarce mereu la level 1
           $prod->backCAT    = $backCAT    = ( ($idT!=1 && $level==3 )?  "<a href='{$backHREF}'> back to &nbsp;<b> $backName </b></a>" : '');
           $prod->img_small  = $img_small  = $this->DEF_img_small;
           $prod->priceSTR   = $priceSTR   = $this->priceSTR;
           $prod->detailsSTR = $detailsSTR = $this->detailsSTR;
           $prod->history    = $history    = $this->C->history_HREF;
           $prod->pagination = $pagination;

           $products = $this->C->getDisplay($prod,'MODELS','products',$this->template,'products');
           return $products;




           #old href:    index.php?idC={$idT}&idT={$idT}&level=1
           #newFormat:   idT-idT-L1 / backName;
           # $backHREF  = "{$idT}-{$idT}-L1/{$backNameF}";

        /** REQ tmpl - DISPLAY_PRODUCTS
                *
                *  history
                *  backCAT
                *  ED
                *  new
                *  href
                *  img_small
                *  name
                *  small_desc
                *  statusPROMO
                *  priceSTR
                *  price
                *  detailsSTR
                *  promo
                *
                 */




          #var_dump($prod);



       }


    function _setINI()           {


        $this->priceSTR     = $this->arr_priceSTR[ $this->lang];
        $this->detailsSTR   = $this->arr_detailsSTR[ $this->lang];

        #===============================================================================================================
        #presetez display-ul pentru a putea recupera name, si description pt meta-uri;

        if(isset($_GET['IDpr']))  $this->DISPLAY_page =  $this->GET_PRODUCT();
        else $this->DISPLAY_page =  $this->GET_LEVEL();

    }


    function __construct($C)    {

        $this->arr_priceSTR     = array('ro'=>'Pret:', 'en'=>'Price:');
        $this->arr_detailsSTR   = array( 'ro'=>'DETALII' , 'en'=>'DETAILS');

        $this->basePath      =  fw_pubURL.'MODELS/products/RES/';
        $this->DEF_img_small = $this->img_small =  $this->basePath.'small_img/site_produs_slice_pisici.jpg';
        $this->DEF_img_big   = $this->img_big   =  $this->basePath.'big_img/site_geanta_produs.jpg';

        /*$this->C = &$C;
        $this->tree = &$C->tree;
        $this->DB = &$C->DB;
        $this->lang = &$C->lang;

        $this->_setINI();*/

    }
}