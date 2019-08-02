<?php


namespace frontend\controllers;


use common\components\helpers\RequestHelper;
use common\redis\TestRedis;
use common\redis\UserRedis;
use common\service\UserService;
use yii\web\Controller;

class LoginController extends Controller
{
    public function actionLogin()
    {
        $userId = RequestHelper::fIntG('userId');
        $userInfo = UserRedis::instance()->getUserInfoById($userId);
        var_dump($userInfo);die;
    }
}