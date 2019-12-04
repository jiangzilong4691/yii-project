<?php


namespace common\redis;


use common\base\BaseRedis;

class TestRedis extends BaseRedis
{
    protected function getConfig($getMaster)
    {
        $selected = $getMaster ? 'master' : 'slaves';
        if(isset(\Yii::$app->params['redis']['result'][$selected]))
        {
            return \Yii::$app->params['redis']['result'][$selected];
        }
        return [];
    }

    public function test()
    {
        $redis = $this->getRedis(0);
        var_dump($this->slaveConn);
        var_dump(self::$_redisConnPool);die;
    }
}