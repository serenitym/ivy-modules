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

class blogHandler_profile
{
   // pointers
    var $blog;
    var $baseQuery;
    var $filters;
    var $rowDb;

    var $recordsArchive;
    var $recordsBlog;

    //=======================================================[ profile Data ]===
    function _hookRow_profile($row)
    {
        $row['record_href']      = $this->rowDb->Get_record_href($row);
        return $row;
    }
    function profile_getData($idTree, $uid)
    {

        $this->blog->tmpIdTree  = $idTree;
        $this->blog->tmpTree    = $this->C->Get_tree($this->blog->tmpIdTree);
        if(count($this->blog->tmpTree) == 0) {

            error_log("[ ivy ] blog_handlers profile_getData:"
                ." nu s-a preluat tmpTree cu id-ul = ".$idTree);
            return '';
        } else {

            /*echo "blog_handlers profile_getData - tmpTree pt id-ul = ".$idTree;
            var_dump($this->tmpTree);*/
            $sql   = $this->baseQuery->Get_queryRecords(array(
                    'category' => $this->blog->tmpIdTree,
                    'uid'=>$uid)
            );
            $query = $sql->fullQuery.' ORDER BY entryDate DESC';

            return $this->C->Db_Get_procRows($this, '_hookRow_profile', $query);
        }

    }
    function profile_setData($uid)
    {
        // archive records writen by author
        $this->recordsArchive = $this->profile_getData('88', $uid );

        // blog records writen by author
        $this->recordsBlog = $this->profile_getData('86', $uid);
    }
}