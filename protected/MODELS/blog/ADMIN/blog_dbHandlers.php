<?php
class blog_dbHandlers extends Cblog
{

    var $posts; // object with posts->postName

    var $HTMLmessage_record ;
    var $HTMLmessage_Records;   # cred ca asta ar trebui sa stea Atemplate_vars

    var $POST_mss = array(
        'succDeleteRecord' => 'You have succesfully deleted the record',
        'mssTags_fail' => 'This tags were not registered because they are banned or have unpermited characters'
    );

    #===============================================[ asincronCalls - methods ]====================================================

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
    function get_validRecords()
    {


        $valid_records = array();
       // $DB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

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
    function get_displayPopup_recordPrior($records, $priorSettings)
    {

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
    function get_recordPrior()
    {

        $records = $this->get_validRecords();
        $priorSettings = TgenTools::readYml(INC_PATH.'etc/MODELS/blog/blog_HomePriorities.yml');

            $this->get_displayPopup_recordPrior($records,$priorSettings);
    }
    function save_recordPrior()
    {
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

        $test."Prioritatile au fost salvate!!";
    }
    function controlREQ_async()
    {

        if($_POST['asyncReq_action'] == 'savePriorities') $this->save_recordPrior();
        elseif($_POST['asyncReq_action'] == 'get_recordPrior') $this->get_recordPrior();
    }

    #===============================================[ DB - methods ]============
    function _hook_addRecord()
    {
        /**
         * aspect:
         *  - title
         *  - modelBlog_name
         */
        $postsConf = &$this->posts_addRecord;
        //var_dump($postsConf);
        $this->posts = handlePosts::Get_postsFlexy($postsConf);
        //echo "_hook_addRecord";
        //var_dump($this->posts);

        $validStat = true;
        $validStat &= !empty($this->posts->title) ? true :
                       $this->fbk->SetGet_badmessFbk(
                           $postsConf['title']['fbk_notempty']
                       );
        //echo '<br>validStat este = '.($validStat ? "true<br>" : "false <br>");
        return $validStat;
        //return false;


    }
    function addRecord()
    {
        $query =     "INSERT INTO blogRecords  (idCat, title, uidRec)
                             VALUES (
                              '{$this->idNode}' ,
                              '{$this->posts->title}' ,
                              '{$this->uid}'
                             )";
        # blogRecords_view = blogRecords + blogRecords_settings;
        # echo "<b>blogRecords </b>".$query."</br>";
        $this->DB->query($query);
        $lastID = $this->DB->insert_id;


        if(!isset($lastID)) {
            error_log("[ ivy ] addRecord : atentie lastId nu a fost recuperat");

        } else {
            $queries = array();
            // insert in blogRecords_stats
            $query =     "INSERT INTO blogRecords_stats (idRecord, entryDate)
                                 VALUES (
                                 '{$lastID}' ,
                                 NOW()
                                 )";
            //echo "<b>blogRecords_stats </b>".$query."</br>";
            array_push($queries, $query);


            // insert in blogRecords_settings
            $query =     "INSERT INTO blogRecords_settings (idRecord, modelBlog_name)
                                 VALUES (
                                 '{$lastID}' ,
                                 '{$this->posts->modelBlog_name}'
                                 )";
            //echo "<b>blogRecords_settings </b>".$query."</br>";
            array_push($queries, $query);

            $this->C->Db_queryBulk($queries, false);
            $location =  "http://".$_SERVER['SERVER_NAME']."/index.php?idT={$this->idTree}&idC={$this->idNode}&idRec={$lastID}";
            $this->C->reLocate($location);
        }

    }

    // update record methods
    function Get_validTags($tagsStr)
    {
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

        $tags = explode(",", $tagsStr);
        $banned_tags = array();

        $res = $this->DB->query("SELECT tagName FROM blogTags_banned");
        while ($row = $res->fetch_assoc()) {
            array_push($banned_tags, $row['tagName']);
        }

        #=======================================================================
        $faild_tags_arr = array();
        $valid_tags     = array();
        $unique_tags    = array_unique($tags);

        foreach ($unique_tags AS $tag) {
            $tag = strtolower(trim($tag));
            if (/*Validation::alphanum($tag,2)  && */!in_array($tag, $banned_tags)) {
                if($tag){
                    array_push($valid_tags, $tag);
                }
            } else {
                    array_push($faild_tags_arr, $tag);
            }
        }
        #======================================[ set warning message ]==========
        $faildTagsStr = implode(', ',$faild_tags_arr);
        if ($faildTagsStr) {
            $postsConf = &$this->posts_updateRecord;
            $postsConf['recordTags']['fbk']['mess'] .= $faildTagsStr;
            $this->fbk->Set_messFbk($postsConf['recordTags']['fbk']);
        }

        return $valid_tags;



    }
    function _hook_updateRecord()
    {
        //=====================================[ preValidate data ]=============
        /**
         * Rules:
         *
         * 1. daca tipul recordului este acelasi cu cel curent scote-l din post
         * 2. daca userul nu are drepturi de editare al recordurilor
         *    - nu are drepturi de moderator si deci de css si js
         *    - nu are dreptul de a schimba data publicarii unui articol
         *
         * 3. daca userul nu are drepturi de publicare atunci nu poate sa isi schimbe
         * data publicarii
         */
        if($this->modelBlog_name == $_POST['modelBlog_name']) {
            unset($_POST['modelBlog_name']);
        }

        if(!$this->user->rights['article_edit']) {
            unset($_POST['css']);
            unset($_POST['js']);
            unset($_POST['publishDate']);

        } else {
            if($_POST['publishDate'] != $this->publishDate) {
                $_POST['republish'] = 1;
            }
        }


        //=====================================[ validate data ]=================

        /*use from yml: blogPests_updateRecord */
        if ($this->user->uid != $this->uidRec
        && !$this->user->rights['article_edit']
        ) {
            return $this->fbk->SetGet_badmess(
                        'error',
                        'Not allowed to edit',
                        'your are not the author of this article!!! ');
                        //."<br> your userID = {$this->user->uid} recorUserdID = $this->uidRec");
        }
        $postsConf =&$this->posts_updateRecord;
        $this->posts = handlePosts::Get_postsFlexy($postsConf, '', true);

        //var_dump($this->blogPsts_updateRecord);
        $validStat = true;

        $validStat &= !empty($this->posts->title) ? true :
                       $this->fbk->SetGet_badmessFbk(
                           $postsConf['title']['fbk_notempty']
                       );

        $validStat &= !empty($this->posts->content) ? true :
                        $this->fbk->SetGet_badmessFbk(
                            $postsConf['content']['fbk_notempty']
                        );


        $validStat &= !empty($this->posts->lead) ? true :
                       $this->fbk->SetGet_badmessFbk(
                           $postsConf['lead']['fbk_notempty']
                       );

        // nu mai are rost sa procesam datele daca sunt invalide
        if (!$validStat) {
            return false;
        }

        //=====================================[ process data ]=================
        // nu ii mai vad sensul
        //$this->posts->content = addslashes($this->posts->content);

        if ($this->status_recordTags) {
            $this->posts->recordTags = $this->Get_validTags($this->posts->recordTags);
        }

        //echo "_hook_updateRecord() ";
        //echo "validation = ".($validStat ? "true" : "false");
        //var_dump($_POST);
        //var_dump($this->posts);

        //$this->C->reLocate();

        return true;
        //return false;
    }

    function update_blogTags($idRecord)
    {

        $query_delete  = "DELETE from blogMap_recordsTags WHERE idRecord = '{$idRecord}'";
        $this->DB->query($query_delete);
        // echo "<br>".$query_delete."<br>";

        foreach($this->posts->recordTags AS $tag){

            $query_insert  = "INSERT INTO blogMap_recordsTags ( idRecord, tagName ) VALUES ('{$idRecord}','{$tag}')";
            $query_replace = "REPLACE INTO blogTags (tagName) values ('{$tag}')          ";
            //echo $query_insert."<br>";
            //echo $query_replace."<br>";

            $this->DB->query($query_insert);
            $this->DB->query($query_replace);
        }

    }
    function updateRecord()
    {

        $posts = &$this->posts;
        // update tags
        if(isset($posts->recordTags) && is_array($posts->recordTags)) {
            $this->update_blogTags($posts->idRecord);
        }

        $queries = array();
        //==============================[ update main blogRecords ]=============
        $columns = 'idCat, title, content, lead, leadSec, city, country';
        $sets = handlePosts::Db_Get_setString($this->posts, $columns);
        $query = "UPDATE blogRecords SET {$sets}
                  WHERE idRecord = '{$posts->idRecord}' ";
        $queries['blogRecords'] = $query;


        //==============================[ update blogRecords_stats ]============
        $columns = 'publishDate, republish';
        $sets = handlePosts::Db_Get_setString($this->posts, $columns);
        if($sets) {
            $query = "UPDATE blogRecords_stats SET {$sets}
                      WHERE idRecord = '{$posts->idRecord}' ";
            $queries['blogRecords_stats'] = $query;

        }


        //=======================[ update blogRecords_settings ]================
        $columns = 'modelBlog_name, css, js, idFolder, relatedStory' ;
        $sets = handlePosts::Db_Get_setString($this->posts, $columns);
        if ($sets) {
            $query = "UPDATE blogRecords_settings SET $sets
                     WHERE idRecord = '{$posts->idRecord}' ";
            $queries['blogRecords_settings'] = $query;

        }

         /*foreach($queries AS $table => $query) {
            echo "<br><br><b>table = $table query = </b> <br> $query ";
        }
        return false;*/


        $this->C->Db_queryBulk($queries, false);
        return true;

    }


    function  publish()
    {
        /**
         * butoanele de publish nu se vor afisa daca userul nu are permisiuni de publicare
         */
        $idRecord      =   $_POST['idRecord'];
        $query = "UPDATE blogRecords_stats SET publishDate = NOW() WHERE idRecord = '{$idRecord}' ";
        //echo $query;
        $this->DB->query($query);
        return true;
    }
    function  unpublish()
    {
        $idRecord      =   $_POST['idRecord'];
        $query = "UPDATE blogRecords_stats SET publishDate = NULL WHERE idRecord = '{$idRecord}' ";
        $this->C->Db_query($query);
        #echo $query;
        return true;

    }
    function  deleteRecord()
    {

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

        $where = !$this->user->rights['article_rm']
                    ? "AND uidRec='{$this->uid}'": '';

        $query = "DELETE FROM blogRecords WHERE idRecord = '{$idRecord}' {$where} ";
        $result = $this->DB->query($query);
        if($result) {
            $this->C->feedback->Set_mess(
                      'Succes',
                      'Deleted',
                      'your article was succesfully deleted!!!');
            return true;
        } else {
            return $this->C->feedback->Set_badmess(
                      'error',
                      'Not allowed to remove this article',
                      'your are not the author of this article
                      and do not have permisiion to delete it!!!');
        }

   }


}
