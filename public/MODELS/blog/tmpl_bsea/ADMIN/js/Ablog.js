ivyMods.set_iEdit.blog = function(){

    iEdit.add_bttsConf({
        'SGrecord': {
            modName: 'blog',
            edit: {atrValue : 'edit Record'  },
            saveBt: {atrValue : 'save', methName: 'updateRecord'}
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