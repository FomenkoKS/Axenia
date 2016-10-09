<?php

class Axenia
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
                case preg_match('/^(\/set) @([\w]+) (-?\d+)/ui ', $text, $matches):
                    if (Util::isInEnum(ADMIN_IDS, $from_id)) {
                        Request::sendMessage($from_id, $this->service->setLevelByUsername($matches[2], $chat_id, $matches[3]));
                    }
                    break;

                case preg_match('/^\/cleanDB((?=@' . BOT_NAME . ')|$)/ui ', $text, $matches):
                    if (Util::isInEnum(ADMIN_IDS, $from_id)) {
                        if ($groups_id = $this->service->getGroupsMistakes()) {
                            foreach ($groups_id as $id) {
                                //Request::sendMessage($from_id,$id);
                                $chat = Request::getChat($id);
                                if ($chat !== false) {
                                    $this->service->rememberChat($chat, $from_id);
                                    //Request::sendMessage($from_id,$id);
                                } else {
                                    $this->service->deleteChat($id);
                                }
                            }
                        }
                        if (defined('LOG_CHAT_ID')) {
                            Request::sendMessage(LOG_CHAT_ID, "The DB cleaning is completed.");
                        }
                    }
                    break;

                case preg_match('/^\/lang((?=@' . BOT_NAME . ')|$)/ui', $text, $matches):
                    if ($this->service->isAdmin($from_id, $chat_id) || $chat['type'] == "private") {
                        $this->sendLanguageKeyboard($chat_id);
                    }
                    break;

                /*case preg_match('/^\/getAdmins/ui', $text, $matches):
                    Request::sendMessage($chat_id, $this->service->isAdmin($from_id, $chat_id));
                    $admins = Request::getChatAdministrators($chat_id);
                    Request::sendMessage($chat_id, $admins);
                    //if(in_array($from_id,$admins['user']['id'])) Request::sendMessage($chat_id, "success");
                    break;*/

                case (preg_match('/^\/start((?=@' . BOT_NAME . ')|$)/ui', $text, $matches)):
                    if ($chat['type'] == "private") {
                        Request::sendTyping($chat_id);
                        Request::sendHtmlMessage($chat_id, Lang::message('chat.greetings'));
                        $this->sendLanguageKeyboard($chat_id, $message_id);
                    } else {
                        $this->service->rememberChat($chat, $from_id);
                    }

                    break;
                case preg_match('/^\/buy((?=@' . BOT_NAME . ')|$)/ui', $text, $matches):
                    Request::sendTyping($chat_id);
                    $this->sendStore($chat_id, $from);
                    break;
                case preg_match('/^\/top((?=@' . BOT_NAME . ')|$)/ui', $text, $matches):
                    Request::sendTyping($chat_id);
                    if ($chat['type'] == "private") {
                        Request::sendMessage($chat_id, Lang::message("karma.top.private"));
                    } else {
                        $out = $this->service->getTop($chat_id, 5);
                        Request::sendHtmlMessage($chat_id, $out);
                    }
                    break;

                case preg_match('/^\/my_stats((?=@' . BOT_NAME . ')|$)/ui', $text, $matches):
                    Request::sendTyping($chat_id);
                    Request::sendHtmlMessage($chat_id, $this->service->getStats($from_id, $chat['type'] == "private" ? NULL : $chat_id), ['reply_to_message_id' => $message_id]);
                    break;

                case preg_match('/^(\+|\-|👍|👎) ?([\s\S]+)?/ui', $text, $matches):
                    $isRise = Util::isInEnum("+,👍", $matches[1]);

                    if (isset($message['reply_to_message'])) {
                        $replyUser = $message['reply_to_message']['from'];
                        $this->service->insertOrUpdateUser($replyUser);

                        if ($replyUser['username'] != BOT_NAME) {
                            $user_id = $replyUser['id'];
                            Request::sendTyping($chat_id);
                            $this->doKarmaAction($isRise, $from_id, $user_id, $chat_id);
                        }
                    } else {
                        if (preg_match('/@([\w]+)/ui', $matches[2], $user)) {
                            $to = $this->service->getUserID($user[1]);
                            if ($to) {
                                $this->doKarmaAction($isRise, $from_id, $to, $chat_id);
                            } else {
                                Request::sendHtmlMessage($chat_id, Lang::message('karma.unknownUser'), array('reply_to_message_id' => $message_id));
                            }
                        }

                    }
                    break;
                case preg_match('/tits|(сис(ек|ьки|ечки|и|яндры))/ui', $text, $matches):
                    if (Lang::isUncensored()) {
                        $tits = json_decode(file_get_contents("http://api.oboobs.ru/boobs/1/1/random"), true);
                        $karma = $this->service->getUserLevel($from_id, $chat_id);
                        $username = $this->service->getUserName($from_id);
                        $newKarma = $karma - 30;
                        if ($newKarma > 0) {
                            Request::sendTyping($chat_id);
                            Request::sendPhoto($chat_id, "http://media.oboobs.ru/boobs/" . sprintf("%05d", $tits[0]['id']) . ".jpg", ["caption" => $username . " подогнал эти сиськи за свои 30 кармы"]);
                            $this->service->setLevel($from_id, $chat_id, $newKarma);
                        }
                    }
                    break;
                case preg_match('/^(\/nash) ([\s\S]+)/ui', $text, $matches):
                    if (Util::isInEnum(ADMIN_IDS, $from_id)) {
                        Request::sendTyping(NASH_CHAT_ID);
                        sleep(1);
                        Request::sendMessage(NASH_CHAT_ID, $matches[2]);
                    }
                    break;
            }
        }

        if (isset($message['new_chat_member'])) {
            $newMember = $message['new_chat_member'];
            if (BOT_NAME == $newMember['username']) {
                $qrez = $this->service->rememberChat($chat, $from_id);
                if ($qrez !== false) {
                    if (defined('LOG_CHAT_ID')) {
                        Request::sendMessage(LOG_CHAT_ID, "Enter in @" . $chat["username"]);
                    }
                    Request::sendTyping($chat_id);
                    Request::sendMessage($chat_id, Lang::message('chat.greetings'), array("parse_mode" => "Markdown"));
                }
            } else {
                $this->service->insertOrUpdateUser($newMember);
            }
        }

        if (isset($message['new_chat_title'])) {
            $this->service->rememberChat($chat, $from_id);
        }

        if (isset($message['left_chat_member'])) {
            $member = $message['left_chat_member'];
            if (BOT_NAME == $member['username']) {
                if (defined('LOG_CHAT_ID')) {
                    Request::sendMessage(LOG_CHAT_ID, $member['username'] . " leave chat @" . $chat["username"]);
                }
                $this->service->deleteChat($chat_id);
            }
        }
    }

    public function sendLanguageKeyboard($chat_id, $message_id = NULL, $text = NULL)
    {
        if ($message_id == NULL && $text == NULL) {
            $ln = Lang::$availableLangs;
            $keys = array_keys($ln);
            $values = array_values($ln);
            $inline_keyboard = array();
            for ($i = 0; $i < count($ln); $i++) {
                $inline_keyboard[$i]['text'] = $values[$i];
                $inline_keyboard[$i]['callback_data'] = $keys[$i];
            }
            if ($chat_id < 0) $text = Lang::message('chat.lang.foradmins');
            $inline_keyboard = array($inline_keyboard);
            Request::sendMessage($chat_id, $text . Lang::message('chat.lang.start'), ["reply_markup" => ['inline_keyboard' => $inline_keyboard]]);
        } else {
            Request::editMessageText($chat_id, $message_id, $text);
        }

    }

    public function doKarmaAction($isRise, $from_id, $user_id, $chat_id)
    {
        $out = $this->service->handleKarma($isRise, $from_id, $user_id, $chat_id);
        if ($out['good'] == true) {
            Request::sendHtmlMessage($chat_id, $out['msg']);
            if ($out['newLevel'] != null) {
                $rewardMessages = $this->service->handleRewards($out['newLevel'], $chat_id, $user_id);
                if (count($rewardMessages) > 0) {
                    foreach ($rewardMessages as $msg) {
                        Request::sendHtmlMessage($chat_id, $msg);
                    }
                }
            }
        } else {
            Request::sendHtmlMessage($chat_id, $out['msg']);
        }
    }

    public function processInline($inline)
    {
        $id = $inline['id'];
        $from = $inline['from'];
        $query = $inline['query'];

        if (Util::isInEnum(ADMIN_IDS, $from['id'])) {
            if (isset($query) && $query !== "") {
                $users = $this->service->getUserList($query);

                if ($users) {
                    Request::answerInlineQuery($id, $users);
                } else {
                    Request::answerInlineQuery($id, [
                        [
                            "type" => "article",
                            "id" => "0",
                            "title" => Lang::message('chat.greetings'),
                            "message_text" => Lang::message('chat.greetings')
                        ]
                    ]);
                }
            }
        }
    }

    public function sendStore($chat_id, $from = NULL, $message = NULL, $text = NULL, $callback = NULL)
    {
        $message_id = $message['message_id'];
        $message_text = $message['text'];
        $button_list[] = [
            [
                'text' => Lang::message('store.button.buy_cats'),
                'callback_data' => 'buy_cats' . '|' . $from['id'] . '|' . '10'
            ]
        ];
        $inline_keyboard = $button_list;
        if (Lang::isUncensored()) {
            $button_list_uncensored[] = array_merge([$button_list[0][0]], [
                ['text' => Lang::message('store.button.buy_tits'),
                    'callback_data' => 'buy_tits' . '|' . $from['id'] . '|' . '30'],
                ['text' => Lang::message('store.button.buy_butts'),
                    'callback_data' => 'buy_butts' . '|' . $from['id'] . '|' . '20'
                ]]);
            $inline_keyboard = $button_list_uncensored;
        }
        $username = $this->service->getUserName($from['id']);
        $karma = $this->service->getUserLevel($from['id'], $chat_id);

        if ($message == NULL && $text == NULL) {
            $text = Util::insert(Lang::message('store.title'), ["user" => $username, "k" => $karma]);
            Request::sendHtmlMessage($chat_id, $text, ["reply_markup" => ['inline_keyboard' => $inline_keyboard]]);
        } else {
            $command = explode("|", $callback);
            $newKarma = $karma - (int)$command[2];
            $newMessage = $message_text;
            if ($newKarma >= 0) {
                Request::sendPhoto($chat_id, $text, ['reply_to_message_id' => $message_id]);
                $newMessage = Util::insert(Lang::message('store.event.' . $command[0]), ["user" => $username, "k" => $newKarma]);
                $this->service->setLevel($from['id'], $chat_id, $newKarma);
            } else {
                $newMessage = Util::insert(Lang::message('store.event.cant_buy'), ["user" => $username, "k" => $karma, "buy" => Lang::message('store.button.' . $command[0])]);
            }
            Request::editMessageText($chat_id, $message_id, $newMessage, ["parse_mode" => "HTML"]);
        }
    }

    public function processCallback($callback)
    {
        $from = $callback['from'];
        $message = $callback['message'];
        $data = $callback['data'];
        $chat_id = $message['chat']['id'];
        $chat_type = $message['chat']['type'];
        $this->service->initLang($chat_id, $chat_type);
        if (in_array($data, array_keys(Lang::$availableLangs)) && ($this->service->isAdmin($from['id'], $chat_id) || $chat_type == "private")) {
            $qrez = $this->service->setLang($chat_id, $chat_type, $data);
            $text = Lang::message('bot.error');
            if ($qrez) {
                Lang::init($data);
                $text = Lang::message('chat.lang.end');
            }
            $this->sendLanguageKeyboard($chat_id, $message['message_id'], $text);
            sleep(1);
            if ($chat_type == "private") {
                Request::sendHtmlMessage($chat_id, Lang::message('user.pickChat', array('botName' => BOT_NAME)));
            }
        } elseif (strpos($data, "buy_") !== false) {
            $data_array = explode('|', $data);
            if ($data_array[1] == $from['id']) {
                switch ($data_array[0]) {
                    case 'buy_tits':
                        $tits = json_decode(file_get_contents("http://api.oboobs.ru/boobs/1/1/random"), true);
                        $rez = "http://media.oboobs.ru/boobs/" . sprintf("%05d", $tits[0]['id']) . ".jpg";
                        break;
                    case 'buy_butts':
                        $butts = json_decode(file_get_contents("http://api.obutts.ru/butts/1/1/random"), true);
                        $rez = "http://media.obutts.ru/butts/" . sprintf("%05d", $butts[0]['id']) . ".jpg";
                        break;
                    case 'buy_cats':
                        $cat = json_decode(file_get_contents("http://random.cat/meow"), true);
                        $rez = $cat["file"];
                        break;
                    default:
                        $rez = $data;
                }
                $this->sendStore($chat_id, $from, $message, $rez, $data);
            }
        }
    }

}

?>