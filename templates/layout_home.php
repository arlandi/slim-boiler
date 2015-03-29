<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>
      <?php
        $title= (isset($global['title'])) ? $global['title'] : $global['site.title'];
        echo $title .' | '. $global['site.name'];
      ?>
    </title>
    <meta name="author" content="<?php echo $global['author.name']; ?>">
    <meta name="description" content="<?php echo $global['site.description']; ?>">
    <!-- Le styles -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,300' rel='stylesheet' type='text/css'>
    <link href="<?php echo $global['assets.prefix'];?>/themes/textpress/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $global['assets.prefix'];?>/themes/textpress/assets/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="<?php echo $global['assets.prefix'];?>/themes/textpress/assets/css/main.css" rel="stylesheet">
    <!-- Le fav and touch icons -->
  </head>
  <body>
  Anggi
  </body>
</html>
