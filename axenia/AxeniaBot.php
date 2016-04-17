<?php
require_once('../core/DB.php');
require_once('../core/Bot.php');

class AxeniaBot extends Bot
{
    protected $db;

    /**
     * AxeniaBot constructor.
     * @param $apiUrl
     * @param $mUser
     * @param $mPass
     * @param $mDB
     */
    public function __construct($apiUrl, $mUser, $mPass, $mDB)
    {
        parent::__construct($apiUrl);
        $this->db = new AxeniaLogic($mUser, $mPass, $mDB);
    }

    public function processMessage($message)
    {
        $chat_id = $message['chat']['id'];
        $from_id = $message['from']['id'];
        $this->db->AddUser($from_id, $message['from']['username'], $message['from']['first_name'], $message['from']['last_name']);
        if (isset($message['text'])) {
            $text = str_replace("@" . BOT_NAME, "", $message['text']);
            switch (true) {
                case preg_match('/^(\/set) @([\w]+) (\d+)/ui ', $text, $matches):
                    $this->setCommand($from_id, $chat_id, $matches);
                    break;
                case preg_match('/^\/PenisLength/ui', $text, $matches):
                case preg_match('/^\/top/ui', $text, $matches):
                case preg_match('/^\/Stats/ui', $text, $matches):
                    $this->topCommand($chat_id);
                    break;
                case preg_match('/^(\+|\-|👍|👎) ?([\s\S]+)?/ui', $text, $matches):
                    $this->countCarmaCommand($from_id, $chat_id, $matches);
                    break;
                case preg_match('/сис(ек|ьки|ечки|и|яндры)/ui', $text, $matches):
                    $this->apiRequest("forwardMessage", array('chat_id' => $chat_id, "from_chat_id" => "@superboobs", "message_id" => rand(1, 2700)));
                    break;
            }

            if (($from_id == 32512143 || $from_id == 5492881) && preg_match('/^(\/nash) ([\s\S]+)/ui', $text, $matches)) {
                $this->apiRequest("sendChatAction", array('chat_id' => -1001016901471, "action" => "typing"));
                $this->apiRequest("sendMessage", array('chat_id' => -1001016901471, "text" => $matches[2], "message_id" => "Markdown"));
            }

        }
        if (isset($message['new_chat_member'])) {
            if ($message['new_chat_member']['username'] == BOT_NAME) {
                $chat = $message['chat'];
                $output = $this->db->AddChat($chat_id, $chat['title'], $chat['type']);
                if ($output !== false) {
                    $this->apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));
                    $this->apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $output, "parse_mode" => "Markdown"));
                }
            } else {
                $this->db->AddUser($message['new_chat_member']['id'], $message['new_chat_member']['username'], $message['new_chat_member']['first_name'], $message['new_chat_member']['last_name']);
            }
        }


        if (isset($message['sticker'])) {
            //обработка получения стикеров
        }
    }

    public function setCommand($from_id, $chat_id, $matches)
    {
        if ($from_id == "32512143") {
            $user_id = GetUserID($matches[2]);
            if ($this->db->setUserLevel($user_id, $chat_id, $matches[3])) {
                $this->apiRequest("sendMessage", array('chat_id' => $from_id, "text" => "У " . $matches[2] . " (" . $user_id . ") в чате " . $chat_id . " карма " . $matches[3]));
            }
        }
    }

    public function countCarmaCommand($from_id, $chat_id, $matches)
    {
        ($matches[1] == "+" || $matches[1] == "👍") ? $level = "+" : $level = "-";

        if (isset($message['reply_to_message'])) {
            $reply = $message['reply_to_message'];
            $this->db->AddUser($reply['from']['id'], $reply['from']['username'], $reply['from']['first_name'], $reply['from']['last_name']);

            if ($reply['from']['username'] != BOT_NAME) {
                $this->apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));
                $output = $this->db->HandleKarma($level, $from_id, $reply['from']['id'], $chat_id);
                $this->apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $output, "parse_mode" => "HTML", "disable_web_page_preview" => true));
            }
        } else {
            if (preg_match('/@([\w]+)/ui', $matches[2], $user)) {
                $to = $this->db->GetUserID($user[1]);
                $to ? $output = $this->db->HandleKarma($level, $from_id, $to, $chat_id) : $output = "Я его не знаю, считать карму не буду";
                $this->apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $output, "parse_mode" => "HTML", "disable_web_page_preview" => true));
            }

        }
    }

    public function topCommand($chat_id)
    {
        $this->apiRequest("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));
        $out = "<b>Самые длинные кармописюны:</b>\r\n";
        $a = array_chunk($this->db->getCarmaList($chat_id), 4);
        foreach ($a as $value) {
            $out .= ($value[0] == "") ? $value[1] . " " . $value[2] : $value[0];
            $out .= " (" . $value[3] . " см)\r\n";
        }
        $out .= "<a href='" . PATH_TO_SITE . "?group_id=" . $chat_id . "'>Подробнее</a>";

        $this->apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $out, "parse_mode" => "HTML", "disable_web_page_preview" => true));
    }

}


?>