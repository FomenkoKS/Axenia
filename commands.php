<?php
function SetHello($text, $chat_id)
{
    $query = "UPDATE  `Chats` SET  `greeterings` =  '" . $text . "' WHERE  id = " . $chat_id;
    //$query="INSERT INTO `Admins` SET `user_id`='".$user_id."',`chat_id`=".$chat_id." ON DUPLICATE KEY UPDATE `user_id`='".$user_id."',`chat_id`=".$chat_id;
    return (Query2DB($query) === false) ? false : "Добавлено";
}

function GetUserID($username)
{
    $query = "SELECT id FROM Users WHERE username='" . str_ireplace("@", "", $username) . "'";
    return (!Query2DB($query)[0]) ? false : Query2DB($query)[0];
}

function GetNameOfUser($id)
{
    $query = "SELECT username,firstname,lastname FROM Users WHERE id=" . $id;
    return (!Query2DB($query)[0]) ? Query2DB($query)[1] : Query2DB($query)[0];
}

function SetAdmin($chat_id, $user_id)
{
    //$query="INSERT INTO `Admins` SET `user_id`='".$user_id."',`chat_id`=".$chat_id." ON DUPLICATE KEY UPDATE `user_id`='".$user_id."',`chat_id`=".$chat_id;
    if ($user_id !== false) {
        $query = "INSERT INTO `Admins` SET `user_id`='" . $user_id . "',`chat_id`=" . $chat_id;
        return (Query2DB($query) === false) ? false : "{username}, жду твоих указаний.";
    } else return "Пользователь не найден";

}

function AddUser($user_id, $username, $firstname, $lastname)
{
    $query = "INSERT INTO `Users` SET `id`='" . $user_id . "',`username`='" . $username . "',`firstname`='" . firstname . "',`lastname`='" . lastname . "' ON DUPLICATE KEY UPDATE `username`='" . $username . "' , `firstname`='" . $firstname . "' , `lastname`='" . $lastname . "'";
    Query2DB($query);
}

function AddChat($chat_id, $title)
{
    $query = "INSERT INTO `Chats` SET `id`=" . $chat_id . ",`title`='" . $title . "' ,`reports_num`=3 ON DUPLICATE KEY UPDATE `title`='" . $title . "'";
    return (Query2DB($query) === false) ? false : "Всем чмаффки в этом чатике.";
}

function CheckAdmin($chat_id, $user_id)
{
    $query = "SELECT id FROM Admins WHERE chat_id=" . $chat_id . " AND user_id=" . $user_id;
    return Query2DB($query)[0];
}

function HandleKarma($dist, $from, $to, $chat_id)
{
    if ($from == $to) return "Давай *без* кармадрочерства";
    $query = "SELECT level FROM Karma WHERE user_id=" . $from . " AND chat_id=" . $chat_id;
    if (!Query2DB($query)[0]) {
        $query = "INSERT INTO `Karma` SET `chat_id`=" . $chat_id . ",`user_id`=" . $from . ",`level`=0";
        Query2DB($query);
        $a = 0;
    } else $a = Query2DB($query)[0];
    if ($a < 0) return "Ты *не можешь* голосовать с отрицательной кармой";
    $output = "*".GetNameOfUser($from) . " (" . $a . ")*";
    $query = "SELECT level FROM Karma WHERE user_id=" . $t . " AND chat_id=" . $chat_id;
    (!Query2DB($query)[0]) ? $b = 0 : $b = Query2DB($query)[0];
    if($a==0)$a=1;
    switch ($dist) {
        case "+":
            $output .= " плюсанул в карму ";
            $result = round($b + sqrt($a),1);
            break;
        case "-":
            $output .= " насрал в карму ";
            $result = round($b - sqrt($a),1);
            break;
    }
    $output .= "*".GetNameOfUser($to) . " (" . $result . ")*";
    $query = "INSERT INTO `Karma` SET `chat_id`=" . $chat_id . ",`user_id`=" . $to . ",`level`=" . $result . " ON DUPLICATE KEY UPDATE `level`=" . $result;
    Query2DB($query);
    return $output;
}
?>