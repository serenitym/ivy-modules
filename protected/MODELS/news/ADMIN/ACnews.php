<?php

class ACnews extends Cnews{

    var $posts ; // stdClass

    function _hook_prevNews(){
        /**
       * USE
       *
           updatePrev:
             title:

               validRules:
                 string:
                   fbk: {type: "warning", name: "News Title", mess: "Your title should be a string" }

                 notEmpty:
                    fbk: {type: "error", name: "News Title", mess: "Your news must have a title" }

             newsDate: ""

             extLink: ""

             lead:
               fbk: {type: "error", name: "News Lead", mess: "Your news must have a Lead" }
               varType: "string"

             newsPic: ""
       *
       *
       * //'/'.str_replace(BASE_URL,'',$alterPicUrl );
       *
       */
      $this->posts = $this->C->processPosts($this->prevNews);
      $vars = &$this->posts->vars;

      if($this->posts->validation){

          $vars->set_newsPic = '';
          if($vars->newsPic)
          {
              $vars->newsPic = '/'.str_replace(BASE_URL,'',$vars->newsPic);
              $vars->set_newsPic = " picUrl = '{$vars->newsPic}' , ";
          }

      }
      return $this->posts->validation;

    }

    function _hook_add_news(){

        return   $this->_hook_prevNews();
    }
    function add_news(){

        $vars = &$this->posts->vars;

        $queries = array();

        $query_add = "INSERT INTO news
                             (dateNews, picUrl, extLink)
                             values ('{$vars->newsDate}','{$vars->newsPic}','{$vars->extLink}')";
        $this->DB->query($query_add);
        $idNw = $this->DB->insert_id;


        foreach($this->C->langs AS $lang){
            array_push($queries, "INSERT INTO news_i18n
                                         (idNw, idLg, title, lead)
                                  values ($idNw, '{$lang}', '{$vars->title}', '{$vars->lead}' )");

        }
        $this->C->Db_queryBulk($queries, false);


    }


    function _hook_updatePrev_news(){

        return   $this->_hook_prevNews();
    }
    function updatePrev_news(){

        /**
         *   updatePrev:
                title:
                newsDate: ""
                extLink: ""
                lead:
                newsPic: ""
         */

        $idNw = $_POST['BLOCK_id'];
        $lg = $this->lang;
        $vars = &$this->posts->vars;

        $queries = array();

        array_push($queries, "UPDATE news SET
                            dateNews = '{$vars->newsDate}',
                            {$vars->set_newsPic}
                            extLink  = '{$vars->extLink}'

                            WHERE idNw = {$idNw}
                            ");

        array_push($queries, " UPDATE news_i18n SET
                                      title = '{$vars->title}',
                                      lead  = '{$vars->lead}'

                               WHERE idNw = {$idNw} AND idLg = '{$lg}'

        ");

       # echo "<b>updatePrev_news</b><br>";
       # echo "query_update  ".$queries[0]."<br>";
       # echo "query_update_i18n  ".$queries[1]."<br>";


        $this->C->Db_queryBulk($queries, false);

    }

    function delete_news(){
        $idNw = $_POST['BLOCK_id'];
        $query = "DELETE from news   WHERE idNw = {$idNw}";
        $this->DB->query($query);

    }

    function _hook_update_news(){

         $this->posts = $this->C->processPosts($this->newsUpdate);
         return $this->posts->validation;

       }
    function update_news(){


        $idNw = $_GET['idNw'];
        $lg = $this->lang;
        $vars = &$this->posts->vars;

        $queries = array();

        array_push($queries, "UPDATE news SET
                            dateNews = '{$vars->dateNews}',

                            WHERE idNw = {$idNw}
                            ");

        array_push($queries, " UPDATE news_i18n SET
                                      title = '{$vars->title}',
                                      lead  = '{$vars->lead}',
                                      content = '{$vars->content}'

                               WHERE idNw = {$idNw} AND idLg = '{$lg}'

        ");

       # echo "<b>update_news</b><br>";
       # echo "query_update  ".$queries[0]."<br>";
       # echo "query_update_i18n  ".$queries[1]."<br>";


        $this->C->Db_queryBulk($queries, false);

    }
}