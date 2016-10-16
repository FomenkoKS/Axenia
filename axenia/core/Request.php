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

    public static function sendHtmlMessage($chat_id, $message, $addition = NULL)
    {
        $data = ['chat_id' => $chat_id, "text" => $message, "parse_mode" => "HTML", "disable_web_page_preview" => true];
        if ($addition != null) {
            $data = array_replace($data, $addition);
        }
        self::exec("sendMessage", $data);
    }


    public static function answerCallbackQuery($callback_query_id, $text, $addition = NULL)
    {
        $data = ['callback_query_id' => $callback_query_id, "text" => $text];
        if ($addition != null) {
            $data = array_replace($data, $addition);
        }
        self::exec("answerCallbackQuery", $data);
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

    public static function send($method, array $content, $post = true) {

        $url = self::$url . $method;
        if ($post)
            $reply = self::sendAPIRequest($url, $content);
        else
            $reply = self::sendAPIRequest($url, array(), false);
        return json_decode($reply, true);
    }

    public static function sendAPIRequest($url, array $content, $post = true) {
        if (isset($content['chat_id'])) {
            $url = $url . "?chat_id=" . $content['chat_id'];
            unset($content['chat_id']);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
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
        file_put_contents("log1", print_r($stat, true));
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

    public static function getChat($chat_id)
    {
        $data = array('chat_id' => $chat_id);

        return self::execJson("getChat", $data);
    }


}