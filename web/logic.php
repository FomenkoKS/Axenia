<?
require_once("config.php");
require_once("functions.php");
if(isset($_GET['please'])){
    $logic=new FemaleLogic();
    switch ($_GET['please']){
        case 'avatar':echo $logic->GetAvatar($_GET['user_id']);
    }
}

class FemaleLogic
{
    public function TypeOfView($get)
    {
        switch (true) {
            case isset($get['username']):
                $type = GetUserID($get['username']) ? "user" : "search";
                break;
            case isset($get['user_id']):
                $type = GetUserName($get['user_id']) ? "user" : "search";
                break;
            case isset($get['group_id']):

                $type = GetGroupName($get['group_id']) ? "group" : "search";
                break;
            default:
                $type = "search";
        }
        return $type;
    }

    public function GetHeader($type, $get)
    {
        switch ($type) {
            case 'user':
                $user_id=(isset($get['user_id'])) ? $get['user_id'] : GetUserID($get['username']);

                $header = GetUserName($user_id);
                $header .= " в гостях у Аксиньи";
                break;
            case 'group':
                $header = "Аксинья в группе «" . getGroupName($get['group_id']) . "»";
                break;
            case 'search':
                $header = $get['username'] . " в гостях у Аксиньи";
                break;
        }
        return $header;
    }

    public function MakeBarChart($id,$type)
    {
        switch($type){
            case "group":
                $query = "select u.username, u.firstname, u.lastname, k.level, u.id from Karma k, Users u where k.user_id=u.id and k.chat_id=" . $id . " order by level desc";
                $title="Карма в этой группе распределена следующим образом:";

                break;
            case "user":
                $query = "select c.title, c.id, k.user_id, k.level, k.chat_id from Karma k, Chats c where k.chat_id=c.id and k.user_id=" . $id . " order by level desc";
                $title="Карма пользователя в группах:";
                break;
        }
        $html = "<div class=\"row text-center\"><h2>".$title."</h2></div>";

        $a = array_chunk(Query2DB($query), 5);
        $max=$a[0][3];
        foreach ($a as $value) {
            $html .= "<div class='row'>";
            switch($type){
                case "group":
                    $html .= "<div class='user_photo img-circle col-md-1' style='background-image:url(users/".$value[4].".jpg)'></div><a class='col-md-1 title load_user' onclick='load_user(".$value[4].")'>";
                    $this->GetAvatar($value[4]);
                    $html .= ($value[0] == "") ? $value[1] . " " . $value[2] : $value[0];
                    $html.="</a>";
                    break;
                case "user":
                    $html.="<a class=\"title col-md-2 load_group\" onclick='load_group(".$value[1].")'>";
                    $html .= $value[0]."</a>";
                    break;
            }
            $val=round(($value[3]/$max)*100);
            $html.="<div class=\"col-md-10\"><div class=\"progress progress-striped active\"><div class=\"progress-bar\" role=\"progressbar\" aria-valuenow=\"".$val."\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: ".$val."%;\">".$value[3]."</div></div></div></div>";
        }
        return $html;
    }

    public function GetAvatar($id){
        $photos = apiRequest("getUserProfilePhotos", array('user_id' => $id));
        if($photos['total_count']>0) {
            $photo_id = $photos['photos'][0][0]['file_id'];
            $query = "select 1 from Users where id=" . $id . " and img=\"" . $photo_id . "\"";
            if (!Query2DB($query)[0] || !file_exists('users/' . $id . '.jpg')) {
                 $photo = apiRequest("getFile", array('file_id' => $photo_id));
                 $photo_file = file_get_contents('https://api.telegram.org/file/bot' . BOT_TOKEN . '/' . $photo['file_path']);
                 $filename = 'users/' . $id . '.jpg';
                 $f = fopen($filename, 'wb');
                 fwrite($f, $photo_file);
/*
                 list($width, $height) = getimagesize($filename);
                 $new_width = 32;
                 $new_height = 32;
                 $image_p = imagecreatetruecolor($new_width, $new_height);
                 $image = imagecreatefromjpeg($filename);
                 imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                 imagejpeg($image_p, $filename, 100);
                 */
                $query = "UPDATE Users SET img=\"" . $photo_id . "\" where id=" . $id;
                 Query2DB($query);
             }
        }
        return $id;
    }

    public function GetStats(){
        $html="<ul class=\"list-unstyled\">";

        $num=Query2DB("select sum(level) from Karma")[0];
        $html.="<li>В кармохранилище содержится ". round($num,1) ." кармы.</li>";

        $num=Query2DB("select count(1) from Users")[0];
        $html.="<li>Аксинья знает ". $num ." человек";
        if($num%10==1 && $num%100<>11) $html.="а";
        $html.=",</li>";

        $num=Query2DB("select count(1) from Users where username<>''")[0];
        $html.="<li>из них ". $num ." завели себе юзернейм.</li>";

        if($num!=count(json_decode(file_get_contents("data/users.json")))){
            file_put_contents("data/users.json",json_encode(Query2DB("select Username from Users where username<>''")),false);
        }

        $num=Query2DB("select count(1) from Karma where level<0")[0];
        $html.="<li>". $num ." с отрицательной кармой,</li>";

        $num=Query2DB("select count(1) from Karma where level>0")[0];
        $html.="<li>а ". $num ." — с положительной</li>";

        $num=Query2DB("select count(1) from (select c.title,k.chat_id,sum(k.level) from Karma k,Chats c where c.id=k.chat_id GROUP BY k.chat_id) k2;")[0];
        $html.="<li>Аксинья сидит в ". $num ." ";
        $html.=($num%10==1 && $num%100<>11)? "группе":"группах" ;
        $html.=".</li>";


        if($num!=count(json_decode(file_get_contents("data/groups.json")))){
            print_r($num);
            file_put_contents("data/groups.json",json_encode(Query2DB("select c.title from Karma k,Chats c where c.id=k.chat_id GROUP BY k.chat_id;")),false);
        }

        $html.="<li>И она рада тебе 😉</li></ul>";
        return $html;
    }
}

?>