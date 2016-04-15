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

function GetUserName($id)
{
    $query = "SELECT username,firstname,lastname FROM Users WHERE id=" . $id;
    return (!Query2DB($query)[0]) ? Query2DB($query)[1] : Query2DB($query)[0];
}

function GetGroupName($id){
    $query = "SELECT title FROM Chats WHERE id=".$id;
    return (!Query2DB($query)[0]) ? false : Query2DB($query)[0];
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
    $query = "INSERT INTO `Users` SET `id`='" . $user_id . "',`username`='" . $username . "',`firstname`='" . $firstname . "',`lastname`='" . $lastname . "' ON DUPLICATE KEY UPDATE `username`='" . $username . "' , `firstname`='" . $firstname . "' , `lastname`='" . $lastname . "'";
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
    if ($from == $to) return "Давай <b>без</b> кармадрочерства";
    if($from!=1) {
        $query = "SELECT level FROM Karma WHERE user_id=" . $from . " AND chat_id=" . $chat_id;
        if (!Query2DB($query)[0]) {
            $query = "INSERT INTO `Karma` SET `chat_id`=" . $chat_id . ",`user_id`=" . $from . ",`level`=0";
            Query2DB($query);
            $a = 0;
        } else $a = Query2DB($query)[0];
        if ($a < 0) return "Ты <b>не  можешь</b> голосовать с отрицательной кармой";
        $output = "<b>" . GetUserName($from) . " (" . $a . ")</b>";
    }else{$output = "<b>Аксинья</b>";}
    $query = "SELECT level FROM Karma WHERE user_id=" . $to . " AND chat_id=" . $chat_id;
    (!Query2DB($query)[0]) ? $b = 0 : $b = Query2DB($query)[0];
    if($a==0)$a=1;
    switch ($dist) {
        case "+":
            $output .= " плюсанул в карму ";
            $result = round($b + sqrt($a),1);
            break;
        case "-":
            $output .= " минусанул в карму ";
            $result=($from!=1)?round($b - sqrt($a),1):$b-0.1;
            break;
    }
    $output .= "<b>".GetUserName($to) . " (" . $result . ")</b>";
    $query = "INSERT INTO `Karma` SET `chat_id`=" . $chat_id . ",`user_id`=" . $to . ",`level`=" . $result . " ON DUPLICATE KEY UPDATE `level`=" . $result;
    Query2DB($query);
    return $output;
}

function Punish($user,$chat){
    if ($chat==-1001016901471)return HandleKarma("-",1,$user,$chat);
}

function SetCarma($chat,$user,$level){
    $query = "INSERT INTO `Karma` SET `chat_id`=" . $chat . ",`user_id`=" . $user . ",`level`=" . $level . " ON DUPLICATE KEY UPDATE `level`=" . $level;
    Query2DB($query);
    return (Query2DB($query) === false) ? false : true;
}
?>