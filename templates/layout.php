<!doctype html>
<html class="no-js" lang="">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>
        <?php $title=( isset($global[ 'title'])) ? $global[ 'title'] : $global[ 'site.title']; echo $title . ' | '. $global[ 'site.name']; ?>
    </title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <!-- Place favicon.ico in the root directory -->
</head>

<body>

    <?php echo $content; ?>

    <script type="text/javascript" src="/js/main.js"></script>
</body>

</html>
