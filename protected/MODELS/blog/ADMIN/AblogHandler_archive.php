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

class AblogHandler_archive extends blogHandler_archive
{
    var $recordsUnpublished;

    function _hookRow_archive($row)
    {
        $row = parent::_hookRow_archive($row);
        $row['EDrecord']      = $this->rowDb->Get_recordED($row['uidRec'], $row['uids']);
        $row['statusPublish'] = $this->rowDb->Get_publishedStatus($row['publishDate']);

        return $row;

    }

    function archive_setData()
    {
        parent::archive_setData();
        // unpublished records
        // set by the parent  $this->sqlRecords
        $this->recordsUnpublished =  $this->blog->unpublished_getData(
            $this->sqlRecords, $this,  '_hookRow_archive');
    }
}