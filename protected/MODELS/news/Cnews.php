<?php
class Cnews{

    /*function DISPLAY(){

        return "
            <div style='min-height: 500px;'>
                Pagina de <b>Noutati</b> este in Lucru !
            </div>
        ";
    }*/

    function get_news(){

        $query = "SELECT
                    idNw,
                    dateNews,
                    picUrl,
                    title,
                    lead,
                    content
                    from vw_news_i18n
                    WHERE
                        idLg = '{$this->lang}' AND idNw = {$_GET['idNw']}
                    ORDER BY dateNews desc
                        ";

        #echo "<b>get_ListNews </b> $query";
        $this->news = new stdClass();
        $this->C->GET_objProperties($this->news, $query);

        $this->template_file = 'news';
    }
    function get_ListNews_external(){

        $query = "SELECT
                           idNw,
                           dateNews,
                           picUrl,
                           extLink,
                           title,
                           lead
                           from vw_news_i18n
                           WHERE
                               idLg = '{$this->lang}' AND length(extLink) > 0
                           ORDER BY idNw desc
                               ";

             // echo "<b>get_ListNews </b> $query";
               $this->news = $this->C->GET_objProperties($this, $query, 'processNews');

               $this->template_file = 'listNews';
    }

    function processNews($row){


        if($row['extLink']){
             $row['linkNews'] = $row['extLink'];
             $row['linkTarget'] = '_black';
        }
        else{
             $row['linkNews'] = publicURL."?idT={$this->idTree}&idC={$this->idNode}&idNw=".$row['idNw'];
             $row['linkTarget'] = '';
        }
        return $row;


    }
    function get_ListNews(){

        $query = "SELECT
                    idNw,
                    dateNews,
                    picUrl,
                    extLink,
                    title,
                    lead
                    from vw_news_i18n
                    WHERE
                        idLg = '{$this->lang}'
                    ORDER BY idNw desc";

        #echo "<b>get_ListNews </b> $query";
        $this->news = $this->C->GET_objProperties($this, $query, 'processNews');

        $this->template_file = 'listNews';
    }

    function _setINI(){
        if(isset($_GET['idNw'])) $this->get_news();
        elseif($this->level == 0) $this->get_ListNews();
        elseif($this->level == 1) $this->get_ListNews_external();
    }
    function __construct(&$C){}
}
