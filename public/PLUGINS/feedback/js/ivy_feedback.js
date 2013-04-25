ivyMods.feedback_init = function(){

    $('.toggleFb').on('click', function(){

        //alert('click pe toggleFb');
            var fbMess = $('.ivy-feedback-mess');
            if(fbMess.is(':visible')){
                fbMess.prev().attr('value','show feedback');
                fbMess.hide();
                //alert('is visible');
            }
            else {
                fbMess.prev().attr('value','hide feedback');
                fbMess.show();

                //alert('pare sa fie invisible');
            }

    });

   $('.fb_readMore').on('click', function(){

       var fb_mess = $(this).prev();
       var overflowY = fb_mess.css('overflow-y');

      // alert(overflowY);
       if(overflowY == 'hidden') {
            $(this).text('show less');
            fb_mess
                .css('overflow-y','visible')
                .css('height', '100%');
       }
       else{
           $(this).text('show more');
           fb_mess
               .css('overflow-y','hidden')
               .css('height', '50px');

       }

   });
}

$(document).ready(function(){

    ivyMods.feedback_init();

});