<?php
require_once('../configs/zina/config.php');

require_once('../axenia/core/util.php');
require_once('../axenia/core/AbstractDao.php');
require_once('../axenia/core/Request.php');

require_once('../axenia/locale/Lang.php');

require_once('../axenia/BotDao.php');
require_once('../axenia/BotService.php');
require_once('../axenia/Axenia.php');

$content = file_get_contents("php://input");
$update = json_decode($content, true);

$db_conn = null;

if (!$update) {
    exit;
} else {
    file_put_contents("array.txt", print_r($update, true));
    Request::setUrl(API_URL);
    $db_conn = new BotDao();
    $bot = new Axenia(new BotService($db_conn));
}

if (isset($update["message"])) {
    try {
        $bot->processMessage($update["message"]);
    } catch (Exception $e) {
        $db_conn->disconnect();
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
            Request::sendHtmlMessage(LOG_CHAT_ID, $errorMsg);
        } else {
            throw $e;
        }
    }
}

if (isset($update["inline_query"])) {
    try {
        $bot->processInline($update["inline_query"]);
    } catch (Exception $e) {
        $db_conn->disconnect();
    }
}

if (isset($update["callback_query"])) {
    try {
        $chat_id = $update["callback_query"]["message"]["chat"]["id"];
        $bot->processCallback($update["callback_query"]);
    } catch (Exception $e) {
        $db_conn->disconnect();
    }
}
?>