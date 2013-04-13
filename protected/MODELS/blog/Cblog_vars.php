<?php
class Cblog_vars{



    var $modelBlog_vars = array();          #CONF: sunt variabilele aditionate lui template_vars in cazul in care avem un blog personalizat
    var $modelBlog_name = '';               #CONF: numele blogului personalizat
    var $modelBlog_tableRecords = '';       #RET : blog[modelBlog_name]_records
    var $template = '';                     #CONF: numele templateului aferent blogului personalizat sau al blogului simplu
    var $modelBlog_vars_ColsStr = "";       # RET: col1, col2...ale tabelului aferent blogModel

    var $nrRecords = 3;
    var $records   = array();               # arrayul multidimensional al recordurilor
    var $blogRecords_vars ;                 # CONF: sunt numele variabilelor pentru REQ pentru tabelul blogRecords    , este util sa avem o separare deoarece face interactiunea cu DB mult mai usoara
    var $template_vars ;                    # CONF: o concatenare a $modelBlog_vars, $blogRecords_vars, $template_vars definite in config
    var $template_file = 'blogRecords';


   #============================================================================================================

    var $queryJOIN;                         # query-JOINU-ul cu modelul de blog cerut
    var $queryWheres = array();             # string cu conditiile interogarii , pot fi setate si din afara acestui model



    var $Pn = 1;                            # defaultul paginii la care ne aflam
    var $LimitEnd = '';                     # end-ul lui LIMIT;
    var $LimitStart = '';                   # startul-ul lui LIMIT;
    var $pagination = '';                   #  HTML




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
    var $current_modelBlog    = 'blog';               # sau poare sa fie modelBlog_name in cazul in care s-a facut request de un articol de un tip special
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


    #======================================[ PRIORITIES - home ]========================================================
    var $priorLevel_1 = array();                    # array-uri cu recorduri prioritare
    var $priorLevel_2 = array();
    var $priority_levels = array();       # pe level1 avem 1 prioritate , pe level2 avem 2 prioritati
    var $totalPriorities = 3;                       # numarul total de prioritati necesare




}