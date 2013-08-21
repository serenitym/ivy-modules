if( typeof ivyMods.blog == 'undefined'  ) {
    ivyMods.blog = {};
}

$.extend (
    ivyMods.blog , {

    sel: {
        basePathPic : "/RES/uploads/images/",
        thumbPathPic : "/RES/uploads/.thumbs/images/",
        colectorPics : '*[class$=thumbRecordPics]',
        imgs:          '*[class$=lead] img, *[class$=content] img',
        article:       'div[class$=SGrecord]',
        articlesBlog:  'div[class~=blogPrevRec]',
        gallery :      '*[class$=thumbRecordPics] a.fancybox',
        liveEdit:      '.ELMcontent',
        adminAuthors: 'form #adminAuthors'

    },
    jqCont: {
      jq: {},
      jqImgs : {},
      jqColectorPics: {},
      gallery : {},
      liveEditStat: {}
    },
    // ========================[ thumbnails ]===================================
    //#1
    get_RecordPics: function(jqCont){

        var recordPics = new Array();
        var src = '';
        var alt = '';



        jqCont.imgs.map(function()
        {
            //console.log(" img = "+ $(this).attr('src'));
            src = $(this)
                    .attr('src')
                    .replace(ivyMods.blog.sel.basePathPic,ivyMods.blog.sel.thumbPathPic);
            alt = $(this).attr('alt');

            recordPics.push( {src: src, alt: alt} );
        });
        /*var test = '';
        alert(recordPics.length);
        for( var key in recordPics) test += recordPics[key]+'\n\n';
        alert(test);
        */

        return recordPics;

    },
    //#1
    get_tmplThumbPics : function(recordPics, group) {

    	if (typeof group == undefined) {
    		var group = 'fancybox';
        	console.log('group: ' + group);
    	} 
    	
        var htmlPics = '';
        var i = 1;
        for( var key in recordPics) {
            htmlPics += "<a class='container-photoThumbs fancybox' " +
                            "data-fancybox-group='" + group + 
                            "' href='" + recordPics[key].src.replace('.thumbs/','') + "'>" +
                                "<img class='photoThumbs' " +
                                    "src='" + recordPics[key].src + "'" +
                                    "alt='" + recordPics[key].alt + "' />" +
                        "</a>" ;
        }
        //alert(htmlPics);
        return htmlPics;
    },
    //#2
    set_thumbPics: function(jqCont, group){

        var recordPics    = this.get_RecordPics(jqCont);
        var htmlThumbPics = this.get_tmplThumbPics(recordPics, group);

        // ascunde pozele care trec de 9
        jqCont.colectorPics.append(htmlThumbPics).find('*[class^=container-photoThumbs]:gt(8)').hide();

    },

    //#1
    createFancybox: function(jqCont){
        /*jqObj.find('a.fancybox').map(function()
         {
             $(this).fancybox();
         });*/
        jqCont.gallery.fancybox({
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

    //=======================[ container pics ]=================================
    //???
    styleSocialButtons: function(){
    	jQuery('[class^="at15t"]').css('background-position', '0px 0px');
    },

    //#1
    // atentie nu functioneaza deoarece se refera un container f maren
    /**
     * make all pics that are more than 85% of container 100%
     * @param jqObj
     */
    resizeContentPics: function(jqCont){
        var containerWidth = jqCont.jq.width();
        var prag = containerWidth*0.55 ;
        console.log("containerWidth " + containerWidth + " prag = " +prag);

        jqCont.imgs.map(function()
        {
            console.log("imagine width " + $(this).attr('src') + ' = ' + $(this).width());
            if ($(this).width() > prag ) {
                $(this).attr('height','');
                $(this).css('height','initial');
                $(this).css('width', '100% !important');
            }

        });
    },

    //#1
    captionContentPics: function(jqCont){

        if(jqCont.liveEditStat == 0) {
            jqCont.imgs.map(function(){
                var caption = $(this).attr('alt');
                if(caption) {
                    $(this).after("<div class='storyCaption'>"+caption+"</div>");
                }
            });
        }
    },

    /**
     * toate actiunile care au loc pe imaginile din container
     *
     * 1. seteaza thumbnailurile pentru imaginile din container
     * 2. creaza gallery pentru thumbnailuri
     * 3. reseteaza latimea imaginilor la 100% daca trec de 85%
     * 4. adauga caption la imagini daca userul nu se afla in live-edit
     *
     * @param jqCont
     * @param group
     */
    set_containerPics: function(jqCont, group){
        //thumb pics
        this.set_thumbPics(jqCont, group);
        this.createFancybox(jqCont);

        // container pics
       // this.resizeContentPics(jqCont);
        this.captionContentPics(jqCont);
    },
    /**
     * datele din si despre containerul ales
     *
     * 1.jq = jq pentru container
     * 2. imgs = jq pentru imaginile din container (cele mari )
     * 3. colectorPics = jq pentru elementul care contine thumbnailurile
     * 4. gallery = jq pentru elementele care trebuie sa fie in gallery
     * 5. liveEditStat = daca ne aflam sau nu in live-edit
     * @param jqContainer
     * @returns {{}}
     */
    get_containerData : function(jqContainer){

        var jqCont = {};

        jqCont.jq   = jqContainer;
        jqCont.imgs = jqContainer.find(this.sel.imgs);
        jqCont.colectorPics =  jqContainer.find(this.sel.colectorPics);
        jqCont.gallery      =  jqContainer.find(this.sel.gallery);
        jqCont.liveEditStat =  jqContainer.find(this.sel.liveEdit).length;

        return jqCont;


    },

    init: function(){
    	var fancyboxGroup = 1;
        var jqCont = {};

        // preperare article
        var article = $(this.sel.article)
        if(article.exists()) {

            jqCont = this.get_containerData(article);
            this.set_containerPics(jqCont);
        }

        // prepare articles
        var articlesBlog = $(this.sel.articlesBlog);
        if(articlesBlog.length > 0) {
            articlesBlog.map(function()
            {
                jqCont = ivyMods.blog.get_containerData($(this));
                ivyMods.blog.set_containerPics(jqCont, fancyboxGroup);
                fancyboxGroup++;


            });
        } else {
           // console.log('no articles from blog found');
        }

    }
}
);

$(document).ready(function(){
    ivyMods.blog.init();
});
