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

class blog_filters extends ivyModule_objProperty
{
    var $subtreeIds;
    /**
     * @var object blog_filter
     * - variabila setata in metoda _init_()
     */
    var $currFilter;


    function hrefFilter($filterName, $filterValue)
    {
        return "filterName={$filterName}&filterValue={$filterValue}";
    }

    // Set_filterRecTypes
    function Get_hrefFilterRecTypes($baseUrl)
    {
        $filters = array();
        $filterName = 'format';

        foreach ($this->blog->formats As $format) {
            //blog_filter($baseUrl, $filterName, $filterValue);
            $filter = new blog_filter($baseUrl, $filterName, $format['format']);
            $filters[$filter->id] = $filter;
            /*array_push($filters,array(
                    'filterName' => $format['format'] ,
                    'filterHref' => $baseUrl."&"
                                    .$this->hrefFilter('format', $format['format'])
                )
            );*/
        }

        return $filters;
    }

    /**
     * @param $baseUrl
     * @param $nrFilters
     *
     * @uses
     * @return array
     */
    function Get_hrefFilterTags($baseUrl, $nrFilters)
    {
        $filters = array();
        $filterName = 'tag';

        $query = "SELECT COUNT(*) AS nrRows, tagName
                  FROM blogMap_recordsTags
                  GROUP BY tagName
                  ORDER By nrRows
                  DESC LIMIT 0, $nrFilters";
        $res = $this->DB->query($query);

        if($res) {
            while($row = $res->fetch_assoc()) {
                //blog_filter($baseUrl, $filterName, $filterValue);
                $filter = new blog_filter($baseUrl, $filterName, $row['tagName']);
                $filters[$filter->id] = $filter;
            }
        }
        return $filters;

    }
    function Get_hrefFilterCountry($baseUrl)
    {
        $filters = array();
        $filterName = 'country';

        $query = "SELECT DISTINCT country FROM `blogRecords` WHERE country IS NOT NULL";
        $res = $this->DB->query($query);

        if ($res) {
            while($row = $res->fetch_assoc()) {
                //blog_filter($baseUrl, $filterName, $filterValue);
                $filter = new blog_filter($baseUrl, $filterName, $row['country']);
                $filters[$filter->id] = $filter;
            }
        }
        return $filters;

    }
    function Get_hrefFilterFolders($baseUrl)
    {
        $filters = array();
        $filterName = 'idFolder';

        $query = "SELECT  idFolder, folderName FROM `blogRecord_folders`";
        $res = $this->DB->query($query);

        if ($res) {
            while($row = $res->fetch_assoc()) {
                //blog_filter($baseUrl, $filterName, $filterValue);
                $filter = new blog_filter($baseUrl, $filterName, $row['idFolder'], $row['folderName']);
                $filters[$filter->id] = $filter;
            }
        }
        return $filters;

    }

    //===============================================[ query Filters ]==========
    //#1
    function Get_idFolderFilter($idFolder)
    {
        return " idFolder = {$idFolder} ";
    }
    //#1
    function Get_formatFilter($format)
    {
        return " format = '{$format}' ";
    }
    //#1
    function Get_tagFilter($filterValue)
    {
        return ' tagsName LIKE "%'.$filterValue.'%" ';
    }
    //#1
    function Get_countryFilter($filterValue)
    {
        return " country = '{$filterValue}' ";
    }
    //#1
    function Get_uidFilter($filterValue)
    {
        return " uidRec = '{$filterValue}' ";
    }
    //#1.1
    function Set_subtreeIds($idNode, &$tree)
    {
        array_push($this->subtreeIds, $idNode);

        if (isset($tree[$idNode]->children )
            && count($tree[$idNode]->children) > 0
        ) {
            foreach ($tree[$idNode]->children AS $idChild) {
                $this->Set_subtreeIds($idChild, $tree);
            }
        }
    }
    //#1.2
    function Get_categoryFilter($idNode = '')
    {
        /*
         * categoria ar trebui deja sa fie stiuta este $idNode
         * acum ne intereseaza toate recordurile care au idCat IN (listaIduri)
         *
         * listaIduri = categoria curenta + categoriile paritzi si subparinti
         * cu s-ar zice toate nodurile unui subTree sau tree
         * aici mi se pare ca avem nevoie de o functie de ajutor (recursiva )
         * */
        //var_dump($this->tree[$this->idNode]);
        $tree   = $this->blog->tmpTree ? $this->blog->tmpTree : $this->tree;
        $idNode = $idNode ? $idNode : $this->idNode;
        //echo "<b>Get_categoryFilter $idNode</b> <br >";

        // clean subTreeIds
        $this->subtreeIds = array();
        $this->Set_subtreeIds($idNode, $tree);
        if (!$this->subtreeIds || count($this->subtreeIds) == 0 ) {
            error_log("[ ivy ] Cblog - Get_categoryFilter : "
                      ." Nu s-au putut lua nodurile din subtree-ul : $idNode"
            );
        } else {
            $subTreeIds = implode(',', $this->subtreeIds);
            error_log("[ ivy ] Cblog - Get_categoryFilter : "
                      ."  pt  subtree-ul : $idNode avem nodurile : $subTreeIds ");
            return " idCat IN ( $subTreeIds )";
        }


    }
    //#1
    function Get_unpublishedFilter(){

        $wheres = array();

        if(!$this->C->user->rights['article_edit']) {
            array_push($wheres,
                " ( uidRec='{$this->C->user->uid}'
                     OR unamesCSV LIKE '%{$this->C->user->uname}%'
                   ) ");
        }

        array_push($wheres, " publishDate IS NULL ");

        // daca userul sa zicem emoderator atunci va putea vedea toate
        // articolele nepublicate altfel le va vedea doar pe acelea pentru care
        // este autor
        $where = implode(' AND ', $wheres);
        return $where;

    }
    function Get_publishFilter()
    {
        return " publishDate is not NULL ";
    }
    //#1
    function Get_basicFilter()
    {
        $wheres = array();
        $wheres['publish'] = $this->Get_publishFilter();
        //array_push($wheres, " publishDate is not NULL ");

        return  $wheres;
    }

    //#2
    function _handle_requestFilters($filters = array())
    {
        //filterList
        $filtersStrs = array();

        // check for requested filter
        if (isset($_REQUEST['filterName']) && isset($_REQUEST['filterValue'])) {
           $filters[$_REQUEST['filterName']] =  $_REQUEST['filterValue'];
        }
        // ar trebui sa am un requested filters si ca array

        if (count($filters)) {

            foreach ($filters AS $filterName => $filterValue) {
                //test if method exists
                if (!method_exists($this, 'Get_'.$filterName.'Filter')) {
                    error_log("[ ivy ] Cblog - Get_queryRecords :"
                              ." Sorry the filter $filterName has no method handler "
                    );
                } else {
                    $filter = $this->{'Get_'.$filterName.'Filter'}($filterValue);
                    $filtersStrs[$filterName] = $filter;
                    //array_push($filtersStrs, $filter);
                }
            }
        }

        return $filtersStrs;
    }

    /**
     * Setarea filtrului curent
     * @uses
     */
    function set_currFilter()
    {
        $currFilterName   = $_REQUEST['filterName'] ?: '';
        $currFilterValue  = $_REQUEST['filterValue'] ?: '';
        $this->currFilter = new blog_filter('', $currFilterName, $currFilterValue);

    }
    function _init_()
    {
        $this->set_currFilter();
        //echo "blog_filter - _init_() : currFilter ";
        //var_dump($this->currFilter);
    }

}