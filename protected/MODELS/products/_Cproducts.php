<?php
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


    function DISPLAY_PRODUCT()  {



    #========================================== [ META'S] ==============================================================

                    $this->C->SEO->meta_DESCRIPTION .=  $this->small_desc;
                    $this->C->SEO->meta_KEYWORDS    .=  $this->name;
    #===================================================================================================================

        $LG = $this->lang;
        $img_big    = $this->img_big;
        $name       = $this->name      ;
        $big_desc   = $this->big_desc;
        $small_desc = $this->small_desc;
        $IDpr = $_GET['IDpr'];

     //_________________________________________________________________________________________________________________
        $price     = $this->price     ;
        $promo     = $this->promo     ;
        $new       = $this->new       ;


        $priceARR = explode('.',strval($price));
        $price = $priceARR[0]."<span style='font-size:19px;'>.".$priceARR[1]."</span>";

        $statusPROMO =$this->statusPromo;
        $promo = ($promo ? 'Promo: '.$promo.' lei' : '' );

    //__________________________________________________________________________________________________________________

        $desc = ($big_desc ? $big_desc : $small_desc);
        $priceSTR   = ( $LG=='ro' ? 'Pret:' : 'Price:');
        $buySTR     = ( $LG=='ro' ? 'Cumpara' : 'Buy');
        $piecesSTR  = ( $LG=='ro' ? 'Cantitate:' : 'Pieces:');

        $nrITEMS = ($_SESSION['basket'][$IDpr] ? $_SESSION['basket'][$IDpr] : 1);

     //__________________________________________________________________________________________________

        $product =
"                   <input type='hidden' name='IDpr' value='{$IDpr}'>
                    <div class='SING DTproduct' id='SGproduct_{$IDpr}_{$LG}' >

                            <table>
                                <tr>
                                    <td class='col1'>

                                            <div class='EDtxt DTname'> $name </div>
                                            <div class='EDeditor DTdesc'> $desc </div>
                                            <!--<div> </div>-->


                                            <div id='cumpara' class='{$statusPROMO}'>
                                                <div class='col11'>
                                                        <div class='DIVprice'>{$priceSTR} <div class='EDtxt DTprice'>$price</div>&nbsp;lei</div>
                                                        <div class='DTpromo'>
                                                           {$promo}
                                                        </div>
                                                </div>
                                                <div class='col22'>
                                                 <form action='' method='post' id='buy'>
                                                     {$piecesSTR}&nbsp;<input type='text' name='cantitate' value='{$nrITEMS}' />

                                                    <input type='submit' name='Buy' value='{$buySTR}' />
                                                  </form>
                                                </div>
                                            </div>

                                    </td>
                                    <td class='col2'>
                                        <div class='DIVpic kkIE'>
                                             <div class='EDpic DTpic' > <img src='{$img_big}'  alt='imagine produs'/> </div>
                                        </div>
                                        <div  id='messONLINE'>
                                             <a href='ymsgr:sendIM?elatiamat'>
                                                 <img style='border:0px solid;' alt='Y!M' src='http://opi.yahoo.com/online?u=elatiamat&amp;m=g&amp;t=2' />
                                             </a>
                                        </div>

                                    </td>

                                </tr>
                            </table>


                    </div>
";

         return $product;
    }
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
    //  echo $query."LIMIT 0,1 <br /><br />".$querySIM."LIMIT 0,4 <br />";
    //  return "<div id='back'><a href='index.php?idT={$idT}&idC={$idC}&level={$level}'>back</a></div>".$this->PROCESS_QUERY('DISPLAY_PRODUCT',$query,0,1);

        if($idT!=1 )
        {
            $level = (($idC==$idT) ? '1' : '3');
            $backName = $this->C->tree[$idC]->name;


            $history =$this->C->history_TITLE;
            #old href:    index.php?idC={$idT}&idT={$idT}&level=nr
            #newFormat:   idT-idC-L{level} / backName;
            $back = "<a href='{$idT}-{$idC}-L{$level}/{$history}'> back to &nbsp;<b>$backName</b></a></div>";
        }
        if($_GET['idT']==1)
        {
          #old href :   index.php?idC=1&idT=1
          $back = "<a href='1-1/Home'> back to &nbsp;<b>Home</b></a></div>";

        }

        $historyHREF = $this->C->history_HREF;
     #==================================================================================================================

        $product     = $this->PROCESS_QUERY('DISPLAY_PRODUCT',$query,0,1);
        $similarPROD = $this->PROCESS_QUERY('DISPLAY_PRODUCTS',$querySIM,0,4);


        $disp = "<div id='back'>
                        <span id='history'>".$this->C->history_HREF."</span>
                         {$back}
                         {$product}                                                                                    ";
        if($idT!=1)
        $disp .="        <div id='similarPROD'>
                            <span>Produse similare</span>
                            {$similarPROD}
                                                                                                                       ";
        $disp .=" <!--[if lte IE 8]> <div class='product'></div> <![endif]-->                                          ";
        $disp .= "</div>";

        /*$this->PROCESS_QUERY('DISPLAY_PRODUCTS',$querySIM,0,4)*/

        return $disp;


    }

    function DISPLAY_PRODUCTS() {



        $LG  = $this->lang;
        $idC = $this->C->idNode;
        $idT = $this->C->idTree;


        $priceSTR   = $this->priceSTR;
        $detailsSTR = $this->detailsSTR;
        $img_small  = $this->img_small;

        $this->ED = ($this->level==3 ? '' : 'n');
        $ED = $this->ED;
    //____________________________________________________________
        $name      = $this->name      ;
        $nameF     = str_replace(' ','_',$name);
        $small_desc= $this->small_desc;
        $price     = $this->price     ;
        $id        = $this->IDpr      ;
        $Pid       = $this->Pid      ;

        $promo     = $this->promo     ;
        $new       = ($this->new ? 'new' : '')       ;


        $priceARR = explode('.',strval($price));
        $price = $priceARR[0]."<span style='font-size:11px;'>.".$priceARR[1]."</span>";

        $statusPROMO =$this->statusPromo;
        $promo = ($promo ? 'Promo: '.$promo.' lei' : '' );
        $idC = ($idC!=1 ? $Pid : $Pid);



       // old -  $href = "index.php?idT=$idT&idC=$idC&IDpr=$id";
          $href = "{$idT}-{$idC}-{$id}/".$this->C->history_TITLE.$nameF;
    //_______________________________________________________________________________________________
                $products =
"                            <div class='{$ED}ENT product' id='product_{$id}_{$LG}' >
                                    <div class='{$new}'><div>{$new}</div></div>
                                    <div class='PRDpic'>
                                        <a href='$href'>
                                                <img src='{$img_small}'  alt='imagine produs'/>
                                         </a>
                                    </div>

                                     <div class='EDtxt name_prod' > {$name} </div>
                                     <div class='EDtxt smallDESC'>  {$small_desc}  </div>
                                      <div class='allPRICE{$statusPROMO}'>
                                          <div class='DIVprice'>
                                              $priceSTR
                                              <span class='EDtxt price'>$price</span>
                                              lei
                                          </div>
                                          <div class='DIVdetails'>
                                             <a href='$href'> $detailsSTR </a>
                                          </div>
                                          <div class='DIVpromo'> {$promo} </div>
                                     </div>
                              </div>
";
        return $products;


    }
    function GET_LEVEL()        {

               $LG = $this->lang;
               $idC = $this->C->idNode;
               $idT = $this->C->idTree;
               $ED  = $this->ED;

               $this->level = $level = (isset($_GET['level']) ? $_GET['level'] : 0 );
               $ED = $this->ED = ($level==3 ? '' : 'n');

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
                    $q = "SELECT id FROM products  WHERE Pid='{$idC}' ";
                    $page = (isset($_GET['Pn']) ? $_GET['Pn'] : 1);
                    $startLIMIT = ($page-1)*12;                                     //ex : daca $page=2; => startLIMIT =12;
                    $pagination = $this->pagination($q,$idC,$idT,3,$page);
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
         #=============================================================================================================


           #old href:    index.php?idC={$idT}&idT={$idT}&level=1
           #newFormat:   idT-idT-L1 / backName;

           $backName  = $this->C->tree[$idT]->name;
           $backNameF = str_replace(' ','_',$backName);
           // $backHREF  = "{$idT}-{$idT}-L1/{$backNameF}";
            $backHREF  = "{$backNameF}";              #pentru ca se intoarce mereu la level 1

           $backCAT    = ( ($idT!=1 && $level==3 )?  "<a href='{$backHREF}'> back to &nbsp;<b> $backName </b></a>" : '');
           $img_small  = $this->DEF_img_small;
           $priceSTR   = $this->priceSTR;
           $detailsSTR = $this->detailsSTR;
           $history    = $this->C->history_HREF;

           $products = "
                         <div id='back'>
                             <span id='history'>$history</span>
                              $backCAT
                        </div>
";
           $products.= " <div class='{$ED}allENTS products' id='products_{$LG}'>
                          <div class='{$ED}ENT product' id='product_new_{$LG}' style='display: none !important;' >
                                 <div class='NOTnew'><div></div></div>
                                 <div class='PRDpic'> <img src='{$img_small}'  alt='imagine produs'/> </div>
                                 <div class='EDtxt name_prod' > name</div>
                                 <div class='EDtxt smallDESC'> small description   </div>
                                   <div class='allPRICE-NOstatusPROMO'>
                                       <div class='DIVprice'> $priceSTR <div class='EDtxt price'></div> lei  </div>
                                       <div class='DIVdetails'>  <a href='#'> $detailsSTR </a> </div>
                                       <div class='DIVpromo'></div>
                                  </div>
                          </div>
";
           $products.= $this->PROCESS_QUERY('DISPLAY_PRODUCTS',$querys[$level],$startLIMIT,12);
           $products.= "<!--[if lte IE 8]> <div class='product'></div> <![endif]-->
                        </div>".
                        $pagination;


           return $products;


       }


    function RESET_DB($type)    {

           $id=$this->IDpr;
           $this->DB->query("UPDATE products SET end_{$type}=NULL , {$type}=NULL WHERE id='{$id}' ");
       }
       //start_ , promo/new, id-ul produsului - daca nu mai este valabila elimina promotia sau NEWstatus din BD
    function CHECK_valability($endDATE) {


           $TMSP_end = strtotime($endDATE);
           $TMSP_today = time();

           if($TMSP_end > $TMSP_today)  return true;
           else return false;
       }


    function PROCESS_QUERY($DISPLAY,$query,$startLIMIT=0,$endLIMIT=1) {



              $result="";
          //__________________________________________________________________________________
              $res = $this->DB->query($query." LIMIT {$startLIMIT},{$endLIMIT} ");
              while($row = $res->fetch_assoc())
              {
                    $this->name         = $row['name'];
                    $this->IDpr         = $row['id'];
                    $this->Pid          = $row['Pid'];

                    $this->small_desc   = (isset($row['small_desc']) ? trim($row['small_desc']) : '');
                    $this->big_desc     = (isset($row['big_desc']) ?  trim($row['big_desc']) : '');

                    $this->price        = $row['price'];
                    $this->promo        = $row['promo'];
                    $this->end_promo    = $row['end_promo'];


                    $this->new          = $row['new'];
                    $this->end_new      = $row['end_new'];
                    $pic_PATH           = $row['imagine'];

              //________________________________________________________________________________________________________

                   if($this->end_promo  )
                   {
                       if(!$this->CHECK_valability($this->end_promo) ) {$this->promo=''; $this->RESET_DB('promo');}
                       else $this->statusPromo='promo';
                   }
                   if($this->new   && !$this->CHECK_valability($this->end_new)   ) { $this->new='';  $this->RESET_DB('new');}



              //_____________________________________________[ picutere settings ]___________________________________________________________
                    //$RES_pic = $this->DB->query("SELECT imagine FROM imagini where id_produs='".$this->IDpr."' ")->fetch_assoc();
                    //$pic_PATH = $RES_pic['imagine'];

              //________________________________________________________________________________________________________
                    if(is_file(fw_pubPath.'MODELS/products/RES/small_img/'.$pic_PATH))   $this->img_small = $this->basePath.'small_img/'.$pic_PATH;
                    if(is_file(fw_pubPath.'MODELS/products/RES/big_img/'.$pic_PATH))     $this->img_big   = $this->basePath.'big_img/'.$pic_PATH;




                    $result .= $this->{$DISPLAY}();
                    $this->img_small=$this->DEF_img_small;
                    $this->img_big  =$this->DEF_img_big  ;
                    unset($this->statusPromo);
              }

              return $result;
    }


    /**
     * pentru level1 DISPLAY
     */
    function getENDpoints($id) {
        $ch = $this->C->tree[$id]->children;
        if($ch)
            foreach($ch AS $id_ch) $this->getENDpoints($id_ch);
        else $this->ENDpoints .="'{$id}',";


    }
    function pagination($query,$idC,$idT,$level,$CURRENpage=1)   {

        #daca nu este deja setat numarul de pagini pt o anumita categorie
        #     nu suntem in admin
        if(!isset($_SESSION['NR_pagesPROD'][$idC]) || !$this->C->admin)
        {
            $NRrows = $this->DB->query($query)->num_rows;
            $pages =  ceil($NRrows / 12);

            unset($_SESSION['NR_pagesPROD']);
            $_SESSION['NR_pagesPROD'][$idC] = $pages;
        }
        else
        {
            $pages = $_SESSION['NR_pagesPROD'][$idC];
        }



        if(isset($pages))
        {
            $pagination = "<div class='pagination'>";
                for($i=1;$i<=$pages;$i++) {

                    $classCURRENT  = ($i==$CURRENpage ? " class='current' " : '');
                    $pagination .=" <a href='index.php?idT=$idT&idC=$idC&level=$level&Pn=$i' $classCURRENT> $i </a> ";
                }

            $pagination .="</div>";

            return $pagination;
        }
    }


    function DISPLAY()   {

        return $this->DISPLAY_page;

    }
    function _setINI()    {


        $this->priceSTR     = ( $this->lang=='ro' ? 'Pret:' : 'Price:');
        $this->detailsSTR   = ( $this->lang=='ro' ? 'DETALII' : 'DETAILS');

       #================================================================================================================
        $this->basePath      =  fw_pubURL.'MODELS/products/RES/';
        $this->DEF_img_small = $this->img_small = $this->basePath.'small_img/site_produs_slice_pisici.jpg';
        $this->DEF_img_big   = $this->img_big   =  $this->basePath.'big_img/site_geanta_produs.jpg';



        #===============================================================================================================
        #presetez display-ul pentru a putea recupera name, si description pt meta-uri;

        if(isset($_GET['IDpr']))  $this->DISPLAY_page =  $this->GET_PRODUCT();
        else $this->DISPLAY_page =  $this->GET_LEVEL();

    }


    function __construct($C)
    {
        $this->C = &$C;
        $this->tree = &$C->tree;
        $this->DB = &$C->DB;
        $this->lang = &$C->lang;

        $this->_setINI();

    }
}