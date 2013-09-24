<?php
/**
 * Class Cblog
 *
 * @package blog
 * @subpackage Cblog
 * @category
 * @copyright Copyright (c) 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @author ioana
 */
class dep_Cblog extends blog_handlers
{
    var $methodHandle ;
    var $assocTmplFile;
    var $tmplFiles;
    var $blogModels;
    var $blogSections;
    var $subtreeIds = array();

    // home vars
    /**
     * @var
     */
    var $tmpTree ;
    var $tmpIdTree ;
    var $filterRecTypes = array();
    var $uid = 0;

    // blog settings
    /**
     * array( array( 'value' => idFolder, 'name' => folderName ) )
     *
     * utilizat pt autocomplete pt liveEdit  ( EDsel )
     * @var array
     */
    var $folders;
    var $jsonFolders;
    /**
     * array( array('idFormat' => '', 'format' => '') )
     * @var array
     */
    var $formats;   //

    // general seters
    function hrefFilter($filterName, $filterValue)
    {
        return "filterName={$filterName}&filterValue={$filterValue}";
    }
    // Set_filterRecTypes
    function Get_hrefFilterRecTypes($baseUrl)
    {
        $filters = array();
        foreach ($this->formats As $format) {

            array_push($filters,array(
                    'filterName' => $format['format'] ,
                    'filterHref' => $baseUrl."&"
                                    .$this->hrefFilter('format', $format['format'])
                )
            );
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
        $query = "SELECT COUNT(*) AS nrRows, tagName
                  FROM blogMap_recordsTags
                  GROUP BY tagName
                  ORDER By nrRows
                  DESC LIMIT 0, $nrFilters";
        $res = $this->DB->query($query);
        if($res) {
            while($row = $res->fetch_assoc()) {

                array_push($filters,array(
                        'filterName' => $row['tagName'],
                        'filterHref' => $baseUrl. "&"
                            . $this->hrefFilter('tag', $row['tagName'])
                        )
                );
            }
        }
        return $filters;

    }
    function Get_hrefFilterCountry($baseUrl)
    {
        $filters = array();
        $query = "SELECT DISTINCT country FROM `blogRecords` WHERE country IS NOT NULL";
        $res = $this->DB->query($query);
        if ($res) {
            while($row = $res->fetch_assoc()) {

                array_push($filters,array(
                        'filterName' => $row['country'],
                        'filterHref' => $baseUrl. "&"
                            . $this->hrefFilter('country', $row['country'])
                    )
                );
            }
        }
        return $filters;

    }
    function Get_hrefFilterFolders($baseUrl)
    {
        $filters = array();
        $query = "SELECT  idFolder, folderName FROM `blogRecord_folders`";
        $res = $this->DB->query($query);
        if ($res) {
            while($row = $res->fetch_assoc()) {

                array_push($filters,array(
                        'filterName' => $row['folderName'],
                        'filterHref' => $baseUrl. "&"
                            . $this->hrefFilter('idFolder', $row['idFolder'])
                    )
                );
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
        $tree   = $this->tmpTree ? $this->tmpTree : $this->tree;
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
    //#1
    function Get_unpublishedFilter(){

        $wheres = array();

        if(!$this->user->rights['article_edit']) {
            array_push($wheres,
                " ( uidRec='{$this->user->uid}'
                     OR unamesCSV LIKE '%{$this->user->uname}%'
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

    //===============================================[ query builders ]=========
    function Get_baseQueryRecord()
    {
        /**
         *  blogRecords_view  -- leftOUTER JOIN  (blogRecords cu blogRecords_settings)
         *
         *
         * VIEW blogTagsName_view AS
                 SELECT idRecord, GROUP_CONCAT( tagName SEPARATOR  ', ' ) AS tagsName
               FROM blogTags
                   JOIN blogMap_recordsTags
                   ON ( blogTags.idTag = blogMap_recordsTags.idTag )
               GROUP BY idRecord
         *
         * -- ATENTIE -- Nu sunt sigura ca este cea mai eficienta metoda cu acest view  (dar mom pare cea mai simpla)
         *
        */

         $query = "SELECT
                     blogRecords_view. idRecord,
                     idCat,
                     idTree,
                     uidRec,
                     title,
                     content,
                     lead,
                     leadSec,
                     country,
                     city,
                     entryDate,
                     publishDate,
                     republish,
                     relatedStory,

                     css,
                     js,
                     SEO,

                     idFormat,
                     format,

                     folderName,
                     idFolder,

                      uid_Rec, fullName,
                      tagsName,

                      uidsCSV,
                      fullNamesCSV,
                      unamesCSV

                      FROM blogRecords_view
                      JOIN
                      (
                          SELECT uid AS uid_Rec, CONCAT(first_name,'  ',last_name) AS fullName
                          from auth_user_details
                      ) AS TBuserName
                      ON (blogRecords_view.uidRec = TBuserName.uid_Rec)

                      LEFT OUTER JOIN
                      (
                          SELECT idRecord, GROUP_CONCAT( tagName SEPARATOR  ', ' ) AS tagsName
                  		  FROM blogMap_recordsTags
                          GROUP BY idRecord
                      ) AS TBtagsName
                      ON (blogRecords_view.idRecord = TBtagsName.idRecord)

                      LEFT OUTER JOIN
                      (
                          SELECT
                            idRecord,
                            GROUP_CONCAT( blogRecords_authors.uid SEPARATOR ', ' )
                              AS uidsCSV ,
                            GROUP_CONCAT(
                             CONCAT (first_name, ' ', last_name )  SEPARATOR ', '
                            ) AS fullNamesCSV ,

                            GROUP_CONCAT( auth_users.name SEpARATOR ', ')
                              AS unamesCSV

                          FROM blogRecords_authors
                          JOIN auth_user_details
                              ON (blogRecords_authors.uid = auth_user_details.uid)

                          JOIN auth_users
                              ON (blogRecords_authors.uid = auth_users.uid)

                          GROUP BY idRecord
                      ) AS TBauthors
                      ON (blogRecords_view.idRecord = TBauthors.idRecord)



                                           ";

         return $query;

    }

    function Get_baseQueryRecords()
    {

        //modelComm_name,commentsView,commentsStat,commentsApprov,SEO,
        // nrRates,ratingTotal,

        
        $query = "SELECT
                    blogRecords_view.idRecord,
                    idCat,
                    idTree,
                    uidRec,
                    title,
                    content,
                    lead,
                    leadSec,
                    country,
                    city,
                    entryDate,
                    publishDate,
                    republish,
                    format,
                    relatedStory,
                    folderName,
                    idFolder,

                    uid_Rec,
                    fullName,
                    tagsName,

                    uidsCSV,
                    fullNamesCSV,
                    unamesCSV


                    FROM blogRecords_view
                         JOIN
                         (
                           SELECT uid AS uid_Rec, CONCAT(first_name,'  ',last_name) AS fullName
                           FROM auth_user_details
                         ) AS TBuserName
                         ON (blogRecords_view.uidRec = TBuserName.uid_Rec)

                         LEFT OUTER JOIN
                         (
                           SELECT idRecord, GROUP_CONCAT( tagName SEPARATOR  ', ' ) AS tagsName
                                FROM blogMap_recordsTags
                                GROUP BY idRecord
                         ) AS TBtagsName
                          ON (blogRecords_view.idRecord = TBtagsName.idRecord)

                          LEFT OUTER JOIN
                          (
                              SELECT
                                idRecord,
                                GROUP_CONCAT( blogRecords_authors.uid SEPARATOR ', ' )
                                  AS uidsCSV ,
                                GROUP_CONCAT(
                                 CONCAT (first_name, ' ', last_name )  SEPARATOR ', '
                                ) AS fullNamesCSV ,

                                GROUP_CONCAT( auth_users.name SEpARATOR ', ')
                                  AS unamesCSV

                              FROM blogRecords_authors
                              JOIN auth_user_details
                                  ON (blogRecords_authors.uid = auth_user_details.uid)

                              JOIN auth_users
                                  ON (blogRecords_authors.uid = auth_users.uid)

                              GROUP BY idRecord
                          ) AS TBauthors
                          ON (blogRecords_view.idRecord = TBauthors.idRecord)

                          ";


     return $query;

    }
    /**
     * @param        $filters  = array($filterName => $filterValue);
     *
     * @return array
     */
    function Get_queryRecords($filters = array(), $query = '')
    {
         $sql = new stdClass();
        //$sql->parts['query'];
        //$sql->fullQuery;

        $sql->parts['query']  = $query ? $query:  $this->Get_baseQueryRecords(); // return string
        $basicFilters         = $this->Get_basicFilter();  //return array
        $requestFilters       = $this->_handle_requestFilters($filters);

        $sql->parts['wheres'] = array_merge($basicFilters, $requestFilters);
        $sql->parts['where']  =  count($sql->parts['wheres']) == 0 ? ''
                                  : ' WHERE '.implode(' AND ', $sql->parts['wheres'])
                                 ;
        $sql->fullQuery       = $sql->parts['query'].
                                $sql->parts['where'];

        /*error_log("[ ivy ] Cblog - Get_queryRecords : "
                  .preg_replace('/\s+/', ' ', $sql->fullQuery)
        );*/

        return $sql;
    }

    function Get_queryRecord()
    {
        $sql = new stdClass();
        //$sql->parts['query'];
        //$sql->fullQuery;

        $sql->parts['query'] = $this->Get_baseQueryRecord();
        $sql->parts['where'] = " WHERE blogRecords_view.idRecord = '{$_GET['idRec']}'" ;

        $sql->fullQuery = implode(' ', $sql->parts);
        error_log("[ ivy ] Cblog - Get_queryRecords : "
                .preg_replace('/\s+/', ' ', $sql->fullQuery)
        );

        return $sql;

    }

    //===============================================[ init blog ]==============
    /**
     * Set blog Settings
     *
     * Uses:
     *
     * * dbTable - blogRecord_folder = (*idFolder*, parentFolder, folderName, idTmpl);
     * * dbTable - blogRecord_folder = (*idFormat*, format, idTmpl);
     *
     * @todo * table - blogTags_banned =>  **bannedTags** = [{idTag: '', tagName: ''}]
     * @todo * table - blogRecord_tmplFiles => **tmplFiles** = array(array( idTmpl=> '', tmplFile=> ''),...)
     *
     * @todo * table - blogRecord_types =>  **types**     = [ {idType: '', type: '', idTmpl:'', tmplFile: '' } ]
     *
     * @uses Cblog::folders      array( array( 'value' => idFolder, 'name' => folderName ) )
     * @uses Cblog::jsonFolders
     * @uses Cblog::formats     array( array('idFormat' => '', 'format' => '') )
     */
    protected function Set_blogSettings()
    {
        //=============================================[ folders ]==============
        $query = "SELECT idFolder AS value, folderName AS name
                  FROM blogRecord_folders";
        $this->folders = $this->C->Db_Get_rows($query);

        $emptySelect = array(
                            array('value' => 0, 'name' => 'none' )
                       );
        $folders = array_merge($emptySelect, $this->folders);

        $this->jsonFolders = json_encode($folders);

       // echo "Set_blogSettings - jsonFolders = ".$this->jsonFolders;

        //=============================================[ formats ]==============
        $query = "SELECT idFormat , format
                  FROM blogRecord_formats";
        $this->formats =     $this->C->Db_Get_rows($query);

    }

    /**
     * init blog
     *
     * @uses Cblog::Set_blogSettings() get blog settings from data base
     * @uses blog_handlers::_handle_requests() resolve requests
     */
    function _init_()
    {
        $this->Set_blogSettings();
        $this->_handle_requests();

        if (!isset($this->uid)) {
            error_log('[ ivy ] Cblog - _init_ : Nici un user nu a fost setat ');
        }
    }

    function __construct()
    {

    }
}
