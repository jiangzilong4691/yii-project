<?php


namespace common\components\helpers;

use common\base\BaseHelper;
class ComHelper extends BaseHelper
{
    /**
     * 格式化字段信息为驼峰
     *
     * @param array $fields   一维字段['zhibo_id','insert_time']
     *
     * @return string
     *
     * @author 姜海强 <jianghaiqiang@zhibo.tv>
     */
    public static function formatFields(array $fields)
    {
        if(!empty($fields))
        {
            foreach ($fields as $key=>$field)
            {
                $tuoFengField=self::lineToTuoFeng($field);
                $field='`'.$field.'`';
                if($field != $tuoFengField)
                {
                    $field=$field.' AS `'.$tuoFengField.'`';
                }
                $fields[$key]=$field;
                unset($tuoFengField);
            }
            return ' '.implode(',',$fields).' ';
        }
        return ' * ';
    }

    /**
     * 格式化为驼峰key数组
     *
     * @param array $info   一维关联数组 e.g.: ['user_name'=>'super man','room_num'=>'10086','user_mobile'=>'110']
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/30
     * @Time: 10:11
     */
    public static function formatHumpInfo(Array $info)
    {
        if(!empty($info))
        {
            foreach ($info as $key=>$value)
            {
                $tuoFengKey = self::lineToTuoFeng($key);
                if($tuoFengKey != $key)
                {
                    $info[$tuoFengKey]=$value;
                    unset($info[$key]);
                }
                unset($tuoFengKey);
            }
        }
        return $info;
    }

    /**
     * 下划线分割转驼峰
     *
     * @param string $str             字符串
     * @param bool   $ucFirst         true为大驼峰，false小驼峰
     *
     * @return mixed|string
     */
    public static function lineToTuoFeng($str,$ucFirst=false)
    {
        $str = ucwords(str_replace('_', ' ', $str));
        $str = str_replace(' ','',lcfirst($str));
        return $ucFirst ? ucfirst($str) : $str;
    }

    /**
     * 获取客户端ip
     *
     * @return string|null
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/24
     * @Time: 17:51
     */
    public static function getClientIp()
    {
        $ip = '';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
            return $ip[0];
        }
        elseif (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']))
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * 获取long型客户端ip
     *
     * @return int
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/24
     * @Time: 17:54
     */
    public static function getClientIpLong()
    {
        return ip2long(self::getClientIp());
    }
}