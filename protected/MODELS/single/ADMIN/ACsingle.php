<?php
class ACsingle extends Csingle
{
    var $post;

    function _hook_savePage()
    {
       /*if($this->contentRights) {
           $this->C->feedBack->Set_mess(
               'error',
               'Permission error',
               'You dont have permissions to edit this page'
           );
           return false;
       }*/

       $this->post->resName  = $_POST['BLOCK_id'];
       $this->post->pathName = $this->C->Module_Get_pathRes($this,
                               $this->post->resName);
       $this->post->desc = $_POST['desc'];

       //error_log("********* [ ivy ] _hook_save_descPage");
       //var_dump($this->post);
       //return false;
       return true;
    }
    function savePage()
    {
      // var_dump($this->post);
       Toolbox::Fs_writeTo($this->post->pathName, $this->post->desc);
       //file_put_contents($this->post->pathName, $this->post->desc);

       //return false;
       return true;
    }

}
