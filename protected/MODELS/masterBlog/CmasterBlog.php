<?php
class CmasterBlog {

    use TblogCategories;

    var $current_modelBlog = 'blog';
    var $categoriesHTML='';

    function _setINI(){

        $this->C->historyArgs = array('type'=>'masterBlog');
        $this->C->SET_HISTORYargs($this->idNode);
        #===============================================================================================================

        if(isset($_GET['recType']))
            $this->current_modelBlog= $_GET['recType'];   #record type sau modelBlog_name

        #===============================================================================================================


        $this->C->SET_general_mod($this->current_modelBlog  , 'MODELS');

         $this->C->{$this->current_modelBlog}->setINI();


        $this->GET_categories($this->idTree);      # seteaza categoriesHTML

    }
    public function __construct($C){


    }
}