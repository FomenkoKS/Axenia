<?php
require_once('config.php');

require_once('core/util.php');
require_once('core/AbstractDao.php');
require_once('core/Request.php');

require_once('locale/Lang.php');

require_once('logic/BotDao.php');
require_once('logic/BotService.php');
require_once('logic/Axenia.php');

ini_set('always_populate_raw_post_data','-1');
$content = file_get_contents('php://input');
$update = json_decode($content, true);

function handle($update){
    Request::setUrl(API_URL);
    $bot = new Axenia(new BotService(new BotDao()));
    $bot->handleUpdate($update);
}

if (isset($update)) {
    handle($update);
}