ivyMods.set_iEdit.blog = function(){


    iEdit.add_bttsConf({
        'SGrecord': {
            modName: 'blog',
            edit: {atrValue : 'edit article'  },
            saveBt: {atrValue : 'save', methName: 'updateRecord'}
           ,extraHtml:[
                "<span>" +
                    "<input type='button' value='more fields'  class='editModeBTT' " +
                    " onclick='fmw.toggle(\"form[id^=EDITform] .admin-extraFields\"); return false;' />" +
                "</span>"
                ,"<span>" +
                    "<input type='button' value='settings' class='editModeBTT' " +
                    " onclick='fmw.toggle(\"form[id^=EDITform] .admin-extraSettings\"); return false;' />" +
                "</span>"
                ,"<span>" +
                    "<input type='hidden' name = 'action_methName'  value='deleteRecord' />" +
                    "<input type='submit' name = 'deleteRecord'  value='delete article' class='editModeBTT'/>" +
                "</span>"
            ]
        },
        'record' : {
           modName: 'blog',
           addBt : { status : false,  methName: 'addRecord', atrValue: 'add Record', style :" background-color: #D9E9F1;'  "},
           saveBt: { status : false},
           deleteBt:{atrValue: 'delete', methName: 'deleteRecord'},
           edit: {atrValue: 'edit Record'},
           extraBts: {
               addRecord : {
                   callBack : "fmw.toggle('#form-addRecord'); return false;",
                   attrValue : 'add article',
                   attrName: 'addRecord',
                   attrType:  'button'
               }
           }
        },

        'recordHome' :{
           addBt: {status: false},
           saveBt:{status:false}
        }
    });
};

/*
ivyMods.Ablog = {

    init: function(){

    }
};

$(document).ready(function(){
    ivyMods.Ablog.init();
});*/
