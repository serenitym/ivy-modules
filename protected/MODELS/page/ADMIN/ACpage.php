<?php
class ACpage extends Cpage
{
    function _setINI()
    {
        //??? da fuck ???
        if($_POST['save_FULLpage'])
        {echo $_POST['page_en']."<br/>"; unset($_POST);}
    }
}