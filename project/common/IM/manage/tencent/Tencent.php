<?php


namespace common\service\IM\manage\tencent;


use common\service\IM\manage\Curl;

class Tencent
{
    //管理账号
    protected $managerId = 'administrator';

    /**
     * 公共请求接口
     *
     * @param   string  $requestUrl
     * @param   array   $requestData
     * @param   string  $appId
     * @param   string  $managerSig
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/24
     * @Time: 10:52
     */
    protected function _comRequest($requestUrl,$requestData,$appId,$managerSig)
    {
        $params = [
            'sdkappid'  => $appId,
            'identifier'=> $this->managerId,
            'userSig'   => $managerSig,
            'random'    => mt_rand(),
            'contenttype' => 'json'
        ];
        $httpParams = http_build_query($params);
        $requestUrl .= '?'.$httpParams;
        return Curl::post($requestUrl,$requestData,true);
    }

    /**
     * 公共请求响应处理接口
     *
     * @param   array       $requestResult      请求结果
     * @param   callable    $callback           请求响应数据处理回调
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/25
     * @Time: 14:57
     */
    protected function _comReturn($requestResult,callable $callback)
    {
        if($requestResult['code'] == '200')
        {
            $resultData = json_decode($requestResult['rpData'],true);
            if(json_last_error() == JSON_ERROR_NONE)
            {
                return call_user_func_array($callback,[$resultData]);
            }
            return [
                'code' => '-1',
                'msg'  => '接口数据json解析错误',
                'result' => []
            ];
        }
        else
        {
            return [
                'code' => '-1',
                'msg'  => $requestResult['msg'],
                'result' => []
            ];
        }
    }
}