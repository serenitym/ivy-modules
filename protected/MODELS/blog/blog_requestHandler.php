<?php
/**
 * PHP Version 5.3+
 *
 * @category 
 * @package 
 * @author Ioana Cristea <ioana@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link http://serenitymedia.ro
 */

class blog_requestHandler extends ivyModule
{
    var $handler;   // obj handlers like : archive, blog, home
    var $handlerPrefix = "blogHandler_";

    // from settings
    var $methodHandles; //objHandlers; asa ar trebui redenumit
    var $tmplFiles;

    // determined;
    var $template_file;
    var $template_context;

    // these are blog variables
    /*var $tmpIdTree;
      var $tmpTree;
    */



    function get_keyHandler($methodHandles)
    {
        $keyHandler  = $_GET['idRec'] ? 'idRec' :  $this->idTree;

        // determinam categoria care trebuie listata
        if (!isset($methodHandles[$keyHandler])) {
            error_log("[ ivy ] blog_handlers - _handle_requests :"
                     . " Atentie nu a fost setat nici un method handler pentru "
                     . " idTree = {$this->idTree}"
                     );
            return false;
        }

        error_log("[ ivy ] blog_handlers - _handle_requests :"
             . " A fost setat method handler = {$methodHandles[$keyHandler]} "
             . " pentru idTree = {$this->idTree}"
              );

        return $methodHandles[$keyHandler];

    }
    function get_tmplFile($objHandleName, $tmplFiles)
    {
        $keyTmplFile =   $objHandleName . ( isset($_GET['idRec']) ? $this->idTree : '');

        if (!isset($tmplFiles[$keyTmplFile])) {
            error_log("[ ivy ] blog_handlers - _handle_requests :  "
                       . "Nu exista template_file asociat cu {$keyTmplFile}"
            );
            return false;
        } else {
            return $tmplFiles[$keyTmplFile];
        }
    }

    /**
     * determina un un obiect to handle the request
     * seteaza:
     *
     *  * $handler
     *  * $template_context
     *  * template_file
     * @uses
     */
    function _handle_requests()
    {

        $objHandleName = $this->get_keyHandler($this->methodHandles);
        $this->handler = $this->C->Module_Build_objProp($this,
                                            $this->handlerPrefix.$objHandleName);

        if($this->handler) {
            $this->template_context = $this->handler;
            $this->template_file    = $this->get_tmplFile($objHandleName, $this->tmplFiles);
        }

    }

}