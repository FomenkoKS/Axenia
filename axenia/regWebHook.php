<?php

require_once('../core/config.php');
require_once('AxeniaBot.php');
require_once('AxeniaLogic.php');

$axeniaBot = new AxeniaBot(API_URL, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);

echo $axeniaBot->apiRequest('SetWebhook', array('url'=> PATH_TO_WEBHOOK));
echo "ok";
?>