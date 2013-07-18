<?php
trait ATcomments{
    # true sau false - editorul poate sa administreze sau nu comenturile
    # aceasta proprietate exista si in Ccoments pentru addComment action
    var $commentsPermss = false;

    function approveComment(){

        $idComm      =   $_POST['BLOCK_id'];
        $query  = "UPDATE {$this->DB_table_origin} SET approved='1'
                          WHERE idComm = '{$idComm}'  ";

        $this->C->Db_query($query,true,'#addCommAnc');

        #echo $query."<br>";

    }
    function ANapproveComment(){

        $idComm      =   $_POST['BLOCK_id'];
        $query  = "UPDATE {$this->DB_table_origin} SET approved='0'
                          WHERE idComm = '{$idComm}'  ";

        $this->C->Db_query($query,true,'#addCommAnc');

        #echo $query."<br>";
    }
    function deleteComment(){
        $idComm      =   $_POST['BLOCK_id'];

        $query  = "DELETE from {$this->DB_table_origin}   WHERE idComm = '{$idComm}'  ";

        $this->C->Db_query($query,true,'#addCommAnc');

        #echo $query."<br>";
    }

    function delPriority(){
        /**
         * WORKING on
         *      Table: blogComments_prior
         *      Columns:
         *      idComm	        int(5) PK
         *      priorityLevel	tinyint(4)
         */

         $idComm  = $_POST['BLOCK_id'];

         $query   = " DELETE from blogComments_prior WHERE idComm = $idComm ";
         $this->C->Db_query($query,true,'#addCommAnc');


    }

    function addPriority(){
         /**
         * WORKING on
         *      Table: blogComments_prior
         *      Columns:
         *      idComm	        int(5) PK
         *      priorityLevel	tinyint(4)
         */

        if(isset($_GET['idRec']))
        {
            $idRecord = $_GET['idRec'];
            $idComm   = $_POST['BLOCK_id'];

            $query    = " DELETE from blogComments_prior WHERE idRecord = $idRecord ";
            $this->C->Db_query($query,false);

            $query    = " INSERT INTO blogComments_prior ( idComm, idRecord, priorityLevel ) values ($idComm, $idRecord, 1)";
            $this->C->Db_query($query,true,'#addCommAnc');
        }

    }


    /**
     * DB_table_origin
     *  - setat de obiectul care cere comments
     *  - orice tabel_origin pentru comenturi trebuie sa contina [teoretic] ??
     *  - ne mai gandim
     *
     * Uses table  blogComments sau DB_table_origin
     *
     *  - idComm
     *  - idP
     *  - idExt
     *  - entryDAte
     *  - userName
     *  - uidComm
     *  - comment
     *  - approved   - default 1
     */
    function controlREQ(){

        if(isset($_POST['approve']))   $this->approveComment();
        if(isset($_POST['ANapprove'])) $this->ANapproveComment();
        if(isset($_POST['delete_comment'])) $this->deleteComment();
        if(isset($_POST['delPriority'])) $this->delPriority();
        if(isset($_POST['addPriority'])) $this->addPriority();




    }

    function setPermissions(){

        #true / false permisiunile administratorului pe commenturi

        $this->commentsPermss = $this->C->user->getCommentsPermss($this->uidRec);

       /* echo "<b>ATcomments - setPermissions </b><br>".
              ($this->commentsPermss ? 'Am permisiuni pe commenturi ' :'Nu am permisiuni pe aceste commenturi ')
                .$this->uidRec
                .' cu userul '.$this->uid."<br>";*/

    }
    function GET_Comments(){

        /** detour pentru GET_Comments - pentru ca trebuie sa se faca setarile de permisiuni*/
        $this->setPermissions();
        parent::GET_Comments();
    }
    function setINI(&$callerObj, $DB_extKey_name,$DB_extKey_value,$DB_table_origin,$DB_extKey_name_getArg=''){


      // echo "<b>ATcomments - setINI</b><br> am idRecord = {$this->uidRec} si uid = {$this->uid} <br> ";
       parent::setINI($callerObj, $DB_extKey_name,$DB_extKey_value,$DB_table_origin,$DB_extKey_name_getArg);



       if($this->commentsPermss)
           $this->controlREQ();

    }
}