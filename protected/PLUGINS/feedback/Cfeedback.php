<?php
class Cfeedback{

    var $mess = array();

    function setFb($fbType,$fbName, $fbMess){

        $fbMess = str_replace('"','',$fbMess);
        $fbMess = str_replace('$','',$fbMess);

        $fb_readMore = strlen($fbMess) > 100 ? true :false;
        array_push(
            $this->mess,
            array('fbType'=>$fbType,
                  'fbName'=>$fbName,
                  'fbMess'=>$fbMess,
                  'fb_readMore' => $fb_readMore)
        );
    }

    function __construct($C){}
}