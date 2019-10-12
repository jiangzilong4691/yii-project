<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR .'src'. DIRECTORY_SEPARATOR . 'vendor',
    'language' => 'zh-CN',
    'timeZone' => 'PRC',
    'components' => [
        'cache' =>
            [
                'class' => 'yii\redis\Cache',
                'redis' => [
                    'hostname' => '',
                    'port' => 6379,
                    'database' => 0,
                    'password' => ''
                ],
                'keyPrefix' => 'cache:',
            ],
        //本地缓存：存储本地非状态数据
        'cache_local'=>
            [
                'class' => 'yii\redis\Cache',
                'redis' => [
                    'hostname' => '127.0.0.1',
                    'port' => 6379,
                    'database' => 0,
                    'password' => ''
                ],
                'keyPrefix' => 'cache:',
            ],
        'session' =>
            [
                'class' => 'common\base\RedisSession',
                'redis' => [
                    'hostname' => '',
                    'port' => 6379,
                    'database' => 1,
                    'password' => ''
                ],
                'keyPrefix'=>'zb_session:',
            ],
        'db' =>
            [
                'class' => 'yii\db\Connection',
                'charset' => 'utf8',
                'tablePrefix' => 'b8_',
                'username' => '',
                'password' => '',
                'dsn' => 'mysql:host=;port=3306;dbname=bo8tv',
                'attributes' => [
                    PDO::ATTR_TIMEOUT => 10
                 ]
            ],
        'vipDb' =>
            [
                'class' => 'yii\db\Connection',
                'dsn' => 'mysql:host=;port=3306;dbname=vip',
                'username' => '',
                'password' => '',
                'charset' => 'utf8',
                'tablePrefix' => 'b8_',
            ],
        'pollDb' =>
            [
                'class' => 'yii\db\Connection',
                'dsn' => 'mysql:host=;port=3306;dbname=b8_poll',
                'charset' => 'utf8',
                'tablePrefix' => 'b8_',
                'serverRetryInterval' => 10,
                'username' => '',
                'password' => '',
            ],
        //路由管理
        'urlManager' =>
            [
                'enablePrettyUrl' => true,
                'suffix'=>'',
                'showScriptName'=>false,
                'rules' => [
                ],
                'cache' => 'cache_local'
            ],
        //异常处理
        'errorHandler' =>
            [

            ]
    ],
];
