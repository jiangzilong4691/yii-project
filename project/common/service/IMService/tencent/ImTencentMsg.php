<?php


namespace common\service\IMService\tencent;


use common\service\IMService\ImService;

class ImTencentMsg
{

    //----------------发送信息类型----------------//

    //文本消息text
    const MSG_TYPE_TEXT = 1;
    //表情消息 face
    const MSG_TYPE_FACE = 2;
    //位置消息 location
    const MSG_TYPE_LOCATION = 3;

    //离线发送：是
    const MSG_PUSH_OFFLINE_YES = 1;
    //离线发送：否
    const MSG_PUSH_OFFLINE_NO  = 0;

    //----------------发送信息类型----------------//

    //----------------是否将信息同步至From_Account在线终端和漫游上----------------//

    //同步
    const MSG_SEND_SYNC_YES = 1;
    //不同步
    const MSG_SEND_SYNC_NO  = 2;

    //----------------是否将信息同步至From_Account在线终端和漫游上----------------//

    //----------------是否离线推送----------------//

    //支持离线发送
    const MSG_OFFLINE_PUSH_YES = 1;

    //不支持离线发送
    const MSG_OFFLINE_PUSH_NO = 0;

    //----------------是否离线推送----------------//

    public $userIdSend;
    public $userIdReceive;

    public $msgContent;

    public function __construct($userIdSend,$userIdReceive,$msgContent)
    {
        $this->userIdSend = $userIdSend;
        $this->userIdReceive = $userIdReceive;
        $this->msgContent = $msgContent;
    }

    // 信息类型  默认文本类型
    public $msgType = self::MSG_TYPE_TEXT;

    //是否同步发送信息  默认同步发送
    public $msgSync = self::MSG_SEND_SYNC_YES;

    //是否支持连线推送  默认不支持
    public $msgOffline = self::MSG_OFFLINE_PUSH_NO;
}