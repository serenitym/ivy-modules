if( typeof ivyMods.blog == 'undefined'  ) {
    ivyMods.blog = {};
}

var disqus_shortname = 'blacksea-beta'; // required: replace example with your forum shortname

$.extend ( true, ivyMods.blog ,
{
	 limitSet : 10,
    sel: {
        basePathPic : "/RES/uploads/images/",
        thumbPathPic : "/RES/uploads/.thumbs/images/",
        colectorPics : '*[class$=thumbRecordPics]',
        imgs:          '*[class$=lead] img, *[class$=content] img',
        iframes:       '*[class$=lead] iframe, *[class$=content] iframe',
        article:       'div[class$=SGrecord]',
        articlesBlog:  'div[class~=blogPrevRec]',
	     blogSet:       function(blogSet){return '*[class^=blogSet_'+blogSet+'] '; },
      //  gallery :      '*[class$=thumbRecordPics] a.fancybox',
        galleria :      '*[class$=thumbRecordPics]',
        liveEdit:      '.ELMcontent',
        adminAuthors: 'form #adminAuthors',
	     getNext_blogRecords: "input[class$=getNext_blogRecords]"

    },
    jqCont: {
      jq: {},
      jqImgs : {},
      jqColectorPics: {},
      gallery : {},
      liveEditStat: {}
    },

	asyncRecords : new fmw.asyncConf({
		restoreCore: true,
		dataSend: {
			modName: 'blog, handler',
			methName: 'blog_renderData'
		}
	}),
	fancyboxGroup: 1,
    // ========================[ event Callbacks ]==============================
    bind_getNext_blogRecords: function(){

	      var loadButton = $(this.sel.getNext_blogRecords);

			loadButton.on('click', function(){

				// atentie se poate culege un vector cu toate datele
				var limitStart = $(this).data('blogLimitstart');
				var limitEnd   = $(this).data('blogLimitend');


				ivyMods.blog.asyncRecords
					.fnpost({"limitStart" : limitStart})
					.done(function(data){


						limitStart += 10;
						loadButton.data('blogLimitstart', limitStart);
						loadButton.parent().before(data);
						ivyMods.blog.onload_articlesBlog(limitStart);

						console.log('limit start = '+limitStart + ' limit end = '+limitEnd);
						if(limitStart >= limitEnd) {
							loadButton.next('input[class$=go-topPage]').css('display', 'block');
							loadButton.remove();
						}
					});
			});

     },


    // ========================[ thumbnails - fancyBox ]========================
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
	 },

    // ========================[ thumbnails - galleria ]========================
	//#1
    get_tmplThumbPics_galleria : function(recordPics) {

	    /**
	     * <div id="galleria">
	         <a href="/img/large1.jpg"><img src="/img/thumb1.jpg" data-title="My title" data-description="My description"></a>
	         <a href="/img/large2.jpg"><img src="/img/thumb2.jpg" data-title="Another title" data-description="My <em>HTML</em> description"></a>
	     </div>

	     using:
	     recordPics.push( {srcBig: srcBig, src: src, alt: alt} );

	     */
    	  var htmlPics = '';
	     var indexImage = 0;
        for( var key in recordPics) {
            htmlPics += "<a class='container-photoThumbs' " +
                            "' href='" + recordPics[key].srcBig + "'" +
                            " data-index-image='"+indexImage+"'"+
	                      ">" +
                                "<img class='photoThumbs' " +
                                    "src='" + recordPics[key].src + "'" +
                                    "data-title='" + recordPics[key].alt + "' " +
                                 //   "data-description='" + recordPics[key].alt + "' " +
	                              "/>" +
                        "</a>" ;
	        indexImage++;
        }
        //alert(htmlPics);
        return htmlPics;
    },
    //#2
    set_thumbPics_galleria: function(jqCont){

	     // preia json cu pozele pt galerie
        var recordPics    = this.get_RecordPics(jqCont);
	     // seteaza html-ul pozelor
        var htmlThumbPics = this.get_tmplThumbPics_galleria(recordPics);

        // ascunde pozele care trec de 9
        jqCont.colectorPics.append(htmlThumbPics).find('*[class^=container-photoThumbs]:gt(8)').hide();

    },

	 createGalleria: function(jqCont){

		 jqCont.colectorPics.find("a").on('click', function(){

			 // =========================[ construieste dom-ul pt galleria ]=======
			 /**
			  * Testeaza si retine cea mai mare inaltime
			  * ( intre cea a browserului si cea a lui body)
			  * @type {Number}
			  */
			 var windowHeight = window.innerHeight;
			 var bodyHeight = $('body').height();
			 var canvasHeight = windowHeight > bodyHeight ? windowHeight : bodyHeight;

			 /**
			  * Adauga partea de dom in care va sta galleria
			  */
			 $('body').append(
             "<div id='galleria-container'>" +
	             "<div id='galleria-canvas' style='height: "+canvasHeight+"px; '></div>" +
                "<div id='galleria' ></div>" +
             "</div>"
          );


			 //==============================[ start galleria ]====================
			 /**
			  * Apeleaza si configureaza galeria
			  * - sa porneasca de la imaginea care a fost apasata
			  * - sa preia imaginile sursa din containerul de imagini sursa
			 * */
			 var indexImage = $(this).data('indexImage');
			 //console.log('createGalleria - indexImage = '+indexImage);
			 Galleria.configure({
				 show: indexImage
			 });
			 Galleria.run('#galleria',{
				 dataSource: '#'+jqCont.galleriaID
			 });


			 //==========================[ seteaza pozita si dimensiunea pt galleria]=

			 var jqGalleria = $('#galleria');
			 var marginLeft = jqGalleria.width() / 2;
			 // pozitia scroll-ui
          //$('#topbar-bsea').position().top;
			 var scrollTop = $(window).scrollTop();
			 var top = (window.innerHeight - jqGalleria.height()) / 2 + scrollTop - 50;
			 jqGalleria.css('margin-left', marginLeft);
			 jqGalleria.css('top', top);

			 // adauga butonul de closeGalleria care va face remove la tot domul de galleria
			 jqGalleria.prepend(
				 "<div>" +
					 "<input type='button' class='ivy-light' value='close' id='galleria-close'" +
					 " onclick=\"$('#galleria-container').remove();\">" +
				 "</div>"
			 );

			 // stop from bubbling
			 return false;
		 });
	 },
	 set_containerPics_galleria: function(jqCont, group){
	     //thumb pics
	     this.set_thumbPics_galleria(jqCont);
	     this.createGalleria(jqCont);
	 },

    //=======================[ container pics ]=================================
    //#1
    get_RecordPics: function(jqCont){

        var recordPics = new Array();
        var src = '';
        var alt = '';
        var srcBig = '';

        jqCont.imgs.map(function()
        {
            //console.log(" img = "+ $(this).attr('src'));
	         srcBig = $(this).attr('src');
            src    = srcBig.replace(ivyMods.blog.sel.basePathPic,ivyMods.blog.sel.thumbPathPic);
            alt    = $(this).attr('alt');

            recordPics.push( {srcBig: srcBig, src: src, alt: alt} );
        });
        /*var test = '';
        alert(recordPics.length);
        for( var key in recordPics) test += recordPics[key]+'\n\n';
        alert(test);
        */

        return recordPics;

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

    resize_iframes: function(jqCont){

	    var containerWidth = jqCont.jq.width();
	    var width = 560;
       var height = 315;
       var proportion =   height/width;

       jqCont.iframes.map(function(){
	        $(this).width(containerWidth);
           $(this).height(containerWidth * proportion);
           /* console.log(
                  "container = " + containerWidth
                + "\n height = " + (containerWidth * proportion)
                +"\n"
            );*/
       });
    },
    //#1
    resizeContentPics: function(jqCont){
        var containerWidth = jqCont.jq.width();
        //console.log("containerWidth " + containerWidth );
        jqCont.imgs.map(function()
        {
            //console.log("imagine width " + $(this).attr('src') + ' = ' + $(this).width());
            $(this).css('height','initial');
	         $(this).width(containerWidth);

        });
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
        jqCont.iframes = jqContainer.find(this.sel.iframes);
        jqCont.colectorPics =  jqContainer.find(this.sel.colectorPics);
       // jqCont.gallery      =  jqContainer.find(this.sel.gallery);
        jqCont.galleriaID      =  jqContainer.find(this.sel.galleria).attr('id');
        jqCont.liveEditStat =  jqContainer.find(this.sel.liveEdit).length;

        return jqCont;


    },

	 //==========================================================================

    onload_article: function(){
	    var jqCont = {};

       // prepare article
       var article = $(this.sel.article)
       if(article.exists()) {

           jqCont = this.get_containerData(article);
	        // daca imaginile gasite sunt > 3 atunci
           // le facem thumbnailuri si gallery
           if(jqCont.imgs.length >= 3){
					Galleria.loadTheme('/assets/galleria/themes/classic/galleria.classic.min.js');
               this.set_containerPics_galleria(jqCont);
           }

	        // set caption for photos
	        this.captionContentPics(jqCont);

	        //resizing pics
	        this.resizeContentPics(jqCont);

	        //resizing iframes
           if(jqCont.iframes.length) {

               this.resize_iframes(jqCont);
               $(window).resize(function() {
                   ivyMods.blog.resize_iframes(jqCont);

               });
           }
	        $(window).resize(function() {
                ivyMods.blog.resizeContentPics(jqCont);
           });
       }
    },

	 onload_articlesBlog: function(blogSet){
		// blogset = setul de articole , see: blogRecords.html

		//var fancyboxGroup = 1;
      var jqCont = {};

		var articlesBlog = $(this.sel.blogSet(blogSet)+this.sel.articlesBlog);
      if(articlesBlog.length > 0) {
	       Galleria.loadTheme('/assets/galleria/themes/classic/galleria.classic.min.js');

          articlesBlog.map(function()
          {
              jqCont = ivyMods.blog.get_containerData($(this));

	           // daca imaginile gasite sunt > 3 atunci
	           // le facem thumbnailuri si gallery
	           if(jqCont.imgs.length >= 3){
		           ivyMods.blog.set_containerPics_galleria(jqCont);

		           //ivyMods.blog.set_containerPics(jqCont, ivyMods.blog.fancyboxGroup);
	           }

              // set caption for photos
	          ivyMods.blog.captionContentPics(jqCont);

              //resizing iframes
	           if(jqCont.iframes.length) {
                  ivyMods.blog.resize_iframes(jqCont);
	           }

              ivyMods.blog.fancyboxGroup++;
          });
      } else {
//          console.log('no articles from blog found la selectorul '+ this.sel.blogSet(blogSet)+this.sel.articlesBlog);
      }

	},

	 //comentarii
    disqus_add: function(){
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    },

    init: function(){
	    this.onload_article();
	    this.onload_articlesBlog('unpublished');
	    this.onload_articlesBlog(10);
	    this.bind_getNext_blogRecords();
	    this.disqus_add();
    }
}
);

$(document).ready(function(){
    ivyMods.blog.init();
});
