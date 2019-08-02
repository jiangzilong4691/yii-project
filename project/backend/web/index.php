<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

//框架源码目录
$src = dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR.'src';

//通用配置文件目录
$commonConfigDir = dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'config';

//当前应用配置文件目录
$appConfigDir = dirname(__DIR__).DIRECTORY_SEPARATOR.'config';

//引入加载类
require ($src.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');
//引入框架
require ($src.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'yiisoft'.DIRECTORY_SEPARATOR.'yii2'.DIRECTORY_SEPARATOR.'Yii.php');
//引入通用配置
require ($commonConfigDir.DIRECTORY_SEPARATOR.'bootstrap.php');
//引入应用配置
require ($appConfigDir.DIRECTORY_SEPARATOR.'bootstrap.php');

//合并配置
$config = yii\helpers\ArrayHelper::merge(
    require $commonConfigDir . DIRECTORY_SEPARATOR . 'main.php',
    require $commonConfigDir . DIRECTORY_SEPARATOR . 'main-local.php',
    require $appConfigDir . DIRECTORY_SEPARATOR . 'main.php',
    require $appConfigDir . DIRECTORY_SEPARATOR . 'main-local.php'
);

$application = new yii\web\Application($config);
$application->run();