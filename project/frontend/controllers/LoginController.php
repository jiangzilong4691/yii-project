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

    public function actionGetRoomInfo()
    {
        $roomNum = RequestHelper::fIntG('room');
        $curlData = CurlHelper::get('http://rest.zhibo.tv/room/get-room-info-new?roomId='.$roomNum);
        if($curlData['code'] == '200')
        {
            $rpData = json_decode($curlData['rpData'],true);
            if(isset($rpData['status']) && $rpData['status'] == '200')
            {
                return $this->apiDataOut($rpData['data']);
            }
        }
        return $this->apiDataOut($curlData);
    }

    public function actionCurl()
    {
        $info = CurlHelper::get('http://rest.zhibo.tv/room/get-room-info');
        $data = json_decode($info['rpdata'],true);
        return $this->apiDataOut($data);
    }

    public function actionTest()
    {
        var_dump($_COOKIE);die;
    }
}