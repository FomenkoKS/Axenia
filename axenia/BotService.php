<?php

class BotService
{

    private $db;

    /**
     * Axenia constructor.
     * @param $db BotDao
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

// region -------------------- Users

    public function getUserID($username)
    {
        return $this->db->getUserID($username);
    }

    //todo
    public function insertOrUpdateUser($user)
    {
        return $this->db->insertOrUpdateUser($user);
    }

    public function rememberUser($user)
    {
        return $this->db->insertOrUpdateUser($user);
    }

    public function getUserName($id)
    {
        return $this->db->getUserName($id);
    }

//endregion

// region -------------------- Lang

    /*
     * Type of chat, can be either “private”, “group”, “supergroup” or “channel”
     */
    public function getLang($id, $chatType)
    {
        if ($chatType == "private") {
            return $this->db->getUserLang($id);
        } elseif (Util::isInEnum("group,supergroup", $chatType)) {
            return $this->db->getChatLang($id);
        }

        return false;
    }

    public function setLang($id, $chatType, $lang)
    {
        if ($chatType == "private") {
            return $this->db->setUserLang($id, $lang);
        } elseif (Util::isInEnum("group,supergroup", $chatType)) {
            return $this->db->setChatLang($id, $lang);
        }

        return false;
    }


    public function initLang($chat_id, $chatType)
    {
        $lang = $this->getLang($chat_id, $chatType);

        if ($lang === false) {
            $lang = 'en';
        }
        Lang::init($lang);
    }

//endregion

// region -------------------- Chats

    public function rememberChat($chat_id, $title, $chatType, $adder_id)
    {
        if (Util::isInEnum("group,supergroup", $chatType)) {
            $res = $this->db->insertOrUpdateChat($chat_id, $title);

            if ($this->db->getChatLang($chat_id) === false) {
                $lang = $this->db->getUserLang($adder_id); //получение языка добавителя
                if ($lang !== false) {
                    $this->db->setChatLang($chat_id, $lang);
                    Lang::init($lang);
                }
            }

            return $res;
        }
        return false;
    }

    public function deleteChat($chat_id)
    {
        return $this->db->deleteChat($chat_id);
    }


    public function getGroupName($chat_id)
    {
        return $this->db->getGroupName($chat_id);
    }


//endregion

//region -------------------- Karma

    public function getTop($chat_id, $limit = 5)
    {
        return $this->db->getTop($chat_id, $limit);
    }

    public function setLevelByUsername($username, $chat_id, $newLevel)
    {
        $user_id = $this->db->getUserID($username);
        if ($user_id !== false) {
            if ($this->db->setUserLevel($user_id, $chat_id, $newLevel)) {
                return Lang::message('karma.manualSet', array($username, $user_id, $chat_id, $newLevel));
            }
        }
        return Lang::message('bot.error');
    }

    private function getUserLevel($from, $chat_id)
    {
        $fromLevelResult = $this->db->getUserLevel($from, $chat_id);
        if (!$fromLevelResult[0]) {
            $this->db->setUserLevel($from, $chat_id, 0);
            return 0;
        } else {
            return $fromLevelResult[0];
        }
    }

    public function handleKarma($isRise, $from, $to, $chat_id)
    {
        if ($from == $to) return Lang::message('karma.yourself');

        $fromLevel = $this->getUserLevel($from, $chat_id);

        if ($fromLevel < 0) return Lang::message('karma.tooSmallKarma');

        $userFrom = $this->getUserName($from);
        $fromLevelSqrt = $fromLevel == 0 ? 1 : sqrt($fromLevel);
        $toLevel = $this->getUserLevel($to, $chat_id);
        $result = round($toLevel + ($isRise ? $fromLevelSqrt : -$fromLevelSqrt), 1);

        $userTo = $this->getUserName($to);

        $res = $this->db->setUserLevel($to, $chat_id, $result);
        if ($res) {
            $mod = $isRise ? 'karma.plus' : 'karma.minus';
            $output = Lang::message($mod, array('from' => $userFrom, 'k1' => $fromLevel, 'to' => $userTo, 'k2' => $result));

            //проверка наград
            $output .= $this->handleRewards($result, $chat_id, $to, $userTo);
            return $output;
        } else {
            return Lang::message('bot.error');
        }
    }

    public function handleKarmaFromBot($isRise, $user_id, $chat_id)
    {
        $user2 = $this->getUserName($user_id);

        if ($user2) {
            $toLevel = $this->getUserLevel($user_id, $chat_id);

            $result = $isRise ? $toLevel + 1 : $toLevel - (($toLevel > 0 && $toLevel <= 1) ? 0.1 : 1);

            $res = $this->db->setUserLevel($user_id, $chat_id, $result);
            if ($res) {
                $mod = $isRise ? 'karma.plus' : 'karma.minus';
                $output = Lang::message($mod, array('from' => Lang::message('bot.name'), 'k1' => '∞', 'to' => $user2, 'k2' => $result));

                //проверка наград
                $output .= $this->handleRewards($result, $chat_id, $user_id, $user2);
                return $output;
            }
            return Lang::message('bot.error');
        } else {
            return Lang::message('karma.unknownUser');
        }

    }

//endregion

//region -------------------- Rewards

    public function handleRewards($currentCarma, $chat_id, $user_id, $usernameTo)
    {
        $output = "";
        //проверка наград
        switch ($currentCarma) {
            case $currentCarma >= 200 and $currentCarma < 500:
                $newType = 2;
                $title = "Кармодрочер";
                $min = 200;
                break;
            case $currentCarma >= 500 and $currentCarma < 1000:
                $newType = 3;
                $title = "Карманьяк";
                $min = 500;
                break;
            case $currentCarma >= 1000:
                $newType = 4;
                $title = "Кармонстр";
                $min = 1000;
                break;
            default:
                $title = "title";
                $min = "min";
                break;
        }
        $oldType = $this->db->getRewardOldType($user_id, $chat_id);
        $groupName = $this->getGroupName($chat_id);
        if ($oldType != false) {
            //если есть награды
            if (isset($newType)) {
                if ($newType <> $oldType[0]) {
                    $desc = Lang::message('reward.desc', array($groupName, $min));
                    $this->db->updateReward($newType, $oldType[0], $desc, $user_id, $chat_id);
                }
                if ($newType > $oldType[0]) {
                    $output .= "\r\n" . Lang::message('reward.new', array('user'=> $usernameTo, 'path' => PATH_TO_SITE, 'user_id' => $user_id, 'title' => $title));
                }
            } else {
                $this->db->deleteReward($user_id, $chat_id);
            }
        } elseif (isset($newType)) {
            //Если нет наград, но
            $desc = Lang::message('reward.desc', array($groupName, $min));
            $this->db->insertReward($newType, $desc, $user_id, $chat_id);
            $output .= "\r\n" . Lang::message('reward.new', array('user'=> $usernameTo, 'path' => PATH_TO_SITE, 'user_id' => $user_id, 'title' => $title));
        }
        return $output;
    }


//endregion


}


?>