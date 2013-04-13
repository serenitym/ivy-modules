<?php
trait TblogCategories{

   var $categoriesHTML='';                     # categoriile blogului
   var $filterCategories = array();
   var $filterCategories_unPublished = array();
   var $filterStat = false;
   var $treeCat;
   var $blogidT;

    #avem nevoie de mai multe tipuri de listare ale categoriilor
    #TREBUIE GANDIT AICI

    function GET_filtres_BDbyUid(){

        $query = "SELECT idCat, count(idRecord) AS nrRecords FROM blogRecords WHERE uidRec = '$this->uid' GROUP BY idCat";


        $this->filterCategories  = $this->C->GET_objProperties_byCat($this, $query,'idCat');

       # var_dump($this->filterCategories);
       # echo $query;
    }

    function GET_filtres_BDsimple(){

        $query = "SELECT COUNT( idCat ) AS nrRecords, idCat
                    FROM
                        (SELECT idCat FROM blogRecords where publishDate IS NOT NULL) AS notNULLrec
                    GROUP BY idCat";
        $this->filterCategories  = $this->C->GET_objProperties_byCat($this, $query,'idCat');


        #daca sunt admin
        if($this->admin){
            #daca nu am permisiuni de editare atunci trebuie sa iau doar acele recorduri pentru care userul este autor
            $wehereAuthor = '';
            if(!$this->C->user->getRecordsPermss())
                $wehereAuthor = " AND uidRec='{$this->C->user->uid}'";

            $query = "SELECT COUNT( idCat ) AS nrRecords, idCat FROM
                         (SELECT idCat FROM blogRecords where publishDate IS NULL  {$wehereAuthor}) AS NULLrec
                         GROUP BY idCat";

            $this->filterCategories_unPublished  = $this->C->GET_objProperties_byCat($this, $query,'idCat');
        }


    }
    function parseCategories($idCat,$type='masterBlog'){

        if($this->treeCat[$idCat]->children)
               {
                   $this->categoriesHTML .="<ul class='nav nav-list'>".($this->categoriesHTML=='' ? "<li class='nav-header'>Categories</li>" : '');

                   foreach($this->treeCat[$idCat]->children AS $idCh)
                        if(!$this->filterStat || isset($this->filterCategories[$idCh]))  #daca este setat un statusul de filtru este off sau daca este ok acest id in baza filtrelor
                         {


                            $name = $this->treeCat[$idCh]->name;
                            $this->categoriesHTML .="<li>
                                                           <a href='index.php?idT={$this->blogidT}&idC={$idCh}&type={$type}'>{$name}
                                                                 ("
                                                                     .(is_array($this->filterCategories[$idCh])
                                                                        ? $this->filterCategories[$idCh][0]['nrRecords']
                                                                        : '0'
                                                                      ).
                                                                 ")
                                                                 ".
                                                                  ($this->admin &&  isset($this->filterCategories_unPublished[$idCh])
                                                                      ? " <span class='text-warning'>
                                                                             (".$this->filterCategories_unPublished[$idCh][0]['nrRecords'].")
                                                                           </span>
                                                                             "
                                                                      : ''
                                                                  )
                                                                 ."

                                                          </a>";
                                                        $this->parseCategories($idCh,$type);
                            $this->categoriesHTML .=   "</li>";
                         }
                   $this->categoriesHTML .="</ul>";

               }
    }

    function GET_categories($idCat,$type='masterBlog',$treeCat='',$listType='')             {

         # ar trebui sa folosesc o metoda de manevra din core
        $this->blogidT = $idCat;

        if($treeCat!='')  $this->treeCat = $treeCat;
              else $this->treeCat = $this->C->tree;

       # var_dump($this->treeCat);

        if($listType!='') {

            $this->filterStat = true;
            $this->{'GET_filtres_'.$listType}();
        }
        else {
            $this->GET_filtres_BDsimple();
        }
        $this->parseCategories($idCat,$type);




     }
}
