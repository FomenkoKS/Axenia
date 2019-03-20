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
    Request::sendMessage(LOG_CHAT_ID, $error);
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
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $key = 'from:' . $update['message']['from']['id'];

        if (isset($update['message']['text']) && !isset($update['message']['forward_from'])) $redis->incr($key);
        $count = $redis->get($key);
        if ($count == 1 || $redis->pttl($key) == -1) $redis->expire($key, 10);
        if ($count > 20) $redis->expire($key, $count);
        if(isset($update['callback_query'])){
            handle($update);
        } else {
            if($count < 7){
                handle($update);
            }
//            else if($count==7){
//                Request::setUrl(API_URL);
//                Request::sendMessage(LOG_CHAT_ID, 'Spam detected from:' . $update['message']['from']['id'] . '(@' . $update['message']['from']['username'] . ') chat:@' . $update['message']['chat']['username'] . ' ttl:' . $redis->pttl($key));
//            }
        }
        $redis->close();

    } catch (Exception $e) {
        redis_error($e);
    }
}