<?php
class Ablog_requestHandler extends Cblog
{

   // not sure where it should be used
   function _hookRow_recordsPrior($row)
   {
       $row = parent::_hookRow_recordsPrior($row);
       /**
        * Daca este un user logat
        *      - daca are permisiuni de master poate edita
        *      - daca nu are permisiuni
        *              - si este autorul recordului  - poate edita
        *
        */
       $row['EDrecord']    = $this->C->Get_recordED($row['uidRec'], $row['uids']);

       return $row;
   }
   //===============================================[ request handlers ]=======

    function unpublished_getData($sql, $handler, $hookName)
    {
        $wheres = $sql->parts['wheres'];
        $wheres['publish'] = $this->filters->Get_unpublishedFilter();
        $where =  count($wheres) == 0 ? ''
                  : ' WHERE '.implode(' AND ', $wheres);

        $fullQuery  = $sql->parts['query'].$where;

        error_log("[ ivy ] ACblog - archive_setData : "
                  .preg_replace('/\s+/', ' ', $fullQuery)
        );

        $query = $fullQuery.' ORDER BY entryDate DESC';
        return $this->C->Db_Get_procRows($handler, $hookName, $query);
    }

    //dep???
    #===============================================[ asincronCalls - methods ]====================================================
    /**
     * RET: valid_records = array( ['idRecord', 'title', 'validPriority','endDate'] );
     *
     *      - title - titlul recordului
     *      - validPriority - daca > 0 inseamna ca este o prioritate expirata
     *
     * - daca prioritatea este expirata va fi deletata din TB - blogRecords_prior
     * - daca este valida va fii adaugata la valid_records
     * @return array
     */
    function get_validRecords()
    {


        $valid_records = array();
       // $DB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

        $query_prior  = "SELECT blogRecords.idRecord, title,  DATEDIFF(NOW(), endDate ) AS validPriority, endDate
                            FROM blogRecords_prior, blogRecords
                            WHERE blogRecords.idRecord = blogRecords_prior.idRecord
                            ORDER BY priorityLevel asc";

        $res = $this->DB->query($query_prior);

        if($res->num_rows > 0)
            while($row = $res->fetch_assoc())
            {
                if($row['validPriority'] > 0){
                    $this->DB->query("DELETE from blogRecords_prior WHERE idRecord = {$row['idRecord']} ");
                }
                else{
                    array_push($valid_records, $row);
                }
            }

        return $valid_records;
    }
    /**
     * RET: popup display
     *
     * @param $records - recorduri valide (neexpirate)
     * @return string  - returneaza displayul popUpului
     */
    function get_displayPopup_recordPrior($records, $priorSettings)
    {

        /**
         * Lucrurile astea ar trebui sa fie facute prin template ,
         * aici nu isi are locul html suff*/

        $priority_levels = '';

        foreach($priorSettings->priority_levels AS $level => $level_nrRecords)
            $priority_levels .="<span class='priority_level muted'>
                                    <small class='level'> Level {$level}</small>
                                    <small class='level_nrRecords'> =  {$level_nrRecords} record(s)</small>
                                     |
                                </span>";
        #==============================================================================================

        $list_prior = '';
        foreach ($records as $record) {
            $list_prior .= "<li  class='ui-state-default' id='recordPriority_{$record['idRecord']}' >
                                 <span>{$record['title']}</span>
                                 <span class='prior_ctrls'>
                                     <input type='text' name='endDate' id='endDate_{$record['idRecord']}' value='{$record['endDate']}' class='input-small'>
                                     <button name='deletePriority_{$record['idRecord']}' class='btn btn-mini'>
                                         <i class='icon-minus-sign'></i>
                                     </button>
                                 </span>
                            </li>";
        }

        #==============================================================================================

        return "
                  <p id='popup-message' class='text-success b'></p>


                 <ul id='sortable-priorities'>
                    $list_prior
                </ul>

                 <br>
                 <p class='m0'>
                    <b>Available no. of home priorities</b>
                    <span  id='totalPriorities'>{$priorSettings->totalPriorities}</span>
                 </p>
                 {$priority_levels}


                <p>
                 <button name='savePriorities'  class='btn btn-mini btn-primary t10'>
                    save
                 </button>
                </p>";



    }
    function get_recordPrior()
    {

        $records = $this->get_validRecords();
        $priorSettings = TgenTools::readYml(INC_PATH.'etc/MODELS/blog/blog_HomePriorities.yml');

            $this->get_displayPopup_recordPrior($records,$priorSettings);
    }
    function save_recordPrior()
    {
        $query_delete = "DELETE from blogRecords_prior";
        $this->DB->query($query_delete);

        $test ='';
        # $test .=$query_delete."<br>";

        # var_dump($_POST['priorities']);

        foreach($_POST['priorities'] AS $priority){

            $idRecord      = $priority['idRecord'];
            $endDate       = $priority['endDate'];
            $priorityLevel = $priority['priorityLevel'];

            $query_insert  = "INSERT into blogRecords_prior
                                    (idRecord, endDate, priorityLevel)
                                    VALUES ('$idRecord','$endDate','$priorityLevel')";
            $this->DB->query($query_insert);
           # $test .=$query_insert."<br>";

        }

        $test."Prioritatile au fost salvate!!";
    }
    function controlREQ_async()
    {

        if($_POST['asyncReq_action'] == 'savePriorities') $this->save_recordPrior();
        elseif($_POST['asyncReq_action'] == 'get_recordPrior') $this->get_recordPrior();
    }


}
