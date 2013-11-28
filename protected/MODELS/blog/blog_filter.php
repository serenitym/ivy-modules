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

class blog_filter
{
    var $name;
    var $value;
    var $desc;
    var $href;
    var $id;
    var $selected = 0;

    function hrefFilter()
    {
        return "filterName={$this->name}&filterValue={$this->value}";
    }
    function idFilter()
    {
        return str_replace(' ','', $this->value);
    }
    function set_selected()
    {
        $this->selected = 1;
    }

    function __construct($baseUrl, $name, $value, $desc = '')
    {
        $this->desc  = $desc ?: $value;
        $this->value = $value;
        $this->name  = $name;
        $this->href  = $baseUrl."&".$this->hrefFilter();
        $this->id    = $this->idFilter();
    }
}