<?php
class Cblog_vars{



    var $template = '';                     #CONF: numele templateului aferent blogului personalizat sau al blogului simplu

    var $nrRecords = 3;
    var $records   = array();               # arrayul multidimensional al recordurilor
    var $blogRecords_vars ;                 # CONF: sunt numele variabilelor pentru REQ pentru tabelul blogRecords    , este util sa avem o separare deoarece face interactiunea cu DB mult mai usoara
    var $template_vars ;                    # CONF: o concatenare a $modelBlog_vars, $blogRecords_vars, $template_vars definite in config
    var $template_file = 'blogRecords';


   #============================================================================================================

    var $queryWheres = array();             # string cu conditiile interogarii , pot fi setate si din afara acestui model



    #====================================================[ Comments Settings ]==========================================

    var $commentsView   = true;               # CONF: daca blogul are enabled partea de commenturi
    var $commentsStat   = true;               # se mai pot sau nu posta commenturi
    var $commentsApprov = false;              # commenturile trebuie aprobate sau nu
    var $modelComm_name = 'comments';         # CONF: numele modelului de comments



    #============================[ pentru ADMIN TEMPLATE - comments_Settings ] =========================================

    var $commView_true  = 'checked';   # enable / disable comments
    var $commView_false = '';

    var $commStat_true   = 'checked';  #daca se mai poate sau nu posta
    var $commStat_false  = '';

    var $commApprov_true = 'checked';  #daca commenturile trebuie aprobate
    var $commApprov_false= '';




    #===================================================================================================================

    var $LG;
    var $C;

    #===================================================================================================================


    #var $current_Layout       = 'masterBlog';        # sau profile in functie de caz
    var $current_modelBlog    = 'blog';               # sau poare sa fie format in cazul in care s-a facut request de un articol de un tip special
    # var $contentTemplate_file = 'blogRecords';       # subtemplate
    var $categoriesHTML       = '';                   # categoriile blogului


    #=======================================[ Permissions ]============================================================
    var $ED=''; # arata daca un anumit ENT sau SING va fii editabil sau nu , daca nu va fii editabil i se va defiini orice valoare
    var $EDrecord = '';

    #____Cuser SETS
    var $editRecordPermss         = false;          # editarea unui record     personal sau  master
    var $pubPermss                = false;          # publicarea unui record   master
    var $webmPermss               = false;          # permisiuni de webMaster ex: editarea prioritatilor pe home
    var $editRecords_Permss       = false;          # delete Records           master

}