<?php
class CLANG
{
    var $lang;
    var $tree;
    var $baseURI;

    function resetTree()
    {/*{{{*/
        if ($this->lang!='en') {
            foreach ($this->tree as $id=>$childElement) {
                $this->tree[$id]->name = $childElement->{'name_'.$this->lang};
            }
        }
    }/*}}}*/

    function setLang()
    {/*{{{*/

      if (isset($_SESSION['lang'])) {
          $this->lang = $_SESSION['lang'];
      }

      if (isset($_GET['lang'])) {
          if ($_GET['lang']!=$this->lang) {
              $this->lang =  $_SESSION['lang'] = $_GET['lang'];
          }
      }

      $this->resetTree();
      return true;
    }/*}}}*/

    function DISPLAY()
    {/*{{{*/
        $this->baseURI = str_replace(publicURL, '', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        $markup = '';
        $template = "<a href='%s'>%s</a>";

        foreach ($this->C->langs as $lang) {
            $href = str_replace($_GET['lang'], $lang, publicURL)
                . $this->baseURI;
            $markup .= sprintf($template, $href, strtoupper($lang));
            $markup .= "<span class='lang_separator'></span>";
        }

        return $markup;
    }/*}}}*/

    public function _init ()
    {/*{{{*/
        $this->SET_lang();
    }/*}}}*/

    function __construct($C)
    {
    }

}

/* vim: set fdm=marker: */
