<?php

class BotRedis
{
    private $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public function __deconstruct()
    {
        $this->redis->close();
    }

    function getDonates($user_id){
        $result=$this->redis->hGet('cookies',$user_id);
        return (empty($result))?0:$result;
    }
}