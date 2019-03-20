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

    function getAllDonates($user_id){
        $result=$this->redis->hGet('cookies',$user_id);
        return (empty($result))?0:$result;
    }

    function setDonates($user_id, $donates)
    {
        $result=$this->redis->hSet('cookies',$user_id, $donates);
        return $result;
    }

    function insertBill($txn_id,$donate,$user_id)
    {
        $this->redis->hSet('bills',$txn_id."_u",$user_id);
        $result=$this->redis->hSet('bills',$txn_id."_n",$donate);
        return $result;
    }

    function getLimit($string){
        return $this->redis->get("limit:".$string);
    }

    function setLimit($string,$limit){
        return $this->redis->set("limit:".$string,$limit);
    }
}