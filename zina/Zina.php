<?php

class Zina
{

    private $service;

    /**
     * Axenia constructor.
     * @param $service BotService
     */
    public function __construct($service)
    {
        $this->service = $service;
    }


    public function processMessage($message)
    {
        $message_id = $message['message_id'];
        $chat = $message['chat'];
        $from = $message['from'];

        $chat_id = $chat['id'];
        $from_id = $from['id'];

        $this->service->insertOrUpdateUser($from);
        $this->service->initLang($chat_id, $chat['type']);

        if (isset($message['text']) || isset($message['sticker'])) {
            if (isset($message['sticker'])) {
                $text = $message['sticker']['emoji'];
            } else {
                $text = str_replace("@" . BOT_NAME, "", $message['text']);
            }
            switch (true) {
                case preg_match('/^\/lang/ui', $text, $matches):
                    $this->sendLanguageKeyboard($chat_id, $message_id);
                    break;

                case (($pos = array_search($text, Lang::$availableLangs)) !== false):
                    Request::sendTyping($chat_id);
                    $qrez = $this->service->setLang($chat_id, $chat['type'], $pos);
                    $replyKeyboardHide = array("hide_keyboard" => true, "selective" => true);
                    $text = Lang::message('bot.error');
                    if ($qrez) {
                        Lang::init($pos);
                        $text = Lang::message('chat.lang.end');
                    }
                    Request::sendMessage($chat_id, $text, array("reply_to_message_id" => $message_id, "reply_markup" => $replyKeyboardHide));
                    sleep(1);
                    if ($chat['type'] == "private") {
                        Request::sendHtmlMessage($chat_id, Lang::message('user.pickChat', array('botName' => BOT_NAME)));
                    }
                    break;

                case (preg_match('/^\/start/ui', $text, $matches) and $chat['type'] == "private"):
                    Request::sendTyping($chat_id);
                    Request::sendHtmlMessage($chat_id, Lang::message('chat.greetings2'));

                    $this->sendLanguageKeyboard($chat_id, $message_id);
                    break;

                case (preg_match('/^\/start/ui', $text, $matches) and $chat['type'] != "private"):
                    $this->service->rememberChat($chat_id, $chat['title'], $chat['type'], $from_id);
                    break;

                case preg_match('/^\/top/ui', $text, $matches):
                case preg_match('/^\/Stats/ui', $text, $matches):
                    Request::sendTyping($chat_id);

                    $out = Lang::message('karma.top.title2', array("chatName" => $this->service->getGroupName($chat_id)));
                    $top = $this->service->getTop($chat_id, 5);
                    $a = array_chunk($top, 4);
                    foreach ($a as $value) {
                        $username = ($value[0] == "") ? $value[1] . " " . $value[2] : $value[0];
                        $out .= Lang::message('karma.top.row2', array("username" => $username, "karma" => $value[3]));
                    }
                    $out .= Lang::message('karma.top.footer', array("pathToSite" => PATH_TO_SITE, "chatId" => $chat_id));

                    Request::sendHtmlMessage($chat_id, $out);
                    break;

                case preg_match('/^(\+|\-|👍|👎) ?([\s\S]+)?/ui', $text, $matches):
                    $isRise = Util::isInEnum("+,👍", $matches[1]);

                    if (isset($message['reply_to_message'])) {
                        $replyUser = $message['reply_to_message']['from'];
                        $this->service->insertOrUpdateUser($replyUser);

                        if ($replyUser['username'] != BOT_NAME) {
                            Request::sendTyping($chat_id);
                            $output = $this->service->handleKarma($isRise, $from_id, $replyUser['id'], $chat_id);
                            Request::sendHtmlMessage($chat_id, $output);
                        }
                    } else {
                        if (preg_match('/@([\w]+)/ui', $matches[2], $user)) {
                            $to = $this->service->getUserID($user[1]);
                            if ($to) {
                                Request::sendHtmlMessage($chat_id, $this->service->handleKarma($isRise, $from_id, $to, $chat_id));
                            } else {
                                Request::sendHtmlMessage($chat_id, Lang::message('karma.unknownUser'), array('reply_to_message_id' => $message_id));
                            }
                        }

                    }
                    break;
            }
        }

        if (isset($message['new_chat_member'])) {
            $newMember = $message['new_chat_member'];
            if (BOT_NAME == $newMember['username']) {
                $qrez = $this->service->rememberChat($chat_id, $chat['title'], $chat['type'], $from_id);
                if ($qrez !== false) {
                    Request::sendTyping($chat_id);
                    Request::sendMessage($chat_id, Lang::message('chat.greetings2'), array("parse_mode" => "Markdown"));
                }
            } else {
                $this->service->insertOrUpdateUser($newMember);
            }
        }

        if (isset($message['new_chat_title'])) {
            $this->service->rememberChat($chat_id, $message['new_chat_title'], $chat['type'], $from_id);
        }

        if (isset($message['left_chat_member'])) {
            //не видит себя когда его удаляют из чата
            $member = $message['left_chat_member'];
            if (BOT_NAME == $member['username']) {
                $this->service->deleteChat($chat_id);
            }
        }
    }

    public function sendLanguageKeyboard($chat_id, $reply_to_message_id)
    {
        $array = array_values(Lang::$availableLangs);
        $replyKeyboardMarkup = array("keyboard" => array($array), "resize_keyboard" => true, "selective" => true, "one_time_keyboard" => true);
        $text = Lang::message('chat.lang.start', array("langs" => Util::arrayInColumn($array)));
        Request::sendMessage($chat_id, $text, array("reply_to_message_id" => $reply_to_message_id, "reply_markup" => $replyKeyboardMarkup));
    }

}

?>