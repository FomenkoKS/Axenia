<?php

class BotDao extends AbstractDao
{

// region -------------------- Users

    public function getUserID($username)
    {
        $username = "'" . (isset($username) ? $this->escape_mimic($username) : '') . "'";
        $username = str_ireplace("@", "", $username);
        $res = $this->select("SELECT id FROM Users WHERE username=$username");
        return (!$res[0]) ? false : $res[0];
    }

    public function insertOrUpdateUser($user)
    {
        $user_id = $user['id'];
        $username = Util::wrapQuotes(isset($user['username']) ? $this->escape_mimic($user['username']) : '');
        $firstname = Util::wrapQuotes(isset($user['first_name']) ? $this->escape_mimic($user['first_name']) : '');
        $lastname = Util::wrapQuotes(isset($user['last_name']) ? $this->escape_mimic($user['last_name']) : '');
        $query = "INSERT INTO Users (id, username, firstname, lastname) VALUES ($user_id,$username,$firstname,$lastname) ON DUPLICATE KEY UPDATE username=$username, firstname=$firstname, lastname=$lastname, last_updated=now()";
        return $this->insert($query);
    }

    public function getUserName($id)
    {
        $res = $this->select("SELECT username,firstname,lastname FROM Users WHERE id=" . $id);
        return (!$res[0]) ? $res[1] : $res[0];
    }

    public function getUsersByName($query)
    {
        if ($query != '') {
            $query = "'" . strtolower($query . "%") . "'";
            $res = $this->select("SELECT id,username,firstname,lastname FROM Users WHERE username LIKE $query or ((username is NULL or username='') and lastname LIKE $query ) or ((username is NULL or username='') and (lastname is NULL or lastname='')  and firstname LIKE $query)");
            return $res;
        }
        return false;
    }

//endregion

// region -------------------- Lang

    public function getChatLang($chat_id)
    {
        $res = $this->select("SELECT lang FROM Chats WHERE id = " . $chat_id, true);
        if (isset($res[0])) {
            return !($res[0]) ? false : $res[0];
        }
        return false;
    }

    public function getUserLang($user_id)
    {
        $res = $this->select("SELECT lang FROM Users WHERE id = " . $user_id);
        return !($res[0]) ? false : $res[0];
    }

    public function setChatLang($chat_id, $lang)
    {
        return $this->update("UPDATE Chats SET lang = '" . $lang . "' WHERE id = " . $chat_id);
    }

    public function setUserLang($user_id, $lang)
    {
        return $this->update("UPDATE Users SET lang = '" . $lang . "' WHERE id = " . $user_id);
    }


//endregion

// region -------------------- Chats

    public function insertOrUpdateChat($chat_id, $title)
    {
        $title = "'" . (isset($title) ? $this->escape_mimic($title) : '') . "'";
        $query = "INSERT INTO Chats(id, title) VALUES($chat_id, $title) ON DUPLICATE KEY UPDATE title = $title";
        return $this->insert($query);
    }

    public function deleteChat($chat_id)
    {
        $query = "DELETE FROM `Chats` WHERE `id` = " . $chat_id;
        return $this->delete($query);
    }


    public function getGroupName($chat_id)
    {
        $res = $this->select("SELECT title FROM Chats WHERE id = " . $chat_id);
        return (!$res[0]) ? false : $res[0];
    }


//endregion

// region -------------------- Admins

    //TODO прибрать
    public function insertAdmin($chat_id, $user_id)
    {
        if ($user_id !== false) {
            return $this->insert("INSERT INTO `Admins` SET `user_id` = '" . $user_id . "',`chat_id` = " . $chat_id);
        }
        return false;
    }

    public function isAdmin($chat_id, $user_id)
    {
        $res = $this->select("SELECT id FROM Admins WHERE chat_id = " . $chat_id . " AND user_id = " . $user_id);
        return $res !== false ? ($res[0] !== false ? true : false) : false;
    }

//endregion

//region -------------------- Karma

    public function getTop($chat_id, $limit = 5)
    {
        $query = "SELECT u . username, u . firstname, u . lastname, k . level FROM Karma k, Users u WHERE k . user_id = u . id AND k . chat_id = " . $chat_id . " ORDER BY level DESC LIMIT " . $limit;
        return $this->select($query);
    }

    /**
     * получить уровень кармы пользователя из чата
     * @param $user_id
     * @param $chat_id
     * @return mixed
     */
    public function getUserLevel($user_id, $chat_id)
    {
        $query = "SELECT level FROM Karma WHERE user_id = " . $user_id . " AND chat_id = " . $chat_id;
        $res = $this->select($query);
        return $res;
    }

    /**
     * Добавляет запись с уровня кармы пользователя в чате.
     * Если пользователь уже имеется с каким то левелом то левел обновится из параметра $level
     * @param $user_id
     * @param $chat_id
     * @param $level
     * @return mixed
     */
    public function setUserLevel($user_id, $chat_id, $level)
    {
        $query = "INSERT INTO `Karma` SET `user_id` = " . $user_id . ",`chat_id` = " . $chat_id . ",`level` = " . $level . " ON DUPLICATE KEY UPDATE `level` = " . $level . ", `last_updated`=now()";
        return $this->insert($query);
    }

//endregion

//region -------------------- Rewards

    public function getUserRewardIds($user_id, $chat_id)
    {
        $res = $this->select("SELECT type_id FROM Rewards WHERE user_id = " . $user_id . " AND group_id = " . $chat_id);
        if ($res !== false) {
            return $res;
        }
        return array();
    }

    public function getUserRewardsInChat($user_id, $chat_id)
    {
        $res = $this->select("SELECT r.type_id AS type_id, rt.code AS code  FROM Rewards r LEFT JOIN Reward_Type rt ON r.type_id=rt.id WHERE r.user_id = " . $user_id . " AND r.group_id = " . $chat_id);
        if ($res !== false) {
            return $res;
        }
        return array();
    }

    public function getUserRewards($user_id)
    {
        $res = $this->select("SELECT r.type_id AS type_id, rt.code AS code, c.title AS title FROM Rewards r LEFT JOIN Reward_Type rt ON r.type_id=rt.id LEFT JOIN Chats c ON r.group_id=c.id WHERE r.user_id = " . $user_id);
        if ($res !== false) {
            return $res;
        }
        return array();
    }

    public function getRewardTypes($types_array)
    {
        if (is_array($types_array) && count($types_array) > 0) {
            $types_array = join(',', $types_array);
            $res = $this->select("SELECT id, code, karma_min FROM Reward_Type WHERE id in ($types_array)", false);
            if ($res !== false) {
                return $res;
            }
        }
        return array();
    }

    public function insertReward($type_id, $desc, $user_id, $chat_id)
    {
        $user_id = "'" . (isset($user_id) ? $this->escape_mimic($user_id) : '') . "'";
        $desc = "'" . (isset($desc) ? $this->escape_mimic($desc) : '') . "'";
        return $this->insert("INSERT INTO Rewards(type_id, user_id, group_id, description) VALUES($type_id, $user_id, $chat_id , $desc)");
    }

//endregion

}


?>