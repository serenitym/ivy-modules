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
        $modCaller->C->Module_config($this, 'MODELS', 'blog');
        $this->Set_objHelpers();
        $this->handler = $this->C->Module_Build_objProp($this,
                                       $this->handlerPrefix.'profile');
    }
}