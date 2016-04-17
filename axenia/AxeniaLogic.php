<?php
require_once('../core/DB.php');
use DB;

class AxeniaLogic extends DB
{

    function doQuery($query)
    {
        return parent::doQuery($query);
    }

    public function SetHello($text, $chat_id)
    {
        $query = "UPDATE  `Chats` SET  `greeterings` =  '" . $text . "' WHERE  id = " . $chat_id;
        return ($this->doQuery($query) === false) ? false : "Добавлено";
    }

    public function GetUserID($username)
    {
        $query = "SELECT id FROM Users WHERE username='" . str_ireplace("@", "", $username) . "'";
        return (!$this->doQuery($query)[0]) ? false : $this->doQuery($query)[0];
    }

    public function GetUserName($id)
    {
        $query = "SELECT username,firstname,lastname FROM Users WHERE id=" . $id;
        return (!$this->doQuery($query)[0]) ? $this->doQuery($query)[1] : $this->doQuery($query)[0];
    }

    public function GetGroupName($id)
    {
        $query = "SELECT title FROM Chats WHERE id=" . $id;
        return (!$this->doQuery($query)[0]) ? false : $this->doQuery($query)[0];
    }

    public function SetAdmin($chat_id, $user_id)
    {
        if ($user_id !== false) {
            $query = "INSERT INTO `Admins` SET `user_id`='" . $user_id . "',`chat_id`=" . $chat_id;
            return ($this->doQuery($query) === false) ? false : "{username}, жду твоих указаний.";
        } else
            return "Пользователь не найден";
    }

    public function AddUser($user_id, $username, $firstname, $lastname)
    {
        $query = "INSERT INTO `Users` SET `id`='" . $user_id . "',`username`='" . $username . "',`firstname`='" . $firstname . "',`lastname`='" . $lastname . "' ON DUPLICATE KEY UPDATE `username`='" . $username . "' , `firstname`='" . $firstname . "' , `lastname`='" . $lastname . "'";
        $this->doQuery($query);
    }

    public function AddChat($chat_id, $title)
    {
        $query = "INSERT INTO `Chats` SET `id`=" . $chat_id . ",`title`='" . $title . "' ,`reports_num`=3 ON DUPLICATE KEY UPDATE `title`='" . $title . "'";
        return ($this->doQuery($query) === false) ? false : "Всем чмаффки в этом чатике.";
    }

    public function CheckAdmin($chat_id, $user_id)
    {
        $query = "SELECT id FROM Admins WHERE chat_id=" . $chat_id . " AND user_id=" . $user_id;
        return $this->doQuery($query)[0];
    }

    /**
     * получить уровень кармы пользователя из чата
     * @param $user_id
     * @param $chat_id
     * @return mixed
     */
    public function getUserLevel($user_id, $chat_id)
    {
        $query = "SELECT level FROM Karma WHERE user_id=" . $user_id . " AND chat_id=" . $chat_id;
        return $this->doQuery($query)[0];
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
        $query = "INSERT INTO `Karma` SET `user_id`=" . $user_id . ",`chat_id`=" . $chat_id . ",`level`=" . $level . " ON DUPLICATE KEY UPDATE `level`=" . $level;
        return $this->doQuery($query);
    }

    public function HandleKarma($dist, $userFrom, $userTo, $chat_id)
    {
        if ($userFrom == $userTo) return "Давай <b>без</b> кармадрочерства";
        if ($userFrom != 1) {
            $userFromLevel = $this->getUserLevel($userFrom, $chat_id);
            if (!$userFromLevel) {
                // если нет в БД, добавляем
                $userFromLevel = $this->setUserLevel($userFrom, $chat_id, 0)[0];
            }
            if ($userFromLevel < 0) {
                return "Ты <b>не  можешь</b> голосовать с отрицательной кармой";
            }
            $output = "<b>" . $this->GetUserName($userFrom) . " (" . $userFromLevel . ")</b>";
        } else {
            $output = "<b>Аксинья</b>";
        }

        $userToLevel = $this->getUserLevel($userTo, $chat_id);

        //если не проставлено в БД еще, то делаем нулевую переменную.
        if (!$userToLevel) {
            $userToLevel = 0;
        }

        // из за математики делаем хак
        if ($userFromLevel == 0) {
            $userFromLevel = 1;
        }
        switch ($dist) {
            case "+":
                $output .= " плюсанул в карму ";
                $newLevel = round($userToLevel + sqrt($userFromLevel), 1);
                break;
            case "-":
                $output .= " минусанул в карму ";
                $newLevel = ($userFrom != 1) ? round($userToLevel - sqrt($userFromLevel), 1) : $userToLevel - 0.1;
                break;
        }
        $output .= "<b>" . $this->GetUserName($userTo) . " (" . $newLevel . ")</b>";
        $this->setUserLevel($userTo, $chat_id, $newLevel);

        // проверка на награды
        $output = $this->handleRewards($newLevel, $userTo, $chat_id, $output);

        return $output;
    }


    public function getOldTypeReward($user_id, $chat_id)
    {
        return $this->doQuery("select type_id from Rewards where user_id=" . $user_id . " and group_id=" . $chat_id . " and type_id>=2 and type_id<=4");
    }

    public function updateReward($new_type_id, $old_type_id, $desc, $user_id, $chat_id)
    {
        $this->doQuery("update Rewards set type_id=" . $new_type_id . ", description='" . $desc . "'  where type_id=" . $old_type_id . " and user_id=" . $user_id . " and group_id=" . $chat_id);
    }

    public function deleteReward($user_id, $chat_id)
    {
        $this->doQuery("delete  from Rewards where user_id=" . $user_id . " and group_id=" . $chat_id . " and (type_id>=2 and type_id<=4)");
    }

    public function insertReward($new_type_id, $desc, $user_id, $chat_id)
    {
        $this->doQuery("insert into Rewards(type_id,user_id,group_id,description) values (" . $new_type_id . "," . $user_id . "," . $chat_id . ",'" . $desc . "')");
    }

    public function handleRewards($newLevel, $userTo, $chat_id, $output)
    {
        switch ($newLevel) {
            case $newLevel >= 200 and $newLevel < 500:
                $new_type_id = 2;
                $title = "Кармодрочер";
                $min = 200;
                break;
            case $newLevel >= 500 and $newLevel < 1000:
                $new_type_id = 3;
                $title = "Карманьяк";
                $min = 500;
                break;
            case $newLevel >= 1000:
                $new_type_id = 4;
                $title = "Кармонстр";
                $min = 1000;
                break;
        }
        //проверка наград
        $old_type_id = $this->getOldTypeReward($userTo, $chat_id);

        if ($old_type_id != false) {
            //если есть награды
            if (isset($new_type_id)) {
                if ($new_type_id <> $old_type_id[0]) {
                    $desc = "Карма в группе " . GetGroupName($chat_id) . " превысило отметку в " . $min;
                    $this->updateReward($new_type_id, $old_type_id[0], $desc, $userTo, $chat_id);
                }
                if ($new_type_id > $old_type_id[0]) {
                    $output .= "\r\nТоварищ награждается отличительным знаком «<a href='" . PATH_TO_SITE . "?user_id=" . $userTo . "'>" . $title . "</a>»";
                }
            } else {
                $this->deleteReward($userTo, $chat_id);
            }
        } elseif (isset($new_type_id)) {
            //Если нет наград, но
            $desc = "Карма в группе " . GetGroupName($chat_id) . " превысило отметку в " . $min;
            $this->insertReward($new_type_id, $desc, $userTo, $chat_id);
            $output .= "\r\nТоварищ награждается отличительным знаком «<a href='" . PATH_TO_SITE . "?user_id=" . $userTo . "'>" . $title . "</a>»";
        }
        return $output;
    }

    public function Punish($user, $chat)
    {
        if ($chat == -1001016901471) return $this->HandleKarma("-", 1, $user, $chat);
    }

//    public function SetCarma($chat, $user, $level)
//    {
//        $query = "INSERT INTO Karma SET chat_id=" . $chat . ",user_id=" . $user . ",level=" . $level . " ON DUPLICATE KEY UPDATE level=" . $level;
//        Query2DB($query);
//        return (Query2DB($query) === false) ? false : true;
//    }

    public function getCarmaList($chat_id)
    {
        $query = "select u.username, u.firstname, u.lastname, k.level from Karma k, Users u where k.user_id=u.id and k.chat_id=" . $chat_id . " order by level desc limit 5";
        return $this->doQuery($query);
    }

}

?>