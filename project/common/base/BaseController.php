<?php


namespace common\base;


use yii\web\Controller;
use yii\web\Response;

class BaseController extends Controller
{
    /**
     * api json 接口数据返回
     *
     * @param mixed     $returnData     接口返回数据
     * @param string    $status         返回状态码
     * @param string    $msg            状态信息
     *
     * @return \yii\console\Response|Response
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/3/23
     * @Time: 16:54
     */
    protected function apiJson($returnData,$status='200',$msg='success')
    {
        $response = \Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        if(is_array($returnData) && isset($returnData['code'],$returnData['desc']))
        {
            $status = $returnData['code'];
            $msg    = $returnData['desc'];
            $returnData = isset($returnData['data'])?$returnData['data']:[];
        }
        $response->data = [
            'status' => $status,
            'msg' => $msg,
            'data' => $returnData
        ];
        return $response;
    }

    /**
     * api jsonp jsonp跨域接口数据返回
     *
     * @param   string      $callBack   跨域函数名值
     * @param   mixed       $data       返回数据信息
     * @param   string      $status     返回状态码
     * @param   string      $msg        返回状态信息
     *
     * @return \yii\console\Response|Response
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/3/23
     * @Time: 17:03
     */
    protected function apiJsonp($callBack,$data,$status='200',$msg='success')
    {
        $response = \Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSONP;
        if(is_array($data) && isset($returnData['code'],$returnData['desc']))
        {
            $status = $data['code'];
            $msg    = $data['desc'];
            $returnData = isset($data['data'])?$data['data']:[];
        }
        $response->data = [
            'callback' => $callBack,
            'data' => [
                'status' => $status,
                'msg' => $msg,
                'data' => $returnData
            ]
        ];
        return $response;
    }

    /**
     * jsonp接口数据返回
     *
     * @param $callback
     * @param $data
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/28
     * @Time: 11:34
     */
    protected function jsonpData($callback,$data)
    {
        exit($callback.'('.json_encode($data).')');
    }
}