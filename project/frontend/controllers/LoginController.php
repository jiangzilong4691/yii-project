<?php


namespace frontend\controllers;


use common\base\redisSentinel\SentinelPool;
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

    public function actionCheckSentinel()
    {
        $config = [
            'zilong' => [
                'masterName' => 'zilong',
                'redisConfig' => [
                    'password' => 'r64E*U9XEcd!dL8L',
                    'timeout'  => 10
                ],
                'group' => [
                    ['host'=>'49.234.97.237','port'=>'26379'],
                    ['host'=>'49.234.97.237','port'=>'26380'],
                    ['host'=>'49.234.97.237','port'=>'26381'],
                ]
            ]
        ];
        $masterConfig = SentinelPool::instance($config['zilong'])->getMasterConfig();
    }
}