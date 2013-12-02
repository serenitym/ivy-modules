<?php
/**
 * PHP Version 5.3+
 *
 * @category 
 * @package  blog
 * @author Ioana Cristea <ioana@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link http://serenitymedia.ro
 */

/**
 * Class Cblog
 *
 * @package blog
 * @subpackage
 * @category
 * @copyright Copyright (c) 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link      http://serenitymedia.ro
 * @author
 */
class Cblog extends blog_requestHandler
{
    //objectsProps
    var $rowDb;  // instance of blog_rowDb
    var $filters; // instance of blog_filters
    var $baseQuery; // instance of blog_baseQuery;


    // home vars
    /**
     * @var
     */
    var $tmpTree ;
    var $tmpIdTree ;
    var $filterRecTypes = array();
    var $uid = 0;

    // blog settings
    /**
     * array( array( 'value' => idFolder, 'name' => folderName ) )
     *
     * utilizat pt autocomplete pt liveEdit  ( EDsel )
     * @var array
     */
    var $folders;
    var $jsonFolders;
    /**
     * array( array('idFormat' => '', 'format' => '') )
     * @var array
     */
    var $formats;

     /**
     * Set blog Settings
     *
     * Uses:
     *
     * * dbTable - blogRecord_folder = (*idFolder*, parentFolder, folderName, idTmpl);
     * * dbTable - blogRecord_folder = (*idFormat*, format, idTmpl);
     *
     * @todo * table - blogTags_banned =>  **bannedTags** = [{idTag: '', tagName: ''}]
     * @todo * table - blogRecord_tmplFiles => **tmplFiles** = array(array( idTmpl=> '', tmplFile=> ''),...)
     *
     * @todo * table - blogRecord_types =>  **types**     = [ {idType: '', type: '', idTmpl:'', tmplFile: '' } ]
     *
     * @uses Cblog::folders      array( array( 'value' => idFolder, 'name' => folderName ) )
     * @uses Cblog::jsonFolders
     * @uses Cblog::formats     array( array('idFormat' => '', 'format' => '') )
     */
    protected function Set_blogSettings()
    {
        //=============================================[ folders ]==============
        $query = "SELECT idFolder AS value, folderName AS name
                  FROM blogRecord_folders";
        $this->folders = $this->C->Db_Get_rows($query);

        $emptySelect = array( array('value' => 0, 'name' => 'none' )                    );
        $folders = array_merge($emptySelect, $this->folders);

        $this->jsonFolders = json_encode($folders);

       // echo "Set_blogSettings - jsonFolders = ".$this->jsonFolders;

        //=============================================[ formats ]==============
        $query = "SELECT idFormat , format
                  FROM blogRecord_formats";
        $this->formats =     $this->C->Db_Get_rows($query);

    }

    protected function Set_objHelpers()
    {
        error_log("[ivy]");
        error_log("[ivy] Cblog - Set_objHelpers ");
        error_log("[ivy] Cblog - Set_objHelpers - tring o set up helpers");
        $this->rowDb     = $this->C->Module_Build_objProp($this, 'blog_rowDb');
        $this->filters   = $this->C->Module_Build_objProp($this, 'blog_filters');
        $this->baseQuery = $this->C->Module_Build_objProp($this, 'blog_baseQuery');

    }
    /**
     * init blog
     *
     * @uses Cblog::Set_blogSettings() get blog settings from data base
     * @uses blog_handlers::_handle_requests() resolve requests
     */
    function _init_()
    {
        //var_dump($this);
        $this->Set_objHelpers();
        $this->Set_blogSettings();
        $this->_handle_requests();

        if (!isset($this->uid)) {
            error_log('[ ivy ] Cblog - _init_ : Nici un user nu a fost setat ');
        }
    }
}

