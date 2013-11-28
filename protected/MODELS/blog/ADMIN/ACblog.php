<?php
/**
 * Class ACblog
 *
 * @package
 * @subpackage
 * @category
 * @copyright Copyright (c) 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link      http://serenitymedia.ro
 * @author
 */
class ACblog extends Ablog_requestHandler
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

    var $folders;


    var $posts; // object with posts->postName

    var $HTMLmessage_record ;
    var $HTMLmessage_Records;   # cred ca asta ar trebui sa stea Atemplate_vars

    var $POST_mss = array(
        'succDeleteRecord' => 'You have succesfully deleted the record',
        'mssTags_fail' => 'This tags were not registered because they are banned or have unpermited characters'
    );

    function Get_rights_articleEdit($uidRec, $uids = array())
    {
        $editRight = ($this->C->user->rights['article_edit'] )
                      ||  $uidRec == $this->C->user->uid
                      || in_array($this->C->user->uid, $uids) ;


        return $editRight;
    }

    #===============================================[ DB - methods ]============
    function _hook_addRecord()
    {
        /**
         * aspect:
         *  - title
         *  - format
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
        $query =     "INSERT INTO blogRecords  (idCat, idTree, title, uidRec)
                             VALUES (
                              '{$this->idNode}' ,
                              '{$this->idTree}' ,
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
            $query =     "INSERT INTO blogRecords_settings (idRecord, idFormat)
                                 VALUES (
                                 '{$lastID}' ,
                                 '{$this->posts->idFormat}'
                                 )";
            //echo "<b>blogRecords_settings </b>".$query."</br>";
            array_push($queries, $query);

            $this->C->Db_queryBulk($queries, false);
            $location =  "http://".$_SERVER['SERVER_NAME']."/index.php?idT={$this->idTree}&idC={$this->idNode}&idRec={$lastID}";
            $this->C->reLocate($location);
        }

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

    //=================================================[init]===================
    function Set_blogSettings()
    {
        parent::Set_blogSettings();

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