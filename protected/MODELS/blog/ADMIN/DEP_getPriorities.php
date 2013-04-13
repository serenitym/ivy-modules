<?php


    $blogModel_name = $core->masterBlog->current_modelBlog;

    echo $core->GET_asincronMeth($blogModel_name,'get_recordPrior' );
