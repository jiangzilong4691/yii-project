<?php
namespace common\redis;

use common\base\BaseRedis;
use common\components\helpers\CacheHelper;
use common\components\helpers\ComHelper;

class UserRedis extends BaseRedis
{
    /**
     * redis 连接配置
     * @return mixed
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/1
     * @Time: 13:51
     */
    protected function getConfig()
    {
        return \Yii::$app->params['redis']['user'];
    }

    // 0 库
    const REDIS_DB_0 = 0;

    //---------------------------------KEY---------------------------------//

    /**
     * 用户信息存储KEY : 用户ID 做key
     * @param   int     $userId     用户ID
     * @return string
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/30
     * @Time: 9:54
     */
    private function getUserInfoByIdKey($userId)
    {
        return 'users:'.$userId;
    }

    //---------------------------------KEY---------------------------------//

    /**
     * 缓存用户信息 ：获取
     * @param   int     $userId     用户ID
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/30
     * @Time: 10:21
     */
    public function getUserInfoById($userId)
    {
        $redis = $this->getRedis(self::REDIS_DB_0);
        if($redis)
        {
            $key = $this->getUserInfoByIdKey($userId);
            $info = $redis->hGetAll($key);
            if(is_array($info) && !empty($info))
            {
                return ComHelper::formatHumpInfo($info);
            }
        }
        return [];
    }

    /**
     * 缓存用户信息 ： 设置
     * @param   array   $userInfo   用户信息
     * @return bool
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/30
     * @Time: 11:26
     */
    public function setUserInfoById($userInfo)
    {
        $redis = $this->getRedis(self::REDIS_DB_0,true);
        if($redis)
        {
            $key = $this->getUserInfoByIdKey($userInfo['userId']);
            if(!$redis->exists($key))
            {
                $result = $redis->multi(\Redis::PIPELINE)
                    ->hMSet($key,$userInfo)
                    ->expire($key,7*CacheHelper::CACHE_ONE_HOUR)
                    ->exec();
                if($result[0])
                {
                    return true;
                }
            }
        }
        return false;
    }

}