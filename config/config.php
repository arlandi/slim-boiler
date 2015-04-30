<?php
return array(
    'site.baseurl'      => '',   // Site URL (Global)
    'site.name'         => '',   // Site name (Global)
    'site.title'        => '',  // Site default title (Global)
    'site.description'  => '',  // Site default description (Global)
    'author.name'       => '', // Global author name
    'date.format'       => 'd M, Y',   // Date format to be used in article page (not for routes)
    'layout.file'       => 'layout',    // Site layout file
    'cache' => array(
        'enabled'   => false, // Enable/Disable cache
        'expiry'    => 24, // Cache expiry, in hours. -1 for no expiry
        'path'      => './cache'
    ),
    // Define routes
    'routes' => array(
        // Site root
        '__root__'  => array(
            'route'     => '/',
            'template'  =>'index'
        )
    ),
);
