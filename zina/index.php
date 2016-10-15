<?php
require_once('../configs/zina/config.php');

require_once('../axenia/core/util.php');
require_once('../axenia/core/AbstractDao.php');
require_once('../axenia/core/Request.php');

require_once('../axenia/locale/Lang.php');

require_once('../axenia/logic/BotDao.php');
require_once('../axenia/logic/BotService.php');
require_once('../axenia/logic/Axenia.php');

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    exit;
} else {
    Request::setUrl(API_URL);
    $bot = new Axenia(new BotService(new BotDao()));
    $bot->handleUpdate($update);
}
