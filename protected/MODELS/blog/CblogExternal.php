<?php
/**
 * pentru cazul in care cineva vrea sa apeleze acest
 * modul
 * - trebuie refacut linkul la core prin modulul care
 * l-a apelat
 */
class CblogExternal extends Cblog
{
    function __construct($modCaller)
    {
        $modCaller->C->Module_configCorePointers($this);
    }
}