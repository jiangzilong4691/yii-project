<?php


namespace console\controllers;


use yii\console\Controller;

class TestController extends Controller
{
    public function actionTest()
    {
        echo php_sapi_name();
    }
}