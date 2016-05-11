<?php
require_once('../configs/format_fm/config.php');

require_once('core/util.php');
require_once('core/AbstractDao.php');
require_once('core/Request.php');

require_once('locale/Lang.php');

require_once('BotDao.php');
require_once('Axenia.php');

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