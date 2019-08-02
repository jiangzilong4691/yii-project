<?php


namespace common\components\helpers;

use common\base\BaseHelper;
use yii\web\Cookie;

class CookieHelper extends BaseHelper
{
    /**
     * 响应设置cookie
     * @return mixed|\yii\web\CookieCollection
     */
    private static function getCKForSet()
    {
        return \Yii::$app->response->cookies;
    }

    /**
     * 请求读取cookie
     * @return mixed|\yii\web\CookieCollection
     */
    private static function getCKForGet()
    {
        return \Yii::$app->request->cookies;
    }

    /**
     * 设置Cookie
     * @param string $key            键
     * @param mixed  $value          值
     * @param int $expire            有效时间
     * @param bool $httpOnly         是否HttpOnly
     * @param null $domain           域
     */
    public static function set($key,$value,$expire=3600,$httpOnly=true,$domain=null)
    {
        $ck=self::getCKForSet();
        $config=['name'=>$key,'value'=>$value,'expire'=>time()+$expire,'httpOnly'=>$httpOnly];
        if(isset($domain) && !empty($domain))
        {
            $config['domain']=$domain;
            if (preg_match("/\.nubb\.com/",$_SERVER["HTTP_HOST"])) {
                $config['domain'] = '.nubb.com';
            }
        }
        $ck->add(new Cookie($config));
        unset($config);
    }

    /**
     * 清除Cookie
     * @param string $key           键
     */
    public static function del($key,$domain=null)
    {
        $ck=self::getCKForGet();
        if(isset($ck[$key]))
        {
            $ck=self::getCKForSet();
            $config=['name'=>$key,'value'=>'','expire'=>1,'httpOnly'=>true];
            if(isset($domain) && !empty($domain))
            {
                $config['domain']=$domain;
                if (preg_match("/\.nubb\.com/",$_SERVER["HTTP_HOST"])) {
                    $config['domain'] = '.nubb.com';
                }
            }
            $ck->add(new Cookie($config));
            unset($config);
        }
    }

    /**获取Cookie
     * @param string $key                    键
     * @param bool   $default_value         不存在要返回的默认值
     * @return mixed
     */
    public static function get($key,$default_value=false)
    {
        $ck=self::getCKForGet();
        return $ck->getValue($key,$default_value);
    }
}