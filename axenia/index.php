<?php
require_once('../configs/format/config.php');

require_once('core/util.php');
require_once('core/AbstractDao.php');
require_once('core/Request.php');

require_once('locale/Lang.php');

require_once('logic/BotDao.php');
require_once('logic/BotService.php');
require_once('logic/Axenia.php');
require_once('logic/ShortUrl.php');

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    exit;
} else {
    // save incoming requset
    //file_put_contents("array.txt", print_r($update, true));
    Request::setUrl(API_URL);
    $bot = new Axenia(new BotService(new BotDao()));

    $bot->handleUpdate($update);
}


