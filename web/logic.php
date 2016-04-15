<?
require_once("config.php");
require_once("functions.php");
if (isset($_POST['please'])) {
    $logic = new FemaleLogic();
    switch ($_POST['please']) {
        case 'userlist':
            echo $logic->GetUsers($_POST['query']);
            break;
        case 'grouplist':
            echo $logic->GetGroups($_POST['query']);
            break;
        case 'header':
            echo $logic->GetHeader("user",$_POST);
            break;
    }
}

class FemaleLogic
{
    public function GetAwards($user_id){
        $rewards=Query2DB("select t.img,t.title,t.level,r.description from Reward_Type t, Rewards r where r.type_id=t.id and r.user_id=".$user_id);

        if(isset($rewards[0])){
            $a=array_chunk($rewards,4);
            $html="<h2>Награды</h2>";
            foreach($a as $item){
                $html.="<div class=\"reward col-xs-6 col-md-3 col-lg-2\" style=\"background-image: url('img/rewards/".$item[0].".png')\" data-placement=\"bottom\" data-toggle=\"tooltip\" data-original-title=\"".$item[1]."(".$item[2]."). ".$item[3]."\"></div>";
            }
            $html.="<script>$('.reward').tooltip();</script>";
            return $html;
        }
    }

    public function TypeOfView($get)
    {
        switch (true) {
            case isset($get['username']):
                $type = GetUserID($get['username']) ? "user" : "stat";
                break;
            case isset($get['user_id']):
                $type = GetUserName($get['user_id']) ? "user" : "stat";
                break;
            case isset($get['group_id']):

                $type = GetGroupName($get['group_id']) ? "group" : "stat";
                break;
            default:
                $type = "stat";
                break;
        }
        return $type;
    }

    public function GetHeader($type, $get)
    {
        switch ($type) {
            case 'user':
                $user_id = (isset($get['user_id'])) ? $get['user_id'] : GetUserID($get['username']);
                $header = "<div class='user_photo img-circle' style='background-image:url(users/" . $user_id . ".jpg)'></div>";
                $header .= GetUserName($user_id);
                $header .= " в гостях у Аксиньи";
                break;
            case 'group':
                $header = "<img class=\"logo\" src=\"/img/logo.png\" alt=\"Axenia's logo\">Аксинья в группе «" . getGroupName($get['group_id']) . "»";
                break;
            case 'stat':
                $header = "<img class=\"logo\" src=\"/img/logo.png\" alt=\"Axenia's logo\">".$get['username'] . " в гостях у Аксиньи";
                break;
        }
        return $header;
    }

    public function MakeBarChart($id, $type)
    {
        switch ($type) {
            case "group":
                $query = "select u.username, u.firstname, u.lastname, k.level, u.id from Karma k, Users u where k.user_id=u.id and k.chat_id=" . $id . " order by level desc";
                $title = "Карма в этой группе распределена следующим образом:";

                break;
            case "user":
                $query = "select c.title, c.id, k.user_id, k.level, k.chat_id from Karma k, Chats c where k.chat_id=c.id and k.user_id=" . $id . " order by level desc";
                $title = "Карма пользователя в группах:";
                break;
        }
        $html = "<div class=\"row text-center\"><h2>" . $title . "</h2></div>";

        $a = array_chunk(Query2DB($query), 5);
        $max = $a[0][3];
        foreach ($a as $value) {
            $html .= "<div class='row'>";
            switch ($type) {
                case "group":
                    $html .= "<div class='user_photo img-circle col-md-1' style='background-image:url(users/" . $value[4] . ".jpg)'></div><a class='col-md-1 title load_user' onclick='load_user(" . $value[4] . ")'>";
                    $this->GetAvatar($value[4]);
                    $html .= ($value[0] == "") ? $value[1] . " " . $value[2] : $value[0];
                    $html .= "</a>";
                    break;
                case "user":
                    $html .= "<a class=\"title col-md-2 load_group\" onclick='load_group(" . $value[1] . ")'>";
                    $html .= $value[0] . "</a>";
                    break;
            }
            $val = round(($value[3] / $max) * 100);
            $html .= "<div class=\"col-md-10\"><div class=\"progress \"><div class=\"progress-bar\" role=\"progressbar\" aria-valuenow=\"" . $val . "\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: " . $val . "%;\">" . $value[3] . "</div></div></div></div>";
        }
        return $html;
    }

    public function GetAvatar($id)
    {
        $photos = apiRequest("getUserProfilePhotos", array('user_id' => $id));
        if ($photos['total_count'] > 0) {
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

    public function GetStats()
    {
        $html = "<ul class=\"list-unstyled\">";

        $num = Query2DB("select sum(level) from Karma")[0];
        $html .= "<li>В кармохранилище содержится " . round($num, 1) . " кармы.</li>";

        $num = Query2DB("select count(1) from Users")[0];
        $html .= "<li>Аксинья знает " . $num . " человек";
        if ($num % 10 == 1 && $num % 100 <> 11) $html .= "а";
        $html .= ",</li>";

        $num = Query2DB("select count(1) from Users where username<>''")[0];
        $html .= "<li>из них " . $num . " завели себе юзернейм.</li>";

        $num = Query2DB("select count(1) from Karma where level<0")[0];
        $html .= "<li>" . $num . " с отрицательной кармой,</li>";

        $num = Query2DB("select count(1) from Karma where level>0")[0];
        $html .= "<li>а " . $num . " — с положительной</li>";

        $num = Query2DB("select count(1) from (select c.title,k.chat_id,sum(k.level) from Karma k,Chats c where c.id=k.chat_id GROUP BY k.chat_id) k2;")[0];
        $html .= "<li>Аксинья сидит в " . $num . " ";
        $html .= ($num % 10 == 1 && $num % 100 <> 11) ? "группе" : "группах";
        $html .= ".</li>";
        $html .= "<li>И она рада тебе 😉</li></ul>";
        return $html;
    }

    public function CheckData()
    {
        $num = Query2DB("select count(1) from Users where username<>''")[0];
        //if($num!=count(json_decode(file_get_contents("data/users.json")))){
        $a = array_chunk(Query2DB("select id,username,firstname,lastname from Users where username<>''"), 4);
        $fp = fopen('data/users.json', 'w');
        fwrite($fp, json_encode($a));
        fclose($fp);
        //}
        //file_put_contents("ar.txt",print_r(array_chunk(Query2DB("select id,username,firstname,lastname from Users where username<>''"),4)));
        $num = Query2DB("select count(1) from (select c.title,k.chat_id,sum(k.level) from Karma k,Chats c where c.id=k.chat_id GROUP BY k.chat_id) k2;")[0];
        if ($num != count(json_decode(file_get_contents("data/groups.json")))) {
            print_r($num);
            file_put_contents("data/groups.json", json_encode(Query2DB("select c.title from Karma k,Chats c where c.id=k.chat_id GROUP BY k.chat_id;")), false);
        }
    }

    public function GetUsers($q)
    {
        $q=stripslashes($q);
        $query = "SELECT distinct u.id,u.username,u.firstname,u.lastname FROM Users u, Karma k, Chats c WHERE u.id=k.user_id and k.chat_id=c.id and (u.username like '%" . $q . "%' or u.firstname like '%" . $q . "%' or u.lastname like '%" . $q . "%') limit 5";
        $a = array_chunk(Query2DB($query), 4);
        return json_encode($a);
    }

    public function GetGroups($q)
    {
        $q=stripslashes($q);
        $query = "SELECT distinct c.id,c.title FROM Users u, Karma k, Chats c WHERE u.id=k.user_id and k.chat_id=c.id and c.title like '%" . $q . "%' limit 5";
        $a = array_chunk(Query2DB($query), 2);
        return json_encode($a);
    }
}

?>