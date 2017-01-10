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
        $query = "
            INSERT INTO Users (id, username, firstname, lastname) 
            VALUES ($user_id,$username,$firstname,$lastname) 
            ON DUPLICATE KEY UPDATE username=$username, firstname=$firstname, lastname=$lastname, last_updated=now()
        ";

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
            $query = "'" . strtolower("%" . $query . "%") . "'";
            $res = $this->select(
                "SELECT id,username,firstname,lastname 
                  FROM Users 
                  WHERE concat(username,firstname,lastname) LIKE $query;"
            );

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


    public function getSilentMode($chat_id)
    {
        $res = $this->select("SELECT silent_mode FROM Chats WHERE id = " . $chat_id, true);
        if (isset($res[0])) {
            return (empty($res[0]) || $res[0] == 0) ? false : true;
        } else return false;
    }

    public function setSilentMode($chat_id, $mode)
    {
        return $this->update("UPDATE Chats SET silent_mode = " . (($mode) ? 1 : 0) . " WHERE id=" . $chat_id);
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

    public function getChatsIds()
    {
        $res = $this->select("SELECT id FROM Chats");
        return (!$res) ? false : $res;
    }

    public function insertOrUpdateChat($chat_id, $title, $username)
    {
        $title = $this->clearForInsert($title);
        $username = $this->clearForInsert($username);

        $query = "
            INSERT INTO Chats(id, title,username) 
            VALUES($chat_id, $title,$username) 
            ON DUPLICATE KEY UPDATE title = $title,username=$username
        ";

        return $this->insert($query);
    }

    public function deleteChat($chat_id)
    {
        $query = "DELETE FROM Chats WHERE id = " . $chat_id;

        return $this->delete($query);
    }

    public function getUserGroups($user_id)
    {
        $res = $this->select(
            "SELECT c.title, c.username 
            FROM Chats c, Karma k 
            WHERE k.chat_id=c.id AND k.user_id=" . $user_id . " 
            ORDER BY c.title"
        );

        return (!$res[0]) ? false : $res;
    }

    public function getMembersCount($chat_id)
    {
        $res = $this->select(
            "SELECT count(1) 
            FROM Karma k 
            WHERE k.chat_id=" . $chat_id
        );

        return (!$res[0]) ? false : $res;
    }

    public function getGroupName($chat_id)
    {
        $res = $this->select("SELECT title FROM Chats WHERE id = " . $chat_id);

        return (!$res[0]) ? false : $res[0];
    }

    public function getGroupsMistakes()
    {
        $res = $this->select(
            "SELECT DISTINCT k.chat_id 
            FROM Karma k 
            WHERE NOT(k.last_updated IS NULL) AND k.chat_id NOT IN (SELECT id FROM Chats)"
        );
        $temp = $this->select(
            "SELECT DISTINCT k.chat_id, c.title 
            FROM Chats c,Karma k 
            WHERE NOT(k.last_updated IS NULL) AND k.chat_id=c.id AND (c.title='')"
        );
        $res = array_merge($res, $temp);

        return (!$res) ? false : $res;
    }


//endregion

// region -------------------- Admins

    //TODO прибрать


//endregion

//region -------------------- Karma

    public function getTop($chat_id, $limit = 5)
    {
        $query = "
        SELECT u . username, u . firstname, u . lastname, k . level 
        FROM Karma k, Users u 
        WHERE k . user_id = u . id AND k . chat_id = " . $chat_id . " 
        ORDER BY level 
        DESC LIMIT " . $limit;

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
        //$query = "INSERT INTO `Karma` SET `user_id` = " . $user_id . ",`chat_id` = " . $chat_id . ",`level` = " . $level . " ON DUPLICATE KEY UPDATE `level` = " . $level . ", `last_updated`=now()";

        $user_id = $this->clearForInsert($user_id);
        $chat_id = $this->clearForInsert($chat_id);
        $level = $this->clearForInsert($level);

        $query = "
            INSERT INTO Karma (user_id, chat_id, level)
            VALUES ($user_id,$chat_id,$level)
            ON DUPLICATE KEY UPDATE level = " . $level . ", last_updated=now()
        ";

        return $this->insert($query);
    }

    public function setLastTimeVote($from_id,$chat_id){
        $query = "
            UPDATE Karma set last_time_voted=now()
            WHERE user_id=" . $from_id . " and chat_id=".$chat_id;
        return $this->insert($query);
    }

    public function checkCooldown($from_id,$chat_id){
        $query = "select now()-last_time_voted from Karma
            WHERE user_id=" . $from_id . " and chat_id=".$chat_id;
        return $this->select($query);
    }

    public function SumKarma($user_id)
    {
        $res = $this->select("SELECT sum(level) FROM Karma WHERE user_id=" . $user_id);

        return (!$res[0]) ? 0 : $res[0];
    }

    public function UsersPlace($user_id)
    {
        $res = $this->select(
            "SELECT count(a.Sumlevel) 
              FROM (
                  SELECT sum(level) AS SumLevel 
                  FROM Karma k 
                  GROUP BY k.user_id) a,
                  (
                  SELECT sum(level) AS SumLevel 
                      FROM Karma 
                      WHERE user_id=" . $user_id . ") u 
                WHERE u.SumLevel<=a.SumLevel 
                ORDER BY a.SumLevel ASC;"
        );

        return (!$res[0]) ? false : $res[0];
    }

    public function getAllKarmaPair()
    {
        $res = $this->select("SELECT k.user_id, k.chat_id FROM Karma k");

        return (!$res[0]) ? false : $res;
    }

    public function deleteUserKarmaInChat($userId, $chatId)
    {
        $query = "DELETE FROM Karma WHERE user_id = " . $userId . " AND chat_id = " . $chatId;

        return $this->delete($query);
    }

    public function deleteAllKarmaInChat($chatId)
    {
        $query = "DELETE FROM Karma WHERE chat_id = " . $chatId;

        return $this->delete($query);
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
        $res = $this->select(
            "SELECT r.type_id AS type_id, rt.code AS code 
            FROM Rewards r 
            LEFT JOIN Reward_Type rt ON r.type_id=rt.id 
            WHERE r.user_id = " . $user_id . " AND r.group_id = " . $chat_id
        );
        if ($res !== false) {
            return $res;
        }

        return array();
    }

    public function getUserRewards($user_id)
    {
        $res = $this->select(
            "select rt.code,count(1) Count 
            from Rewards r, Reward_Type rt 
            where r.type_id=rt.id and r.user_id=$user_id 
            group by rt.code"
        );

        return $res;
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
        $user_id = $this->clearForInsert($user_id);
        $desc = $this->clearForInsert($desc);

        return $this->insert(
            "INSERT INTO Rewards(type_id, user_id, group_id, description) 
            VALUES($type_id, $user_id, $chat_id , $desc)"
        );
    }

    public function deleteUserRewardsInChat($userId, $chatId)
    {
        $query = "DELETE FROM Rewards WHERE user_id = " . $userId . " AND group_id = " . $chatId;

        return $this->delete($query);
    }

    public function deleteAllRewardsInChat($chatId)
    {
        $query = "DELETE FROM Rewards WHERE group_id = " . $chatId;

        return $this->delete($query);
    }

//endregion

}


?>