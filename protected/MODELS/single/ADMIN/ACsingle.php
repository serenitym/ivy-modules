<?php
class ACsingle extends Csingle{

    function setDISPLAY() {
         if(isset($_POST['save_single']))
         {
             $content = $_POST['single_'.$this->LG];
             file_put_contents($this->resPath,stripslashes($content));
         }
     }

    function _init_(){

        $this->resPath = $this->C->Module_Get_pathRes($this);
        $this->setDISPLAY();

    }
}
