<?php

class Request
{

    public static $url;

    /**
     * @param mixed $url
     */
    public static function setUrl($url)
    {
        self::$url = $url;
    }

    public static function apiRequestWebhook($method, $parameters)
    {
        if (!is_string($method)) {
            error_log("Method name must be a string\n");

            return false;
        }

        if (!$parameters) {
            $parameters = array();
        } else if (!is_array($parameters)) {
            error_log("Parameters must be an array\n");

            return false;
        }

        $parameters["method"] = $method;

        header("Content-Type: application/json");
        echo json_encode($parameters);

        return true;
    }

    public static function sendTyping($chat_id)
    {
        return self::exec("sendChatAction", array('chat_id' => $chat_id, "action" => "typing"));
    }

    public static function exec($method, $parameters)
    {
        if (!is_string($method)) {
            error_log("Method name must be a string\n");

            return false;
        }

        if (!$parameters) {
            $parameters = array();
        } else if (!is_array($parameters)) {
            error_log("Parameters must be an array\n");

            return false;
        }

        foreach ($parameters as $key => &$val) {
            // encoding to JSON array parameters, for example reply_markup
            if (!is_numeric($val) && !is_string($val)) {
                $val = json_encode($val);
            }
        }
        $url = self::$url . $method . '?' . http_build_query($parameters);
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        return self::exec_curl_request($handle);
    }

    private static function exec_curl_request($handle)
    {
        $response = curl_exec($handle);
        if ($response === false) {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            error_log("Curl returned error $errno: $error\n");
            curl_close($handle);

            return false;
        }

        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);

        if ($http_code >= 500) {
            // do not wat to DDOS server if something goes wrong
            sleep(10);

            return false;
        } else if ($http_code != 200) {
            $response = json_decode($response, true);
            error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
            if ($http_code == 401) {
                throw new Exception('Invalid access token provided');
            }

            return false;
        } else {
            $response = json_decode($response, true);
            if (isset($response['description'])) {
                error_log("Request was successfull: {$response['description']}\n");
            }
            $response = $response['result'];
        }

        return $response;
    }

    public static function sendMessage($chat_id, $text, $addition = NULL)
    {
        $data = ['chat_id' => $chat_id, 'text' => $text];
        if ($addition != null) {
            $data = array_replace($data, $addition);
        }
        self::exec("sendMessage", $data);
    }

    public static function sendPhoto($chat_id, $path, $addition = NULL)
    {
        $data = ['chat_id' => $chat_id, 'photo' => $path];
        if ($addition != null) {
            $data = array_replace($data, $addition);
        }
        self::exec("sendPhoto", $data);
    }

    public static function sendDocument($chat_id, $path, $addition = NULL)
    {
        $data = ['chat_id' => $chat_id, 'document' => $path];
        if ($addition != null) {
            $data = array_replace($data, $addition);
        }
        self::exec("sendDocument", $data);
    }

    public static function sendInvoice($chat_id, $title, $description, $payload,$provider_token,$start_parameter,$currency,$prices , $addition = NULL)
    {
        $data = [
            'chat_id'           => $chat_id,
            'title'             => $title,
            'description'       => $description,
            'payload'           => $payload,
            'provider_token'    => $provider_token,
            'start_parameter'   => $start_parameter,
            'currency'          => $currency,
            'prices'            => $prices
        ];
        if ($addition != null) {
            $data = array_replace($data, $addition);
        }
        return self::exec("sendInvoice", $data);
    }

    public static function sendHtmlMessage($chat_id, $message, $addition = NULL)
    {
        $data = ['chat_id' => $chat_id, "text" => $message, "parse_mode" => "HTML", "disable_web_page_preview" => true];
        if ($addition != null) {
            $data = array_replace($data, $addition);
        }
        self::exec("sendMessage", $data);
    }

    public static function deleteMessage($chat_id, $message_id)
    {
        $data = ['chat_id' => $chat_id, "message_id" => $message_id];

        self::exec("deleteMessage", $data);
    }


    public static function answerCallbackQuery($callback_query_id, $text, $addition = NULL)
    {
        $data = ['callback_query_id' => $callback_query_id, "text" => $text];
        if ($addition != null) {
            $data = array_replace($data, $addition);
        }
        self::exec("answerCallbackQuery", $data);
    }

    public static function answerPreCheckoutQuery($pre_checkout_query_id)
    {
        $data = [
            'pre_checkout_query_id' => $pre_checkout_query_id,
            "ok" => true
        ];
        self::exec("answerPreCheckoutQuery", $data);
    }


    public static function editMessageText($chat_id, $message_id, $text, $addition = NULL)
    {
        $data = ['chat_id' => $chat_id, "message_id" => $message_id, "text" => $text];
        if ($addition != null) {
            $data = array_replace($data, $addition);
        }
        self::execJson("editMessageText", $data);
    }

    public static function execJson($method, $parameters)
    {
        if (!is_string($method)) {
            error_log("Method name must be a string\n");

            return false;
        }

        if (!$parameters) {
            $parameters = array();
        } else if (!is_array($parameters)) {
            error_log("Parameters must be an array\n");

            return false;
        }

        $parameters["method"] = $method;

        $handle = curl_init(self::$url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

        return self::exec_curl_request($handle);
    }

    public static function send($method, array $content, $post = true)
    {

        $url = self::$url . $method;
        if ($post)
            $reply = self::sendAPIRequest($url, $content);
        else
            $reply = self::sendAPIRequest($url, array(), false);
        return json_decode($reply, true);
    }

    public static function sendAPIRequest($url, array $content, $post = true)
    {
        if (isset($content['chat_id'])) {
            $url = $url . "?chat_id=" . $content['chat_id'];
            unset($content['chat_id']);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        if (defined('BOT_PROXY') && defined('BOT_PROXYPORT')) {
            curl_setopt($ch, CURLOPT_PROXY, BOT_PROXY);
            curl_setopt($ch, CURLOPT_PROXYPORT, BOT_PROXYPORT);
            if (defined('BOT_PROXYUSERNAME') && defined('BOT_PROXYUSERPWD')) {
                curl_setopt($ch, CURLOPT_PROXYUSERNAME, BOT_PROXYUSERNAME);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, BOT_PROXYUSERPWD);
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type:multipart/form-data"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            $stat = fstat($content['photo']);
            curl_setopt($ch, CURLOPT_INFILESIZE, 5555);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public static function answerInlineQuery($inline_id, $results, $addition = NULL)
    {
        $data = array('inline_query_id' => $inline_id, 'results' => $results);
        if ($addition != null) {
            $data = array_replace($data, $addition);
        }
        self::execJson("answerInlineQuery", $data);
    }

    public static function getChatAdministrators($chat_id)
    {
        $data = array('chat_id' => $chat_id);

        return self::execJson("getChatAdministrators", $data);
    }

    public static function getFile($file_id)
    {
        $data = array('file_id' => $file_id);

        return self::execJson("getFile", $data);
    }

    public static function getChat($chat_id)
    {
        $data = array('chat_id' => $chat_id);
        return self::execJson("getChat", $data);
    }

    public static function getUserProfilePhotos($user_id)
    {
        $data = array('user_id' => $user_id);
        return self::execJson("getUserProfilePhotos", $data);
    }

    public static function getChatMember($user_id, $chat_id)
    {
        $data = array('user_id' => $user_id, 'chat_id' => $chat_id);

        return self::execJson("getChatMember", $data);
    }

    public static function isChatMember($user_id, $chat_id)
    {
        //The member's status in the chat. Can be “creator”, “administrator”, “member”, “left” or “kicked”
        $chatMember = self::getChatMember($user_id, $chat_id);

        if (isset($chatMember['status'])) {
            return $chatMember['status'];
        }
        return $chatMember;
    }

    public static function getChatMembersCount($chat_id)
    {
        $data = array('chat_id' => $chat_id);
        $response = 0;
        try {
            $response = self::execJson("getChatMembersCount", $data);
        } catch (Exception $error) {
            $response = -1;
        }
        return $response ? $response : -1;
    }


}