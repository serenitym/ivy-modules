<?php

$GLOBALS['queries'] = 2;
/**
 * DB_extKey_name
 * DB_extKey_value
 * DB_table         = prefix + origin + postfix
 * DB_table_origin
 * DB_table_postfix
 * DB_table_prefix
 *
 */

class Ccomments{


    var $modelComm_vars = array();          #CONF: sunt variabilele aditionate lui template_vars in cazul in care avem un commenturi personalizate
    var $modelComm_name = '';               #CONF: numele commenturilor personalizat
    var $template = '';                     #CONF: numele templateului aferent commenturilor personalizat sau al blogului simplu




    var $DB_table  = '';              #RET :  = [modelComm_name]_[comm_table]
    var $DB_table_origin = 'blogComments';       # SETreq: tabelul de referinta pentru commenturi, master (setat in cazul asta de Cblog)
    var $DB_extKey_value;                    # SETreq: valoarea legaturii externe
    var $DB_extKey_name;                     # SETreq: numele legaturii externe
    var $DB_extKey_name_getArg;              # SETreq: arg pentru href

    var $queryJOIN;                         # query-JOINU-ul cu modelul de tipul de comenturi cerut
    var $queryWHERE;                        # general WHERE

    var $nrComm = 3;
    var $comments   = array();              # arrayul multidimensional al commenturilor
    var $comm_vars ;                        # CONF:   sunt numele variabilelor pentru REQ pentru tabelul blogRecords    , este util sa avem o separare deoarece face interactiunea cu DB mult mai usoara
    var $template_vars ;                    # CONF: o concatenare a $modelBlog_vars, $blogRecords_vars, $template_vars definite in config
    
    var $total_nrComments=0;

    #===================================================================================================================
    
    var $Pn = 1;                            # defaultul paginii la care ne aflam
    var $LimitEnd   = '';                     # end-ul lui LIMIT;
    var $LimitStart = '';                   # startul-ul lui LIMIT;
    var $pagination = '';                   #  HTML

    #===================================================================================================================

    var $LG;
    var $C;
    var $ED=''; # arata daca un anumit ENT sau SING va fii editabil sau nu , daca nu va fii editabil i se va defiini orice valoare
                # acesta este un pointer la ED-ul lui Cblog

    #===================================================================================================================
    var $commentsApprov     = false; # daca commenturile trebuie sau nu aprobate

    var $commentsStat       = true;  # (nu)se mai  pot nu posta comenturi

    var $commentsView       = true;  # (nu)se vad  comenturile

    var $commentsPermss     = false; # editorul (NU) poate sa administreze sau nu comenturile

    var $addCommFeedback    = false; # a fost adaugat un mesaj

    # alpha
    var $commentsPrior      = true;   #  deocamdata este byDefault true - ar putea fii pus in blogRecords_settings


    function ProcessComments($row)            {

        // ???  preluate direct din acest obiect prin $o->
       /* # inspecial necesare pentru editare
        $row['ED'] = &$this->ED;
        $row['LG'] = &$this->LG;
        $row['commentsApprov'] = &$this->commentsApprov;*/

        # ??? nu ar trebui conditionat cumva ? asta sa fie o variabila or something???
        # se poate adauga prioritate
        $row['prior'] = true;


        $this->C->rating->setINI('blog',
                                 $this->DB_extKey_name,
                                 $this->DB_extKey_value,'SET_Comm'.$row['uidComm'],
                                 $row['uidComm'] ,
                                 true);
        $this->C->thumbs->setINI('blogComments',
                                 'idComm',
                                  $row['idComm'],
                                 'SET_thComm'.$row['idComm']
                                 );
        return $row;
    }


    function setPagination($queryPagination)  {

        $GET_args = array('idC'=>$this->idC,
                          'idT'=>$this->idT,
                          'type'=>$this->type,
                          $this->DB_extKey_name_getArg => $this->DB_extKey_value);

        //GET_pagination($query,$nrItems,$GETargs,$uniq,&$obj='', $ANCORA='')   {
        $this->pagination =
                $this->C->GET_pagination($queryPagination,
                                         $this->nrComm,
                                         $GET_args,
                                        'comments_'.$this->idC,
                                         $this,
                                         '#addCommAnc'
                                           );


        #LimitStart, LimitEnd si Pn vor fii definite de GET_pagination ?! nu foarte transparenta ideea

        #===============[ TESTE ]=========================================
        #echo 'pagination Query pentru Ccomments '.$queryPagination."<br>";
        # echo '<b>Ccomments - setPagination </b> pagination = '.$this->pagination."<br>";


    }
    function GET_Comments()                   {

 #==================================================[ Testing stuff ]============================================

        global $qFn;

        $queriesStr['Qmain']         = ' "SELECT * from $o->DB_table_origin" ';

        $queriesStr['Qjoin']         = ' " JOIN {$o->DB_table_origin}_text"
                                          ." ON  (blogComments.idComm = {$o->DB_table_origin}_text.idComm)" ';

        $queriesStr['Qjoin_model']  = ' " JOIN {$o->DB_table_origin}_{$o->modelComm_name}"
                                         ." ON  (blogComments.idComm = {$o->DB_table_origin}_{$o->modelComm_name}.idComm)" ';


        $queriesStr['QWhere_idExt']  = ' "idExt = \' $o->DB_extKey_value\'" ';
        $queriesStr['QWhere_approv'] = ' " approved = 1 " ';
      #  $queriesStr['QWhere_notIds'] = ' " idComm NOT IN ( o->$excluded_ids_str ) " ';




        $queriesStr['Qjoin_prior']   = '" JOIN blogComments_prior"
                                        ." ON (blogComments.idComm = blogComments_prior.idComm) " ';


        $queriesStr['Qjoin_thumbs']  = '" JOIN blogComments_thumbs "
                                         ." ON (blogComments.idComm = blogComments_thumbs.idComm) " ';
        $queriesStr['QWhere_thumbs'] = ' " blogComments.idComm NOT IN (".$o->priorComment[0]["idComm"].") " ';



        $queriesStr['QOrder_pag']    = ' " ORDER BY entryDate DESC LIMIT {$o->LimitStart},{$o->nrComm}  " ';


        # $queries['mainQ'] = create_function('&$o','return '.$queriesStr['mainQ'].';');

        foreach($queriesStr  AS $qName => $qStr)
            $qFn[$qName] = create_function('&$o','return '.$qStr.';');


       #===============================================================================================================


        $query = $qFn['Qmain']($this)
                    . ($this->modelComm_name
                        ? $qFn['Qjoin_model']($this)
                        : $qFn['Qjoin']($this));

        $where =    ' WHERE '
                        .$qFn['QWhere_idExt']($this)
                        .(!$this->commentsPermss ? ' AND '.$qFn['QWhere_approv']($this) : '');


        $this->GET_topComments($query, $where);
        $this->setPagination($query.$where);
        #===============================================================================================================

        $query .= $where
                    .$qFn['QOrder_pag']($this);
        #===============================================================================================================

        echo " Ccomments - GET_Comments - $query <br>";

        #query-ul acesta va fii extras si pus intr-un array multidimensional numit $records
        $this->comments = $this->C->GET_objProperties($this, $query, 'ProcessComments');
        $this->total_nrComments = count($this->comments);


       unset($qFn) ;

       # TgenTools::info_ech_ObjMod($query,$this,'GET_comments');
       # var_dump($this->comments);



        
    }
    function GET_topComments($query, &$where='')          {

        // that is if $commentsPrior = true
        /**
         * WORKING on
         *      Table: blogComments_prior
         *      Columns:
         *      idComm	        int(5) PK
         *      priorityLevel	tinyint(4)
         *
         *      Table: blogComments
         *       Columns:
         *       idComm	int(5) PK AI
         *       idP	int(5)  ????
         *       idExt	int(5)
         *       entryDate	date
         *       userName	char(30)
         *       uidComm	int(4)
         *       comment	text
         *       ratingUp	int(3)      - deprecated
         *       ratingDown	int(3)      - deprecated
         *       approved	tinyint(1)
         *       Related Tables:blogRecords (idExt → idRecord)
         *
         * Table: thumbs_comm
            Columns:
            idComm	int(5) PK
            thumbsUp	int(3)
            thumbsDown	int(3)
            Related Tables:blogComments (idComm → idComm)

         *
         */
        /**
         *   # se face si Mergeul lui modelBlog_vars cu template_vars
             # crearea joinului cu tabelul modelului de commenturi care a facut requestul
             # atentie numele tabelului de commenturi pentru modelul de comm este format astfel   blog[name]_records
        */

        /**
         * avem aici deaface cu un query cu o gramada de conditii cu tot felul de componentte
         * venite din te miri ce parti ale acestui script...
         * posibila razna
         * oare nu exista o metoda prin care sa le tin in check ...mai organizate, mai usor de vizualizat?
         * trebuie sa ma gandesc...
         * All formed query
         *
         *      SELECT * from {$this->DB_table_origin}
         *          ? JOIN {$this->DB_table } ON (blogComments.idComm = {$this->DB_table }.idComm)
         *            JOIN blogComments_prior ON (blogComments.idComm = blogComments_prior.idComm)
         *
         */

        /** {{{ the queries
         *   $queriesStr['Qmain']         = ' "SELECT * from $o->DB_table_origin" ';
             $queriesStr['Qjoin']         = ' "JOIN $o->modelComm_name."_".$o->DB_table_origin"
                                              ." ON  (blogComments.idComm = {$o->DB_table }.idComm)" ';


             $queriesStr['QWhere_idExt']  = ' "idExt = \' $o->DB_extKey_value\'" ';
             $queriesStr['QWhere_approv'] = ' " approved = 1 " ';
             $queriesStr['QWhere_notIds'] = ' " idComm NOT IN ( o->$excluded_ids_str ) " ';


             $queriesStr['Qjoin_thumbs']   = '" JOIN thumbs_comm ON (blogComments.idComm = thumbs_comm.idComm) " ';

             $queriesStr['QWhere_thumbs'] = ' " blogComments.idComm NOT IN (".$o->priorComment[0]["idComm"].") " ';

             $queriesStr['Qjoin_prior']   = '" JOIN blogComments_prior"
                                              ." ON (blogComments.idComm = blogComments_prior.idComm) " ';




             $queriesStr['QOrder_pag']    = ' " ORDER BY entryDate DESC LIMIT {$o->LimitStart},{$o->nrComm}  " ';
         *
         * use  $qFn[$qName] = create_function('&$o','return '.$qStr.';');
         }}}
         */

        $excluded_ids = array();

        global $qFn;
        $query_priorComm = $query
                             .$qFn['Qjoin_prior']($this)
                              .$where;



        $this->priorComment = $this->C->GET_objProperties($this, $query_priorComm, 'ProcessComments', true);
        if(count($this->priorComment) > 0)
        {
            array_push($excluded_ids,$this->priorComment[0]['idComm']);

            $this->priorComment[0]['prior'] = false;    # nu se poate adauga prioritate pentru ca e deja prioritar

        }
        #~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        #  TgenTools::info_ech_ObjMod('priorComm '.$query_priorComm,$this,'GET_topComments');
        # echo "new GET_topComments <b>query_priorComm</b>-  $query_priorComm <br><br>";



        #===========================================[ TOP voted ]=================================================

        $query_topComm = $query
                            . $qFn['Qjoin_thumbs']($this)
                                . $where
                                    .(isset($this->priorComment[0]['idComm'])
                                        ? " AND ".$qFn['QWhere_thumbs']($this)
                                        :'')
                                    ." ORDER BY thumbsUp desc LIMIT 0,1 ";



        $this->TOPcomment = $this->C->GET_objProperties($this, $query_topComm, 'ProcessComments', true);

        if(count($this->TOPcomment) > 0)
        {
            array_push($excluded_ids,$this->TOPcomment[0]['idComm']);

        }
        #~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        # TgenTools::info_ech_ObjMod('topComm '.$query_topComm,$this,'GET_topComments');
        # echo "new GET_topComments <b>query_topComm</b>- $query_topComm <br><br>";



        #============================================================================================

        if(count($excluded_ids) > 0){
            $where .= "AND blogComments.idComm NOT IN (". implode(',',$excluded_ids).") " ;
        }


    }
    #============================================================================================


    function addComment()    {

        /**
         * $_POST
         *  userName
         *  rating
         *  comment             - comment
         *
         *
         * $_GET
         * idExt <- $_GET['idRec']        - idRecord  sau  $this->DB_extKey_value - setat de Cblog
         *
         * from - Cuser via Ccore
         * uid          - userID optional
         *
         * idP - in cazul in care este un reply ; feeture not implemented yet
         *
         * entryDate - NOW()
         *
         * from Cblog
         * approved  - if(this->commentsApprov)
         *
         */


        $sqlV['idExt']    = $this->DB_extKey_value;  //idRecord
        $sqlV['uidComm']  = isset($this->uid) ?  $this->uid : '';
        $sqlV['approved'] = isset($this->commentsApprov) &&  $this->commentsApprov==1 && !$this->commentsPermss
                            ? 0 : 1;
        $sqlV['userName'] = $_POST['userName'];
        #~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        $query = "INSERT into {$this->DB_table_origin} SET ";
        $query.=   $this->C->DMLsql_setValues_fromArr($sqlV).",entryDate = NOW()  ";

        $this->DB->query($query);
       # echo "Ccomments <b>addComment</b> $query <br>";

        $this->addComment_data();



    }
    function addComment_data(){

        $sqlV['idComm']   =  $this->DB->insert_id;
        $sqlV['comment']  =  $_POST['comment'];
        #~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        $query  = "INSERT into {$this->DB_table_origin}_text SET  ";
        $query .=         $this->C->DMLsql_setValues_fromArr($sqlV);

        $this->DB->query($query);

        #echo "comments <b>addComment</b> $query <br>";


    }
    function controlREQ()    {

        if(isset($_POST['addComment'])){


            $this->addComment();
            $this->C->reLocate('','#addCommAnc','&addComm=true');

        }
        if(isset($_GET['addComm'])) $this->addCommFeedback = true;
    }
    #=============================================================================================

    function setINI(&$callerObj, $DB_extKey_name,$DB_extKey_value,$DB_table_origin,$DB_extKey_name_getArg='')        {


        #Console::logSpeed('setINI Start Ccomments');

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
        $this->C->SET_tableRelations_settings
                     ($this, $DB_extKey_name, $DB_extKey_value, $DB_table_origin);


        $this->DB_extKey_name_getArg = $DB_extKey_name_getArg;


        #=============================================================================================


        $this->uidRec         = &$callerObj->uidRec;

        $this->commentsStat   = &$callerObj->commentsStat;
        $this->commentsApprov = &$callerObj->commentsApprov;


        # nu realizez la ce este folostit, pare sa nu fie folosit la nimic
        $this->callObj = &$callerObj;
        $this->ED      = &$callerObj->ED;

        #=============================================================================================

        $this->C->set_general_mod('thumbs','PLUGINS');

        #vedem daca exista vreun request de blog, adcica un modelBlog extends Cblog
        // if($this->modelComm_name!='')  $this->setJOIN_with_commModel();

        #===============================================================================================================


        $this->GET_Comments();

        self::controlREQ();

        #Console::logSpeed('setINI End Ccomments');

    }


    
    function __construct($C){
       # echo 'AM instantiat Ccomments';

    }


}