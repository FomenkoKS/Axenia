<?php
require_once("logic.php");
require_once("functions.php");
$logic = new FemaleLogic();
$type = $logic->TypeOfView($_GET);
$logic->CheckData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Портал для анализа кармы тётушки Аксиньи">
    <meta name="author" content="Fomenko C.S.">
    <link rel="image_src" href="<? echo PATH_TO_SITE; ?>img/logo.png"/>
    <meta property="og:title" content="Аксинья">
    <meta property="og:image" content="<? echo PATH_TO_SITE; ?>img/logo.png">
    <meta property="og:site_name" content="Axenia Bot">
    <meta property="og:description" content="Портал для анализа кармы тётушки Аксиньи">
    <title><? echo strip_tags($logic->GetHeader($type, $_GET)); ?></title>
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
                echo $logic->GetHeader($type, $_GET);
                ?>
            </h1>
        </div>
    </div>
</header>
<section id="srch">
    <div class="container">
        <div class="row">
            <?
            include("search_view.php");
            ?>
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
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <button class="btn btn-sm btn-dark" data-toggle="modal" data-target="#myModal" id="for_food">
                    Пожертвовать на проект
                </button>
                <p>Написать автору <strong class="tg_user wrote">abrikos</strong><br><a
                        href="https://telegram.me/storebot?start=Axenia_bot">Оставить отзыв</a></p>
            </div>
            <div class="col-md-2 col-md-offset-7 text-right">
                <strong>Добавить к себе:</strong>
                <ul class="list-unstyled">
                    <li class="tg_user add_to_group">Axenia_Bot</li>
                    <li class="tg_user add_to_group">ZinaBot</li>
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
                        width="508" height="160"></iframe>
                </iframe>
            </div>
        </div>
    </div>
</div>
<script src="js/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/typeahead.min.js"></script>
<script src="js/scripts.js"></script>
</body>

</html>
