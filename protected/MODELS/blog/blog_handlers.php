<?php
class blog_handlers extends Cblog_vars
{
    // records list
    var $records;

    // archive.html - filters
    var $filterRecTypes ;
    var $filterTags     ;
    var $filterCountries;
    var $filterFolders  ;

    //===============================================[ data filters ]===========
    // processors
    //======================================================[PROCESS - ENTRY's]=


    function Get_content(&$row, $lenght = 100)      {

         if(!$row['content']){
             $string =    substr(strip_tags($row['content']),0,$lenght);
             return substr($string, 0, strrpos( $string, ' ') );
         }
         else
             return $row['content'];
    }
    function Get_content_noPic(&$row, $lenght = 100){

        if (preg_match_all("/<img\b[^>]+?src\s*=\s*[\'\"]?([^\s\'\"?\#>]+).*\/>/", $row['content'], $matches))
        foreach($matches[0] AS $match)
            $row['content'] = str_replace($match,'',$row['content']);

        #var_dump($matches[0]);

         if($row['content']){
             $string =    substr(strip_tags($row['content']),0,$lenght);
             return substr($string, 0, strrpos( $string, ' ') );
         }
         else
             return $row['content'];
    }
    function Get_leadSec(&$row, $lenght = 70)       {

         if(!$row['leadSec']){

             $string = $row['lead']
                       ? substr(strip_tags($row['lead']),0,$lenght)
                       : substr(strip_tags($row['content']),0,$lenght);
             return substr($string, 0, strrpos( $string, ' ') );
         } else {
             return $row['leadSec'];
         }
    }
    function Get_recordPics(&$row)                  {
       // preg_match_all("/<img\b[^>]+?src\s*=\s*[\'\"]?([^\s\'\"?\#>]+).*\/>/", $row['content'], $matches);
        //preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $row['content'], $matches);
        preg_match_all('/(?<=\<img).+src=[\'"]([^\'"]+)/i', $row['content'], $matches);
        //echo "<b>Get_record_mainPic : matches </b>";
        //var_dump($matches);
        return $matches;

    }
    function Get_record_mainPic(&$row)              {
         #====================================[ main Pic ]===========================================================================
        /*$row['record_mainPic_src'] = preg_match_all("/<img\b[^>]+?src\s*=\s*[\'\"]?([^\s\'\"?\#>]+).*\/>/", $row['content'], $matches)
                                 ? $matches[1][0]
                                 : "";*/
        $matches = $this->Get_recordPics($row);

        if ($matches[1]) {
            return  $matches[1][0];
        }

        # echo $row['title']."<br>".var_dump($matches)."<br>";
    }
    function Get_record_href(&$row)                 {

       $idTree = !$this->tmpIdTree ? $this->idTree : $this->tmpIdTree;
       return "index.php?idT={$idTree}".
                 "&idC={$row['idCat']}".
                 "&idRec={$row['idRecord']}".
                 //"&type={$currentLayout}".
                  ($row['format'] && $row['format']!='blog' ? "&recType={$row['format']}" : '');

    }
    function Get_record_authorHref($uid)            {

        return "index.php?idT=3".
                         "&idC=3".
                         "&uid={$uid}";

    }
    function Get_record_hrefFolderFilter($idFolder) {

        $href = "?idT={$this->idTree}&idC={$this->idNode}&"
                .$this->hrefFilter('idFolder', $idFolder);
        return  $href;

    }
    function Get_tagsArray($tagsName)               {
        $tags = str_getcsv($tagsName, ',');
        foreach($tags AS $key=>$tag) {
            $tags[$key] = trim($tag);
        }

        return $tags;
    }
    function Get_publishedStatus ($publishDate)     {
        return $publishDate ? 'record-published' : 'record-unpublished';

    }
    function Get_recordHrefHome($row)               {
        return  "index.php?idT={$this->tmpIdTree}".
                  "&idC={$row['idCat']}".
                  "&idRec={$row['idRecord']}".
                   ($row['format'] && $row['format']!='blog' ? "&recType={$row['format']}" : '');
    }
    function Get_authors($uids, $fullNames)         {
        $authors    = array();
        foreach ($uids AS $key => $uid) {
            array_push($authors, array(
                    "uid" => $uid,
                    "fullName" => $fullNames[$key],
                    "authorHref" => $this->Get_record_authorHref($uid)
                ));
        }
        //var_dump( $authors);

        return $authors;
    }

    // deprecated methods
    /**
     * function GET_Rating(&$row)                      {

        return  isset($row['ratingTotal']) ?  $row['ratingTotal'] / $row['nrRates'] : '0';

    }*/
    /**
   * function SET_total_nrComments(&$row)            {

        // daca vizibilitatea commenturilor este disable
        if($row['commentsView'])
        {
            $query_total_nrComments = "SELECT idComm  from blogComments WHERE idExt = '{$row['idRecord']}'  AND approved='1' ";
            return
                    $this->DB->query($query_total_nrComments)->num_rows;
        }
        else
            return 0;

    }
 */

    //=====================================[Control - PROCESS - ENTRY's]=======

    //_hookRow_blogHome
    function _hookRow_blogHome($row)
    {
        $row['record_href'] = $this->Get_recordHrefHome($row);
        $row['EDrecord'] = 'not';
        #var_dump($row);
        return $row;
    }
    //_hookRow_blog
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
        $row['record_href'] = $this->Get_record_href($row);
        $row['tags']        = $this->Get_tagsArray($row['tagsName']);
        if(isset($this->tree[$row['idCat']])) {
            $row['catResFile']  = $this->tree[$row['idCat']]->resFile;
        }

        // atuhor & authors
        $row['authorHref']  = $this->Get_record_authorHref($row['uidRec']);

        // daca are mai multi autori
        if ($row['uidsCSV']) {
            $row['uids']       = explode(', ',$row['uidsCSV']);
            $row['fullNames']  = explode(', ',$row['fullNamesCSV']);
            $row['authors']    = $this->Get_authors($row['uids'], $row['fullNames']);

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

        $row['leadSec']          = $this->Get_leadSec($row, 80);
        $row['record_mainPic']   = $this->Get_record_mainPic($row);
        $row['record_href']      = $this->Get_record_href($row);
        $row['ReadMore_link']    = "<a href='{$row['record_href']}'> Read More</a>";


        #var_dump($row);

        return $row;
    }
    function _hookRow_recordRelated($row)
    {
        $row['record_href'] = $this->Get_record_href($row);

        return $row;
    }
    function _hookRow_record($row)
    {

       // $row['Rating']           = $this->SET_Rating($row);
       // $row['total_nrComments'] = $this->SET_total_nrComments($row);

        #=======================[Rating set & thumbs set]===============================================================
       // $byUid = (isset($this->uid) ? $this->uid : false );

        //function setINI($ratingType, $extKey_name, $extKey_value, $setName='', $getbyUid= false, $onlyUser=false) {
        /* $this->C->rating->setINI('blog',
                                 'idRecord',
                                 $row['idRecord'],
                                'SET_recordRating',
                                 $byUid );*/
        #$this->C->rating->template_file = 'ratingRecord_bigStar';
        #===============================================================================================================
        $row['tags']        = $this->Get_tagsArray($row['tagsName']);
        $row['hrefFolderFilter']  = $this->Get_record_hrefFolderFilter($row['idFolder']);
        // pt blogRecord.html
        if(isset($this->tree[$row['idCat']])) {
            $row['catResFile']  = $this->tree[$row['idCat']]->resFile;
        }

        // atuhor & authors
        $row['authorHref']  = $this->Get_record_authorHref($row['uidRec']);

        // daca are mai multi autori
        if ($row['uidsCSV']) {
            $row['uids']       = explode(', ',$row['uidsCSV']);
            $row['fullNames']  = explode(', ',$row['fullNamesCSV']);
            $row['authors']    = $this->Get_authors($row['uids'], $row['fullNames']);

        }

        return $row;

     }
    // for external use CblogSite - profile.html
    function _hookRow_profile($row)
    {
        $row['record_href']      = $this->Get_record_href($row);
        return $row;
    }

    //deprecated ???
    function _hookRow_recordsPrior($row)
    {


        $row['lead']             = $this->SET_lead($row);
        $row['record_mainPic']   = $this->Get_record_mainPic($row);
        $row['content']          = $this->Get_content_noPic($row,1050);
        $row['record_href']      = $this->Get_record_href($row);
        $row['ReadMore_link']    = "<a href='{$row['record_href']}'> Read More</a>";
        // $row['Ratidng']           = $this->SET_Rating($row);
        // $row['total_nrComments'] = $this->SET_total_nrComments($row);

       // $this->C->rating->setINI('blog','idRecord',$row['idRecord'], 'SET_record'.$row['idRecord']);

        return $row;

    }
    function _hookRow_priorities($row)    {

       /**
        * LOGISTICS
        *
        * FROM query
        * "SELECT idRecord,  DATEDIFF(NOW(), endDate ) AS validPriority
                                        FROM blogRecords_prior
                                        ORDER BY priorityLevel asc"

        *  - daca dataCurenta > data expirarii prioritatii => DATEDIFF > 0
        *      - prioritatea este invalida si este deletata din tabelul de prioritati
        *      - se returneaza false pentru ca acest record sa nu fie adaugat ca rezultat
        *
        * - daca prioritatea este valida se returneaza doar idRecord
        */
       if($row['validPriority'] > 0){

           $this->DB->query("DELETE from blogRecords_prior WHERE idRecord = {$row['idRecord']} ");
           return false;
       }

       else return $row['idRecord'];
   }

    //===============================================[ request handlers ]=======

    //==========================================================[ home Data ]===
    function home_GetDataBlogLatest()
    {
         // echo "home_setData()";
        $this->tmpIdTree = 86;
        $this->tmpTree    = $this->C->Get_tree($this->tmpIdTree);
        $homeBlogRecords  = array();

        $queryBase = "SELECT
                        idRecord,idCat,uidRec,entryDate,
                        publishDate,title, format
                    FROM blogRecords_view ";

        foreach ($this->tmpTree[$this->tmpIdTree]->children AS $idCat) {

            $sql   = $this->Get_queryRecords(array('category' => $idCat), $queryBase);
            $query = $sql->fullQuery . ' ORDER BY publishDate DESC LIMIT 2';

            $homeBlogRecords[$idCat]['records']
                = $this->C->Db_Get_procRows($this, '_hookRow_blogHome', $query);

            $homeBlogRecords[$idCat]['catName']
                = $this->tmpTree[$idCat]->name;
        }

       // var_dump($this->homeBlogRecords);
       // clean tmpData;
        unset($this->tmpIdTree);
        unset($this->tmpTree);

        return $homeBlogRecords;

    }
    function home_GetDataArchiveLatest()
    {
       $this->tmpIdTree = 88;
       $this->tmpTree    = $this->C->Get_tree($this->tmplIdTree);

       $sql   = $this->Get_queryRecords(array('category' => $this->tmpIdTree));
       $query = $sql->fullQuery.' ORDER BY entryDate DESC LIMIT 8';

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
        $this->filterRecTypes = $this->Get_hrefFilterRecTypes($baseUrl);

    }

    //==========================================================[ blog Data ]===
    function blog_setData()
    {
        $sql   = $this->Get_queryRecords(array('category' => ''));
        $query = $sql->fullQuery . ' ORDER BY entryDate DESC';
        $this->records = $this->C->Db_Get_procRows($this, '_hookRow_blog', $query);
    }

    //=======================================================[ archive Data ]===
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
        $sql   = $this->Get_queryRecords(array('category' => ''));
        $query = $sql->fullQuery.' ORDER BY entryDate DESC';
        $this->records = $this->C->Db_Get_procRows($this, '_hookRow_archive', $query);
        //echo "blog_handlers - archive_setData : this->records";
        //var_dump($this->records);

        //====================================[ archive filters ]===============
        $idArchive = $this->blogSections["archive"];
        $baseUrl = "?idT={$idArchive}&idC={$idArchive}";
        $this->filterRecTypes   =  $this->Get_hrefFilterRecTypes($baseUrl);
        $this->filterTags       =  $this->Get_hrefFilterTags($baseUrl, 5);
        $this->filterCountries  =  $this->Get_hrefFilterCountry($baseUrl);
        $this->filterFolders    =  $this->Get_hrefFilterFolders($baseUrl);

    }

    //========================================================[ record Data ]===
    function record_GetDataRelated()
    {
        // filtres onwhitch the related articles are based
        $filtres = array(
            'category' => '',
            'tag'      => $this->tags[0],
            'country'  => $this->country,
            'format'   => $this->format
        );

        // get SQL parts
        $sql = $this->Get_queryRecords();
        $fullQuery = $sql->parts['query']
                     . (!$sql->parts['where'] ? ' WHERE ' :
                         $sql->parts['where'] . ' AND ' )
                     . " blogRecords_view.idRecord != {$this->idRecord} AND ";

        // select by filters
        $queries = array();
        foreach($filtres AS $filterName => $filterValue) {
            $query = "(".$fullQuery
                        .$this->{'Get_'.$filterName.'Filter'}($filterValue)
                                    ."ORDER BY RAND( ) LIMIT 0,1 )";
            array_push($queries, $query);
            //echo $query."<br><br>";
        }
        $query = implode(' UNION ', $queries );
        //echo $query;

        // return related records
        return $this->C->Db_Get_procRows($this, '_hookRow_recordRelated', $query);



    }
    function record_setData()
    {
        $sql = $this->Get_queryRecord();
        $this->record         = $this->C->Db_Set_procModProps($this, '_hookRow_record', $sql->fullQuery);
        $this->recordsRelated = $this->record_GetDataRelated();
        //var_dump($this);
        //var_dump($this->recordsRelated);


        //echo "<b>blog_handlers - record_setData()</b><br>";
        //echo "<b>query = </b><br>". $sql->fullQuery;
        //var_dump($this->record);
    }

    //=======================================================[ profile Data ]===
    function profile_getData($idTree, $uid)
    {
        $this->tmpIdTree = $idTree;
        $this->tmpTree    = $this->C->Get_tree($this->tmplIdTree);
        $sql   = $this->Get_queryRecords(array('category' => $this->tmpIdTree, 'uid'=>$uid));
        $query = $sql->fullQuery.' ORDER BY entryDate DESC';

        return $this->C->Db_Get_procRows($this, '_hookRow_profile', $query);

    }
    function profile_setData($uid)
    {
        // archive records writen by author
        $this->recordsArchive = $this->profile_getData('88', $uid );

        // blog records writen by author
        $this->recordsBlog = $this->profile_getData('86', $uid);
    }

    //=============================================[ reuquest controller ]======
    function _handle_requests()
    {
         // setarea metodei to handle the request
         // setarea template_file

         // determinam categoria care trebuie listata
         if (!isset($this->methodHandles[$this->idTree])) {
         // if (!isset($this->tree[$this->idTree]->modOpt->handler)) {
             // $this->template_file = 'blogRecords';
             error_log("[ ivy ] blog_handlers - _handle_requests :"
                     . " Atentie nu a fost setat nici un method handler pentru "
                     . " idTree = {$this->idTree}"
                     );
        } else {
             $this->methodHandle = $this->methodHandles[$this->idTree];
           //  $this->methodHandle  = $this->tree[$this->idTree]->modOpt->handler;
             $this->assocTmplFile = $this->methodHandle;

             error_log("[ ivy ] blog_handlers - _handle_requests :"
                     . " A fost setat method handler = {$this->methodHandle} "
                     . " pentru idTree = {$this->idTree}"
                      );
        }

        // daca trebuie afisat un articol
        if (isset($_GET['idRec'])) {
            $this->methodHandle = 'record';
            $this->assocTmplFile .='Record';
        }


        //set template_file
        if (!isset($this->tmplFiles[$this->assocTmplFile])) {
            error_log("[ ivy ] blog_handlers - _handle_requests :  "
                       . "Nu exista template_file asociat cu {$this->assocTmplFile}"
            );
        } else {
            $this->template_file = $this->tmplFiles[$this->assocTmplFile];
        }


        //call method handler
        if (!method_exists($this, $this->methodHandle.'_setData')) {
            error_log("[ ivy ] blog_handlers - _handle_requests :  "
                       . "Nu exista metoda asociata pentru prefixul {$this->methodHandle}"
            );
        } else {
            $this->{$this->methodHandle.'_setData'}();
        }
    }

}
