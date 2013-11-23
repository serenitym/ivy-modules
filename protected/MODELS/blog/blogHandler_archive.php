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

class blogHandler_archive extends ivyModule_objProperty
{
    // records list
    var $records;

    // archive.html - filters
    var $formatFilters ;
    var $tagFilters     ;
    var $countryFilters;
    /**
     * @var array(
     * 'filterName' => array(
     *  'filterName' => ''
     *  'filterValue' => ''
     *  'filterDesc' => ''
     *  'filterHref' => 'filterName={$filterName}&filterValue={$filterValue}'
     * ))
     */
    var $idFolderFilters  ;

    var $cls_selectedFilter = array(0=>'', 1=>'filter-selected');
    var $cls_selectedFilters = 'filters-selected';
    /**
     * @var array ex: array('format'=>'filters-selected',
     *                       'tag'=>'',
     *                       'country'=>'',
     *                       'idFolder'=>'');
     */
    var $filtersSets_selectedStat = array('format'=>'',
                                          'tag'=>'',
                                          'country'=>'',
                                          'idFolder'=>'');

    //mans
    var $sqlRecords;

   // pointers
    var $blog;
    var $baseQuery;
    var $filters;
    var $rowDb;

    function _hookRow_archive($row)
    {
        /**
         * RET DATA from DB - to process
         *
         * [ TB blogRecords_view]
           idRecord,
           idCat,uidRec,entryDate,publishDate,nrRates,ratingTotal,
           title,content,lead,
         *
         * [ TB blogRecords_settings ]
           format,modelComm_name,commentsView,commentsStat,commentsApprov,SEO,

         * [ TB blogMap_recordsTags ]
           uid_Rec, fullName,

         * [ TB blogMap_recordsTags]
           tagsName
        */

        $row['leadSec']          = $this->rowDb->Get_leadSec($row, 80);
        $row['record_mainPic']   = $this->rowDb->Get_record_mainPic($row);
        $row['record_href']      = $this->rowDb->Get_record_href($row);
        $row['ReadMore_link']    = "<a href='{$row['record_href']}'> Read More</a>";


        #var_dump($row);
        return $row;
    }
    function archive_setData()
    {
        /**
         * Setted data:
         * ->records
         *
         * + idRecord
         * + idCat
         * + uidRec
         * + entrydate
         * + publishDate
         * DEP + nrRates
         * DEP + ratingTotal
         * + title
         * + content
         * + lead
         * + leadSec
         * + country
         * + city
         * + format
         * + modelComm_name
         * + commentsView
         * + commentsApprov
         * + SEO
         * + uid_Rec
         * + fullName
         * + tagsName
         *
         * processed data
         * + record_mainPic
         * + record_href
         * + ReadMore_link
         * + EDrecord
         *
        */
        $this->sqlRecords   = $this->baseQuery->Get_queryRecords(array('category' => ''));

        $query = $this->sqlRecords->fullQuery.' ORDER BY publishDate DESC, idRecord DESC';
        $this->records = $this->C->Db_Get_procRows($this, '_hookRow_archive', $query);
        //echo "blog_handlers - archive_setData : this->records";
        //var_dump($this->records);
    }

    function filters_setData()
    {
        $idArchive = $this->blog->blogSections["archive"];
        $baseUrl = "?idT={$idArchive}&idC={$idArchive}";
        $this->formatFilters    =  $this->filters->Get_hrefFilterRecTypes($baseUrl);
        $this->tagFilters       =  $this->filters->Get_hrefFilterTags($baseUrl, 5);
        $this->countryFilters   =  $this->filters->Get_hrefFilterCountry($baseUrl);
        $this->idFolderFilters  =  $this->filters->Get_hrefFilterFolders($baseUrl);

        // retinerea filtrului curent
        $currFilterName = $this->filters->currFilter->name;
        $currFilterId = $this->filters->currFilter->id;
        if($currFilterName){
            // retine setul din care face parte filtrul curent selectat
            $this->filtersSets_selectedStat[$currFilterName] = $this->cls_selectedFilters;
            // numele setului curent
            $filtersSet = &$this->{$currFilterName.'Filters'};

            // retine filtrul din setul curent de filtre
            if(isset($filtersSet[$currFilterId])) {
                $filtersSet[$currFilterId]->set_selected();
            }

        } else {
            // default este filtru de foldere
            $this->filtersSets_selectedStat['idFolder'] = $this->cls_selectedFilters;

        }
    }

    function _init_()
    {
        $this->archive_setData();
        $this->filters_setData();
        $this->C->siteTitle = $this->tree[$this->idTree]->name;
    }

}