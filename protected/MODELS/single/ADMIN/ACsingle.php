<?php
class ACsingle extends Csingle{

    function setDISPLAY() {
         if(isset($_POST['save_single']))
         {
             $content = $_POST['single_'.$this->LG];
             file_put_contents($this->resPath,$content);
         }
     }

    function _setINI(){

        $this->resPath = $this->C->get_resPath_forObj($this);
        $this->setDISPLAY();

    }
}