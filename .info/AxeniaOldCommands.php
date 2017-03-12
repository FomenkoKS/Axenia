<?php
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

//case Util::startsWith($text, ("/qqq")):
//                        if (Util::isInEnum(ADMIN_IDS, $from_id) && QQQ == 1) {
//                            Request::sendTyping($chat_id);
//                            $arr = '';
//                            $count = 0;
//                            $groups_id = [];
//                            foreach ($groups_id as $id) {
//                                $isStealInChat = Request::sendTyping($id);
//                                if ($isStealInChat['error_code'] == '400') {
//                                    $arr = $arr . $id . ",";
//                                }
//                                $count++;
//                                sleep(1);
//                                if(($count%5)==0){
//                                    Request::sendMessage($from_id, $count);
//                                }
//                            }
//                            Request::sendMessage($from_id, "Itog\r\n\r\n".$arr);
//                        }
//                        break;

//START TRANSACTION;
//SET @chatid := '-1,-2,';
//DELETE FROM Rewards
//WHERE FIND_IN_SET(group_id,@chatid);
//DELETE FROM Karma
//WHERE FIND_IN_SET(chat_id,@chatid);
//DELETE FROM Chats
//WHERE FIND_IN_SET(id,@chatid);
//COMMIT;

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