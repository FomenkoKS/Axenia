<?php

class AbstractDao
{
    protected static $connection;

    public function connect()
    {
        // Try and connect to the database
        if (!isset(self::$connection)) {
            self::$connection = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
            self::$connection->query("SET SESSION collation_connection = 'utf8_general_ci'");
            self::$connection->query("SET NAMES 'utf8'");
        }

        // If connection was not successful, handle the error
        if (self::$connection === false) {
            return false;
        }
        return self::$connection;
    }

    /**
     * Query the database
     *
     * @param $query The query string
     * @return mixed The result of the mysqli::query() function
     */
    public function query($query)
    {
        $connection = $this->connect();
        $result = $connection->query($query);

        $out = array();

        if ($result == false) {
            printf("Error message: %s\n", $this->error());
            return false;
        }

        while ($row = $result->fetch_assoc()) {
            //$rows[] = $row;
            foreach ($row as $value) {
                array_push($out, $value);
            }
        }
        return $out;

    }

    /**
     * Fetch the last error from the database
     */
    public function error()
    {
        $connection = $this->connect();
        return $connection->error;
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
}