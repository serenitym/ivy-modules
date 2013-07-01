<?php
class Cprofile {

    use TblogCategories;

    var $current_model    = 'profile';
    var $current_tmplFile = 'profilePage';
    var $categoriesHTML='';

    var $uid ;

    function _setINI(){

        #var_dump($this->C->tree);
        $this->C->historyArgs = array('type'=>'profile');
        $this->C->SET_HISTORYargs($this->idNode);

        $this->uid = &$this->C->user->uid;

        #===============================================================================================================

        #echo $this->C->tree[$this->idNode]->type;
        $currType = &$this->C->tree[$this->idNode]->type;
        if($currType != 'profile')
        {

            if(isset($_GET['recType'])) #daca s-a cerut un recordType
            {
                $this->current_model    = $_GET['recType'];   #record type = modelBlog_name


            }
            else
            {
                $this->current_model = ( $currType=='masterBlog' ? 'blog' : $currType  );

            }

            #======================================================================================
            $this->C->SET_general_mod($this->current_model  , 'MODELS');


            array_push($this->C->{$this->current_model}->queryWheres," uidRec = '{$this->uid}' ")  ;
            # $this->C->{$this->current_model}->current_Layout = 'profile';               #mostly used fore hrefs

            $this->C->{$this->current_model}->setINI();




        }
        #===============================================================================================================


        $BlogTreeCat = $this->C->GET_tree(85);              # imi returneaza vectorul deserializat de categorii

        $this->GET_categories('85','profile',$BlogTreeCat,'BDbyUid'); # aici aceasta metoda ar trebui sa faca cumva o sortare in functie de UID-ul userului


    }
    public function __construct($C){


    }
}