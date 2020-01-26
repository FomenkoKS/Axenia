<?php
require_once('../configs/axenia/config.php');

require_once('core/util.php');
require_once('core/AbstractDao.php');
require_once('core/Request.php');

require_once('locale/Lang.php');

require_once('logic/BotDao.php');
require_once('logic/BotService.php');
require_once('logic/BotRedis.php');
require_once('logic/Axenia.php');
require_once('logic/ShortUrl.php');

$content = file_get_contents('php://input');
$update = json_decode($content, true);

function redis_error($error)
{
    Request::setUrl(API_URL);
    Request::sendMessage(LOG_CHAT_ID, 'Redis: '.$error);
    throw new error($error);
}

function handle($update){
    Request::setUrl(API_URL);
    $bot = new Axenia(new BotService(new BotDao()));
    $bot->handleUpdate($update);
}

if (!$update) {
    exit;
} else {
    try {
        handle($update);
    } catch (Exception $e) {
        handle($update);
    }
}