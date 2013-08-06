ivyMods.blog = {

    basePathPic : "/RES/uploads/images/",
    thumbPathPic : "/RES/uploads/.thumbs/images/",
    colectorPics : '*[class$=thumbRecordPics]',
    containersPics: ['*[class$=lead]', '*[class$=content]'],

    get_RecordPics: function(jqObj){

        var recordPics = new Array();
        var src = '';
        var alt = '';


        for (var key in this.containersPics) {

            jqObj.find(this.containersPics[key] +" img").map(function()
            {
                console.log(" img = "+ $(this).attr('src'));
                src = $(this)
                        .attr('src')
                        .replace(ivyMods.blog.basePathPic,ivyMods.blog.thumbPathPic);
                alt = $(this).attr('alt');

                recordPics.push( {src: src, alt: alt} );
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
        var i = 1;
        for( var key in recordPics) {
            htmlPics += "<a class='container-photoThumbs fancybox' " +
                            "data-fancybox-group='button' " +
                            "href='" + recordPics[key].src.replace('.thumbs/','') + "'>" +
                                "<img class='photoThumbs' " +
                                    "src='" + recordPics[key].src + "'" +
                                    "alt='" + recordPics[key].alt + "' />" +
                        "</a>" ;
        }
        //alert(htmlPics);
        return htmlPics;
    },
    set_thumbPics: function(jqObj){

        var recordPics    = this.get_RecordPics(jqObj);
        var htmlThumbPics = this.get_tmplThumbPics(recordPics);

        jqObj.find(this.colectorPics).append(htmlThumbPics);

    },
    resizeContentPics: function(jqObj){
        var containerWidth = jqObj.width();
        //alert(containerWidth);

        jqObj.find('img').map(function()
        {
            if ($(this).width() > containerWidth*0.75 ) {
                $(this).attr('height','');
                $(this).width(containerWidth);
            }

        });
    },
    createFancybox: function(jqObj){
        /*jqObj.find('a.fancybox').map(function()
         {
             $(this).fancybox();
         });*/
        jqObj.find('a.fancybox').fancybox({
                beforeShow : function() {
                    var alt = this.element.find('img').attr('alt');

                    this.inner.find('img').attr('alt', alt);

                    this.title = alt;
                },
				openEffect : 'elastic',
				openSpeed  : 150,

				closeEffect : 'elastic',
				closeSpeed  : 150,

				closeBtn  : true,

				helpers : {
					title : {
						type : 'outside'
					},
					buttons	: {},
					thumbs : {
						width  : 50,
						height : 50
					}
                },

				afterLoad : function() {
					this.title = 'Image ' + (this.index + 1) + ' of ' + this.group.length + (this.title ? ' - ' + this.title : '');
				}
        });
    },
    styleSocialButtons: function(){
    	jQuery('[class^="at15t"]').css('background-position', '0px 0px');
    },
    init: function(){
        this.set_thumbPics($('*[class$=SGrecord]'));
        $('*[class~=blogPrevRec]').map(function()
        {
            ivyMods.blog.set_thumbPics($(this));
        });

        this.createFancybox($('div.thumbRecordPics'));
        this.resizeContentPics($('div.content'));

    }
};

$(document).ready(function(){
    ivyMods.blog.init();
});
