// ACEST js ar trebui encapsulat



/*===========================[ Other settings ]=====================================*/
function toggle_pic(){
   //in acest mod as putea sa preiau pozele printr-un script
    // astfel nu incarc domul inutil
   $('.uploaded_pics').toggle();
    carousel_firstStart();

}
function toggle_otherSettings(){
    //alert('in togle');
    $('.record_otherSett').toggle();

}
/*===========================[ HOME priority ADMIN ]===============================*/
//alert('in glogRecord.js');
var currentRecord = '';
var idRecord = '';
var totalPriorities;


/*======================================================================================*/

/**
 * STEPS :function to call after the load of popUp-content
 *  - (1) - apply ui-sorable
 *
 *  - (2) - set datepicker
 *              - formatDate - ISO firendly yy-mm-dd
 *              - available interval 0 - 14 days
 *
 *  - (3) - select totalPriorities available
 *
 *
 *
 *  - (4) - see if the current record  should have an adding form
 *              - daca lista nu este plina
 *              - daca nu a fost adaugat deja in lista
 *
 *  - (5) - bind save button
 *
 *  - (6) - bind delete button
 *
 */
/**
 * WORKING model
 *
 * popUp-content - source: file -  'MODELS/blog/ADMIN/getPriorities.php'
 *
 * WORK tmpl :
 *  <p id='popup-message' class='text-success b'></p>

    <-- ------------------------------------[ Priority list ]-------------------------------->

     <ul id='sortable-priorities'>
        <li  class='ui-state-default' id='recordPriority_{$record['idRecord']}' >
             <span>{$record['title']}</span>
             <span class='prior_ctrls'>
                 <input type='text' name='endDate' id='endDate_{$record['idRecord']}' value='{$record['endDate']}' class='input-small'>
                 <button name='deletePriority_{$record['idRecord']}' class='btn btn-mini'>
                     <i class='icon-minus-sign'></i>
                 </button>
             </span>
        </li>
        ...
        <li>...</li>

    </ul>
    <-- ------------------------------------[ Details of Priorities ]-------------------------------->

     <br>
     <p>
        <b>Available no. of home priorities</b>
        <span  id='totalPriorities'>{$priorSettings->totalPriorities}</span>
     </p>
     {$priority_levels}


    <p>
     <button name='savePriorities'  class='btn btn-mini btn-primary t10'>
        save
     </button>
    </p>
 */
function bindsPopup_priority(){

 // (1)_________________________________________________________________________________
    $( "#sortable-priorities" ).sortable({ placeholder: "ui-state-highlight" });
    $( "#sortable-priorities" ).disableSelection();


 // (2)_________________________________________________________________________________
    $( "#sortable-priorities li input[id^=endDate]" )
        .datepicker({dateFormat: 'yy-mm-dd',minDate: 0, maxDate: "+14D"});


 // (3)_________________________________________________________________________________
    totalPriorities = parseInt( $('#popUp #popUp-content #totalPriorities').text() );


 // (4)_________________________________________________________________________________
    priority_currentRecord_ctrl();

// (5) (6)_________________________________________________________________________________

    $('button[name^=deletePriority]')
        .live('click',function(){
        deletePriority($(this).parents('li[id^=recordPriority]'));
    });

    $('button[name=savePriorities]').bind('click',function(){ savePriorities() });

   // alert('In function bindsPopup_priority');

}
/*======================================================================================*/


/**
 * add li- current record
 * - to save changes click add button
 *
 * STEPS:
 *  - add HTML tmpl
 *  - call datepicker
 *  - remove the form for adding current record as a priority
 */
function addPriority_currentRecord(){

    var date=new Date();
    date.setDate(date.getDate() + 14);
    var endDate=date.toISOString().slice(0,10);


    $( "#sortable-priorities").append(
         "<li  class='ui-state-default' id='recordPriority_"+idRecord+"' >"+
               "<span>"+currentRecord+"</span>" +
                "<span class='prior_ctrls'>" +
                    "<input type='text' name='endDate' id='endDate_"+idRecord+"' value='"+endDate+"' class='input-small'> "+
                    "<button name='deletePriority_"+idRecord+"' class='btn btn-mini'>" +
                        "<i class='icon-minus-sign'></i>" +
                    "</button>" +
                 "</span>"+
          "</li>"
         )
        .find( "li input[id=endDate_"+idRecord+"]" )
                .datepicker({dateFormat: 'yy-mm-dd',minDate: 0, maxDate: "+14D"});;
     $('p#currentRecord').remove();
    // alert('intra aiurea');

}
/**
 * adding form to add current record as a priority
 *
 *  STEPS:
 *      - add html tmpl
 *      - bind addPriority
 */
function priority_currentRecord_tmpl(){

     $('#sortable-priorities')
     .after(
        " <p id='currentRecord'>"+
           "<span id='priorCurrentRecord_"+idRecord+"'>"+
              currentRecord+
           "</span>"+
           "<button name='addPriority'  class='btn btn-mini'>Add priority</button>"+
        " </p>"
     ).next()
         .find('button[name=addPriority]')
            .bind('click',function(){addPriority_currentRecord();} );

}
/**
 *  see if the current record  should have an adding form
 *              - daca lista nu este plina
 *              - daca nu a fost adaugat deja in lista
 */
function priority_currentRecord_ctrl(){

        var nrPriorities         = $( "#sortable-priorities li" ).length;
        var currentPriority_stat = $( "#sortable-priorities li[id$="+idRecord+"]" ).length;


        // alert('currentPriority_stat '+currentPriority_stat+' nrPriorities '+nrPriorities);
        if(!currentPriority_stat   && nrPriorities < totalPriorities){

            priority_currentRecord_tmpl();

        }
}

/**
 * Remove li-priority from ul
 *  - to save the remove - user shoul click save
 * @param liRecord
 */
function deletePriority(liRecord){

    var idRecord_arr = liRecord.attr('id').split('_');
    var idRecord_del = idRecord_arr[1];

    if(idRecord_del == idRecord)
        priority_currentRecord_tmpl();

    liRecord.remove();
    // alert('prioritatea '+idRecord_del+' trebuie deletata');
}

/**
 * Preluare date(ul#sortable-priorities li) + mesaj succes
 */
function savePriorities(){

    /**
     * STEPS:
     *  (1) - preluarea datelor pentru salvare
     *        ca array[0,1,..] = {idRecord: idRecord_prior, endDate : endDate, priorityLevel : priorityLevel };
     *  (2) -  add a message of succes
     *      - asyncReq_action : 'savePriorities' - from ATblog- controlREQ_async
     * @type {Array}
     */
    /**
     * working TMPL
     * <ul id='sortable-priorities'>
     *     <li  class='ui-state-default' id='recordPriority_{$record['idRecord']}' >
                <span>{$record['title']}</span>
                <span class='prior_ctrls'>
                    <input type='text' name='endDate' id='endDate_{$record['idRecord']}' value='{$record['endDate']}' class='input-small'>
                    <button name='deletePriority_{$record['idRecord']}' class='btn btn-mini'>
                        <i class='icon-minus-sign'></i>
                    </button>
                </span>
           </li>
     * </ul>
     * priorities = new Array();

     am nevoie de idRecord, priorityLevel - un contor, endDate
     */
    var priorities = new Array();
    var priorityLevel = 1;
    var priority = '';
    var idRecord_prior = '';

    var test = '';

    // (1)
    $( "#sortable-priorities li" ).map(function(){
         priority = $(this).find('input[name=endDate]');

         idRecord_prior = priority.attr('id').split('_')[1];
         endDate        = priority.val();

         priorityData = {idRecord: idRecord_prior, endDate : endDate, priorityLevel : priorityLevel };
         priorities.push(priorityData);
         //test +="\n"+'idRecord_prior '+idRecord_prior+' endDate '+endDate+' priorityLevel '+priorityLevel;
         priorityLevel++;
    });

    // (2)
    $('#popup-message').load(
        procesSCRIPT_file,
        {
             asyncReq_action : 'savePriorities',
             priorities : priorities
        }

    );

    //alert(test);

}



/*======================================================================================*/

/**
 * on click called by TMPL - blogRecord.html - ADMIN toolbar - btn - priorityBtn
 *
 * "<input type='button' name='priorityBtn' value='Priority'
                                                onclick=\"callPopup_priority();\">"
 */
function callPopup_priority(){
    //alert('Apelez functia callPopup_priority');
    //popUp_ini(pathLoad, dataSend, completeFunc, header)
    currentRecord = $('form input[class$=title]').val();
    idRecord      = $('form input[name=BLOCK_id]').val();

    //popUp_ini(pathLoad, dataSend, completeFunc, header, width, height)

   /* popUp_ini('MODELS/blog/ADMIN/getPriorities.php',
               {currentRecord: currentRecord, idRecord : idRecord},
                'bindsPopup_priority',
                'HomePriorities');*/
    /**
    * popUp_call(pathLoad,opt)
    * opt = {dataSend:{}, completeFunc:'',procesSCRIPT : 'true', headerName:'',widthPop:'', heightPop:''}
    *
    */
   var popUpPriorities = new  popUp_call(
                {
                    dataSend :
                    {
                        asyncReq_action : 'get_recordPrior',
                        currentRecord: currentRecord,
                        idRecord : idRecord
                    },
                    completeFunc:  'bindsPopup_priority',
                    headerName :  'HomePriorities'
                });
//asyncReq_action : 'savePriorities',
}

