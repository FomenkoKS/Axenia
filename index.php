<?php
require_once('config.php');
require_once('functions.php');
require_once('commands.php');
$content = file_get_contents("php://input");
$update = json_decode($content, true);
if (!$update) {
    // receive wrong update, must not happen
    exit;
}
file_put_contents("array.txt", print_r($update, true));
if (isset($update["message"])) {
    processMessage($update["message"]);
}

/*
if (isset($update["inline_query"])) {
    processInline($update["inline_query"]["query"], $update["inline_query"]["id"]);
}*/
function processMessage($message)
{
    // process incoming message
    file_put_contents("array.txt",print_r($message,true));
    //$message_id = $message['message_id'];
    $chat_id = $message['chat']['id'];
    $from_id = $message['from']['id'];
    AddUser($from_id, $message['from']['username'], $message['from']['first_name'], $message['from']['last_name']);

    if (isset($message['text'])) {
        $text = str_replace("@" . BOT_NAME, "", $message['text']);
        switch (true) {
            case preg_match('/^(\+|\-|👍|👎) ?([\s\S]+)?/ui', $text, $matches):
                ($matches[1]=="+" || $matches[1]=="👍") ? $level="+" : $level="-";
                if (isset($message['reply_to_message'])) {
                    $reply = $message['reply_to_message'];
                    apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));

                    $output=HandleKarma($level,$from_id,$reply['from']['id'],$chat_id);
                    apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $output, "parse_mode"=>"Markdown"));
                } else {
                    if(preg_match('/@([\s\S]+)/ui', $matches[2], $user)){
                        $to=GetUserID($user[1]);
                        $to ? $output=HandleKarma($level,$from_id,$to,$chat_id):$output="Я его не знаю, считать карму не буду";
                        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $output, "parse_mode"=>"Markdown"));
                    }

                }
                break;
        }

        if (isset($message['reply_to_message'])) {
            /*$text = str_replace("@" . BOT_NAME, "", $message['text']);
            $reply = $message['reply_to_message'];
            if ($reply['from']['username'] == BOT_NAME) {

            }*/
        }
    }
    if (isset($message['new_chat_participant'])) {
        if ($message['new_chat_participant']['username'] == BOT_NAME) {
            $chat = $message['chat'];
            $output = AddChat($chat_id, $chat['title'], $chat['type']);
            if ($output !== false) {
                apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $output, "parse_mode"=>"Markdown"));
            }
        }
    }
}

?>