<?php


namespace common\components\helpers;

use common\base\BaseHelper;

/**
 * curl 助手类
 * Class CurlHelper
 * @package common\components
 */
class CurlHelper extends BaseHelper
{

    /**
     * 校验 https 请求
     * @param $url
     * @return bool
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/24
     * @Time: 15:40
     */
    private static function checkHttps($url)
    {
        if(!empty($url) && ($schema = parse_url($url,PHP_URL_SCHEME)) != null )
        {
            return strtolower($schema) == 'https';
        }
        return false;
    }

    /**
     * curl get 请求
     * @param   string      $url        请求url
     * @param   int         $timeOut    请求超时时间
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/24
     * @Time: 16:15
     */
    public static function get($url,$timeOut=30)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        //https请求关闭证书校验
        if(self::checkHttps($url))
        {
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        }

        curl_setopt($ch,CURLOPT_TIMEOUT,$timeOut);

        return self::curlReturn($ch);
    }

    /**
     * curl post 请求
     * @param   string      $url    请求地址
     * @param   array       $data   请求参数 e.g. : ['name'=>'tom','age'=>19]
     * @param int $timeOut
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/24
     * @Time: 16:42
     */
    public static function post($url,$data=[],$timeOut=30)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        //https请求关闭证书校验
        if(self::checkHttps($url))
        {
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        }

        if(!empty($data))
        {
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        }

        curl_setopt($ch,CURLOPT_TIMEOUT,$timeOut);

        return self::curlReturn($ch);
    }

    /**
     * curl 单一请求 公共返回
     * @param   resource    $ch     curl句柄
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/24
     * @Time: 16:16
     */
    private static function curlReturn($ch)
    {
        $result = curl_exec($ch);
        if($result !== false)
        {
            $return = [
                'code' => '200',
                'msg' => 'success',
                'data' => $result
            ];
        }
        else
        {
            $return = [
                'code' => '-1',
                'msg' => curl_error($ch),
                'data' => ''
            ];
        }
        curl_close($ch);
        return $return;
    }
}