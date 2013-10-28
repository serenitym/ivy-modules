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

class blogHandler_record extends ivyModule_objProperty
{
   var $record;
   var $recordsRelated;
   var $recordRelated;

   // pointers
    var $blog;
    var $baseQuery;
    var $filters;
    var $rowDb;


    function _hookRow_recordRelated($row)
    {
        $row['record_href'] = $this->rowDb->Get_record_href($row);

        return $row;
    }
    function _hookRow_record($row)
    {
        $row['authorRights'] = $this->rowDb
                                    ->Get_rights_articleEdit($row['uidRec'], $row['uids']);

        // daca articolul nu este publicat si userul nu are permisiuni de autor
        //sau nu este nimeni logat - eroare 404
        if(!$row['publishDate'] && !$row['authorRights']) {
            Toolbox::relocate(PUBLIC_URL);
        }

        $row['tags']              = $this->rowDb->Get_tagsArray($row['tagsName']);
        $row['hrefFolderFilter']  = $this->rowDb->Get_record_hrefFolderFilter($row['idFolder']);
        // pt blogRecord.html
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

        return $row;

     }

    function record_SetDataRelated()
    {
        // filtres onwhitch the related articles are based
        $filtres = array(
            'category' => '',
            'tag'      => $this->tags[0],
            'country'  => $this->country,
            'format'   => $this->format
        );

        // get SQL parts
        $sql = $this->baseQuery->Get_queryRecords();
        $fullQuery = $sql->parts['query']
                     . (!$sql->parts['where'] ? ' WHERE ' :
                         $sql->parts['where'] . ' AND ' );

        $excludeRecords = array($this->idRecord);
        //======================================[ editor's pick related ]======
        if($this->relatedStory) {
            $query = $fullQuery." blogRecords_view.idRecord = {$this->relatedStory}";
            //echo $query;

            $this->recordRelated
                = $this->C->Db_Get_procRow($this, '_hookRow_recordRelated', $query);
           // var_dump($this->recordRelated);

            array_push($excludeRecords, $this->relatedStory);
        }

        //======================================[ select by filters ]===========
        $this->recordsRelated = array();
        foreach($filtres AS $filterName => $filterValue) {

            $filter = $this->filters->{'Get_'.$filterName.'Filter'}($filterValue);
            $strExcludeRecords = implode(', ', $excludeRecords);

            $query = "({$fullQuery}
                        blogRecords_view.idRecord NOT IN ({$strExcludeRecords})
                        AND {$filter} ORDER BY RAND( ) LIMIT 0,1
                      )";

            $recordRelated = $this->C->Db_Get_procRow($this, '_hookRow_recordRelated', $query);
            // daca a gasit ceva pe acest filtru
            if($recordRelated) {
                array_push($excludeRecords, $recordRelated['idRecord']);
                array_push($this->recordsRelated,$recordRelated );
                // echo "<b>filter found  last_id = ".$recordRelated['idRecord']." </b><br>";
                // var_dump($recordRelated);
            }
            //echo "<b>record_SetDataRelated - filter by {$filterName}</b>".$query."<br><br>";
        }

    }
    function record_setData()
    {

        $sql                = $this->baseQuery->Get_queryRecord();
        $this->record       = $this->C->Db_Set_procModProps($this, '_hookRow_record', $sql->fullQuery);
        $this->record_SetDataRelated();
        //var_dump($this);
        //var_dump($this->recordsRelated);


        //echo "<b>blog_handlers - record_setData()</b><br>";
        //echo "<b>query = </b><br>". $sql->fullQuery;
        //var_dump($this->record);
    }
    function _init_()
    {
        $this->record_setData();
    }
}