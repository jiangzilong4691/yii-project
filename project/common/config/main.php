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
                    'hostname' => '49.234.97.237',
                    'port' => 6379,
                    'database' => 0,
                    'password' => 'r64E*U9XEcd!dL8L'
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
                    'password' => 'r64E*U9XEcd!dL8L'
                ],
                'keyPrefix' => 'cache:',
            ],
        'session' =>
            [
                'class' => 'common\base\RedisSession',
                'redis' => [
                    'hostname' => '49.234.97.237',
                    'port' => 6379,
                    'database' => 1,
                    'password' => '49.234.97.237'
                ],
                'keyPrefix'=>'zb_session:',
            ],
        'db' =>
            [
                'class' => 'yii\db\Connection',
                'charset' => 'utf8',
                'tablePrefix' => 'b8_',
                'username' => 'program_user',
                'password' => '0SYwCNjGPeM!$qns',
                'dsn' => 'mysql:host=192.168.20.5;port=3306;dbname=bo8tv',
                'attributes' => [
                    PDO::ATTR_TIMEOUT => 10
                 ]
            ],
        'vipDb' =>
            [
                'class' => 'yii\db\Connection',
                'dsn' => 'mysql:host=192.168.20.5;port=3306;dbname=vip',
                'username' => 'program_user',
                'password' => '0SYwCNjGPeM!$qns',
                'charset' => 'utf8',
                'tablePrefix' => 'b8_',
            ],
        'pollDb' =>
            [
                'class' => 'yii\db\Connection',
                'dsn' => 'mysql:host=192.168.20.5;port=3306;dbname=b8_poll',
                'charset' => 'utf8',
                'tablePrefix' => 'b8_',
                'serverRetryInterval' => 10,
                'username' => 'program_user',
                'password' => '0SYwCNjGPeM!$qns',
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
