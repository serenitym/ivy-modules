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

class blogHandler_home extends ivyModule_objProperty
{
    var $homeBlogRecords;
    var $records;
    var $filterRecTypes;

    // pointers
    var $blog;
    var $baseQuery;
    var $filters;
    var $rowDb;

    // from yml - settings
    var $blogSections;

    //_hookRow_blogHome
    function _hookRow_blogHome($row)
    {
        $row['record_href'] = $this->rowDb->Get_recordHrefHome($row);
        $row['EDrecord'] = 'not';
        #var_dump($row);
        return $row;
    }
    function _hookRow_blog($row)
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

        /**
         * * Setted data:
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
         */
        $row['record_href'] = $this->rowDb->Get_record_href($row);
        $row['tags']        = $this->rowDb->Get_tagsArray($row['tagsName']);
        if(isset($this->tree[$row['idCat']])) {
            $row['catResFile']  = $this->tree[$row['idCat']]->resFile;
        }

        // atuhor & authors
        $row['authorHref']  = $this->rowDb->Get_record_authorHref($row['uidRec']);

        // daca are mai multi autori
        if ($row['uidsCSV']) {
            $row['uids']       = explode(', ',$row['uidsCSV']);
            $row['fullNames']  = explode(', ',$row['fullNamesCSV']);
            $row['authors']    = $this->rowDb->Get_authors($row['uids'], $row['fullNames']);

        }
        #var_dump($row);
        return $row;
    }
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

    function home_GetDataBlogLatest()
    {
         // echo "home_setData()";
        $tmpIdTree = $this->blog->tmpIdTree = 86;
        $tmpTree   = $this->blog->tmpTree    = $this->C->Get_tree($this->blog->tmpIdTree);

        $homeBlogRecords  = array();

        $queryBase = "SELECT
                        idRecord,idCat,uidRec,entryDate,
                        publishDate,title, format
                    FROM blogRecords_view ";

        foreach ($tmpTree[$tmpIdTree]->children AS $idCat) {

            $sql   = $this->baseQuery->Get_queryRecords(array('category' => $idCat), $queryBase);
            $query = $sql->fullQuery . ' ORDER BY publishDate DESC LIMIT 2';

            $homeBlogRecords[$idCat]['records']
                = $this->C->Db_Get_procRows($this, '_hookRow_blogHome', $query);

            $homeBlogRecords[$idCat]['catName']
                = $this->blog->tmpTree[$idCat]->name;
        }

       // var_dump($this->homeBlogRecords);
       // clean tmpData;
        unset($this->blog->tmpIdTree);
        unset($this->blog->tmpTree);

        return $homeBlogRecords;

    }
    function home_GetDataArchiveLatest()
    {
       $this->blog->tmpIdTree = 88;
       $this->blog->tmpTree    = $this->C->Get_tree($this->blog->tmplIdTree);

       $sql   = $this->baseQuery->Get_queryRecords(array('category' => $this->blog->tmpIdTree));
       $query = $sql->fullQuery.' ORDER BY publishDate DESC, idRecord DESC LIMIT 8';

       return $this->C->Db_Get_procRows($this, '_hookRow_archive', $query);

    }
    function home_setData()
    {
        //====================================[get latest in blog categories]===
        $this->homeBlogRecords = $this->home_GetDataBlogLatest();
        //====================================[get latest in archive categories]=
        $this->records         = $this->home_GetDataArchiveLatest();
        //====================================[ archive filters ]===============
        $idArchive = $this->blogSections["archive"];
        $baseUrl   = "?idT={$idArchive}&idC={$idArchive}";
        $this->filterRecTypes = $this->filters->Get_hrefFilterRecTypes($baseUrl);
    }


    function _init_()
    {
        $this->home_setData();
    }
}