ivyMods.set_iEdit.blog = function(){

    iEdit.add_bttsConf(
        {
            SGrecord: {
               edit: {  atrValue : 'edit Record',   style: " style='width:60px;  margin-left: -40px;'  "  }
            },
            record : {
               addBt :{ atrValue:'add Record', style :" style='width:80px;  margin-left: -60px; background-color: #D9E9F1;'  "},
               saveBt:{ satus : false}
            },

            recordHome :{
               addBt: {status: false},
               saveBt:{status:false}
            }
        }
    );
}