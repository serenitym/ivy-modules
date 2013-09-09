<?php
class ACblog extends blog_dbHandlers
{
    /**
     * Permissions used: ( not updated )
     *
     * array (size=21)
       'gid' => boolean true
       'comm_save'
       'comm_edit'
       'comm_pub'
       'comm_rm'
       'article_edit'   // to edit an article means to : edit / save
       'article_save'   // este destul de pointles pentru ca daca editezi si nu potzi salva nu are nici un sens
       'article_pub'
       'article_rm'
       'mute_user'
       'page_add'
       'page_edit'
       'page_pub'
       'page_rm'
       'user_add'
       'user_edit'
       'user_rm'
       'group_add'
       'group_edit'
       'group_rm'
       'perm_manage'
     *
     * used like :$this->user->rights['article_edit']
     *
     * uclass = [ root, guest, subscriber, moderator, editor,
     *           publisher, webmaster, admin ]
     *
     */

    var $authors = array(); //autorii contribuitori


    function Get_rights_articleEdit($uidRec, $uids = array())
    {
        $editRight = ($this->user->rights['article_edit'] )
                      ||  $uidRec == $this->uid
                      || in_array($this->uid, $uids) ;


        return $editRight;
    }
    //=========================================[ permisiuni asupra articlului ]=
    /**
     * Daca este un user logat
     *      - daca are permisiuni de master poate edita
     *      - daca nu are permisiuni
     *              - si este autorul recordului  - poate edita
     *
     */
    function Get_recordED($uidRec, $uids = array())
    {
        $editRight = $this->Get_rights_articleEdit($uidRec, $uids);
        //var_dump($this->user->rights);
        // error_log("[ ivy ] ACblog - Get_recordED pt {$uidRec} permisiuni = {$editRight} ");

        return !$editRight ? 'not' :'';
    }
    function Get_blogCategories(){
        //@todo: aceast mod de aface lucrurile  este temporar
        $cats = $this->tree[$this->idTree]->children;

        if(!$cats) {
            return false;
        }
        $catsStr = implode(', ', $cats);

        $query = "SELECT id AS idCat , name_en AS catName
                    FROM ITEMS
                    WHERE type='blog' AND id IN ($catsStr) ";
        $blogCategories = $this->C->Db_Get_rows($query);

        return $blogCategories;
    }

   function _hookRow_archive($row)
   {
       $row = parent::_hookRow_archive($row);
       $row['EDrecord']    = $this->Get_recordED($row['uidRec'], $row['uids']);
       $row['statusPublish'] = $this->Get_publishedStatus($row['publishDate']);

       return $row;

   }
   function _hookRow_blog($row)
   {

       $row = parent::_hookRow_blog($row);
       $row['EDrecord']    = $this->Get_recordED($row['uidRec'], $row['uids']);
       $row['statusPublish'] = $this->Get_publishedStatus($row['publishDate']);


       return $row;
   }
   function _hookRow_record($row)
   {
       if(!$row['commentsView'])   {$row['commView_true'] = '';   $row['commView_false'] = 'checked'; }
       if(!$row['commentsStat'])   {$row['commStat_true'] = '';   $row['commStat_false'] = 'checked'; }
       if(!$row['commentsApprov']) {$row['commApprov_true'] = ''; $row['commApprov_false'] = 'checked'; }

       $row = parent::_hookRow_record($row);
       // nu prea isi are rostul daca nu poate administra
       $row['blogCategories'] = $this->Get_blogCategories();

       $this->ED = $this->Get_recordED($row['uidRec'], $row['uids']);


       return $row;
   }
   function _hookRow_recordsPrior($row)
   {
       $row = parent::_hookRow_recordsPrior($row);
       /**
        * Daca este un user logat
        *      - daca are permisiuni de master poate edita
        *      - daca nu are permisiuni
        *              - si este autorul recordului  - poate edita
        *
        */
       $row['EDrecord']    = $this->C->Get_recordED($row['uidRec'], $row['uids']);

       return $row;
   }

    //===============================================[ request handlers ]=======
    function record_setAuthors()
    {
        // relate to $this->Get_authors;
        $selectedAuthors = array();
        foreach ($this->authors AS $key => $author) {
            array_push($selectedAuthors, array(
                    'id' => $author['uid'],
                    'name' => $author['fullName']
                ));
        }
        $authorsJSON = json_encode($selectedAuthors);
        $this->C->jsTalk .= "
            if( typeof ivyMods.blog == 'undefined'  ) {
                ivyMods.blog = {};
            }
            ivyMods.blog.authors = $authorsJSON;
        ";

        //echo "<b>ACblog - record_setAuthors</b><br>";
       /* var_dump($selectedAuthors);
        var_dump(json_encode($selectedAuthors));*/



    }
    function record_setData()
    {
        //$this->C->Module_Set_incFiles($this, 'js','js_record');
        // preluarea datelor de record
        parent::record_setData();
        $this->record_setAuthors();

    }

    function unpublished_setData($sql, $hookName)
    {
        $wheres = $sql->parts['wheres'];
        $wheres['publish'] = $this->Get_unpublishedFilter();
        $where =  count($wheres) == 0 ? ''
                  : ' WHERE '.implode(' AND ', $wheres);

        $fullQuery  = $sql->parts['query'].$where;

        error_log("[ ivy ] ACblog - archive_setData : "
                  .preg_replace('/\s+/', ' ', $fullQuery)
        );

        $query = $fullQuery.' ORDER BY entryDate DESC';
        $this->recordsUnpublished = $this->C->Db_Get_procRows($this,
                                    $hookName, $query);
    }
    function archive_setData()
    {
        parent::archive_setData();
        // unpublished records
        // set by the parent  $this->sqlRecords
        $this->unpublished_setData($this->sqlRecords, '_hookRow_archive');
    }
    function blog_setData()
    {
        parent::blog_setData();
        // unpublished records
        // set by the parent  $this->sqlRecords
        $this->unpublished_setData($this->sqlRecords, '_hookRow_blog');
    }

    //==============================================[ blog settings ]===========
    function saveFormat()
    {
        $data = 'saveFormat ';
        foreach($_POST AS $postName => $postValue) {
            $data .= 'postName = '.$postName."\n";
            $data .= 'postValue = '.$postValue."\n";
        }
        echo $data;
        $format = $_POST['format'];
        $id = $_POST['BLOCK_id'];

    }
    function addFormat()
    {
        echo 10;
    }
    function deleteFormat()
    {
        $data = 'deleteFormat ';
        foreach($_POST AS $postName => $postValue) {
            $data .= 'postName = '.$postName."\n";
            $data .= 'postValue = '.$postValue."\n";
        }
        echo $data;
    }

    function saveFolder()
    {
        $data = 'saveFolder ';
        /*foreach($_POST AS $postName => $postValue) {
            $data .= 'postName = '.$postName."\n";
            $data .= 'postValue = '.$postValue."\n";
        }
        echo $data;*/
        $folderName = $_POST['folderName'];
        $id = $_POST['BLOCK_id'];

        $query = "UPDATE blogRecord_folders
                  SET folderName = '{$folderName}'
                  WHERE idFolder = {$id}";

        $this->DB->query($query);

    }
    function addFolder()
    {
        $folderName = $_POST['folderName'];

        $query = "INSERT INTO blogRecord_folders
                  SET folderName = '{$folderName}'";
        $this->DB->query($query);

        $id = $this->DB->insert_id;
        if($id){
            echo $id;
        }
    }
    function deleteFolder()
    {
        /*$data = 'deleteFormat ';
        foreach($_POST AS $postName => $postValue) {
            $data .= 'postName = '.$postName."\n";
            $data .= 'postValue = '.$postValue."\n";
        }
        echo $data;
        */
        $id = $_POST['BLOCK_id'];

        $query = "DELETE FROM  blogRecord_folders WHERE idFolder = {$id}";
        //$this->DB->query($query);
    }

    function Set_toolbarAdminBlog()
    {
        error_log("ACblog - userul are permisiuni pentru a edita setarile blogului" );
        array_push($this->C->TOOLbar->buttons,
            "<input type='button' onclick='ivyMods.blog.popUpblogSettings(); return false;'  name='blogSettings' value='blog Settings' >"
        //    "<a href='".Toolbox::curURL()."&blogSettings'> blog Settings </a>"
        );
        /**
         * <input type='button' name='blogSettings' value='blog Settings'
                 onclick = \"ivyMods.blog.popUpblogSettings(); return false;\">

         */
    }
    function Set_dataBlogSettings()
    {
        /*echo "blog - folders <br>";
        var_dump($this->folders);

        echo "blog - formats <br>";
        var_dump($this->formats);*/

        $template = $this->C->Render_objectFromPath($this,
        "MODELS/blog/tmpl_bsea/ADMIN/tmpl/blog_settings.html"
        );

        if($template) {
            echo $template;
        } else {
            echo "Sorry the template could not be rendered";
        }
        return false;
    }

    function Get_blogSettings()
    {
        parent::Get_blogSettings();

        //=============================================[ folders ]==============
        $query = "SELECT  idFolder, folderName
                  FROM blogRecord_folders";
        $this->folders =   $this->C->Db_Get_rows($query);

    }
    function _init_()
    {
        // link to user
        $this->user     = &$this->C->user;
        // use  $this->feedback->setFb($fbType,$fbName, $fbMess);
        $this->fbk = &$this->C->feedback;
        parent::_init_();

        if($this->user->cid <= 2) {
          $this->Set_toolbarAdminBlog();
        }


    }

    /*function __wakeup(){

       // echo " Wakeup ACblog ";
        $this->C->DB_reConnect();
        $this->controlREQ_async();
    }*/
}