<?php

class Cblog extends Cblog_vars{





    #=======================================[PROCESS - ENTRY's]=========================================================


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
    function SET_lead(&$row, $lenght = 70)          {

         if(!$row['lead']){
             $string =    substr($row['content'],0,$lenght);
             return substr($string, 0, strrpos( $string, ' ') );
         }
         else
             return $row['lead'];
    }
    function SET_record_mainPic(&$row)              {
         #====================================[ main Pic ]===========================================================================
        /*$row['record_mainPic_src'] = preg_match_all("/<img\b[^>]+?src\s*=\s*[\'\"]?([^\s\'\"?\#>]+).*\/>/", $row['content'], $matches)
                                 ? $matches[1][0]
                                 : "";*/
        if(preg_match_all("/<img\b[^>]+?src\s*=\s*[\'\"]?([^\s\'\"?\#>]+).*\/>/", $row['content'], $matches))
            return  $matches[0][0];

        # echo $row['title']."<br>".var_dump($matches)."<br>";
    }
    function SET_record_href(&$row)                 {

       $currentLayout = $this->C->mgrName;

       return "index.php?idT={$this->idTree}".
                 "&idC={$row['idCat']}".
                 "&idRec={$row['idRecord']}".
                 "&type={$currentLayout}".
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

    function SET_ED_LG(&$row)                       {

         $row['ED'] = $this->ED;
         $row['LG'] = $this->LG;
    }

    /**
    * #=======================================================[Control - PROCESS - ENTRY's]=======================================
   */


    function ProcessRecords($row)       {
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

        $row['lead']             = $this->SET_lead($row);
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
       $this->SET_ED_LG($row);

        #===============================================================================================================

       // $this->C->rating->setINI('blog','idRecord',$row['idRecord'], 'SET_record'.$row['idRecord']);

        #===============================================================================================================
        #var_dump($row);

        return $row;
    }

    function AProcessRecord(&$row)      {

        if(!$row['commentsView'])   {$row['commView_true'] = '';   $row['commView_false'] = 'checked'; }
        if(!$row['commentsStat'])   {$row['commStat_true'] = '';   $row['commStat_false'] = 'checked'; }
        if(!$row['commentsApprov']) {$row['commApprov_true'] = ''; $row['commApprov_false'] = 'checked'; }

    }
    function ProcessRecord($row)        {

        # $row['Rating']           = $this->SET_Rating($row);
         $row['total_nrComments'] = $this->SET_total_nrComments($row);


         $this->SET_ED_LG($row);

         if($this->admin)
            $this->AProcessRecord($row);




        #=======================[Rating set & thumbs set]===============================================================
        $byUid = (isset($this->uid) ? $this->uid : false );

        //function setINI($ratingType, $extKey_name, $extKey_value, $setName='', $getbyUid= false, $onlyUser=false) {
        $this->C->rating->setINI('blog',
                                 'idRecord',
                                 $row['idRecord'],
                                'SET_recordRating',
                                 $byUid );
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


    /**
     * #=======================================================[ Home - priorities ]=======================================
    */

    /**
     * RET : array  $this->{'priorLevel_'.$Level}[]
     * @param $priorities - vector cu recordurile prioritizate
     */
    function SET_prioritiesLevels($priorities)              {

        /**
         * priorities       = array cu id-urile recordurilor prioritizate
         * priorities_level = spune cate recorduri sunt pe fiecare level
         * nrPrior_needed   = totalul de recorduri prioritizate
         *
         * (1) - metoda va seta $this->[level1, level2...etc][0,1] = cu recordurile prioritizate
         * aceste array-ul vor fii utilizate in templateul pentru home = blogRecord_home
         */


        $countPriority = 1;
        $countLevel = 1;

        // i am making a set for masterBlog
        $this->homeSET = new stdClass();
        $this->homeSET->priorLevel_1 = array();                    # array-uri cu recorduri prioritare
        $this->homeSET->priorLevel_2 = array();

        foreach($priorities AS $priority)
        {
           if($this->priority_levels[$countLevel] < $countPriority)
           {
               $countLevel ++;
               $countPriority = 1;
           }

           array_push($this->{'priorLevel_'.$countLevel} , $priority); # 1 # $this->priorLevel_1[0]
           array_push($this->homeSET->{'priorLevel_'.$countLevel} , $priority); # 1 # $this->priorLevel_1[0]
           $countPriority++;

        }
        # echo 'priorLevel_1'."<br>";
        # var_dump($this->priorLevel_1);
        # echo 'priorLevel_2'."<br>";
        # var_dump($this->priorLevel_2);

    }

    /**
    * RET : str $query_priorRecords
    */
    function SETctrl_queryPriorities(&$query_origin, &$where_origin){

        /*
         * daca suntem pe prima pagina => home
         *  - trebuie schimbat templateul pe blogRecord_home
         *  -(1)- interogat tabelul de prioritati
         *           - (2)-RET: priorRecords = array(idRec1, idRec2, etc...);
         *                               - doar id-uri cu prioritati valide
         *           - exista recorduri prioritare + ultimele recorduri adaugate = nrPrior_needed
         *           - nu exista - ultimele recorduri adaugate = nrPrior_needed
         *
         *
         * */

        $query_prior  = "SELECT idRecord,  DATEDIFF(NOW(), endDate ) AS validPriority
                           FROM blogRecords_prior
                           ORDER BY priorityLevel asc";
        # 2
        $priorRecords = $this->C->GET_objProperties($this, $query_prior, "ProcessPriorities");

        #var_dump($priorRecords); echo "<br>";

        $nrPrior_set        =  count($priorRecords);
        $nrPrior_needed     =  $this->totalPriorities;
        $query_priorRecords =  array();




    #==============================================================================================================
    /**
     * Daca sunt setate recorduri prioritare
     *     - (1) - alteram query-ul principal pentru a nu lua si recordurile prioritizate
     *     - (2) - dupa queryul pricipal alegem doar recordurile prioritizate
     *     - (3) -  $nrPrior_left  - numarul de prioritati ramase necompletate
     *                             - se vor lua din ultime recorduri uploadate mai putin cele deja prioritizate
     *     - (4) - alteram LimitStartul queryului prioritar pentru a incepe dupa recordurile prioritizate

     *
     */
     if($nrPrior_set)
     {
          #echo "Avem ".$nrPrior_set." prioritati setate <br>";
          # 3
          $nrPrior_left      =  $nrPrior_needed - $nrPrior_set ;
          $priorRecords_str  =  implode(' ,',$priorRecords);

          #_______________________________________________________________________________________________________
          # 2
          $query_priorRecords[0] =  $query_origin.
                                      " JOIN  blogRecords_prior
                                         ON (blogRecords_view.idRecord = blogRecords_prior.idRecord)
                                      ".
                                      $this->C->ADD_toStr_queryWhere(
                                          $where_origin,
                                        " blogRecords_view.idRecord IN ({$priorRecords_str})
                                          ORDER BY priorityLevel asc "
                                      );
          if($nrPrior_left > 0)
          {
              $query_priorRecords[1] =  $query_origin.
                      $this->C->ADD_toStr_queryWhere(
                                          $where_origin,
                                        " blogRecords_view.idRecord NOT IN ({$priorRecords_str})
                                          ORDER BY entryDate DESC LIMIT 0,{$nrPrior_left} "
                                      );


              # 4
              $this->LimitStart +=$nrPrior_left;


          }

          # 1
          $where_origin = $this->C->ADD_toStr_queryWhere
                         (
                            $where_origin,
                            "  blogRecords_view.idRecord NOT IN ({$priorRecords_str})"
                         );



      # echo "<b>Query pentru prioritati </b>".var_dump($query_priorRecords)."<br><br>";
      }



     #==============================================================================================================
     /**
       *  Daca nu avem prioritati setate
       *      -  se aleg primele recorduri
       *      -  alteram LimitStartul queryului prioritar pentru a incepe dupa recordurile prioritizate
       */
     else{
         $query_priorRecords[0] = $query_origin."  ORDER BY entryDate DESC LIMIT 0,{$nrPrior_needed}";
         $this->LimitStart +=$nrPrior_needed;
     }

     #==============================================================================================================

        return $query_priorRecords;

    }

    function GET_priorities(&$query_origin, &$where)         {

        /**
         * LOGISTICS
         *
        * daca suntem pe prima pagina => home
        *  - trebuie schimbat templateul pe blogRecord_home
        *
        *  SETctrl_queryPriorities(&$query_origin, $where)
        *  - interogat tabelul de prioritati
        *      - exista recorduri prioritare + ultimele recorduri adaugate = nrPrior_needed
        *      - nu exista - ultimele recorduri adaugate = nrPrior_needed
         *
        *  SET_prioritiesLevels($priorities)
        *  - recorudrile prioritare vor fii puse pe array-uri de level
        *      - 2 nivele de prioritati 1 si 2[2 records]
        *         - priorLevel_1[0] - cu datele recordului
        *         - priorLevel_2[0,1]
        *
        *
        * */

        $this->C->GETconf($this,INC_PATH.'etc/MODELS/blog/blog_HomePriorities.yml' );
        #echo 'totalPriorities '.$this->totalPriorities."<br>";
        #echo 'priority_levels '.var_dump($this->priority_levels)."<br>";

        #=======================================================================================================
        // este chemat din master blog via renderDISPLAY_byTmplFile or something
       // $this->template_file = 'blogRecord_home';
        $this->template_vars = array_merge($this->template_vars, $this->template_homePrior_vars);
        #=======================================================================================================

        $query_priorRecords = $this->SETctrl_queryPriorities($query_origin, $where);

        $priorities1  = $this->C->GET_objProperties($this, $query_priorRecords[0], 'ProcessRecords_prior');

        $priorities2  = isset($query_priorRecords[1])
                        ? $this->C->GET_objProperties($this, $query_priorRecords[1], 'ProcessRecords_prior')
                        : array();

        $priorities   = array_merge($priorities1, $priorities2);
        # echo "Priorities <br>";
        # var_dump($priorities);
        #=====================================================================================================

        $this->SET_prioritiesLevels($priorities);


    }


    /**
      #======================================================[ Records + homeRecords]=====================================
     */

    # GET_Records <- * -> GET_priorities()
    function CHECK_homeStatus(&$query, &$where) {
        /*
         * (1)
         *  daca suntem pe prima pagina (Home)
         *      GET_priorities($query, $where)    - pointeri = $query & $where deoarece e posibil ca metoda sa produca alterari
         * */

        if($this->idTree==$this->idNode && (!isset($_GET['Pn']) || $_GET['Pn']==1))
                    $this->GET_priorities($query, $where);
    }
    # CALLED by SET_queryRecords
    function SETctrl_Records_queryWheres()     {

        if(!$this->editRecords_Permss )
        {
            if(!$this->admin)
                    $this->C->ADD_queryWheres($this,"publishDate is not NULL");
            else    $this->C->ADD_queryWheres($this,"(uidRec='{$this->uid}' OR publishDate is not NULL)");
        }


         #inseamna ca suntem pe o categorie descendenta din blog
         if($this->idTree!=$this->idNode)
             $this->C->ADD_queryWheres($this," idCat = '{$this->idNode}' ");


         # echo "<b>Query wheres</b> ".$this->C->SET_queryWheres($this)."<br>";
         return  $this->C->SET_queryWheres($this);


    }


    function _handle_queryRescordsFiltres()
    {

    }

    # GET_Records <- * -> SETctrl_Records_queryWheres()
    function SET_queryRecords()
    {

        $query = "SELECT
                    blogRecords_view.idRecord,
                    idCat,uidRec,entryDate,publishDate,nrRates,ratingTotal,
                    title,content,lead,
                    modelBlog_name,modelComm_name,commentsView,commentsStat,commentsApprov,SEO,

                    uid_Rec, fullName,

                    tagsName

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

                          ";


     return $query;

    }

    function Get_RecordsData()
    {
        /**
         *  inseamna ca avem de afisat un carnat de recorduri in functie de nrRecors (configurabil)
         *  deasemenea ne intereseaza ca pentru userul normal unpulished records should not be displayed (doar pentru admin sau ?)
         *  pentru compunerea queryului avem nevoie de  queryWhere, Pn, PnEnd
         *  ORDER BY s-ar putea sa ajunga si el sa fie variabil
         */
        /**
         * LOGISTICS
         *  (1)
         *  SET_queryRecords($query, $where)  - seteaza main query si where prin pointeri
         *
         *  (2)
         *   - daca suntem pe pagina de home se vor seta prioritatile
         *
         *  (3)
         *  setare ordonare si limite - LimitStart  si Pn vor fii definite de
         *                              GET_pagination($queryPagination, $this->nrRecords, $GET_args, $this->idNode,$this);
         *
         *  (4)
         *  - date rezultate in urma queryului vor fii puse in acest array mutidimesional
         *
        */
        # var_dump($this->blogModels);

        # 1
        $query = $this->SET_queryRecords();

        #==============================================================================================================
        $where =  $this->SETctrl_Records_queryWheres();
        $where .= " ORDER BY entryDate DESC";

        # 2
        //$this->CHECK_homeStatus($query, $where);
        # 3
        //$this->setPagination($query.$where);
        //$query .=  $where."LIMIT {$this->LimitStart},{$this->nrRecords}" ;

        #=======================================================================

        # 4
        $this->records = $this->C->GET_objProperties($this, $query.$where, 'ProcessRecords');

        // echo $query;
        // var_dump($this->records);
    }

    /**
     * Seteaza template_file pentru un listing de recorduri
     * in functie de idTree ( daca nu atentzioneaza ca nu exista )
     * sau ar trebui setat un template_file default pentru listing
     */
    function Set_tmplFileRecords()
    {
        if (!isset($this->listRecords_tmplFile[$this->idTree])) {
            // $this->template_file = 'blogRecords';
            error_log("[ ivy ] Cblog - Set_tmplFileRecords :"
                      . " Atentie nu a fost setat nici un template_file pentru "
                      ." idTree = {$this->idTree}"
            );
        } else {
            $this->template_file = $this->listRecords_tmplFile[$this->idTree];
        }
    }
    function GET_Records()
    {
        $this->Set_tmplFileRecords();
       // $this->Get_RecordsData();
    }


    /**
     #======================================================[ Record + comments + blModel]===============================
      */
    function GET_comments()               {
   #================================[ crearea obiectului de Comments ]=========================================

    if($this->commentsView || $this->pubPermss)
    {

        if(!isset($this->modelComm_name ))
            $this->modelComm_name  = 'comments';


        $core = &$this->C;
        $core->SET_general_mod($this->modelComm_name,'MODELS');
        if(is_object($core->{$this->modelComm_name}))
        {

           # echo "S-a instantiat modelul de Comments <br>";
            $core->{$this->modelComm_name}
                    ->setINI($this,'idRecord', $_GET['idRec'], 'blogComments','idRec');
        }
        #else
           # echo "NU S-a instantiat modelul de Comments <br>";



        #echo 'Modelul Commenturilor este '.$modelComm_name."</br>";


    }
}

    function setJOIN_with_blogModel()     {


           $this->modelBlog_name         = $modelBlog = $_GET['recType'];   #record type sau= modelBlog_name

           #======================== [templating settings ]================================================================
           /**
            * LOGISTICS
            *
            *  modelBlog_vars_ColsStr
            *      - creaza un string cu colonele blogModel
            *      - ATENTIE!!! - not sure for what purpose
            *
            *  template_vars
            *      - concatenra pentru template_vars complet
            *      - modelBlog_vars = tinute separat pentru administrarea mai usoara a datelor in cadrul DB
            *
            *  current_modelBlog
            *      - pentru afisarea continutului de blog - utilizat de masterBlog.html
            *
            *  template_file
            *      - contrangere : templateul pentru record va fii construit dupa blogRecord_[modelBlog_name].html
            */
           $this->modelBlog_vars_ColsStr = implode(",", $this->modelBlog_vars)." ,";

           $this->template_vars          = array_merge($this->template_vars, $this->modelBlog_vars);

           $this->current_modelBlog      = $modelBlog;

           $this->template_file          = 'blogRecord'.'_'.$this->modelBlog_name ;



           #================================= [DB settings]================================================================
           $this->modelBlog_tableRecords  = "blog{$this->modelBlog_name}_records";

           $this->queryJOIN               =  "LEFT outer JOIN {$this->modelBlog_tableRecords} ON (blogRecords_view.idRecord = {$this->modelBlog_tableRecords}.idRecord)";


       }

    function SET_queryRecord()            {

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
                       blogRecords_view.idRecord,
                       idCat,uidRec,entryDate,publishDate,nrRates,ratingTotal,
                       title,content,lead,
                       modelBlog_name,modelComm_name,commentsView,commentsStat,commentsApprov,SEO,
                       {$this->modelBlog_vars_ColsStr}

                       uid_Rec, fullName,

                       tagsName

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
                         )AS TBtagsName
                         ON (blogRecords_view.idRecord = TBtagsName.idRecord)

                                      ";

    $query .=                 $this->queryJOIN
                               .( isset($queryWhere) ? "WHERE $queryWhere ": '')
                                   ." WHERE blogRecords_view.idRecord = '{$_GET['idRec']}'" ;

    # echo "GET_Record  = ".$query."</br>";
    return $query;

    }
    function GET_Record()                 {


        /**
         * opt - GET requests
         *  # idc&odT => typeul de myBlog
            # idRec   =>id-ul recordului
            # recType => tipul recordului
         */
        /**
         * LOGISTICS
         *
         *  (1)
         *      modelBlog_name = $_GET['recType']
         *
         *      setJOIN_with_blogModel() - seteaza variabilele necesare pentru mergeul cu blogModel cerut
         *
         * (2)
         *      SET_queryRecord()        - returneaza queryul necesat pentru recordul cerut
         *
         *      GET_objProperties($this, $query, 'ProcessRecord');
         *                            # procesarea datelor din record este facuta de ProcessRecord
         *                            # daca exista un model atunci ProcessRecords se va referii la metoda acestuia
         * (3)
         *      setRecord_Permissions()  - necesar pentru GET_comments
         *                                 Seteaza
         *                                  - editRecordPermss
         *                                  - pubPermss
         *                                  - ED
         *
         *     GET_comments()           - seteaza obiectul de comments
         */

        #===========================================================================================================
        $this->template_file = 'blogRecord';
        #===========================================================================================================
        # 1
        if(isset($_GET['recType']))   $this->setJOIN_with_blogModel();              #realizaz legaturile cu modeluldeblog;

        # 2
         $query = $this->SET_queryRecord();
         $this->C->GET_objProperties($this, $query, 'ProcessRecord');

        # 3
        if($this->admin)
            $this->setRecord_Permissions();

        //$this->GET_comments();


    }


    /**
     * #======================================================[ MAN meths ]=========== =====================================
    */

    function setPagination($queryPagination)    {


        #atentie ca s-ar putea sa apara probleme cand vom pune diverse filtre
        //deprecated teoretic
        //$GET_args = array('idC'=>$this->idNode,'idT'=>$this->idTree, 'type'=>$this->type);
        $GET_args = array('idC'=>$this->idNode,'idT'=>$this->idTree, 'type'=>$this->type);

        $this->pagination = $this->C->GET_pagination($queryPagination, $this->nrRecords, $GET_args, $this->idNode,$this);

        #LimitStart, LimitEnd si Pn vor fii definite de GET_pagination




    }

    # still pending
    # functie IDIOATA - TREBUIE TREBUIE TREBUIE SA TREC LA YAMPL...KKT DE file.ini!!!!
    # din cauza array-urilor multidimensionale
    function set_blogModels()      {

        foreach($this->blogModels AS $key=>$blogModel)
            $REblogModels[$key]['blogModel_name'] =  $blogModel;

        $this->blogModels = $REblogModels;

    }


    function _init_()              {

       // $this->C->set_general_mod('rating','PLUGINS');


        //ATENTIE !!! this is depreacated and it should be solved
        $this->set_blogModels();
        $this->template_vars = array_merge($this->template_vars,
                                           $this->blogRecords_vars,
                                           $this->blogRecords_settings_vars);

        #===============================================================================================================

         // daca s-a facut un request de record

        isset($_GET['idRec']) ? $this->GET_Record() :  $this->GET_Records();

        #===============================================================================================================
        if (!isset($this->uid)) {
            error_log('[ ivy ] Cblog - _init_ : Nici un user nu a fost setat ');
        }


    }

    function testMethod() {

        $query_prior  = "SELECT blogRecords.idRecord, title,  DATEDIFF(NOW(), endDate ) AS validPriority, endDate
                                FROM blogRecords_prior, blogRecords
                                WHERE blogRecords.idRecord = blogRecords_prior.idRecord
                                ORDER BY priorityLevel asc";
        $testPrior = $this->C->GET_objProperties($this, $query_prior);
        var_dump($testPrior);

        return "sunt in testMethod al obiectului blog <br>";
    }
    function __construct($C){  }


}
