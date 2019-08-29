<?php


namespace common\base;


use yii\web\Controller;

class BaseController extends Controller
{
    /**
     * 接口json数据返回 无特殊要求不再直接exit输出
     * @param $returnInfo
     * @return \yii\web\Response
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/5
     * @Time: 15:17
     */
    protected function apiDataOut($returnInfo)
    {
        return $this->asJson($returnInfo);
    }
}