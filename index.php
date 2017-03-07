<?php
require_once("SiteService.php");
$site = new SiteService();
$type = $site->getViewType($_GET);
//$site->CheckData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Портал для анализа кармы бота Аксиньи - карма-бота Telegram">
    <meta name="author" content="Fomenko C.S.">
    <link rel="image_src" href="<? echo PATH_TO_SITE; ?>img/logo.png"/>
    <meta property="og:title" content="Карма бот Аксинья">
    <meta property="og:image" content="<? echo PATH_TO_SITE; ?>img/logo.png">
    <meta property="og:site_name" content="Axenia Telegram Karma Bot">
    <meta property="og:description" content="Портал для анализа кармы бота Аксиньи - карма-бота Telegram">
    <title><? echo strip_tags($site->createHeaderView($type, $_GET)); ?></title>
    <link rel="icon" type="image/png" href="img/favicon.png"/>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Roboto+Condensed|Roboto|Roboto+Slab&subset=latin,cyrillic'
          rel='stylesheet' type='text/css'>
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
<body>

<header id="top" class="header">
    <div class="container">
        <div class="row">
            <h1 id="header"><?
                echo $site->createHeaderView($type, $_GET);
                ?>
            </h1>
        </div>
    </div>
</header>
<section id="srch">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 center">
                <div class="row">
                    <div class="col-lg-6 col-lg-offset-3">
                        <h3>Найти:</h3>
                        <div class="input-group">
                            <div class="input-group-btn" id="search-button">
                                <button type="button" class="btn btn-default" data-toggle="dropdown" id="search_btn"
                                        value="0">
                                    Пользователя
                                </button>
                                <ul class="dropdown-menu" id="dropTypes">
                                    <li><a href="#">Пользователя</a></li>
                                    <li><a href="#">Группу</a></li>
                                </ul>
                            </div>
                            <input type="text" class="form-control" id="searchline" title="Search"/>
                            <div id="suggestions"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="cnt">
    <div class="container">
        <div class="row" id="content">
            <?
            include($type . "_view.php");
            ?>
        </div>
    </div>
</section>
<footer>
    <div class="container" style="background-color: rgba(92, 80, 155, 0.4);">
        <div class="row" style="padding: 20px 0;">
            <div class="col-md-6 col-xs-6">
                <button class="btn btn-sm btn-dark" data-toggle="modal" data-target="#myModal" id="for_food">
                    Пожертвовать на проект
                </button>
                <p style="padding-top: 4px;" >Написать автору <strong class="tg_user wrote">abrikos</strong><br><a
                        href="https://telegram.me/storebot?start=Axenia_bot">Оставить отзыв</a></p>
            </div>
            <div class="col-md-6 col-xs-6 text-right">
                <strong>Добавить к себе:</strong>
                <ul class="list-unstyled">
                    <li class="tg_user add_to_group">Axenia_Bot</li>
                </ul>
            </div>
        </div>
    </div>
</footer>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Накорми Аксинью</h4>
            </div>
            <div class="modal-body center-block text-center">
                <iframe frameborder="0" allowtransparency="true" scrolling="no"
                        src="https://money.yandex.ru/embed/donate.xml?account=41001224651196&quickpay=donate&payment-type-choice=on&mobile-payment-type-choice=on&default-sum=100&targets=%D0%90%D0%BA%D1%81%D0%B8%D0%BD%D1%8C%D0%B5+%D0%BD%D0%B0+%D0%B5%D0%B4%D1%83&target-visibility=on&project-name=&project-site=&button-text=05&comment=on&hint=%D0%9E%D1%81%D1%82%D0%B0%D0%B2%D1%8C%D1%82%D0%B5+%D1%81%D0%B2%D0%BE%D0%B9+%D1%8E%D0%B7%D0%B5%D1%80%D0%BD%D0%B5%D0%B9%D0%BC+%D0%B4%D0%BB%D1%8F+%D0%BF%D0%BE%D0%BB%D1%83%D1%87%D0%B5%D0%BD%D0%B8%D1%8F+%D0%B0%D1%87%D0%B8%D0%B2%D0%BA%D0%B8&successURL=https%3A%2F%2Fabrikoseg.ru%2Faxenia%2Fweb%2F"
                        width="508" height="160">
                </iframe>
            </div>
        </div>
    </div>
</div>
<script src="js/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/typeahead.min.js"></script>
<script src="js/scripts.js"></script>

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
