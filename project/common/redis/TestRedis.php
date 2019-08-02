<?php


namespace common\redis;


use common\base\BaseRedis;

class TestRedis extends BaseRedis
{
    protected function getConfig()
    {
        return \Yii::$app->params['redis']['result'];
    }

    public function test()
    {
        $redis = $this->getRedis(0);
        var_dump($this->slaveConn);
        var_dump(self::$_redisConnPool);die;
    }
}