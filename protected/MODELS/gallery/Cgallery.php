<?php
class Cgallery{

   /* function DISPLAY(){

        return 'Acesta ar trebui sa fie un sample gallery';
    }*/

    var $urlImages = array();

    function get_images(){

        $this->urlImages = $this->C->get_resImages_urls($this->modName);
        sort($this->urlImages);
    }
    function _setINI(){
       # var_dump($this);
        //var_dump($this->captions);
        $this->get_images();
    }

    function __construct(&$C){}
}