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
    $redis=new Redis();
    function redis_error($error) {
        Request::sendMessage(LOG_CHAT_ID,$error);
        throw new error($error);
    }
    $redis->connect('127.0.0.1', 6379);
    $key="from:".$update['message']['from']['id'];
    file_put_contents("1",print_r($update,true));
    if(isset($update['message']['text']) && !isset($update['message']['forward_from']))$redis->incr($key);
    $count=$redis->get($key);
    if($count==1 || $redis->pttl($key)==-1) $redis->expire($key,10);
    if($count>20) $redis->expire($key,$count);
    if(($count<7 || isset($update['callback_query'])) && ($update['message']['from']['username']!=BOT_NAME)){
        Request::setUrl(API_URL);
        $bot = new Axenia(new BotService(new BotDao()));
        $bot->handleUpdate($update);
        if(!($count<7 || isset($update['callback_query']))) Request::sendMessage(LOG_CHAT_ID,"Spam count: ". $count." from:".$update['message']['from']['id']."(@".$update['message']['from']['username'].") chat:@".$update['message']['chat']['username']." ttl:".$redis->pttl($key));

    }
    $redis->close();
}

