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
    try {
        $bot->processMessage($update["message"]);
    } catch (Exception $e) {
        if (defined('LOG_CHAT_ID')) {
            $message = $update["message"];
            $chat = $message['chat'];
            $from = $message['from'];
            $errorMsg = "<b>Caught Exception!</b>\n";
            $temp = "On message of user :uName [<i>:uid</i>] in group ':cName' [<i>:cid</i>]\n";
            $errorMsg .= Util::insert($temp,
                array('uid' => $from['id'],
                    'uName' => Util::getFullNameUser($from),
                    'cid' => $chat['id'],
                    'cName' => $chat['type'] == 'private' ? Util::getFullNameUser($chat) : $chat['title'])
            );
            $errorMsg .= Util::insert("<i><b>Error message:</b></i> <code>:0</code>\n<i><b>Error description:</b></i>\n<pre>:1</pre>", array($e->getMessage(), $e));
            Request::sendHtmlMessage($chat['id'], $errorMsg);
        } else {
            throw $e;
        }
    }
}

if (isset($update["inline_query"])) {
    Request::setUrl(API_URL);
    $bot = new Axenia(new BotService(new BotDao()));
    $bot->processInline($update["inline_query"]);
}

?>