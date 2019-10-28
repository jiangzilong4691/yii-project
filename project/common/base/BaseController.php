<?php


namespace common\base;


use yii\web\Controller;

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
    protected function apiDataOut($returnInfo=[],$code = '200',$msg = 'success')
    {
        return $this->asJson([
            'code' => $code,
            'msg' => $msg,
            'data' => $returnInfo
        ]);
    }

    /**
     * jsonp跨域接口数据返回
     *
     * @param   string  $callback   回调处理函数名
     * @param   array   $data       返回数据
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/28
     * @Time: 12:49
     */
    protected function jsonpData($callback,$data)
    {
        exit($callback.'('.json_encode($data).')');
    }
}