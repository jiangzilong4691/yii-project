<?php
namespace common\service\IM;

use common\service\IM\exception\InstantMsgingNotFoundException;
use common\service\IM\ImExecutor\ImTencent;

class InstantMessaging
{
    //腾讯云
    const IM_OBJECT_TENCENT = 1;

    /**
     * @var object IM
     */
    private $imObject;

    /**
     * InstantMessaging constructor.
     * @param $imObjectId
     * @throws InstantMsgingNotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    private function __construct($imObjectId)
    {
        if(isset(self::$imObjectConfig[$imObjectId]))
        {
            $this->imObject = \Yii::createObject(self::$imObjectConfig[$imObjectId]);
        }
        else
        {
            throw new InstantMsgingNotFoundException('服务对象不存在');
        }
    }


    public static function server($objectId)
    {
        return new self($objectId);
    }

    /**
     * 实例配置
     * @var array
     */
    private static $imObjectConfig = [
        self::IM_OBJECT_TENCENT =>
            [
                'class' => ImTencent::class,
                'appId' => '11111',
                'secretKey' => '11111'
            ],
    ];

    /**
     * 执行任务
     *
     * @param array $missionParams  任务数组 e.g. ['mission' => '','data'=>['userId'=>1497428,'userName'=>'Jack']]
     *
     * @return mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/21
     * @Time: 14:14
     */
    public function execute($missionParams)
    {
        return $this->imObject->execMission($missionParams);
    }
}