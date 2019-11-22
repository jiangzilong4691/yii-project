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
     *
     * @param $url
     *
     * @return bool
     *
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
     * CURL get请求
     *
     * @param   string      $url            请求地址
     * @param   int         $connTimeOut    尝试连接超时时间
     * @param   int         $execTimeout    执行超时时间
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/14
     * @Time: 10:43
     */
    public static function get($url,$connTimeOut=30,$execTimeout=60)
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

        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$connTimeOut);
        curl_setopt($ch,CURLOPT_TIMEOUT,$execTimeout);

        return self::curlReturn($ch);
    }

    /**
     * curl post 请求
     *
     * @param   string      $url            请求地址
     * @param   array       $data           请求参数 e.g. : ['name'=>'tom','age'=>19]
     * @param   bool        $postJson       是否发送json请求数据
     * @param   array       $custome        自定义参数信息 e.g. : ['header'=>['Content-type:application/json'],'special'=>'']
     * @param   int         $connTimeout    尝试连接超时时间
     * @param   int         $execTimeout    执行超时时间
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/24
     * @Time: 16:42
     */
    public static function post($url,$data=[],$postJson=false,$custome=[],$connTimeout=30,$execTimeout=60)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        //https请求关闭证书校验
        if(self::checkHttps($url))
        {
            //禁止curl验证对等证书
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
            //禁止校验公用名
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        }

        //设置请求头
        if(isset($custome['header']) && !empty($custome['header']))
        {
            curl_setopt($ch,CURLOPT_HTTPHEADER,$custome['header']);
        }

        if(!empty($data))
        {
            if($postJson)
            {
                $data = json_encode($data);
                curl_setopt($ch,CURLOPT_HTTPHEADER,['Content-type:application/json']);
            }
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        }

        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$connTimeout);
        curl_setopt($ch,CURLOPT_TIMEOUT,$execTimeout);

        return self::curlReturn($ch);
    }

    /**
     * curl 单一请求 公共返回
     *
     * @param   resource    $ch     curl句柄
     *
     * @return array
     *
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
                'rpData' => $result
            ];
        }
        else
        {
            $return = [
                'code' => '-1',
                'msg' => curl_error($ch),
                'rpData' => ''
            ];
        }
        curl_close($ch);
        return $return;
    }

    /**
     * curl 批量 get 请求
     *
     * @param array $urls   e.g. ['key'=>$url]
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/11/22
     * @Time: 15:38
     */
    public static function multiGet(Array $urls)
    {
        if(empty($urls))
        {
            return [];
        }
        $chs = [];
        $data = [];
        $mch = curl_multi_init();
        foreach ($urls as $key=>$url)
        {
            $chs[$key] = curl_init();
            curl_setopt_array($chs[$key],[
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0
            ]);
            curl_multi_add_handle($mch,$chs[$key]);
        }

        $active = null;
        // 执行批处理句柄
        do {
            $mrc = curl_multi_exec($mch, $active);
            if($active !== null)
            {
                echo $active;
            }
        } while ($active > 0);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mch) != -1) {
                do {
                    $mrc = curl_multi_exec($mch, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        foreach ($urls as $rqkey=>$url)
        {
            $data[$rqkey] = curl_errno($chs[$rqkey]) == 0 ? curl_multi_getcontent($chs[$rqkey]) : '';
            curl_multi_remove_handle($mch,$chs[$rqkey]);
        }
        curl_multi_close($mch);
        return $data;
    }
}