<?php
function apiRequestWebhook($method, $parameters)
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

function apiRequestJson($method, $parameters)
{
    if (!is_string($method)) {
        error_log("Method name must be a string\n", 3, "/wwww/abrikoseg.ru/anfisa/my-errors.log");
        return false;
    }

    if (!$parameters) {
        $parameters = array();
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n", 3, "/wwww/abrikoseg.ru/anfisa/my-errors.log");
        return false;
    }


    $parameters["method"] = $method;

    $handle = curl_init(API_URL);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
    curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

    return exec_curl_request($handle);
}

/**
 * Делает запрос в базу данных
 *
 * $a = !array();      // This will === true;
 * $a = !array('a');   // This will === false;
 * $s = !"";           // This will === true;
 * $s = !"hello";      // This will === false;
 *
 * @param $query sql Запрос
 * @return array|bool Массив значений или false если запрос не выполнен
 */
function Query2DB($query)
{
    $mysqli = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
    $mysqli->connect_errno;
    $mysqli->query("SET SESSION collation_connection = 'utf8_general_ci'");
    $mysqli->query("SET NAMES 'utf8'");
    $out = array();

    $result = $mysqli->query($query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            foreach ($row as $value) {
                array_push($out, $value);
            }
        }
        $mysqli->close();
        return $out;
    } else {
        printf("Error message: %s\n", $mysqli->error);
        $mysqli->close();
        return false;
    }
}

/**
 * Содержится ли элемент в списке
 * @param $enumString String с элементами через , (1, '23', 'test')
 * @param $value
 * @return true/false
 */
function isInEnum($enumString, $value)
{
    $enumArray = explode(',', $enumString);
    return in_array($value, $enumArray);
}


?>