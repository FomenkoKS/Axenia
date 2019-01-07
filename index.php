<?php
require_once("SiteService.php");
$site = new SiteService();
$type = $site->getViewType($_GET);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title>Karmabot for Telegram Axenia</title>

    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="http://axeniabot.ru/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="http://axeniabot.ru/apple-touch-icon-152x152.png" />
    <link rel="icon" type="image/png" href="http://axeniabot.ru/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="http://axeniabot.ru/favicon-16x16.png" sizes="16x16" />
    <meta name="application-name" content="Karmabot for Telegram Axenia"/>
    <meta name="msapplication-TileColor" content="#FFFFFF" />
    <meta name="msapplication-TileImage" content="http://axeniabot.ru/mstile-144x144.png" />

    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/style.css" rel="stylesheet">

    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({
            google_ad_client: "ca-pub-9486791700063351",
            enable_page_level_ads: true
        });
    </script>
  </head>

  <body class="text-center">
 
    <?
        include($type . "_view.php");
    ?>      
   
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
    <script src="./js/popper.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script>
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');
    
        ga('create', 'UA-92646836-1', 'auto');
        ga('send', 'pageview');
    
    </script>
  </body>
</html>
