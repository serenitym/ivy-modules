<?php
class CLANG
{
    var $lang;
    var $tree;
    var $baseURI;

    function resetTree()
    {/*{{{*/
        if ($this->lang!=$this->C->langs[0]) {
            foreach ($this->tree as $id=>$childElement) {
                $this->tree[$id]->name = $childElement->{'name_'.$this->lang};
            }
        }
    }/*}}}*/

    function setLang()
    {/*{{{*/

      if (isset($_GET['lang'])) {
          $this->lang = $_GET['lang'];
      }

      $this->C->lang = $this->lang ;
      $this->resetTree();
      return true;
    }/*}}}*/

    function DISPLAY()
    {/*{{{*/
        return $this->createSelector();
    }/*}}}*/

    public function createSelector( $container='', $class='', $separator='')
    {/*{{{*/
        $container = $container ?: 'div';
        $class = $class ?: 'lang_selector';
        $separator = $separator ?: "<span class='lang_separator'></span>";
        $this->baseURI = str_replace(
            publicURL, '',
            'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
        );

        $markup = "<$container class='$class'>";
        $template = "<a href='%s'>%s</a>";

        foreach ($this->C->langs as $lang) {
            $href = str_replace('/'.$this->lang.'/', '/'.$lang.'/', publicURL)
                . $this->baseURI;
            $markup .= sprintf($template, $href, strtoupper($lang));
            $markup .= $separator;
        }

        $markup = substr($markup, 0, -(strlen($separator)));
        $markup .= "</$container>";

        return $markup;

    }/*}}}*/

    public function _init ()
    {/*{{{*/
        $this->setLang();
    }/*}}}*/

    function __construct($C)
    {
    }

}

/* vim: set fdm=marker: */
