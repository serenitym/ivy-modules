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

 class objProperty
{
   // core pointers
    var $C;       // pointer to core
    var $DB;      // data base pointer
    var $lang;    // current language
    var $admin;   // status admin
    var $mgrName; // general module manager Name;
    var $mgrType; // general module manager type

    //node pointers
    var $tree;
    var $idNode;
    var $idTree;

    var $nodeLevel;
    var $nodeResFile;

    var $caller;
    var $objName;
    //var $modName
}