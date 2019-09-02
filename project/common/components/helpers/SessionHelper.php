<?php


namespace common\components\helpers;

use common\base\BaseHelper;
/**
 * session 助手类 copy原平台code
 * Class SessionHelper
 * @package common\components
 */
class SessionHelper extends BaseHelper
{
    /**
     * 获取session实例
     * @return \yii\web\Session
     * @author 姜海强
     */
    private static function getSession()
    {
        return \Yii::$app->session;
    }

    /**
     * 自定义session超时时间
     * @param $expireSeconds
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/25
     * @Time: 10:21
     */
    public static function setSessionTimeout($expireSeconds)
    {
        self::getSession()->setTimeout($expireSeconds);
    }

    /**
     * SESSION  获取
     * @param string $key                Key
     * @param mixed $defaultValue       Key
     * @return mixed
     */
    public static function get($key,$defaultValue=false)
    {
        return self::getSession()->get($key,$defaultValue);
    }

    /**
     * SESSION  设置
     * @param string $key           Key
     * @param mixed  $value         值
     * @param int    $expire        有效期
     */
    public static function set($key,$value,$expire=0)
    {
        if(is_int($expire) && $expire>0)
        {
            self::setSessionTimeout($expire);
        }
        return self::getSession()->set($key,$value);
    }

    /**
     * SESSION  清理
     * @param string $key      Key,不传会清空服务器SESSION
     * @return mixed|void
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/9/2
     * @Time: 10:29
     */
    public static function del($key=null)
    {
        if(isset($key))
        {
            return self::getSession()->remove($key);
        }
        else
        {
            return self::getSession()->destroy();
        }
    }

    /**
     * 获取SessionID
     * @return string
     * @author 姜海强 <jianghaiqiang@zhibo.tv>
     */
    public static function getSessionId()
    {
        return self::getSession()->getId();
    }
}