<?php
require_once('../core/config.php');
require_once('AxeniaBot.php');
require_once('AxeniaLogic.php');
$content = file_get_contents("php://input");
$update = json_decode($content, true);


if (!$update) {
    exit;
}

if (isset($update["message"])) {
    $axeniaBot = new AxeniaBot(API_URL, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
    $axeniaBot->processMessage($update["message"]);
}


?>