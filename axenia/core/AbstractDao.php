<?php

class AbstractDao
{
    protected static $connection;

    public function select($query, $isNeedToConvert = true)
    {
        $out = array();

        $connection = $this->connect();
        $result = $connection->query($query);

        if ($result) {
            if ($isNeedToConvert) {
                while ($row = $result->fetch_assoc()) {
                    //$rows[] = $row;
                    foreach ($row as $value) {
                        array_push($out, $value);
                    }
                }
            } else {
                while ($row = $result->fetch_assoc()) {
                    array_push($out, $row);
                }
            }

        } else {
            error_log("Error query: " . $query . "\n " . $this->error() . "\n");

            return false;
        }

        return $out;
    }

    public function connect()
    {
        // Try and connect to the database
        if (!isset(self::$connection)) {
            self::$connection = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
            /* проверка соединения */
            if (self::$connection->connect_errno) {
                error_log(printf("Error connection: %s\n", self::$connection->connect_error));
                exit();
            }
            self::$connection->query("SET SESSION collation_connection = 'utf8_general_ci'");
            self::$connection->query("SET NAMES 'utf8'");
        }

        // If connection was not successful, handle the error
        if (self::$connection === false) {
            return false;
        }

        return self::$connection;
    }

    public function disconnect()
    {
        if (!isset(self::$connection)) {
            try {
                self::$connection->close();
            } catch (Exception $e) {
                //do noting
            }
            self::$connection = null;
        }
    }

    /**
     * Fetch the last error from the database
     */
    public function error()
    {
        $connection = $this->connect();
        $errorMessage = $connection->error;
        $this->disconnect();
        return $errorMessage;
    }

    public function selectOne($query, $isNeedToConvert = true)
    {
        $out = array();

        $connection = $this->connect();
        $result = $connection->query($query);

        if ($result) {
            if ($isNeedToConvert) {
                $row = $result->fetch_assoc();
                foreach ($row as $value) {
                    array_push($out, $value);
                }
            } else {
                $row = $result->fetch_assoc();
                array_push($out, $row);
            }
        } else {
            error_log("Error query: " . $query . "\n " . $this->error() . "\n");

            return false;
        }

        return $out;
    }

    public function delete($query)
    {
        return $this->update($query);
    }

    public function update($query)
    {
        $connection = $this->connect();
        $result = $connection->query($query);

        if ($result) {
            return true;
        } else {
            error_log("Error query: " . $query . "\n " . $this->error() . "\n");

            return false;
        }
    }

    public function insert($query)
    {
        return $this->update($query);
    }

    /**
     * Quote and escape value for use in a database query
     *
     * @param string $value The value to be quoted and escaped
     * @return string The quoted and escaped string
     */
    public function quote($value)
    {
        $connection = $this->connect();

        return "'" . $connection->real_escape_string($value) . "'";
    }

    /**
     * Just a little function which mimics the original mysql_real_escape_string but which doesn't need an active mysql connection.
     * @param $inp
     * @return array|mixed
     */
    public function escape_mimic($inp)
    {
        if (is_array($inp))
            return array_map(__METHOD__, $inp);

        if (!empty($inp) && is_string($inp)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }

        return $inp;
    }

    public function clearForInsert($inp)
    {
        return "'" . (isset($inp) ? $this->escape_mimic($inp) : '') . "'";
    }
}