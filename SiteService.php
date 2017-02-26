<?php

require_once("configs/site/config.php");
require_once('axenia/core/util.php');
require_once('axenia/core/AbstractDao.php');
require_once('axenia/core/Request.php');
require_once('axenia/logic/BotDao.php');

require_once('SiteDao.php');

class SiteService
{

    private $db;

    /**
     * Axenia constructor.
     */
    public function __construct()
    {
        $this->db = new SiteDao();
    }

    public function getUserID($username)
    {
        return $this->db->getUserID($username);
    }

    public function createRewardView($user_id)
    {
        $rewards = $this->db->getUserRewardsFull($user_id);

        if (isset($rewards[0])) {
            $a = array_chunk($rewards, 4);
            $html = "<h2>Награды</h2>";
            foreach ($a as $item) {
                $html .= "<div class=\"reward col-xs-6 col-md-3 col-lg-2\" style=\"background-image: url('img/rewards/" . $item[0] . ".png')\" data-placement=\"bottom\" data-toggle=\"tooltip\" data-original-title=\"" . $item[1] . "(" . $item[2] . "). " . $item[3] . "\"></div>";
            }
            $html .= "<script>$('.reward').tooltip();</script>";
            return $html;
        }
        return "";
    }


    public function createCharBarView($id, $type)
    {
        switch ($type) {
            case "group":
                $rez = $this->db->getGroupKarmaList($id);
                $title = "Карма в этой группе распределена следующим образом:";
                break;
            case "user":
                $rez = $this->db->getUserKarmaList($id);
                $title = "Карма пользователя в группах:";
                break;
        }
        $html = "<div class=\"row text-center\"><h2>" . $title . "</h2></div>";

        $a = array_chunk($rez, 5);
        $max = $a[0][3];
        foreach ($a as $value) {
            $html .= "<div class='row'>";
            switch ($type) {
                case "group":
                    $html .= "<div class='user_photo img-circle col-md-1' style='background-image:url(users/" . $value[4] . ".jpg)'></div><a class='col-md-1 title load_user' onclick='load_user(" . $value[4] . ")'>";
                    //$this->GetAvatar($value[4]);
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

    public function getViewType($get)
    {
        switch (true) {
            case isset($get['username']):
                $type = $this->db->getUserID($get['username']) ? "user" : "stat";
                break;
            case isset($get['user_id']):
                $type = $this->db->getUserName($get['user_id']) ? "user" : "stat";
                break;
            case isset($get['group_id']):
                $type = $this->db->getGroupName($get['group_id']) ? "group" : "stat";
                break;
            default:
                $type = "stat";
                break;
        }
        return $type;
    }

    public function createHeaderView($type, $get)
    {
        switch ($type) {
            case 'user':
                $user_id = isset($get['user_id']) ? $get['user_id'] : $this->db->getUserID($get['username']);
                $header = "<div class='user_photo img-circle' style='background-image:url(users/" . $user_id . ".jpg)'></div>";
                $header .= isset($get['username']) ? $get['username'] : $this->db->getUserName($user_id);
                $header .= " в гостях у Аксиньи";
                break;
            case 'group':
                $header = "<a href='".PATH_TO_SITE."'><img class=\"logo\" src=\"/img/logo.png\" alt=\"Axenia's logo\"></a>Аксинья в группе «" . $this->db->getGroupName($get['group_id']) . "»";
                break;
            case 'stat':
                $header = "<a href='".PATH_TO_SITE."'><img class=\"logo\" src=\"/img/logo.png\" alt=\"Axenia's logo\"></a>" . $get['username'] . " в гостях у Аксиньи";
                break;
        }
        return $header;
    }


    public function rememberUserPhoto($user_id)
    {
        //$photos = apiRequest("getUserProfilePhotos", array('user_id' => $user_id));
        $photos = Request::exec("getUserProfilePhotos", ['user_id' => $user_id]);
        if ($photos['total_count'] > 0) {
            $photo_id = $photos['photos'][0][0]['file_id'];
            $isInDB = $this->db->isUserPhotoRemembered($user_id, $photo_id);
            if (!$isInDB || !file_exists('users/' . $user_id . '.jpg')) {
                $photo = Request::exec("getFile", ['file_id' => $photo_id]);
                $photo_file = file_get_contents(API_FILE_URL . $photo['file_path']);
                $filename = 'users/' . $user_id . '.jpg';
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
                $this->db->updateUserPhoto($user_id, $photo_id);
            }
        }
        return $user_id;
    }

//    public function CheckData()
//    {
//        $num = Query2DB("select count(1) from Users where username<>''")[0];
//        if ($num != count(json_decode(file_get_contents("data/users.json")))) {
//            $a = array_chunk(Query2DB("select id,username,firstname,lastname from Users where username<>''"), 4);
//            $fp = fopen('data/users.json', 'w');
//            fwrite($fp, json_encode($a));
//            fclose($fp);
//        }
//        $num = Query2DB("select count(1) from (select c.title,k.chat_id,sum(k.level) from Karma k,Chats c where c.id=k.chat_id GROUP BY k.chat_id) k2;")[0];
//        if ($num != count(json_decode(file_get_contents("data/groups.json")))) {
//            file_put_contents("data/groups.json", json_encode(Query2DB("select c.title from Karma k,Chats c where c.id=k.chat_id GROUP BY k.chat_id;")), false);
//        }
//    }


    public function createStatsView()
    {
        $html = "<ul class=\"list-unstyled\">";

        $num = $this->db->getCountAllKarma();
        $html .= "<li>В кармохранилище содержится " . round($num, 1) . " кармы.</li>";

        $num = $this->db->getCountUsers();
        $html .= "<li>Аксинья знает " . $num . " человек";
        if ($num % 10 == 1 && $num % 100 <> 11) $html .= "а";
        $html .= ",</li>";

        $num = $this->db->getCountUsernames();
        $html .= "<li>из них " . $num . " завели себе юзернейм.</li>";

        $num = $this->db->getCountKarmaNegative();
        $html .= "<li>" . $num . " с отрицательной кармой,</li>";

        $num = $this->db->getCountKarmaPositive();
        $html .= "<li>а " . $num . " — с положительной</li>";

        $num = $this->db->getCountGroups();
        $html .= "<li>Аксинья сидит в " . $num . " ";
        $html .= ($num % 10 == 1 && $num % 100 <> 11) ? "группе" : "группах";
        $html .= ".</li>";
        $html .= "<li>И она рада тебе 😉</li></ul>";
        return $html;
    }

    public function getUserListJson($postQuery)
    {
        $query = stripslashes($postQuery);
        $users = $this->db->getUsersByName($query, 5);
        $out = array_chunk($users, 4);
        return json_encode($out);
    }

    public function getGroupListJson($postQuery)
    {
        $query = stripslashes($postQuery);
        $groups = $this->db->getGroupsByName($query, 5);
        $out = array_chunk($groups, 2);
        return json_encode($out);
    }

}

?>