<?php
require_once('functions.php');
require_once('commands.php');
function processMessage($message)
{
    $message_id = $message['message_id'];
    $chat_id = $message['chat']['id'];
    $from_id = $message['from']['id'];
    AddUser($from_id, $message['from']['username'], $message['from']['first_name'], $message['from']['last_name']);

    if (isset($message['text'])) {
        $text = str_replace("@" . BOT_NAME, "", $message['text']);
        switch (true) {
            case preg_match('/^(\/set) @([\w]+) (\d.+)/ui ', $text, $matches):
                if ($from_id == "32512143") if (SetCarma($chat_id, GetUserID($matches[2]), $matches[3])) apiRequest("sendMessage", array('chat_id' => $from_id, "text" => "У " . $matches[2] . " (" . GetUserID($matches[2]) . ") в чате " . $chat_id . " карма " . $matches[3]));
                break;
            case preg_match('/^\/ping/ui ', $text, $matches):
                $photos = apiRequest("getUserProfilePhotos", array('user_id' => $from_id));
                $photo_id = $photos['photos'][0][0]['file_id'];
                $photo = apiRequest("getFile", array('file_id' => $photo_id));
                $photo_file = file_get_contents('https://api.telegram.org/file/bot' . BOT_TOKEN . '/' . $photo['file_path']);
                $f = fopen('photos/' . $photo_id, 'wb');
                fwrite($f, $photo_file);
                apiRequest("sendPhoto", array('chat_id' => $chat_id, "photo" =>  $photo_id ));
                break;
            case preg_match('/^\/PenisLength/ui', $text, $matches):
            case preg_match('/^\/top/ui', $text, $matches):
            case preg_match('/^\/Stats/ui', $text, $matches):
                $query = "select u.username, u.firstname, u.lastname, k.level from Karma k, Users u where k.user_id=u.id and k.chat_id=" . $chat_id . " order by level desc limit 5";
                $out = "<b>Самые длинные кармописюны:</b>\r\n";
                $a = array_chunk(Query2DB($query), 4);
                foreach ($a as $value) {
                    $out .= ($value[0] == "") ? $value[1] . " " . $value[2] : $value[0];
                    $out .= " (" . $value[3] . " см)\r\n";
                }
                $out.="<a href='".PATH_TO_SITE."?group_id=".$chat_id."'>Подробнее</a>";
                apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $out, "parse_mode" => "HTML"));

                break;
            case preg_match('/ок\?? ?ок/ui', $text, $matches):
                apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Ок. " . Punish($from_id, $chat_id), "parse_mode" => "HTML"));
                break;
            case preg_match('/([х|x|h|?][е|e]\W?){2}/ui', $text, $matches):
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Вот тебе и хехе. " . Punish($from_id, $chat_id), "parse_mode" => "HTML"));
                break;
            case preg_match('/б[о|а]?([\s\S+]?ян/ui', $text, $matches):
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Твоя жизнь - боян. " . Punish($from_id, $chat_id), "parse_mode" => "HTML"));
                break;
            case preg_match('/^(\+|\-|?|?) ?([\s\S]+)?/ui', $text, $matches):
                ($matches[1] == "+" || $matches[1] == "?") ? $level = "+" : $level = "-";

                if (isset($message['reply_to_message'])) {
                    $reply = $message['reply_to_message'];
                    AddUser($reply['from']['id'], $reply['from']['username'], $reply['from']['first_name'], $reply['from']['last_name']);

                    if ($reply['from']['username'] != BOT_NAME) {
                        apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));
                        $output = HandleKarma($level, $from_id, $reply['from']['id'], $chat_id);
                        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $output, "parse_mode" => "HTML"));
                    }
                } else {
                    if (preg_match('/@([\w]+)/ui', $matches[2], $user)) {
                        $to = GetUserID($user[1]);
                        $to ? $output = HandleKarma($level, $from_id, $to, $chat_id) : $output = "Я его не знаю, считать карму не буду";
                        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $output, "parse_mode" => "HTML"));
                    }
                }
                break;
            case preg_match('/сис(ек|ьки|ечки|и|яндры)/ui', $text, $matches):
                apiRequest("forwardMessage", array('chat_id' => $chat_id, "from_chat_id" => "@BoobsChannel", "message_id" => rand(1, 1240)));

                break;
        }

        if (($from_id == 32512143 || $from_id == 5492881) && preg_match('/^(\/nash) ([\s\S]+)/ui', $text, $matches)) {
            apiRequest("sendChatAction", array('chat_id' => -1001016901471, "action" => "typing"));
            apiRequest("sendMessage", array('chat_id' => -1001016901471, "text" => $matches[2], "message_id" => "Markdown"));
        }

    }
    if (isset($message['new_chat_member'])) {
        if ($message['new_chat_member']['username'] == BOT_NAME) {
            $chat = $message['chat'];
            $output = AddChat($chat_id, $chat['title'], $chat['type']);
            if ($output !== false) {
                apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $output, "parse_mode" => "Markdown"));
            }
        }else AddUser($message['new_chat_member']['id'], $message['new_chat_member']['username'], $message['new_chat_member']['first_name'], $message['new_chat_member']['last_name']);

    }
}