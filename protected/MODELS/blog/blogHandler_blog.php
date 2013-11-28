<?php
class blogHandler_blog extends ivyModule_objProperty
{

    var $sqlRecords;
    var $limitStart = 10;
    var $noRecords = 10;
    var $totalRecords;
    var $records;

    // pointers
    var $blog;
    var $baseQuery;
    var $filters;
    var $rowDb;

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
        if(!$row['leadSec']){
            $row['leadPrev']    =  $row['lead'];
            $row['contentPrev'] = $row['content'];
        } else {
            $row['leadPrev']    =  $row['leadSec'];
            $row['contentPrev'] = $row['lead'] . ' ' .$row['content'];
        }


        $row['record_href']     = $this->rowDb->Get_record_href($row);
        $row['tags']            = $this->rowDb->Get_tagsArray($row['tagsName']);
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


    function blog_setRecords($fullQuery, $limitStart = 0, $limitEnd = 10)
    {
        $query = $fullQuery . "ORDER BY publishDate DESC, idRecord DESC LIMIT {$limitStart}, {$limitEnd}";
        //echo "<b>blog_setData</b> {$query}";
        $this->records = $this->C->Db_Get_procRows($this, '_hookRow_blog', $query);
    }
    function blog_setData()
    {
        $this->sqlRecords = $this->baseQuery->Get_queryRecords(array('category' => ''));
        $this->blog_setRecords($this->sqlRecords->fullQuery);

        // for async stats
        $res = $this->DB->query($this->sqlRecords->fullQuery);
        $this->totalRecords = $res->num_rows;

    }
    function blog_renderData()
    {
        $this->limitStart = $_POST['limitStart'];
        $this->blog_setRecords($this->sqlRecords->fullQuery, $this->limitStart);
        // daca nu suntem la capatul articolelor
        error_log("[ ivy ] blog_handlers - blog_renderData : "
                  .preg_replace('/\s+/', ' ', $this->sqlRecords->fullQuery)
        );

        $this->limitStart += 10;
        $this->noRecords = $this->limitStart;

        if($this->totalRecords < $this->limitStart){
            $this->noRecords = $this->totalRecords;
        }

        // error_log("**************[ivy] in blog_renderData");
        $this->blog->template_file = "blogRecords";
        echo $this->C->Handle_Render($this->blog);

    }
    function _init_()
    {
        $this->blog_setData();
        $this->C->siteTitle = $this->tree[$this->idTree]->name;

    }


}