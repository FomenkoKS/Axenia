<?php
require_once('config.php');
require_once('../axenia/functions.php');
require_once('../axenia/commands.php');
$content = file_get_contents("php://input");
$update = json_decode($content, true);
if (!$update) {
    exit;
}
if (isset($update["message"])) {
    processMessage($update["message"]);
}

function processMessage($message)
{
    $chat_id = $message['chat']['id'];
    $from_id = $message['from']['id'];
    AddUser($from_id, $message['from']['username'], $message['from']['first_name'], $message['from']['last_name']);

    if (isset($message['text'])) {
        $text = str_replace("@" . BOT_NAME, "", $message['text']);
        switch (true) {
            case preg_match('/^\/top/ui', $text, $matches):
            case preg_match('/^\/Stats/ui', $text, $matches):
                $query = "select u.username, u.firstname, u.lastname, k.level from Karma k, Users u where k.user_id=u.id and k.chat_id=" . $chat_id . " order by level desc limit 5";
                $out = "<b>Самые почётные люди чата \"". GetGroupName($chat_id)."\":</b>\r\n";
                $a = array_chunk(Query2DB($query), 4);
                foreach ($a as $value) {
                    $out .= ($value[0] == "") ? $value[1] . " " . $value[2] : $value[0];
                    $out .= " (" . $value[3] . ")\r\n";
                }
                $out .= "<a href='" . PATH_TO_SITE . "?group_id=" . $chat_id . "'>Подробнее</a>";
                apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $out, "parse_mode" => "HTML", "disable_web_page_preview"=>true));

                break;
            case preg_match('/^(\+|\-|👍|👎) ?([\s\S]+)?/ui', $text, $matches):
                ($matches[1] == "+" || $matches[1] == "👍") ? $level = "+" : $level = "-";

                if (isset($message['reply_to_message'])) {

                    $reply = $message['reply_to_message'];
                    AddUser($reply['from']['id'], $reply['from']['username'], $reply['from']['first_name'], $reply['from']['last_name']);

                    if ($reply['from']['username'] != BOT_NAME) {
                        apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));
                        $output = HandleKarma($level, $from_id, $reply['from']['id'], $chat_id);
                        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $output, "parse_mode" => "HTML", "reply_to_message_id"=>$message, "disable_web_page_preview"=>true));
                    }
                } else {
                    if (preg_match('/@([\w]+)/ui', $matches[2], $user)) {
                        $to = GetUserID($user[1]);
                        $to ? $output = HandleKarma($level, $from_id, $to, $chat_id) : $output = "Я его не знаю, считать карму не буду";
                        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $output, "parse_mode" => "HTML", "disable_web_page_preview"=>true));
                    }

                }
                break;
        }

        if (($from_id == 32512143 || $from_id == 5492881) && preg_match('/^(\/nash) ([\s\S]+)/ui', $text, $matches)) {
            apiRequest("sendChatAction", array('chat_id' => -1001016901471, "action" => "typing"));
            apiRequest("sendMessage", array('chat_id' => -1001016901471, "text" => $matches[2], "message_id" => "Markdown"));
        }
    }

    if (isset($message['new_chat_participant'])) {
        if ($message['new_chat_participant']['username'] == BOT_NAME) {
            $chat = $message['chat'];
            $output = AddChat($chat_id, $chat['title'], $chat['type']);
            if ($output !== false) {
                apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $output, "parse_mode" => "Markdown"));
            }
        }
    }
}

?>