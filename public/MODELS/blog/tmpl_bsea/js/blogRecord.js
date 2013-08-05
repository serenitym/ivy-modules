ivyMods.blog = {

    basePathPic : "/RES/uploads/images/",
    thumbPathPic : "/RES/uploads/.thumbs/images/",
    colectorPics : '*[class$=thumbRecordPics]',
    containersPics: ['*[class$=lead]', '*[class$=content]'],

    get_RecordPics: function(jqObj){

        var recordPics = new Array();
        var src = '';


        for (var key in this.containersPics) {

            jqObj.find(this.containersPics[key] +" img").map(function()
            {
                console.log(" img = "+ $(this).attr('src'));
                src = $(this)
                        .attr('src')
                        .replace(ivyMods.blog.basePathPic,ivyMods.blog.thumbPathPic);

                recordPics.push( src );
            });
        }
        /*var test = '';
        alert(recordPics.length);
        for( var key in recordPics) test += recordPics[key]+'\n\n';
        alert(test);
        */

        return recordPics;

    },
    get_tmplThumbPics : function(recordPics) {

        var htmlPics = '';
        for( var key in recordPics) {
            htmlPics += "<div class='container-photoThumbs'>" +
                            "<img class='photoThumbs' src='"+recordPics[key]+"'/>" +
                        "</div>" ;
        }
        //alert(htmlPics);
        return htmlPics;
    },
    set_thumbPics: function(jqObj){

        var recordPics    = this.get_RecordPics(jqObj);
        var htmlThumbPics = this.get_tmplThumbPics(recordPics);

        jqObj.find(this.colectorPics).append(htmlThumbPics);

    },
    init: function(){
        this.set_thumbPics($('*[class$=SGrecord]'));
        $('*[class~=blogPrevRec]').map(function()
        {
            ivyMods.blog.set_thumbPics($(this));
        });

    }
};

$(document).ready(function(){
    ivyMods.blog.init();
});