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

    public function handleUpdate($update)
    {
        if (isset($update["message"]) || isset($update["inline_query"]) || isset($update["callback_query"])) {
            try {
                if (isset($update["message"])) {
                    $this->processMessage($update["message"]);
                } elseif (isset($update["inline_query"])) {
                    $this->processInline($update["inline_query"]);
                } elseif (isset($update["callback_query"])) {
                    $this->processCallback($update["callback_query"]);
                }
            } catch (Exception $e) {
                print_r("Boterror!");
                $this->service->handleException($e, $update);
            }
        }
    }

    /**
     * Check if is need to handle the message by bot
     * @param $message
     * @return bool
     */
    private function needToHandle($message)
    {
        if ($message['chat']['type'] != "channel") {
            if (isset($message['text'])) {
                return Util::startsWith($message['text'], ["/", "+", "-", '👍', '👎']);
            }
            if (isset($message['sticker'])) {
                return Util::startsWith($message['sticker']['emoji'], ['👍', '👎']);
            }
            if (isset($message['new_chat_member']) || isset($message['new_chat_title']) || isset($message['left_chat_member'])) {
                return true;
            }
        }

        return false;
    }

    public function processMessage($message)
    {
        if ($this->needToHandle($message)) {
            $message_id = $message['message_id'];
            $chat = $message['chat'];
            $from = $message['from'];

            $chatType = $chat['type'];

            $chat_id = $chat['id'];
            $from_id = $from['id'];
            if ($this->service->checkCoolDown($from_id, $chat_id, $chatType)) {
                $this->service->insertOrUpdateUser($from);
                $isNewChat = $this->service->initLang($chat_id, $chatType);
                if ($isNewChat) {
                    $this->service->rememberChat($chat, $from_id);
                }

                if (isset($message['text']) || isset($message['sticker'])) {
                    $isPrivate = $chatType == "private";
                    $postfix = $isPrivate ? "" : ("@" . BOT_NAME);
                    if (isset($message['sticker'])) {
                        $text = $message['sticker']['emoji'];
                    } else {
                        $text = $message['text'];
                    }
                    switch (true) {
                        case Util::startsWith($text, ["+", "-", '👍', '👎']):
                            if (preg_match('/^(\+|\-|👍|👎) ?([\s\S]+)?/ui', $text, $matches)) {
                                $isRise = Util::isInEnum("+,👍", $matches[1]);
                                if (isset($message['reply_to_message'])) {
                                    $replyUser = $message['reply_to_message']['from'];
                                    if ($replyUser['username'] != BOT_NAME && !$this->service->isBot($replyUser['username'])) {
                                        $this->service->insertOrUpdateUser($replyUser);
                                        $this->doKarmaAction($isRise, $from_id, $replyUser['id'], $chat_id);
                                    }
                                } else {
                                    if (!$isPrivate) {
                                        if (preg_match('/@([\w]+)/ui', $matches[2], $user)) {
                                            if (BOT_NAME != $user[1] && !$this->service->isBot($user[1])) {
                                                $to = $this->service->getUserID($user[1]);
                                                if ($to) {
                                                    $this->doKarmaAction($isRise, $from_id, $to, $chat_id);
                                                } else {
                                                    Request::sendHtmlMessage($chat_id, Lang::message('karma.unknownUser'), ['reply_to_message_id' => $message_id]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            break;
                        case (Util::startsWith($text, "/buy" . $postfix)):
                            Request::sendTyping($chat_id);
                            $this->sendStore($chat_id, $from);
                            break;
                        case (Util::startsWith($text, "/settings" . $postfix)):
                            Request::sendTyping($chat_id);
                            $this->sendSettings($chat_id, NULL, NULL, $this->service->isAdminInChat($from_id, $chat));
                            break;
                        case (Util::startsWith($text, "/top" . $postfix)):
                            Request::sendTyping($chat_id);
                            if ($isPrivate) {
                                Request::sendMessage($chat_id, Lang::message("karma.top.private"));
                            } else {
                                $out = $this->service->getTop($chat_id, 5);
                                Request::sendHtmlMessage($chat_id, $out);
                            }
                            break;
                        case (Util::startsWith($text, "/my_stats" . $postfix)):
                            Request::sendTyping($chat_id);
                            Request::sendHtmlMessage($chat_id, $this->service->getStats($from_id, $isPrivate ? NULL : $chat_id), ['reply_to_message_id' => $message_id]);
                            break;
                        case (Util::startsWith($text, "/start" . $postfix)):
                            if ($isPrivate) {
                                Request::sendTyping($chat_id);
                                Request::sendHtmlMessage($chat_id, Lang::message('chat.greetings'));
                                //$this->sendLanguageKeyboard($chat_id);
                            } else {
                                $this->service->rememberChat($chat, $from_id);
                            }
                            break;
                        case (Util::startsWith($text, "/help" . $postfix)):
                            Request::sendHtmlMessage($chat_id, Lang::message('chat.help'));
                            break;
                        case Util::startsWith($text, ("/set @")):
                            if (Util::isInEnum(ADMIN_IDS, $from_id)) {
                                if (preg_match('/^(\/set) @([\w]+) (-?\d+)/ui ', $text, $matches)) {
                                    Request::sendMessage($from_id, $this->service->setLevelByUsername($matches[2], $chat_id, $matches[3]));
                                }
                            }
                            break;
                        case Util::startsWith($text, ("/lala")):
                            if (defined('TRASH_CHAT_ID')) {
                                Request::sendTyping($chat_id);
                                $ok = false;
                                do {
                                    $message = Request::exec("forwardMessage", array('chat_id' => TRASH_CHAT_ID, "from_chat_id" => "@rgonewild", "disable_notification" => true, "message_id" => rand(1, 15096)));
                                    if ($message !== false && isset($message['photo'])) {
                                        $array = $message['photo'];
                                        $file_id = $array[0]['file_id'];
                                        foreach ($array as $file) {
                                            $height = (int)$file['height'];
                                            if ($height > 600 && $height <= 1280) {
                                                $file_id = $file['file_id'];
                                            }
                                        }
                                        Request::sendPhoto($chat_id, $file_id);
                                        $ok = true;
                                    }
                                    sleep(1);
                                } while (!$ok);
                            }
                            break;
//                    case Util::startsWith($text, ("/cleanDB")):
//                        if (Util::isInEnum(ADMIN_IDS, $from_id)) {
//                            Request::sendTyping($chat_id);
//                            $count = 0;
//                            $updated = 0;
//                            $deleted = 0;
//                            if ($groups_id = $this->service->getChatsIds()) {
//                                foreach ($groups_id as $id) {
//                                    $count++;
//                                    $chat = Request::getChat($id);
//                                    $isStealInChat = Request::sendTyping($id);
//                                    if ($chat !== false && $isStealInChat !== false) {
//                                        $this->service->rememberChat($chat);
//                                        $updated++;
//                                    } else {
//                                        $this->service->deleteChat($id);
//                                        $deleted++;
//                                    }
//                                }
//                            }
//
//                            $out = Util::insert("The database was cleaned.\nChats count-:c. Updated-:u, deleted-:d.", ["c" => $count, "u" => $updated, "d" => $deleted]);
//                            Request::sendMessage($chat_id, $out);
//                            if (defined('LOG_CHAT_ID') && LOG_CHAT_ID != $chat_id) {
//                                Request::sendMessage(LOG_CHAT_ID, $out);
//                            }
//                        }
//                        break;
//                    case Util::startsWith($text, ("/cleanKarma")):
//                        if (Util::isInEnum(ADMIN_IDS, $from_id)) {
//                            Request::sendTyping($chat_id);
//                            $count = 0;
//                            $deleted = 0;
//                            if ($userChatPair = $this->service->getAllKarmaPair()) {
//                                foreach ($userChatPair as $pair) {
//                                    $count++;
//                                    $isUserStealInChat = Request::getChatMember($pair[0], $pair[1]);
//                                    if ($isUserStealInChat === false ||
//                                        (isset($isUserStealInChat['status']) &&
//                                            $isUserStealInChat['status'] == 'left' || $isUserStealInChat['status'] == 'kicked')
//                                    ) {
//                                        $this->service->deleteUserDataInChat($pair[0], $pair[1]);
//                                        $deleted++;
//                                    }
//                                }
//                            }
//
//                            $out = Util::insert("The karma was cleaned.\nChats count-:c. Deleted-:d.", ["c" => $count, "d" => $deleted]);
//                            Request::sendMessage($chat_id, $out);
//                            if (defined('LOG_CHAT_ID') && LOG_CHAT_ID != $chat_id) {
//                                Request::sendMessage(LOG_CHAT_ID, $out);
//                            }
//                        }
//                        break;

//                    case Util::startsWith($text, ("/setPresent")):
//                        if (Util::isInEnum(ADMIN_IDS, $from_id)) {
//                            Request::sendTyping($chat_id);
//                            $count = 0;
//                            $isOut = 0;
//                            $isIn = 0;
//                            if ($groups_id = $this->service->getChatsIds()) {
//                                foreach ($groups_id as $id) {
//                                    $count++;
//                                    $chat = Request::getChat($id);
//                                    $isStealInChat = Request::sendTyping($id);
//                                    if ($chat !== false && $isStealInChat !== false) {
//                                        $this->service->setBotPresentedInChat($id, true);
//                                        $isIn++;
//                                    } else {
//                                        $this->service->setBotPresentedInChat($id, false);
//                                        $isOut++;
//                                    }
//                                }
//                            }
//
//                            $out = Util::insert("The presenting in chats was setted.\nChats count-:c. In-:u, Out-:d.", ["c" => $count, "u" => $isIn, "d" => $isOut]);
//                            Request::sendMessage($chat_id, $out);
//                            if (defined('LOG_CHAT_ID') && LOG_CHAT_ID != $chat_id) {
//                                Request::sendMessage(LOG_CHAT_ID, $out);
//                            }
//                        }
//                        break;

                        /*case preg_match('/^\/getAdmins/ui', $text, $matches):
                            Request::sendMessage($chat_id, $this->service->isAdminInChat($from_id, $chat));
                            $admins = Request::getChatAdministrators($chat_id);
                            Request::sendMessage($chat_id, $admins);
                            //if(in_array($from_id,$admins['user']['id'])) Request::sendMessage($chat_id, "success");
                            break;*/

//                    case Util::startsWith($text, ("/nash")):
//                        if (Util::isInEnum(ADMIN_IDS, $from_id)) {
//                            if (preg_match('/^(\/nash) ([\s\S]+)/ui', $text, $matches)) {
//                                Request::sendTyping(NASH_CHAT_ID);
//                                sleep(1);
//                                Request::sendMessage(NASH_CHAT_ID, $matches[2]);
//                            }
//                        }
//                        break;
//                case preg_match('/tits|(сис(ек|ьки|ечки|и|яндры))/ui', $text, $matches):
//                    if (Lang::isUncensored()) {
//                        $tits = json_decode(file_get_contents("http://api.oboobs.ru/boobs/1/1/random"), true);
//                        $karma = $this->service->getUserLevel($from_id, $chat_id);
//                        $username = $this->service->getUserName($from_id);
//                        $newKarma = $karma - 30;
//                        if ($newKarma > 0) {
//                            Request::sendTyping($chat_id);
//                            Request::sendPhoto($chat_id, "http://media.oboobs.ru/boobs/" . sprintf("%05d", $tits[0]['id']) . ".jpg", ["caption" => $username . " подогнал эти сиськи за свои 30 кармы"]);
//                            $this->service->setLevel($from_id, $chat_id, $newKarma);
//                        }
//                    }
//                    break;
                    }
                } elseif (isset($message['new_chat_member'])) {
                    $newMember = $message['new_chat_member'];
                    if (BOT_NAME == $newMember['username']) {
                        $isRemembered = $this->service->rememberChat($chat, $from_id);
                        $this->service->setBotPresentedInChat($chat_id, true);
                        if ($isRemembered !== false) {
                            if (defined('LOG_CHAT_ID')) {
                                Request::sendHtmlMessage(LOG_CHAT_ID, " ❗ " . Request::getChatMembersCount($chat_id) . "|" . $this->service->getChatMembersCount($chat_id) . " (" . Util::getChatLink($chat) . ")");
                            }
                            Request::sendMessage($chat_id, Lang::message('chat.greetings'), array("parse_mode" => "Markdown"));
                        }
                    }
                    // убрал пока
                    //else { $this->service->insertOrUpdateUser($newMember); }
                } elseif (isset($message['new_chat_title'])) {
                    $this->service->rememberChat($chat, $from_id);
                } elseif (isset($message['left_chat_member'])) {
                    $member = $message['left_chat_member'];
                    if (BOT_NAME == $member['username']) {
                        //$isDeleted = $this->service->deleteChat($chat_id);
                        $this->service->setBotPresentedInChat($chat_id, false);
                        if (defined('LOG_CHAT_ID')) {
                            Request::sendHtmlMessage(LOG_CHAT_ID, " ❕ -1|" . $this->service->getChatMembersCount($chat_id) . " (" . Util::getChatLink($chat) . ")");
                        }
                    }
                }
            }
        }
    }

    public function doKarmaAction($isRise, $from_id, $user_id, $chat_id)
    {
        $out = $this->service->handleKarma($isRise, $from_id, $user_id, $chat_id);
        if (!$this->service->isSilentMode($chat_id)) {
            Request::sendHtmlMessage($chat_id, $out['msg']);
        }

        if ($out['good'] == true) {
            if ($out['newLevel'] != null) {
                $rewardMessages = $this->service->handleRewards($out['newLevel'], $chat_id, $user_id);
                if (count($rewardMessages) > 0) {
                    foreach ($rewardMessages as $msg) {
                        Request::sendHtmlMessage($chat_id, $msg);
                    }
                }
            }
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

    public function sendStore($chat_id, $from = NULL, $message = NULL, $text = NULL, $callback = NULL, $callback_id = NULL)
    {
        $message_id = $message['message_id'];
        $message_text = $message['text'];
        $button_list = [[
            [
                'text' => Lang::message('store.button.buy_cats'),
                'callback_data' => 'buy_cats' . '|' . $from['id'] . '|' . '10'
            ], [
                'text' => Lang::message('store.button.buy_gif'),
                'callback_data' => 'buy_gif' . '|' . $from['id'] . '|' . '10'
            ]
        ]];
        $inline_keyboard = $button_list;
        if (Lang::isUncensored()) {
            array_push($button_list, [
                ['text' => Lang::message('store.button.buy_tits'),
                    'callback_data' => 'buy_tits' . '|' . $from['id'] . '|' . '30'],
                ['text' => Lang::message('store.button.buy_butts'),
                    'callback_data' => 'buy_butts' . '|' . $from['id'] . '|' . '20'
                ]]);
            $inline_keyboard = $button_list;
        }
        $username = $this->service->getUserName($from['id']);
        $karma = $this->service->getUserLevel($from['id'], $chat_id);

        if ($message == NULL && $text == NULL) {
            $text = Util::insert(Lang::message('store.title'), ["user" => $username, "k" => $karma]);
            Request::sendHtmlMessage($chat_id, $text, ["reply_markup" => ['inline_keyboard' => $inline_keyboard]]);
        } else {
            $command = explode("|", $callback);
            $newKarma = $karma - (int)$command[2];
            if ($newKarma >= 0) {
                switch ($command[0]) {
                    case 'buy_gif':
                        Request::sendDocument($chat_id, $text, ['reply_to_message_id' => $message_id]);
                        break;
                    default:
                        if (Util::endsWith($text, "gif") == 1){
                            Request::sendDocument($chat_id, $text, ['reply_to_message_id' => $message_id]);
                        } else {
                            Request::sendPhoto($chat_id, $text, ['reply_to_message_id' => $message_id]);
                        }
                }

                $newMessage = Util::insert(Lang::message('store.event.' . $command[0]), ["user" => $username, "k" => $newKarma]);
                $callbackMessage = Util::insert(Lang::message('store.callback'), ["buy" => Lang::message('store.button.' . $command[0]), "k" => $newKarma]);
                $this->service->setLevel($from['id'], $chat_id, $newKarma);
            } else {
                $newMessage = Util::insert(Lang::message('store.event.cant_buy'), ["user" => $username, "k" => $karma, "buy" => Lang::message('store.button.' . $command[0])]);
                $callbackMessage = Util::insert(Lang::message('store.callback.cant_buy'), ["buy" => Lang::message('store.button.' . $command[0])]);
            }
            Request::editMessageText($chat_id, $message_id, $newMessage, ["parse_mode" => "HTML"]);
            Request::answerCallbackQuery($callback_id, $callbackMessage);
        }
    }

    public function sendSettings($chat_id, $message = NULL, $type = NULL, $showButtons = true)
    {
        $postfixSilentMode = "silent_mode" . ($this->service->isSilentMode($chat_id) ? "_off" : "_on");
        switch ($type) {
            case "set_cooldown":
                $minuteText = Lang::message('settings.minute');
                $button_list = [
                    [
                        ['text' => "0.1" . $minuteText, 'callback_data' => 'set_0'],
                        ['text' => "1" . $minuteText, 'callback_data' => 'set_1'],
                        ['text' => "2" . $minuteText, 'callback_data' => 'set_2']
                    ],
                    [['text' => Lang::message("settings.button.back"), 'callback_data' => "set_back"]]
                ];
                $text = Lang::message('settings.select.cooldown');
                break;
            case "set_lang":
                $ln = Lang::availableLangs();
                $keys = array_keys($ln);
                $values = array_values($ln);
                $button_list = [
                    [
                        ['text' => $values[0], 'callback_data' => $keys[0]],
                        ['text' => $values[1], 'callback_data' => $keys[1]]
                    ],
                    [['text' => $values[2], 'callback_data' => $keys[2]]],
                    [['text' => Lang::message("settings.button.back"), 'callback_data' => "set_back"]]
                ];
                $text = Lang::message('settings.select.lang');
                break;
            default:
                $button_list = [
                    [
                        ['text' => Lang::message("settings.button.toggle_silent_mode"),
                            'callback_data' => 'set_toggle_silent_mode'
                        ],
                        ['text' => Lang::message('settings.button.lang'),
                            'callback_data' => 'set_lang'
                        ]
                    ],
                    [['text' => Lang::message('settings.button.set_cooldown'),
                        'callback_data' => 'set_cooldown'
                    ]]
                ];

                $text = Lang::message('settings.title') . "\r\n";
                $text .= Lang::message("settings.title.".$postfixSilentMode) . "\r\n";
                $text .= Lang::message("settings.title.lang", ["lang" => Lang::getCurrentLangDesc()]) . "\r\n";
                $text .= Lang::message('settings.title.cooldown', ["cooldown" => $this->service->getCooldown($chat_id)]);
                break;
        }
        $inline_keyboard = $button_list;

        if ($message == NULL) {
            if ($showButtons) {
                Request::sendHtmlMessage($chat_id, $text, ["reply_markup" => ['inline_keyboard' => $inline_keyboard]]);
            } else {
                Request::sendHtmlMessage($chat_id, $text);
            }
        } else {
            Request::editMessageText($chat_id, $message['message_id'], $text, ["reply_markup" => ['inline_keyboard' => $inline_keyboard], "parse_mode" => "HTML"]);
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
        $isAdminInChat = $this->service->isAdminInChat($from['id'], $message['chat']);
        if (in_array($data, array_keys(Lang::availableLangs()))) {
            if ($isAdminInChat) {
                $qrez = $this->service->setLang($chat_id, $chat_type, $data);
                if ($qrez) {
                    Lang::init($data);
                }
                $this->sendSettings($chat_id, $message, NULL);
            } else {
                Request::answerCallbackQuery($callback['id'], Lang::message('settings.title'));
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
                    case 'buy_gif':
                        //giphy! ты подвёл этот город((
                        //$gif = json_decode(file_get_contents("http://api.giphy.com/v1/gifs/random?api_key=dc6zaTOxFJmzC"), true);
                        //$rez = $gif["data"]["image_url"];
                        if (defined('TRASH_CHAT_ID')) {
                            Request::sendTyping($chat_id);
                            $ok = false;
                            do {
                                $tmp = Request::exec("forwardMessage", array('chat_id' => TRASH_CHAT_ID, "from_chat_id" => "@GIFsChannel", "disable_notification" => true, "message_id" => rand(1, 1920)));
                                if ($tmp !== false && isset($tmp['document']) && !isset($tmp['text'])) {
                                    $array = $tmp['document'];
                                    $rez = $array['file_id'];
                                    $ok = true;
                                }
                                sleep(1);
                            } while (!$ok);
                        }
                        break;
                    default:
                        $rez = $data;
                }
                $this->sendStore($chat_id, $from, $message, $rez, $data, $callback['id']);
            } else {
                Request::answerCallbackQuery($callback['id'], Lang::message('store.wrongPick', array('user' => $data_array[1])));
            }
        } elseif(strpos($data, "set_") !== false) {
            if ($isAdminInChat) {
                switch ($data) {
                    case 'set_toggle_silent_mode':
                        $this->service->toggleSilentMode($chat_id);
                        break;
                    case 'set_0':
                        $this->service->setCooldown($chat_id, 0.1);
                        break;
                    case 'set_1':
                        $this->service->setCooldown($chat_id, 1);
                        break;
                    case 'set_2':
                        $this->service->setCooldown($chat_id, 2);
                        break;
                    case 'set_back':
                        $data = NULL;
                        break;
                }
                $this->sendSettings($chat_id, $message, $data);
            } else {
                Request::answerCallbackQuery($callback['id'], Lang::message('settings.title'));
            }
        }
    }

}

?>