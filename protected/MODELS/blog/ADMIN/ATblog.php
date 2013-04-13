<?php
/**
 *  trait TmethDB - method reLocate - pramAdd = GET vars for feedback messages
 *
 *      succDeleteRecord={$idRecord}
 *      mssTags_fail = taguri banned
 */
trait ATblog{


    # provenite din getRecordPermss


    var $HTMLmessage_record ;
    var $HTMLmessage_Records;   # cred ca asta ar trebui sa stea Atemplate_vars

    var $POST_mss = array(
        'succDeleteRecord' => 'You have succesfully deleted the record',
        'mssTags_fail' => 'This tags were not registered because they are banned or have unpermited characters'
    );

    #===============================================[ asincronCalls - methods ]====================================================
    /*
     *
     <ul id="sortable-priorities">
         <li class="ui-state-default">Item 1</li>
         <li class="ui-state-default">Item 2</li>
         <li class="ui-state-default">Item 3</li>
         <li class="ui-state-default">Item 4</li>
         <li class="ui-state-default">Item 5</li>
         <li class="ui-state-default">Item 6</li>
         <li class="ui-state-default">Item 7</li>
     </ul>
    */
    /**
     * RET: valid_records = array( ['idRecord', 'title', 'validPriority','endDate'] );
     *
     *      - title - titlul recordului
     *      - validPriority - daca > 0 inseamna ca este o prioritate expirata
     *
     * - daca prioritatea este expirata va fi deletata din TB - blogRecords_prior
     * - daca este valida va fii adaugata la valid_records
     * @return array
     */
    function get_validRecords(){


        $valid_records = array();
       // $DB = new mysqli(dbHost,dbUser,dbPass,dbName);

        $query_prior  = "SELECT blogRecords.idRecord, title,  DATEDIFF(NOW(), endDate ) AS validPriority, endDate
                            FROM blogRecords_prior, blogRecords
                            WHERE blogRecords.idRecord = blogRecords_prior.idRecord
                            ORDER BY priorityLevel asc";

        $res = $this->DB->query($query_prior);

        if($res->num_rows > 0)
            while($row = $res->fetch_assoc())
            {
                if($row['validPriority'] > 0){
                    $this->DB->query("DELETE from blogRecords_prior WHERE idRecord = {$row['idRecord']} ");
                }
                else{
                    array_push($valid_records, $row);
                }
            }

        return $valid_records;
    }

    /**
     * RET: popup display
     *
     * @param $records - recorduri valide (neexpirate)
     * @return string  - returneaza displayul popUpului
     */
    function get_displayPopup_recordPrior($records, $priorSettings){

        /**
         * Lucrurile astea ar trebui sa fie facute prin template ,
         * aici nu isi are locul html suff*/

        $priority_levels = '';

        foreach($priorSettings->priority_levels AS $level => $level_nrRecords)
            $priority_levels .="<span class='priority_level muted'>
                                    <small class='level'> Level {$level}</small>
                                    <small class='level_nrRecords'> =  {$level_nrRecords} record(s)</small>
                                     |
                                </span>";
        #==============================================================================================

        $list_prior = '';
        foreach ($records as $record) {
            $list_prior .= "<li  class='ui-state-default' id='recordPriority_{$record['idRecord']}' >
                                 <span>{$record['title']}</span>
                                 <span class='prior_ctrls'>
                                     <input type='text' name='endDate' id='endDate_{$record['idRecord']}' value='{$record['endDate']}' class='input-small'>
                                     <button name='deletePriority_{$record['idRecord']}' class='btn btn-mini'>
                                         <i class='icon-minus-sign'></i>
                                     </button>
                                 </span>
                            </li>";
        }

        #==============================================================================================

        return "
                  <p id='popup-message' class='text-success b'></p>


                 <ul id='sortable-priorities'>
                    $list_prior
                </ul>

                 <br>
                 <p class='m0'>
                    <b>Available no. of home priorities</b>
                    <span  id='totalPriorities'>{$priorSettings->totalPriorities}</span>
                 </p>
                 {$priority_levels}


                <p>
                 <button name='savePriorities'  class='btn btn-mini btn-primary t10'>
                    save
                 </button>
                </p>";



    }

    function get_recordPrior(){

        $records = $this->get_validRecords();
        $priorSettings = TgenTools::READyml(incPath.'etc/MODELS/blog/blog_HomePriorities.yml');

        echo
                $this->get_displayPopup_recordPrior($records,$priorSettings);
    }

    function save_recordPrior() {



        $query_delete = "DELETE from blogRecords_prior";
        $this->DB->query($query_delete);

        $test ='';
        # $test .=$query_delete."<br>";

        # var_dump($_POST['priorities']);

        foreach($_POST['priorities'] AS $priority){

            $idRecord      = $priority['idRecord'];
            $endDate       = $priority['endDate'];
            $priorityLevel = $priority['priorityLevel'];

            $query_insert  = "INSERT into blogRecords_prior
                                    (idRecord, endDate, priorityLevel)
                                    VALUES ('$idRecord','$endDate','$priorityLevel')";
            $this->DB->query($query_insert);
           # $test .=$query_insert."<br>";

        }

        echo $test."Prioritatile au fost salvate!!";
    }

    #===============================================[ DB - methods ]====================================================
    function  addRecord()    {

        $title          = $_POST['title_'.$this->LG];
        $modelBlog_name = $_POST['modelBlog_name'];




        $query =     "INSERT INTO blogRecords (idCat, entryDate, title, uidRec)
                                       VALUES ('{$this->idC}' ,NOW(), '{$title}' , '{$this->uid}')";
        # blogRecords_view = blogRecords + blogRecords_settings;
        $this->C->DB->query($query);
        $lastID = $this->C->DB->insert_id;

        # echo "<b>blogRecords </b>".$query."</br>";



        if(isset($lastID))
        {
            $query =     "INSERT INTO blogRecords_settings (idRecord, modelBlog_name)
                                       VALUES ('{$lastID}' ,'{$modelBlog_name}')";
            $this->C->DB->query($query);

            #echo "<b>blogRecords_settings </b>".$query."</br>";

            #___________________________________________________________________________________________________________
            # idT & idC => locatie in tree + categoria curenta
            # type = masterBlog sau profile
            # idRec = id-ul recordului tocmai inserat
            # recType = tipul de articol inserat

            #daca s-a ales un tip de articol!=blog => inseram si in tabelul aferent lui , pt ca mai apoi sa facem update la amandoua
            $location =  "http://".$_SERVER['SERVER_NAME']."/index.php?idT={$this->idT}&idC={$this->idC}&type={$this->type}&idRec={$lastID}";

            if($modelBlog_name!='blog'){

                $location               .= "&recType={$modelBlog_name}";
                $modelBlog_tableRecords  =  'blog'.$modelBlog_name.'_records';
                 $this->C->DMLsql("INSERT INTO $modelBlog_tableRecords (idRecord) VALUES ('{$lastID}')", true,'',$location);

                # echo "<b>modelBlog_tableRecords </b>".$query."</br>";
            }
            else{
                $this->C->reLocate($location);       # relocare fara recType (modelBlog, tipul recordului)
            }
        }
        #echo $query;

        # ? nu cred ca e prea bine ce am facut eu aici...adica ma gandesc ca ar fii o idee sa fac cu replace la updateRecord
        # ? nu peste tot este utila o functie de setValues , mai ales la queryurile mici

    }

    function  deleteRecord() {

        $idRecord = $_POST['BLOCK_id'];

        /**
         *  Deleteul trebuie sa se faca din (aceste tabele trebuie relationate intre ele)
         *      - blogRecords
         *      - blogRecords_settings
         *      - blogComments
         *      - [blogName]_records
         *
          */

        /**
         * daca userul nu are permisiuni de master pe recorduri
         * atunci va putea deleta doar recordurile pentru care este autor
         */

        $where = !$this->editRecords_Permss
                    ? "AND uidRec='{$this->uid}'": '';

        $error_message = "<p class='text-error b'> You might not have permission to delete this record ({$idRecord})  </p>";

        $query = "DELETE FROM blogRecords WHERE idRecord = '{$idRecord}' {$where} ";
        # DMLsql($query,$reset=true,$ANCORA='',$location='',$paramAdd='', $errorMessage='')
        $this->HTMLmessage_Records =
                $this->C->DMLsql($query, true, '','',"&succDeleteRecord={$idRecord}",$error_message);

        # daca deleteul a reusit atunci trimitem o variabila de succes - seccDeleteRecord
        # daca nu vom avea un mesaj de eroare

    }
    function  succesDelete() {
        #var_dump($this->template_vars);
        echo '<br>sunt ATblog succesDelete';
        $idRecord = $_GET['succDeleteRecord'];
        $this->HTMLmessage_Records =
                "<h6 class='text-success'>
                       You have succesfully deleted the record  ({$idRecord})
                </h6>";


    }


    function  update_blogModel($idRecord)          {

         #var_dump($this->modelBlog_vars);
         $set_blogModel_records = $this->C->DMLsql_setValues($this->modelBlog_vars);
         $query = "UPDATE {$this->modelBlog_tableRecords} SET {$set_blogModel_records} WHERE idRecord = '{$idRecord}' ";
         $this->C->DMLsql($query, false);

        # echo '</br></br>'.$query.'</br>';
    }
    function  update_blogSettings($idRecord)       {

        $set_blogRecords_settings   = $this->C->DMLsql_setValues($this->blogRecords_settings_vars);
        $query = "UPDATE blogRecords_settings
                        SET {$set_blogRecords_settings}
                        WHERE idRecord = '{$idRecord}' ";
        $this->C->DMLsql($query, false);

        #echo '</br><b> blogRecords_settings </b>'.$query.'</br>';

    }

    function  validate_tags($tags, &$faild_tags=''){

        /**
         *  - trim
         *  - mai mult de 2 caractere
         *  - not in banned tags
         *  - not including banned characters like *,&,^ etc
         *
         *  - tagurile valide vor fii adaugate in valid_tags
         *    iar cele invalide se vor adauga la stringul faildTags
         *
         * - LANGUAGE SUPPORT???
         */

        $banned_tags = array();

        $res = $this->DB->query("SELECT tagName FROM blogTags_banned");
        while($row = $res->fetch_assoc())

            array_push($banned_tags, $row['tagName']);


        #==========================================================================================
        $faild_tags_arr = array();
        $valid_tags     = array();
        $unique_tags    = array_unique($tags);

        foreach($unique_tags AS $tag)
        {
            $tag =  trim($tag);
            if(Validation::alphanum($tag,2)  && !in_array($tag, $banned_tags))
                array_push($valid_tags, $tag);
            else
                array_push($faild_tags_arr, $tag);

        }

        #==========================================================================================

        $faild_tags = implode(',',$faild_tags_arr);

        return $valid_tags;

    }
    function  update_blogTags($idRecord)           {

        /*
         * LOGISTICS
         *  delete all tags associated with this record
         *  insert new tags
         *  update the blogTags table if necesary
         *
         *
         *
         *  DELETE from blogMap_recordsTags WHERE idRecord = ''
            INSERT INTO blogMap_recordsTags ( tagName ) VALUES ('')
            REPLACE into blogTags (tagName ) values ('' )
        */

        $query_delete  = "DELETE from blogMap_recordsTags WHERE idRecord = '{$idRecord}'";
        $this->DB->query($query_delete);
        # echo "<br>".$query_delete."<br>";


        $recordTags = explode(", ",$_POST['recordTags_'.$this->LG]);        #deoarece avem tag1, tag2, etc...
        $faild_tags = '';
        $valid_tags = $this->validate_tags($recordTags,$faild_tags);

        foreach($valid_tags AS $tag){

            $query_insert  = "INSERT INTO blogMap_recordsTags ( idRecord, tagName ) VALUES ('{$idRecord}','{$tag}')";
            $query_replace = "REPLACE INTO blogTags (tagName) values ('{$tag}')          ";

            # echo $query_insert."<br>";
            # echo $query_replace."<br>";

            $this->DB->query($query_insert);
            $this->DB->query($query_replace);



        }

        #var_dump($recordTags);
        #=======================================================================================================
        if($faild_tags)
            return "&mssTags_fail=$faild_tags";         #reLocate => paramAdd
        else
            return "";
    }

    function  updateRecord()    {

        /**
         *
         * array (size=5)
           'idT'
           'idC'
           'idRec'
           'type'    => string 'masterBlog / profile' (length=10)
           'recType' => string 'articles / blog' (length=8)
         POST
         array (size=7)
           'BLOCK_id'
           'save_SGrecord'
         *
           'title_ro'
           'entryDate_ro'
           'lead_ro'
           'content_ro'
         *
         * 'recordTags_en'
         *
         *  opt[+] modelBlog_vars
         *
           'commStat' => string '1 / 0' - enable /disable (length=1)
         */

        /**
         * DISPONIBILE UTILE - ale obiectului curent
         *  modelBlog_tableRecords  =  blog[modelBlog_name]_records =  numele tabelului pentru blogModel
         *  modelBlog_vars          = numele variabilelor asociate tabelului pt modelBlog
         *
         */
        # echo 'GET';         var_dump($_GET);
        # echo 'POST';        var_dump($_POST);
        #   deci update pe:
        #   modelBlog_tableRecords = [blogName]_records  cu var names din modelBlog_vars
        #   blogRecords  cu var names din blogRecords_vars
        /*
                 $recType       =   isset($_GET['recType']);


                 $entryDate     =   $_POST['entryDate_'.$this->LG];
                 $title         =   $_POST['title_'.$this->LG];
                 $lead          =   $_POST['lead_'.$this->LG];
                 $content       =   $_POST['content_'.$this->LG];
                 $commentsStat  =   $_POST['commStat'];
                */

        $idRecord      =   $_POST['BLOCK_id'];

        /**
         *  Daca userul are permisiuni de editare pe acest record
         *  - este acelasi user care a scris recordul
         *  - are o clasa de master
         */
        if($this->editRecordPermss)
        {


             if($this->modelBlog_tableRecords!='')
                 $this->update_blogModel($idRecord);

            $this->update_blogSettings($idRecord);

            #teoretic ar trebui sa pun o conditie ca sa fie optional

            $reLocate_mssTags_fail = $this->update_blogTags($idRecord);

            $set_blogRecords            = $this->C->DMLsql_setValues($this->blogRecords_vars);
            $query = "UPDATE blogRecords SET {$set_blogRecords}   WHERE idRecord = '{$idRecord}' ";

            $this->C->DMLsql($query, true, '','',$reLocate_mssTags_fail);

            //echo '</br><b> blogRecords </b>'.$query.'</br>';




            // var_dump($this->blogRecords_vars);
            // var_dump($_POST);
        }
        else{
            echo 'Aparent nu am permisiuni pentru a face update <br>';
        }

    }


    function  publishRecord()   {

        /**
         * butoanele de publish nu se vor afisa daca userul nu are permisiuni de publicare
         */
        $idRecord      =   $_POST['BLOCK_id'];

        $query = "UPDATE blogRecords SET publishDate = NOW() WHERE idRecord = '{$idRecord}' ";
        $this->C->DMLsql($query);



    }
    function  UnPublishRecord() {
        $idRecord      =   $_POST['BLOCK_id'];

        $query = "UPDATE blogRecords SET publishDate = NULL WHERE idRecord = '{$idRecord}' ";
        $this->C->DMLsql($query);
        #echo $query;
    }

    function controlREQ_async(){

        if($_POST['asyncReq_action'] == 'savePriorities') $this->save_recordPrior();
        elseif($_POST['asyncReq_action'] == 'get_recordPrior') $this->get_recordPrior();
    }
    #===============================================[ overwrited methods for ADMIN]=====================================

    function GET_record(){

        # C->SET_INC_ext($mod_name,$type_MOD,$extension,$folder='',$template='',$ADMINstr='')
        # adaug js-ul de admin al modelului blog
        $this->C->SET_INC_ext('blog','MODELS','js','js_record','','ADMIN');
        parent::GET_record();
    }

    #===============================================[ CONTROLING methods ]==============================================

    /**
     * Controls any requirments coming from $_GET or $_POST
     */
    function controlREQ() {


        if(isset($_POST['delete_record']) || isset($_POST['delete_recordHome']))
                                              $this->deleteRecord();
        if(isset($_POST['save_SGrecord']))    $this->updateRecord();

        if(isset($_POST['save_addrecord']))   $this->addRecord();

        if(isset($_POST['publishRecord']))    $this->publishRecord();
        if(isset($_POST['UnPublishRecord']))  $this->UnPublishRecord();

        #====================[ POST messages ]====================================================================
        if(isset($_GET['succDeleteRecord']))  $this->succesDelete();
        if(isset($_GET['mssTags_fail']))
            $this->HTMLmessage_record =
                    "<h6>".
                        $this->POST_mss['mssTags_fail'].' '.$_GET['mssTags_fail']
                    ."</h6>";



        #trebuie avut grija si de gestionarea commenturilor

    }

    function setRecordS_Permissions(){
             $this->C->user->getRecordsPermss($this);
    }
    # ATENTIE!!!!---nedefinitivat inca
    function setRecord_Permissions(){

        /**
         * uidRec este setat in parent::setINI -> GET_Record()
         * este ID-ul userul care a scris acest record
         *
         * getRecordPermss SETS:
         *      - pubPermss = true /false           - userul are sau nu permisiuni de publicare
         *      - editRecordPermss = true / false   - userul are sau nu permisiuni de editare
         */
        if(isset($this->uidRec) )
        {
            $this->C->user->getRecordPermss($this, $this->uidRec);
            #echo 'Permisiune la editare ED '.$this->ED.'<br>';
        }
        else{
            #pendding

             #$this->C->user->getRecordPermss($this, $this->uidRec);
        }

    }

    function set_picManager(){

        $core = &$this->C;

        if(!is_object($core->picManager))
            $core->SET_general_mod('picManager','PLUGINS');

        if(is_object($core->picManager))
        {

           # echo "S-a instantiat modelul de Comments <br>";
            $core->picManager
                    ->setINI('idRecord', $_GET['idRec'],
                             'blog_picManager',
                             'SET_managerPics',
                             'get_managerPics');
            #setINI($DB_extKey_name,$DB_extKey_value, $DB_table_prefix, $getAction='')
        }

    }
    function setINI(){

        # echo 'S-a activat partea de admin ATblog';

        #plus variabialele pt templateul de admin
        #atentie ca asta ar trenuii sa se intample si in cazul unui blogModel in cadrul lui
        $this->template_vars = array_merge($this->template_vars, $this->Atemplate_vars);


        if(isset($_GET['idRec']))  $this->set_picManager();

        /**
         * inainte de interogare DB pentru ca avem nevoie de conditii
         * seteaza
         *  - editRecords_Permss
         */
        $this->setRecordS_Permissions();

        /**
         * pentru a seta permisiunile pentru acest record imi trebuie uidRec (ID-ul userului care a scris acest record)
         * pentru asta trebuie sa preiau intai recordul din baza de date
         * de aceea se apeleaza intai parent::setINI
         */
         parent::setINI();

        // mutat in GET_record deasupra lui GET_comments
        // am nevoie de permisiuni pentru comments
        /**
         * Seteaza
         *      - editRecordPermss
         *      - pubPermss
         *      - ED
         */
       // $this->setRecord_Permissions();

        $this->controlREQ();



    }

    function __wakeup(){

        $this->C->DB_reConnect();
        $this->controlREQ_async();
    }
}
