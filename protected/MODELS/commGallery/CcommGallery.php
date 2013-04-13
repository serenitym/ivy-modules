<?php

class CcommGallery extends  Ccomments{

    function updateComment(){

    }

    function addComment_data(){


        /**
         * POST
         *  userName
         *  rating
         *  comment             - comment
         *
         *  picTitle
         *  picLoc
         *  picDate
         *  picUrl
         *
         */
        if($_POST['picUrl']!=''){

            # test this entry data???
            $idComm    = $this->DB->insert_id;
            $picUrl    = $_POST['picUrl'];
            $picTitle  = $_POST['picTitle'];
            $picLoc    = $_POST['picLoc'];
            $picDate   = $_POST['picDate'];
            $picDescr  = $_POST['picDescr'];
            #~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

            $query = "INSERT into blogComments_commGallery
                        (idComm, picUrl, picTitle, picLoc, picDate, picDescr )
                        values ( $idComm , '$picUrl', '$picTitle', '$picLoc', '$picDate' ,'$picDescr') ";

            $this->DB->query($query);


        }
       # echo "CcommGallery <b>addComment</b> $query <br>";
    }
    function ProcessComments($row)            {

        // ???  preluate direct din acest obiect prin $o->
       /* # inspecial necesare pentru editare
        $row['ED'] = &$this->ED;
        $row['LG'] = &$this->LG;
        $row['commentsApprov'] = &$this->commentsApprov;*/

        # ??? nu ar trebui conditionat cumva ? asta sa fie o variabila or something???
        # se poate adauga prioritate
        $row['prior'] = true;



        $this->C->thumbs->setINI('blogComments',
                                 'idComm',
                                  $row['idComm'],
                                 'SET_thComm'.$row['idComm']
                                 );
        return $row;
    }

    /*function addComment(){

    }*/
      function __construct($C){
        // echo 'AM instantiat CcommGallery'."<br>";

    }
}