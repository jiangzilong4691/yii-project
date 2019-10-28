<?php


namespace common\base;


class BaseService
{
    /**
     * 单例对象池
     * @var array
     */
    protected static $instancePool = [];

    /**
     * 禁止子类克隆
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/25
     * @Time: 10:31
     */
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * 单例对象池获取实例
     * 注：一定要注释 return static 不然IDE无法定位子类实例 无法自动提示
     *
     * @return static
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/25
     * @Time: 10:35
     */
    public static function instance()
    {
        $class = get_called_class();
        if(!isset(self::$instancePool[$class]))
        {
            self::$instancePool[$class] = new $class();
        }
        return self::$instancePool[$class];
    }
}