<?php
return [
    'adminEmail' => 'admin@example.com',
    'redis' =>
        [
            'result' =>
                [
                    'master' => [
                            'host' => '192.168.2.13',
                            'port' => '6379',
                            'password' => '3e2e1c913d27b3a4f45e3b275a01c84b26fb29',
                            'timeout' => '3'
                        ],
                    'slaves' => [
                            [
                                'host' => '192.168.2.13',
                                'port' => '6379',
                                'password' => '3e2e1c913d27b3a4f45e3b275a01c84b26fb29',
                                'timeout' => '3'
                            ],
                            [
                                'host' => '192.168.2.13',
                                'port' => '6379',
                                'password' => '3e2e1c913d27b3a4f45e3b275a01c84b26fb29',
                                'timeout' => '3'
                            ]
                        ]
                ],
            'user' =>
                [
                    'master' => [
                        'host'=>'192.168.2.13',
                        'port'=>'6379',
                        'password'=>'3e2e1c913d27b3a4f45e3b275a01c84b26fb29',
                        'timeout'=>'3'
                    ],
                    'slaves' => [
                        [
                            'host'=>'192.168.2.13',
                            'port'=>'6379',
                            'password'=>'3e2e1c913d27b3a4f45e3b275a01c84b26fb29',
                            'timeout'=>'3'
                        ]
                    ]
                ]
        ],
    'redisSentinel' => [
        'zilong' => [
            'masterName' => 'zilong',
            'redisConfig' => [
                'password' => '1111111',
                'timeout' => 60
            ],
            'groups' => [
                ['host'=>'49.234.97.237','port'=>'26379'],
                ['host'=>'49.234.97.237','port'=>'26380'],
                ['host'=>'49.234.97.237','port'=>'26381'],
            ]
        ]
    ]
];
