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
require_once('logic/Redis.php');

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    exit;
} else {
    $redis=new Redis();
    $redis->connect('127.0.0.1', 6379, 2.5);
    function redis_error($error) {
        Request::sendMessage(LOG_CHAT_ID,$error);
        throw new error($error);
    }
    $key="from:".$update['message']['from']['id'];

    file_put_contents("1",print_r($update,true));

    if(isset($update['message']['text']))$redis->incr($key);
    $count=$redis->get($key);
    if($count==1) $redis->expire($key,10);
    if($count<7 || isset($update['callback_query'])){
        Request::setUrl(API_URL);
        $bot = new Axenia(new BotService(new BotDao()));
        $bot->handleUpdate($update);
    }
    $redis->close();
}


