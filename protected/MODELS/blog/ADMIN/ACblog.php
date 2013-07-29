<?php
class ACblog extends blog_dbHandlers
{
    /**
     * Permissions used:
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

    /**
     * Daca este un user logat
     *      - daca are permisiuni de master poate edita
     *      - daca nu are permisiuni
     *              - si este autorul recordului  - poate edita
     *
     */
    function Get_recordED($uidRec)
    {
        $editRight =( ($this->user->rights['article_edit']
                   && $this->user->rights['article_rm'] )
                   ||  $uidRec == $this->uid )
                    ? true
                    : false;

        //var_dump($this->user->rights);
        error_log("[ ivy ] ACblog - Get_recordED pt {$uidRec} permisiuni = {$editRight} ");

        if (!$editRight) {
            return 'not';
        }

        return '';
    }

   function _hookRow_archive($row)
   {
       $row = parent::_hookRow_archive($row);
       $row['EDrecord']    = $this->Get_recordED($row['uidRec']);
       $row['statusPublish'] = $this->Get_publishedStatus($row['publishDate']);

       return $row;

   }
   function _hookRow_blog($row)
   {

       $row = parent::_hookRow_blog($row);
       $row['EDrecord']    = $this->Get_recordED($row['uidRec']);
       $row['statusPublish'] = $this->Get_publishedStatus($row['publishDate']);


       return $row;
   }
   function _hookRow_record($row)
   {
       if(!$row['commentsView'])   {$row['commView_true'] = '';   $row['commView_false'] = 'checked'; }
       if(!$row['commentsStat'])   {$row['commStat_true'] = '';   $row['commStat_false'] = 'checked'; }
       if(!$row['commentsApprov']) {$row['commApprov_true'] = ''; $row['commApprov_false'] = 'checked'; }

       $row = parent::_hookRow_record($row);
       $this->ED = $this->Get_recordED($row['uidRec']);

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
       $row['EDrecord']    = $this->C->Get_recordED($row['uidRec']);

       return $row;
   }

    function record_setData()
    {
        // preluarea datelor de record
        $this->C->Module_Set_incFiles($this, 'js','js_record');

        parent::record_setData();
    }

    function _init_()
    {
        // link to user
        $this->user     = &$this->C->user;
        // use  $this->feedback->setFb($fbType,$fbName, $fbMess);
        $this->fbk = &$this->C->feedback;
        parent::_init_();
        // setatat functile de apelat din .js
        // $this->controlREQ();
    }

    /*function __wakeup(){

       // echo " Wakeup ACblog ";
        $this->C->DB_reConnect();
        $this->controlREQ_async();
    }*/
}