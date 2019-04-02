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

    function insertReward($user_id,$type){
        return $this->redis->sAdd('achievement:'.$user_id,$type);
    }

    function addTypeReward(){
        $this->redis->sAdd('achievementByLevel',10);
        $this->redis->sAdd('achievementByLevel',100);
        $this->redis->sAdd('achievementByLevel',500);
        $this->redis->sAdd('achievementByLevel',1000);
        $this->redis->sAdd('achievementByLevel',5000);
        $this->redis->sAdd('achievementByLevel',10000);
        $this->redis->sAdd('achievementByLevel',50000);
        $this->redis->sAdd('achievementByLevel',100000);
        $this->redis->sAdd('achievementByLevel',1000000);
        $this->redis->sAdd('achievementByLevel',10000000);
        $this->redis->sort('achievementByLevel');
    }

    function getTitulLevels(){
        return $this->redis->sMembers('achievementByLevel');
    }
}