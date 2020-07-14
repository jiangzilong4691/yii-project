<?php


namespace common\components\helpers;


class RedisCacheHelper extends CacheHelper
{
    /**
     * 缓存组件
     *
     * @return \yii\caching\CacheInterface
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/7/14
     * @Time: 14:24
     */
    private static function getCache()
    {
        return \Yii::$app->cache;
    }

    /**
     * 缓存：读取
     *
     * @param   mixed   $key        缓存key
     * @param   bool    $useMaster  是否主库读
     *
     * @return mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/7/14
     * @Time: 14:29
     */
    public static function get($key,$useMaster=false)
    {
        $cache = self::getCache();
        if($useMaster)
        {
            $cache->enableReplicas = false;
        }
        return self::getCache()->get($key);
    }

    /**
     * 缓存：存储
     *
     * @param   mixed   $key    缓存key
     * @param   mixed   $value  缓存数据
     * @param   int     $expire 缓存过期时间
     *
     * @return bool
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/7/14
     * @Time: 14:30
     */
    public static function set($key,$value,$expire = 0)
    {
        return self::getCache()->set($key,$value,$expire);
    }

    /**
     * 缓存：批量存储 （批量存储数据不宜过多，本质是单个key-value存储）
     *
     * @param   array   $items      存储数据 key-value e.g. ['name'=>'jiang']
     * @param   int     $expire     过期时间
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/7/14
     * @Time: 14:36
     */
    public static function mset(Array $items,$expire = 0)
    {
        return self::getCache()->multiSet($items,$expire);
    }

    /**
     * 缓存：批量读取
     *
     * @param array $keys
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/7/14
     * @Time: 14:40
     */
    public static function mget(Array $keys)
    {
        return self::getCache()->multiGet($keys);
    }

    /**
     * 缓存：删除
     *
     * @param $key
     *
     * @return bool
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/7/14
     * @Time: 14:41
     */
    public static function delete($key)
    {
        return self::getCache()->delete($key);
    }
}