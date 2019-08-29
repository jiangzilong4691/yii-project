<?php


namespace frontend\controllers;


use common\components\helpers\CurlHelper;
use common\components\helpers\RequestHelper;
use common\service\UserService;

class LoginController extends BaseController
{
    public function actionLogin()
    {
        $userId = RequestHelper::fIntG('userId');
        $userInfo = UserService::instance()->getUserInfoById($userId);
        $data = [
            'code' => '200',
            'msg' => 'success',
            'info' =>[
                'useInfo' => $userInfo
            ]
        ];
        return $this->apiDataOut($data);
    }

    public function actionCurl()
    {
        $info = CurlHelper::get('http://rest.zhibo.tv/room/get-room-info');
        $data = json_decode($info['data'],true);
        return $this->apiDataOut($data);
    }

    public function actionTest()
    {
        var_dump($_COOKIE);die;
    }
}