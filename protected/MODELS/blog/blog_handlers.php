<?php
class blog_handlers extends Cblog_vars
{
    //===============================================[ data filters ]===========
    // processors
    #=======================================================[PROCESS - ENTRY's]=


    function SET_content(&$row, $lenght = 100)      {

         if(!$row['content']){
             $string =    substr(strip_tags($row['content']),0,$lenght);
             return substr($string, 0, strrpos( $string, ' ') );
         }
         else
             return $row['content'];
    }
    function SET_content_noPic(&$row, $lenght = 100){

        if(preg_match_all("/<img\b[^>]+?src\s*=\s*[\'\"]?([^\s\'\"?\#>]+).*\/>/", $row['content'], $matches))
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
    function SET_leadSec(&$row, $lenght = 70)       {

         if(!$row['leadSec']){

             $string = $row['lead']
                       ? substr($row['lead'],0,$lenght)
                       : substr($row['content'],0,$lenght);
             return substr($string, 0, strrpos( $string, ' ') );
         } else {
             return $row['leadSec'];
         }
    }
    function Set_recordPics(&$row)
    {
       // preg_match_all("/<img\b[^>]+?src\s*=\s*[\'\"]?([^\s\'\"?\#>]+).*\/>/", $row['content'], $matches);
        //preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $row['content'], $matches);
        preg_match_all('/(?<=\<img).+src=[\'"]([^\'"]+)/i', $row['content'], $matches);
        //echo "<b>SET_record_mainPic : matches </b>";
        //var_dump($matches);
        return $matches;

    }
    function SET_record_mainPic(&$row)              {
         #====================================[ main Pic ]===========================================================================
        /*$row['record_mainPic_src'] = preg_match_all("/<img\b[^>]+?src\s*=\s*[\'\"]?([^\s\'\"?\#>]+).*\/>/", $row['content'], $matches)
                                 ? $matches[1][0]
                                 : "";*/
        $matches = $this->Set_recordPics($row);

        if ($matches) {
            return  $matches[1][0];
        }

        # echo $row['title']."<br>".var_dump($matches)."<br>";
    }
    function SET_record_href(&$row)                 {

       $currentLayout = $this->C->mgrName;

       $idTree = !$this->tmpIdTree ? $this->idTree : $this->tmpIdTree;
       return "index.php?idT={$idTree}".
                 "&idC={$row['idCat']}".
                 "&idRec={$row['idRecord']}".
                 "&type={$currentLayout}".
                  ($row['modelBlog_name'] && $row['modelBlog_name']!='blog' ? "&recType={$row['modelBlog_name']}" : '');

    }
    function Set_recordHrefHome($row)               {
        return  "index.php?idT={$this->tmpIdTree}".
                  "&idC={$row['idCat']}".
                  "&idRec={$row['idRecord']}".
                   ($row['modelBlog_name'] && $row['modelBlog_name']!='blog' ? "&recType={$row['modelBlog_name']}" : '');
    }

    function SET_Rating(&$row)                      {

        return  isset($row['ratingTotal']) ?  $row['ratingTotal'] / $row['nrRates'] : '0';

    }
    function SET_total_nrComments(&$row)            {

        # daca vizibilitatea commenturilor este disable
        if($row['commentsView'])
        {
            $query_total_nrComments = "SELECT idComm  from blogComments WHERE idExt = '{$row['idRecord']}'  AND approved='1' ";
            return
                    $this->DB->query($query_total_nrComments)->num_rows;
        }
        else
            return 0;

    }

    function Get_tagsArray($tagsName)               {
        $tags = str_getcsv($tagsName, ',');
        foreach($tags AS $key=>$tag) {
            $tags[$key] = trim($tag);
        }

        return $tags;

    }
    // ne vom referi la acestea in template ca $o->ED si $o->lang
    /**
     * function SET_ED_LG(&$row)                       {

         $row['ED'] = $this->ED;
         $row['LG'] = $this->LG;
    }*/



     //=====================================[Control - PROCESS - ENTRY's]========

    function ProcessRecords_archive($row)       {
        /**
         * RET DATA from DB - to process
         *
         * [ TB blogRecords_view]
           idRecord,
           idCat,uidRec,entryDate,publishDate,nrRates,ratingTotal,
           title,content,lead,
         *
         * [ TB blogRecords_settings ]
           modelBlog_name,modelComm_name,commentsView,commentsStat,commentsApprov,SEO,

         * [ TB blogMap_recordsTags ]
           uid_Rec, fullName,

         * [ TB blogMap_recordsTags]
           tagsName
        */

        #atentie aceasta metoda va trebui suprascrisa de un modelBlog
        #care sa manipuleze datele altfel in caz necesar

        $row['leadSec']          = $this->SET_leadSec($row, 80);
        $row['record_mainPic']   = $this->SET_record_mainPic($row);
        $row['record_href']      = $this->SET_record_href($row);
        $row['ReadMore_link']    = "<a href='{$row['record_href']}'> Read More</a>";
        # $row['Rating']           = $this->SET_Rating($row);         #s-ar putea sa nici nu mai am nevoie de asta
       // $row['total_nrComments'] = $this->SET_total_nrComments($row);


        /**
         * Daca este un user logat
         *      - daca are permisiuni de master poate edita
         *      - daca nu are permisiuni
         *              - si este autorul recordului  - poate edita
         *
         */
        $row['EDrecord']        = $this->C->user->get_EDrecord($this,$row['uidRec']);

        /**
         * ATENTIE
         * if( $row['EDrecord'] == '') $row['total_nrComments_anAppr'] = si aici ca mai sus;
         */
       //$this->SET_ED_LG($row);

        //=======================================================================

       // $this->C->rating->setINI('blog','idRecord',$row['idRecord'],
        //'SET_record'.$row['idRecord']);

        //=======================================================================
        $row['catResFile'] = $this->tree[$row['idCat']]->resFile;

        #var_dump($row);

        return $row;
    }
    function ProcessRecords_blog($row)          {
        /**
         * RET DATA from DB - to process
         *
         * [ TB blogRecords_view]
           idRecord,
           idCat,uidRec,entryDate,publishDate,nrRates,ratingTotal,
           title,content,lead,
         *
         * [ TB blogRecords_settings ]
           modelBlog_name,modelComm_name,commentsView,commentsStat,commentsApprov,SEO,

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
         * + modelBlog_name
         * + modelComm_name
         * + commentsView
         * + commentsApprov
         * + SEO
         * + uid_Rec
         * + fullName
         * + tagsName
         */
        $row['record_href'] = $this->SET_record_href($row);
        $row['tags']        = $this->Get_tagsArray($row['tagsName']);

        /**
         * Daca este un user logat
         *      - daca are permisiuni de master poate edita
         *      - daca nu are permisiuni
         *              - si este autorul recordului  - poate edita
         *
         */
        $row['EDrecord']        = $this->C->user->get_EDrecord($this,$row['uidRec']);

        //=======================================================================
        $row['catResFile'] = $this->tree[$row['idCat']]->resFile;

        #var_dump($row);

        return $row;
    }
    function ProcessRecords_blogHome($row)       {

        $row['record_href'] = $this->SET_recordHrefHome($row);
        #var_dump($row);
        return $row;
    }


    function AProcessRecord(&$row)      {

        if(!$row['commentsView'])   {$row['commView_true'] = '';   $row['commView_false'] = 'checked'; }
        if(!$row['commentsStat'])   {$row['commStat_true'] = '';   $row['commStat_false'] = 'checked'; }
        if(!$row['commentsApprov']) {$row['commApprov_true'] = ''; $row['commApprov_false'] = 'checked'; }

    }
    function ProcessRecord($row)        {

       // $row['Rating']           = $this->SET_Rating($row);
       // $row['total_nrComments'] = $this->SET_total_nrComments($row);


       //$this->SET_ED_LG($row);
       // avand in vedere ca AProcessRecord deals with Comments it is no necesary here
       // chiar daca partea de comenturi e ok ca idee probabil trebuie rescrisa oricum
       if ($this->admin) {
          $this->AProcessRecord($row);
       }

        #=======================[Rating set & thumbs set]===============================================================
        $byUid = (isset($this->uid) ? $this->uid : false );

        //function setINI($ratingType, $extKey_name, $extKey_value, $setName='', $getbyUid= false, $onlyUser=false) {
       /* $this->C->rating->setINI('blog',
                                 'idRecord',
                                 $row['idRecord'],
                                'SET_recordRating',
                                 $byUid );*/
        #$this->C->rating->template_file = 'ratingRecord_bigStar';
        #===============================================================================================================


        return $row;

     }

    function ProcessRecords_prior($row) {


        $row['lead']             = $this->SET_lead($row);
        $row['record_mainPic']   = $this->SET_record_mainPic($row);
        $row['content']          = $this->SET_content_noPic($row,1050);
        $row['record_href']      = $this->SET_record_href($row);
        $row['ReadMore_link']    = "<a href='{$row['record_href']}'> Read More</a>";
      #  $row['Ratidng']           = $this->SET_Rating($row);
        $row['total_nrComments'] = $this->SET_total_nrComments($row);


        /**
         * Daca este un user logat
         *      - daca are permisiuni de master poate edita
         *      - daca nu are permisiuni
         *              - si este autorul recordului  - poate edita
         *
         */
        $row['EDrecord']        = $this->C->user->get_EDrecord($this,$row['uidRec']);

        /**
         * ATENTIE
         * adica sa arate si commenturile neaprobate pentru autorul recordului
         * if( $row['EDrecord'] == '') $row['total_nrComments_anAppr'] = si aici ca mai sus;
         */
       $this->SET_ED_LG($row);

        $this->C->rating->setINI('blog','idRecord',$row['idRecord'], 'SET_record'.$row['idRecord']);

        return $row;

    }
    function ProcessPriorities($row)    {

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

    function home_setDataBlogLatest()
    {
         // echo "home_setData()";
        $this->tmpIdTree = 86;
        $this->tmpTree    = $this->C->Get_tree($this->tmplIdTree);

        $queryBase = "SELECT
                        idRecord,idCat,uidRec,entryDate,
                        publishDate,title, modelBlog_name
                    FROM blogRecords_view ";

        foreach ($this->tmpTree[$this->tmpIdTree]->children AS $idCat) {

            $sql   = $this->Get_queryRecords(array('category' => $idCat), $queryBase);
            $query = $sql->fullQuery . ' ORDER BY publishDate DESC LIMIT 2';

            $this->homeBlogRecords[$idCat]['records']
                = $this->C->Db_Get_procRows($this, 'ProcessRecords_blogHome', $query);

            $this->homeBlogRecords[$idCat]['catName']
                = $this->tmpTree[$idCat]->name;
        }

       // var_dump($this->homeBlogRecords);
       // clean tmpData;
        unset($this->tmpIdTree);
        unset($this->tmpTree);

    }
    function home_setDataArchiveLatest()
    {
       $this->tmpIdTree = 88;
       $this->tmpTree    = $this->C->Get_tree($this->tmplIdTree);

       $sql   = $this->Get_queryRecords(array('category' => $this->tmpIdTree));
       $query = $sql->fullQuery.' ORDER BY entryDate DESC LIMIT 8';
       $this->records = $this->C->Db_Get_procRows($this, 'ProcessRecords_archive', $query);

    }
    function home_setData()
    {
        //====================================[get latest in blog categories]===
        $this->home_setDataBlogLatest();
        //====================================[get latest in archive categories]=
        $this->home_setDataArchiveLatest();

        //====================================[ archive filters ]===============
        $idArchive = $this->blogSections["archive"];
        $this->Set_filterRecTypes("?idT={$idArchive}&idC={$idArchive}");

    }
    function blog_setData()
    {
        $sql   = $this->Get_queryRecords(array('category' => ''));
        $query = $sql->fullQuery . ' ORDER BY entryDate DESC';
        $this->records = $this->C->Db_Get_procRows($this, 'ProcessRecords_blog', $query);
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
         * + modelBlog_name
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
        $this->records = $this->C->Db_Get_procRows($this, 'ProcessRecords_archive', $query);
        //echo "blog_handlers - archive_setData : this->records";
        //var_dump($this->records);

        //====================================[ archive filters ]===============
        $idArchive = $this->blogSections["archive"];
        $this->Set_filterRecTypes("?idT={$idArchive}&idC={$idArchive}");

    }
    function record_setData()
    {
        $sql = $this->Get_queryRecord();
        $this->record = $this->C->Db_Set_procModProps($this, 'ProcessRecord', $sql->fullQuery);

        if ($this->admin) {
            $this->setRecord_Permissions();
        }
        //echo "<b>blog_handlers - record_setData()</b><br>";
        //var_dump($this->record);
    }

    function _handle_requests()
    {
        /* isset($_GET['idRec'])
         ? $this->GET_Record()
         :  $this->GET_Records();*/

         // setarea metodei to handle the request
        // setarea template_file

         if (!isset($_GET['idRec'])) {
             if (!isset($this->methodHandles[$this->idTree])) {
            // if (!isset($this->tree[$this->idTree]->modOpt->handler)) {
                 // $this->template_file = 'blogRecords';
                 error_log("[ ivy ] blog_handlers - _handle_requests :"
                           . " Atentie nu a fost setat nici un method handler pentru "
                           ." idTree = {$this->idTree}"
                         );
             } else {
                 $this->methodHandle = $this->methodHandles[$this->idTree];
               //  $this->methodHandle  = $this->tree[$this->idTree]->modOpt->handler;
                 $this->assocTmplFile = $this->methodHandle;

                 error_log("[ ivy ] blog_handlers - _handle_requests :"
                           . " A fost setat method handler = {$this->methodHandle} "
                           ." pentru idTree = {$this->idTree}"
                          );
             }

         } else {
             $this->methodHandle  = 'record';
             $this->assocTmplFile = !isset($_GET['recType'])
                                    ? 'record'
                                    : $_GET['recType'];


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