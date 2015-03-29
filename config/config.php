<?php
return array(
    'site.baseurl'      => 'http://goodcoffee.nyc',   // Site URL (Global)
    'site.name'         => 'Goodcoffee',   // Site name (Global)
    'site.title'        => '',  // Site default title (Global)
    'site.description'  => '',  // Site default description (Global)
    'author.name'       => '', // Global author name
    'date.format'       => 'd M, Y',   // Date format to be used in article page (not for routes)
    'layout.file'       => 'layout',    // Site layout file
    'assets.prefix'     => '', // prefix to be added with assets files
    'prefix'            => '',   // prefix to be added with all URLs (not to assets). eg : '/blog'
    'google.analytics'  => false, // Google analytics code. set false to disable
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
            'template'  =>'index',
            'layout'    => 'layout_home'
        )
    ),
);
