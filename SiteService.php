<?php

require_once("configs/site/config.php");

require_once('axenia/core/AbstractDao.php');
require_once('axenia/logic/BotDao.php');
require_once('axenia/logic/BotService.php');
/*
require_once('SiteDao.php');*/

class SiteService
{

    private $db;

    /**
     * Axenia constructor.
     */
    public function __construct()
    {
        $this->db = new BotDao();
    }


    public function getViewType($get)
    {
        switch (true) {
            case isset($get['username']):
                //$type = $this->db->getUserID($get['username']) ? "user" : "cover";
                break;
            case isset($get['user_id']):
                //$type = $this->db->getUserName($get['user_id']) ? "user" : "cover";
                break;
            case isset($get['chat_id']):
                $type='chat';
                //$type = $this->db->getGroupName($get['chat_id']) ? "chat" : "cover";
                break;
            case isset($get['donate']):
                //$type = ($get['donate']=="success") ? "thanks" : "cover";
                break;
            default:
                $type = "cover";
                break;
        }
        return $type;
    }

    public function getTopUsers($chat_id){
        $users=$this->db->getTop($chat_id, 100);
        return $users;
    }




/*
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
                $this->db->updateUserPhoto($user_id, $photo_id);
            }
        }

        return $user_id;
    }
*/
}

?>