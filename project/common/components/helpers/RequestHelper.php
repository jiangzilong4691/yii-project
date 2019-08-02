<?php


namespace common\components\helpers;


use common\base\BaseHelper;
/**
 * web 请求助手类
 * Class RequestHelper
 * @package common\components
 */
class RequestHelper extends BaseHelper
{

    /**
     * string 类型
     */
    const STRING = 'string';
    /**
     * int类型
     */
    const INT = 'int';
    /**
     * float类型
     */
    const FLOAT = 'float';

    /**参数过滤
     * @param string $type 转换类型
     * @param mixed  $val   e.g. :$_REQUEST['userName'],$_POST['passWord'],$_GET['redirectUrl']
     * @return float|int|string     过滤后的值
     */
    public static function filterParam($type = 'string', $val = '')
    {
        if (isset($val))
        {
            $type = strtolower($type);
            switch ($type)
            {
                case self::STRING:
                    return addslashes(trim($val));
                case self::INT:
                    return (int)trim($val);
                case self::FLOAT:
                    return (float)trim($val);
                default:
                    return addslashes(trim($val));
            }
        }
        return false;
    }

    /**
     * 非区分GET|POST 获取STRING类型数据
     * @param string $key
     * @return string
     */
    public static function fString($key)
    {
        return (isset($_REQUEST[$key]) && !empty(trim($_REQUEST[$key]))) ? self::filterParam(self::STRING, $_REQUEST[$key]) : '';
    }

    /**
     * 非区分GET|POST 获取INT类型数据
     * @param string $key
     * @return int
     */
    public static function fInt($key)
    {
        return isset($_REQUEST[$key]) ? self::filterParam(self::INT, $_REQUEST[$key]) : 0;
    }

    /**
     * 非区分GET|POST 获取FLOAT类型数据
     * @param string $key
     * @return float
     */
    public static function fFloat($key)
    {
        return isset($_REQUEST[$key]) ? self::filterParam(self::FLOAT, $_REQUEST[$key]) : 0;
    }

    /**
     * 获取GET STRING类型数据
     * @param string $key
     * @return float|int|string
     */
    public static function fStringG($key)
    {
        return (isset($_GET[$key]) && !empty(trim($_GET[$key]))) ? self::filterParam(self::STRING, $_GET[$key]) : '';
    }

    /**
     * 获取GET Int类型数据
     * @param string $key
     * @return float|int|string
     */
    public static function fIntG($key)
    {
        return isset($_GET[$key]) ? self::filterParam(self::INT, $_GET[$key]) : 0;
    }

    /**
     * 获取GET Float类型数据
     * @param string $key
     * @return float|int|string
     */
    public static function fFloatG($key)
    {
        return isset($_GET[$key]) ? self::filterParam(self::FLOAT, $_GET[$key]) : 0;
    }

    /**
     * 获取POST STRING类型数据
     * @param string $key
     * @return float|int|string
     */
    public static function fStringP($key)
    {
        return (isset($_POST[$key]) && !empty(trim($_POST[$key]))) ? self::filterParam(self::STRING, $_POST[$key]) : '';
    }

    /**
     * 获取POST INT类型数据
     * @param string $key
     * @return float|int|string
     */
    public static function fIntP($key)
    {
        return isset($_POST[$key]) ? self::filterParam(self::INT, $_POST[$key]) : 0;
    }

    /**
     * 获取POST Float类型数据
     * @param string $key
     * @return float|int|string
     */
    public static function fFloatP($key)
    {
        return isset($_POST[$key]) ? self::filterParam(self::FLOAT, $_POST[$key]) : 0;
    }
}