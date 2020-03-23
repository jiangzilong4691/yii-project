<?php


namespace common\base;


use yii\web\Controller;
use yii\web\Response;

class BaseController extends Controller
{
    /**
     * 接口json数据返回 无特殊要求不再直接exit输出
     *
     * @param $returnInfo
     *
     * @return \yii\web\Response
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/5
     * @Time: 15:17
     */
    protected function apiJson($returnInfo=[],$code = '200',$msg = 'success')
    {
        if(isset($returnInfo['code'],$returnInfo['desc']))
        {
            $code = $returnInfo['code'];
            $msg = $returnInfo['desc'];
            $returnInfo = [];
        }
        return $this->asJson([
            'status' => $code,
            'msg' => $msg,
            'data' => $returnInfo
        ]);
    }

    /**
     * 跨域json数据返回
     *
     * @param   string      $callBack       回调函数名
     * @param   array       $data           返回信息
     * @param   string      $status         返回状态码
     * @param   string      $msg            返回状态信息
     *
     * @return \yii\console\Response|Response
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/3/23
     * @Time: 17:34
     */
    protected function apiJsonp($callBack,$data,$status='200',$msg='success')
    {
        $response = \Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSONP;
        if(isset($returnData['code'],$returnData['desc']))
        {
            $status = $data['code'];
            $msg    = $data['desc'];
            $returnData = [];
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