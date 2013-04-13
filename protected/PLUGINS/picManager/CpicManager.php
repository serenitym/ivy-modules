<?php


class CpicManager{
    /*var $idPic;
    var $picUrl;
    var $picTitle;
    var $picAuth;
    var $picLoc;
    var $picDescr;
    var $picDate;*/

    var $DB_extKey_name  ;              #idRecord
    var $DB_extKey_value ;
    var $DB_table_origin='picManager' ;
    var $DB_table_prefix ;              #blog
    var $DB_table;                      #blog_picManager prefix_tableorigin

    var $setName;


 //   var $SET_managerPics     = array('ITEMS_managerPics'=>array());

    var $moduleStat = 'notSett';


    function get_managerPics(){
        /**
         *  TB - blog_picManager
         *  idPic
         *  idRecord
         *  picUrl
         *  picTitle
         *  picAuth
         *  picLoc
         *  picDescr
         *  picDate
         */

        $setObj   = &$this->{$this->setName};

        $query = "SELECT {$setObj->DB_extKey_name} , picUrl, picTitle, picAuth, picLoc, picDescr, picDate, idPic
                        FROM  {$setObj->DB_table}
                        WHERE {$setObj->DB_extKey_name} = {$setObj->DB_extKey_value} ORDER BY idPic desc ";

        $setObj->ITEMS_managerPics = $this->C->GET_objProperties($this,$query);

        //TgenTools::info_ech_ObjMod('ITEMS_managerPics ',$this, 'get_managerPics',$this->SET_managerPics['ITEMS_managerPics']);
        //TgenTools::info_ech("query $query");

    }

    function getPics($action){

        $this->{$action}();

    }
    function setINI($DB_extKey_name,$DB_extKey_value, $DB_table, $setName, $getAction=''){
        /**
         * SET_tableRelations_settings
         *            (&$obj,$extKname, $extKvalue, $tbOrigin, $tbPostfix='', $tbPrefix='', $bond='_')
         *
         * SETEAZA
              * $obj->DB_table         = prefix + origin + postfix
              *
              *                           @param        $obj           - obiectul pentru care se fac setarile
              * $obj->DB_extKey_name      @param        $extKname      - numele cheii externe
              * $obj->DB_extKey_value     @param        $extKvalue     - valoarea cheii externe
              * $obj->DB_table_origin     @param        $tbOrigin      - numele tabelului de origine
              * $obj->DB_table_Postfix    @param string $tbPostfix
              * $obj->DB_table_Prefix     @param string $tbPrefix
              *                           @param string $bond          - concatenare nume DB_table

         */
      /*  $this->C->SET_tableRelations_settings
                     ($this, $DB_extKey_name, $DB_extKey_value, $this->DB_table_origin,'',$DB_table_prefix);*/

        $this->setName = $setName;
        if(!isset($this->$setName))
        {
            $this->$setName = new stdClass();
            $set = &$this->$setName;

            $set->DB_table        = $DB_table;
            $set->DB_extKey_value = $DB_extKey_value;
            $set->DB_extKey_name  = $DB_extKey_name;

            if($getAction) $this->getPics($getAction);
        }
    }

    function __construct($C){}
}