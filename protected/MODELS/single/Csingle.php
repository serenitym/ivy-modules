<?php
class Csingle{


   /* function DISPLAY(){

        $LG = $this->LG;
        $idC = $this->C->idNode;

        if(file_exists($this->RESpath))
             $pageContent = file_get_contents($this->RESpath);
        else
        {
            $pageContent = 'Nu exista continut la pagina <b>'.$this->RESpath.'</b>';
            file_put_contents($this->RESpath,$pageContent);
        }


        #_________________________________________________________________

        $display ="<div class='SING ALLpage' id='single_{$idC}_{$LG}'>
                        <div class='EDeditor single'>
                            $pageContent
                        </div>
                  </div>";

        return  $display;
    }*/

    function __construct($C){

    }
}