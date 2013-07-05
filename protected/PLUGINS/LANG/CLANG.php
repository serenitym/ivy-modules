<?php
class CLANG
{
    var $baseURI;

    function resetTree()
    {/*{{{*/
        if ($this->lang!=$this->C->langs[0]) {
            foreach ($this->C->tree as $id=>$childElement) {
                $this->C->tree[$id]->name = $childElement->{'name_'.$this->lang};
            }
        }
    }/*}}}*/

    function setLang()
    {/*{{{*/

      if (isset($_GET['lang'])) {
          $this->lang = $_GET['lang'];
      }

      $this->C->lang = $this->lang ;
      $langISO = $this->getLanguageCode($this->lang);

      putenv("LC_ALL=" . $langISO);
      setlocale(LC_ALL, $langISO);

      $this->resetTree();
      return true;
    }/*}}}*/

    private function getLanguageCode($lang)
    {/*{{{*/
        $languages = array(
            'en' => 'en_US',
            'ro' => 'ro_RO'
        );

        return $languages["$lang"];
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
            substr(publicURL, 0, -1), '',
            'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
        );

        $markup = "<$container class='$class'>";
        $template = "<a href='%s'>%s</a>";

        foreach ($this->C->langs as $lang) {
            $href = str_replace('/'.$this->lang.'/', '/'.$lang.'/', publicURL)
                . substr($this->baseURI, 1);
            $markup .= $lang == $this->lang
                ? sprintf($template, $href, '<b>' . strtoupper($lang) . '</b>')
                : sprintf($template, $href, strtoupper($lang));
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
