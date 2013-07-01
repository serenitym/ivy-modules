<?php
class ratingSet{



    var $Rating       = '';      # ratingul total
    var $nrRates      = '';      # numarul de persoane care au votat
    var $stars        = array('-empty','-empty','-empty','-empty','-empty');
                                 # vectorul cu stelute ex: stars[0,1..][empty] = -empty
    var $totalRating   = '';     #suma totala a ratingurilor


}
class ratingSet_user extends ratingSet{

    var $uid;                    # userID
    var $uRating       = '';     # ratingul total
    var $ustars        =  array('-empty','-empty','-empty','-empty','-empty');         #nota acordata de user-ul curent

#___MAN's
    var $DB_extKey_name  = '';      # numele cheii externe din tabelul rating_[postFix_table]
    var $DB_extKey_value = '';      # valoarea cheii externe
    var $DB_table_postfix   = '';      # postfixul tabelului de rating , stabilit de modelul apelant

}
class Crating extends ratingSet_user{

    var $ratingTable  = '';

    var $setName = '';
    var $setmod;
    var $onlyUser = false;





    function getRating(){

        $setmod   = &$this->{$this->setName};

        $query = "SELECT nrRates, totalRating, CEIL(totalRating / nrRates) AS Rating
                  FROM (

                        SELECT COUNT( * ) AS nrRates, SUM( rating ) AS totalRating
                        FROM  rating_{$setmod->DB_table_postfix}
                        WHERE {$setmod->DB_extKey_name} = '{$setmod->DB_extKey_value}'

                        ) AS total";
        /**
         *  TABLE: rating_[postFix_table] -> returns;
         *      - nrRates
         *      - Rating
         *      - totalRating
         */

        #echo "for setName <b>{$this->setName}</b> ".$query;


        $rateRes = $this->C->GET_modProperties($setmod , $query);

        if(count($rateRes) > 0)
            for($i=0; $i<$setmod->Rating;$i++)  $setmod->stars[$i] = '';

        #var_dump($this->$setName);




    }
    function getRating_byUid($uid){

        $setmod   = &$this->{$this->setName};

        $query = "  SELECT rating AS uRating
                        FROM  rating_{$setmod->DB_table_postfix}
                        WHERE {$setmod->DB_extKey_name} = '{$setmod->DB_extKey_value}' AND uid = '{$uid}' ";

       # echo "for setName <b>{$this->setName}</b> ".$query;

        $rateRes = $this->C->GET_modProperties($setmod , $query);

        if(count($rateRes) > 0)
        for($i=0; $i<$setmod->uRating;$i++)
            $setmod->ustars[$i] = '';

        #var_dump($setmod->ustars);


    }

    /**
     *  functie apelata de cel care cere un ratind de acest tip
     *   Crating va crea un obiect pt fiecare set cerut de genul $this->[setName] = new ratingSet
     *
     * setul
     *  -  setmod trebuie sa contina toate var necesare template_vars
     *  -  variabile sunt utilizate de  javaScript pt a putea  trimite datele necesare pentru procesare DB
     *
     *
     * @param $postFix_table       - postfixul tavelului de rating => tabelName = rating_[postFix_table]
     * @param $extKey_name      - numele cheii externe din tabel
     * @param $extKey_value     - valoarea cheii externe
     * @param $setName          - numele unic al setului  ar trebui sa fie unic si preferabil cu un prefix SET_
     * @param bool $getbyUid
     * @param bool $onlyUser    - daca doresc sa se preia datele doar despre un anumit user si atunci voi avea doar variabilelelui
     */
    function setINI($postFix_table, $extKey_name, $extKey_value, $setName, $getbyUid= false, $onlyUser=false) {

        $this->setName = $setName;
        if( !isset($this->$setName))
        {

             if($getbyUid)  {
                 $this->$setName = new ratingSet_user();
                 $this->$setName->uid =  $getbyUid;
                 $this->template_vars = array_merge($this->template_vars, $this->uRating_vars);
             }
             else
                 $this->$setName = new ratingSet();



            /**
             * SET_tableRelations_settings (&$mod,$extKname, $extKvalue, $tbOrigin, $tbPostfix='', $tbPrefix='', $bond='_')
             *
             * SETEAZA
                  * $mod->DB_table         = prefix + origin + postfix
                  *
                  *                           @param        $mod           - obiectul pentru care se fac setarile
                  * $mod->DB_extKey_name      @param        $extKname      - numele cheii externe
                  * $mod->DB_extKey_value     @param        $extKvalue     - valoarea cheii externe
                  * $mod->DB_table_origin     @param        $tbOrigin      - numele tabelului de origine
                  * $mod->DB_table_Postfix    @param string $tbPostfix
                  * $mod->DB_table_Prefix     @param string $tbPrefix
                  *                           @param string $bond          - concatenare nume DB_table

             */
            $this->C->SET_tableRelations_settings
                         ($this->$setName, $extKey_name,  $extKey_value, 'rating', $postFix_table );

            #===========================================================================================================

            if(!$onlyUser)$this->getRating();
            if($getbyUid) $this->getRating_byUid($getbyUid);

        }

    }

    function __construct($C){}
}