<?php
require_once('../configs/axenia/config.php');

require_once('core/util.php');
require_once('core/AbstractDao.php');
require_once('core/Request.php');

require_once('locale/Lang.php');

require_once('BotDao.php');
require_once('BotService.php');
require_once('Axenia.php');

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