<?php
require_once("configs/site/config.php");
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

function GetGroupName($id)
{
    $query = "SELECT title FROM Chats WHERE id=" . $id;
    return (!Query2DB($query)[0]) ? false : Query2DB($query)[0];
}

function Query2DB($query)
{
    $mysqli = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
    $mysqli->connect_errno;
    $mysqli->query("SET SESSION collation_connection = 'utf8_general_ci'");
    $mysqli->query("SET NAMES 'utf8'");
    $a = array();
    if ($result = $mysqli->query($query)) {
        while ($row = mysqli_fetch_assoc($result)) foreach ($row as $value) array_push($a, $value);
        $mysqli->close();
        return $a;
    } else {
        printf("Errormessage: %s\n", $mysqli->error);
        $mysqli->close();
        return false;
    }

}

function exec_curl_request($handle)
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
    //file_put_contents("array.txt",print_r($response,true));
    return $response;
}

function apiRequest($method, $parameters)
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
    $url = API_URL . $method . '?' . http_build_query($parameters);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    return exec_curl_request($handle);
}

?>