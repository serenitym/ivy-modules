<?php
/**
 * IT is a handler to further adjust the changes that will occure in GEN_edit
 */
class ACproducts_handle_GENedit
{
    var $ENDpoints;
    var $id;
    var $masterTREE;
    var $DB;
    /**
     * Propagation of DELETE from id -- it specific to this products module -- because it refers only to level3 ITEMS
     *
     * because producs refer only to endITEMS (those that have no other childrens);
     */
    function getENDpoints($id) {
        $ch = $this->masterTREE[$id]->children;
        if($ch)
            foreach($ch AS $id_ch) $this->getENDpoints($id_ch);
        else $this->ENDpoints .="'{$id}',";


    }
    function deleteITEM_pics(){


        $this->getENDpoints($this->id);

        $ENDpoints = '('.substr($this->ENDpoints,0,-1).')';

        $query =
"                  SELECT  imagine  FROM products LEFT outer JOIN imagini ON(products.id = imagini.id_produs)
                                       WHERE   Pid IN  {$ENDpoints} ;
";
        $res = $this->DB->query($query);
        while($row = $res->fetch_assoc())
        {
             $pic_PATH = $row[imagine];
              if(is_file(FW_PUB_PATH.'MODELS/products/RES/small_img/'.$pic_PATH)) unlink(FW_PUB_PATH.'MODELS/products/RES/small_img/'.$pic_PATH);
              if(is_file(FW_PUB_PATH.'MODELS/products/RES/big_img/'.$pic_PATH)) unlink(FW_PUB_PATH.'MODELS/products/RES/big_img/'.$pic_PATH);
        }
    }
    function __construct($DB, $change_type, $id, $detail,$typeSTATUS, $masterTREE)
    {
        /**
         *   REQ- ACGEN_edit : $DB, $change_type, $id, $detail, $masterTREE
         *
         *   $changes = array( 'addNewITEM', 'updateITEM', 'deleteITEM');
         *   $id = id from ITEMS;
         *   $detail = ('val'=>'', 'type'=>)
         *   $typeSTATUS = 'OLD / NEW'
         *   $masterTREE  = array(id=> object ITEM) - form propagation
         *   =================================================================================
         *
         *   if an ITEMS.id of type=products
         *      $change_type  = 'deleteITEM'  => it means that a category will be deleted therefore it's pictures should be deleted
         *
         *       $change_type = 'updateITEM' AND $typeSTATUS='OLD' => an ITEM will change this type in something else
         *       $change_type = 'addNewITEM' AND $typeSTATUS='NEW' => an ITEM will be added with this type = products
         */


       // file_put_contents(FW_PUB_PATH."MODELS/products/ADMIN/test.txt",'change_type '.$change_type.' id '.$id.' detail '.$detail.' typeSTATUS '.$typeSTATUS."<br />");

        $this->id = $id;
        $this->DB = $DB;
        $this->masterTREE = $masterTREE;
        if($change_type == 'deleteITEM') $this->deleteITEM_pics();
    }
}