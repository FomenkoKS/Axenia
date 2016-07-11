<?php
require_once('../configs/zina/config.php');

require_once('../axenia/core/util.php');
require_once('../axenia/core/AbstractDao.php');
require_once('../axenia/core/Request.php');

require_once('../axenia/locale/Lang.php');

require_once('../axenia/BotDao.php');
require_once('../axenia/BotService.php');
require_once('../axenia/Axenia.php');

$content = file_get_contents("php://input");
$update = json_decode($content, true);


if (!$update) {
    exit;
}

if (isset($update["message"])) {
    Request::setUrl(API_URL);
    $bot = new Axenia(new BotService(new BotDao()));
    $bot->processMessage($update["message"]);
}

if (isset($update["inline_query"])) {
    Request::setUrl(API_URL);
    $bot = new Axenia(new BotService(new BotDao()));
    $bot->processInline($update["inline_query"]);
}

?>