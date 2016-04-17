<?php

class DB
{
    protected $credential;

    /**
     * DB constructor.
     */
    public function __construct($user, $password, $database)
    {
        $this->credential = array(
            'user' => $user,
            'password' => $password,
            'database' => $database,
        );
    }

    protected function doQuery($query)
    {
        $mysqli = new mysqli('localhost', $this->credential['user'], $this->credential['password'], $this->credential['database']);
        $mysqli->connect_errno;
        $mysqli->query("SET SESSION collation_connection = 'utf8_general_ci'");
        $mysqli->query("SET NAMES 'utf8'");
        $a = array();
        if ($result = $mysqli->query($query)) {
            while ($row = mysqli_fetch_assoc($result)) {
                foreach ($row as $value) {
                    array_push($a, $value);
                }
            }
            $mysqli->close();
            return $a;
        } else {
            $mysqli->close();
            return false;
        }
    }
}

?>