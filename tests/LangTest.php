<?php
define("BOT_NAME", "Axenia_bot");
require_once('../axenia/core/util.php');
require('../axenia/locale/Lang.php');

Lang::init("ru");

var_dump(Lang::message("user.pickChat", array("botName" => BOT_NAME)));
var_dump(Lang::message("karma.plus", array("from" => "formatq", "to" => "abrikoseg", "k1" => "100", "k2" => "666")));
