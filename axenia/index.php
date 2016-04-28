<?php
require_once('../configs/axenia/config.php');
require_once('util.php');
require_once('AbstractDao.php');
require_once('BotDao.php');
require_once('Axenia.php');
require_once('Request.php');
$content = file_get_contents("php://input");
$update = json_decode($content, true);


if (!$update) {
    exit;
}

if (isset($update["message"])) {
    Request::setUrl(API_URL);
    $bot = new Axenia(new BotDao());
    $bot->processMessage($update["message"]);
}

?>