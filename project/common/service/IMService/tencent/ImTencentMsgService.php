<?php


namespace common\service\IMService\tencent;


use common\entity\im\ImMsgRecordEntity;
use common\service\IM\ImExecutor\IM;
use common\service\IM\ImExecutor\ImTencent;
use common\service\IM\InstantMessaging;
use common\service\IMService\ImService;

/**
 * 用户单聊消息管理类
 * Class ImTencentMsgService
 * @package common\service\IMService\tencent
 */
class ImTencentMsgService extends ImService
{

    public static $sendMsgKey = 'GvZ72YCHglmAJSDq';
    /**
     * 单发单聊消息
     * @param   ImTencentMsg   $msg    信息
     * [
     *      'sync' => Integer,
     *      'fromAccount' => '1497428',
     *      'toAccount'   => '2568' || ['145','56978','78789'],
     *      'lifeTime'    => Integer,
     *      'msgType'     => '',
     *      'msgContent'  => '' || []
     *      'offlinePush' => 1 or 0
     * ]
     * sync : 是否把消息同步到 From_Account 在线终端和漫游上
     * fromAccount : 选填 指定的发送方账号
     * toAccount   : 必填 消息接收方账号
     * lifeTime    : 选填 消息离线保存时长，最长7天（604800s） 0值只发在线用户
     * msgType     : 消息类型 目前只做 文本类型
     * msgContent  : 消息内容
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/24
     * @Time: 16:38
     */
    private function msgSend(ImTencentMsg $msg)
    {
        $sendContentInfo = [
            'sync' => $msg->msgSync,
            'fromAccount' => (string)$msg->userIdSend,
            'toAccount' => $msg->userIdReceive,//['1497428','548796','369597'],
            'msgType'  => $msg->msgType,
            'msgContent' => $msg->msgContent
        ];
        if(is_string($sendContentInfo['toAccount']) || is_int($sendContentInfo['toAccount']))
        {
            if(is_int($sendContentInfo['toAccount']))
                $sendContentInfo['toAccount'] = (string)$sendContentInfo['toAccount'];
            return $this->_msgSendSingle($sendContentInfo);
        }
        else if(is_array($sendContentInfo['toAccount']))
        {
            return $this->_msgSendBatch($sendContentInfo);
        }
        return ['send' => false,'desc' => '接收账号仅支持字符串或字符串数组'];

    }

    /**
     * 发送信息并更新发送信息结果
     *
     * @param ImTencentMsg $msg
     * @param $recordId
     *
     * @return array
     *
     * @throws \yii\db\Exception
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/3/17
     * @Time: 18:08
     */
    private function sendUpdateRecord(ImTencentMsg $msg,$recordId)
    {
        $sendResult = $this->msgSend($msg);
        if($sendResult['send'])
        {
            //发送成功 修改发送状态 返回success
            ImMsgRecordEntity::model()->updateSendRecord($recordId,ImMsgRecordEntity::MSG_SEND_RESULT_SUCCESS,$sendResult['desc'],$sendResult['sendKey']);
            return ['code' => '200','desc'=>'发送成功'];
        }
        else
        {
            //发送失败 修改发送状态 返回fail
            ImMsgRecordEntity::model()->updateSendRecord($recordId,ImMsgRecordEntity::MSG_SEND_RESULT_FAIL,$sendResult['desc']);
            return ['code' => '-1','desc'=>'发送失败'];
        }
    }

    /**
     * 新增信息发送
     *
     * @param   int        $receiveUserId       接收用户ID
     * @param   string     $msgContent          发送消息内容
     * @param   int        $msgContentType      发送信息内容类型  e.g. 0 无分类  1 中奖信息 2 一般通知
     * @param   int         $sendUserId         指定的发送方用户ID 不指定 默认为管理员发送
     * @param   int         $msgType
     * @param   int         $sync
     *
     * @return array
     *
     * @throws \yii\db\Exception
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/3/17
     * @Time: 18:07
     */
    public function addMsgSend($receiveUserId,$msgContent,$msgContentType,$sendUserId=0,$msgType=ImTencentMsg::MSG_TYPE_TEXT,$sync=ImTencentMsg::MSG_SEND_SYNC_YES)
    {
        //添加记录 部分信息暂时默认 当前仅考虑单条发送
        $recordId = ImMsgRecordEntity::model()->addRecord($sendUserId,$receiveUserId,$msgContent,$msgContentType,$msgType,$sync);
        if($recordId)
        {
            $msg = new ImTencentMsg($sendUserId,$receiveUserId,$msgContent);
            $msg->msgType = $msgType;
            $msg->msgSync = $sync;
            return $this->sendUpdateRecord($msg,$recordId);
        }
        return ['code' => '-1','desc'=>'添加发送记录失败'];
    }

    /**
     * 已编辑信息发送
     *
     * @param   int     $msgId      已编辑待发送信息记录ID
     *
     * @return array
     *
     * @throws \yii\db\Exception
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/3/17
     * @Time: 17:14
     */
    public function editedMsgSend($msgId)
    {
        $msgInfo = ImMsgRecordEntity::model()->getInfoByRecordId($msgId);
        if(!empty($msgId))
        {
            $msg = new ImTencentMsg($msgInfo['userIdSend'],$msgInfo['userIdReceive'],$msgInfo['msgContent']);
            $msg->msgType = (int)$msgInfo['msgType'];
            $msg->msgSync = (int)$msgInfo['msgSync'];
            return $this->sendUpdateRecord($msg,$msgId);
        }
        return ['code' => '-1','desc' => '已编辑待发送信息不存在'];
    }

    /**
     * 单发单聊 单发信息
     *
     * @param $sendContentInfo
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/25
     * @Time: 11:16
     */
    private function _msgSendSingle($sendContentInfo)
    {
        $command = [
            'mission' => IM::TENCENT_MISSION_MSG_SEND,
            'data' => [
                'sendContentInfo' => $sendContentInfo,
                'multi' => 0
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $sendResult = $execResult['data'];
            if($sendResult['code'] == '200')
            {
                //发送成功后会返回发送信息的时间和标识key用于撤回 需要入库做记录
                //TODO 返回结果入库记录
                $returnResult = [
                    'send' => true,
                    'desc'=>'信息已发送成功【发送时间：'.date('Y-m-d H:i:s',$sendResult['result']['msgTime']).'】',
                    'sendKey' => $sendResult['result']['msgKey'],
                ];
            }
            else
            {
                $returnResult = ['send' => false,'desc'=>$sendResult['msg']];
            }
        }
        else
        {
            $returnResult = ['send' => false,'desc'=>$execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 单发单聊 批量发信息
     *
     * @param $sendContentInfo
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/25
     * @Time: 11:16
     */
    private function _msgSendBatch($sendContentInfo)
    {
        if(count($sendContentInfo['toAccount']) > 500)
        {
            return ['code' => '-1','desc' => '单次请求最多支持500个账号发送'];
        }
        $sendContentInfo['toAccount'] = array_map('strval',$sendContentInfo['toAccount']);
        $command = [
            'mission' => IM::TENCENT_MISSION_MSG_SEND,
            'data' => [
                'sendContentInfo' => $sendContentInfo,
                'multi' => 1
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $sendResult = $execResult['data'];
            if($sendResult['code'] == '200')
            {
                $returnResult = ['code' => '200','desc'=>'信息已发送成功【发送记录key为:'.$sendResult['result']['msgKey'].'】'];
            }
            else
            {
                $returnResult = ['code' => '201','desc'=> $sendResult['errorList']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 单聊消息导入
     *
     * @param   string  $fromAccount    发送方 e.g. '1497428'
     * @param   string  $toAccount      接收方 e.g. '25648'
     * @param   array   $msgInfo        消息载体 e.g. ['time'=>1577257236,'msgType'=>'text','msgContent'=>'']
     * time:      必填     发送消息的时间
     * msgType:   必填     发送消息类型
     * msgContent 必填     发送消息的内容
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/25
     * @Time: 15:50
     */
    public function msgImport($fromAccount,$toAccount,Array $msgInfo)
    {
        $command = [
            'mission' => IM::TENCENT_MISSION_MSG_IMPORT,
            'data' => [
                'fromAccount' => $fromAccount,
                'toAccount'   => $toAccount,
                'msgInfo'     => $msgInfo
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {
                return ['code' => '200','desc' => '导入成功'];
            }
            return ['code' => '201','desc' => $resultData['msg']];
        }
        else
        {
            return ['code' => '-1','desc' => $execResult['msg']];
        }
    }

    /**
     * 单聊消息撤回
     *
     * @param   string  $toAccount   已发送接收方账号
     * @param   string  $msgKey      已发送信息的记录key
     * @param   string  $fromAccount 指定的发送方账号 ：未指定用户，已管理员身份发送的信息 此参数默认为空
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/25
     * @Time: 15:04
     */
    public function msgWithdraw($toAccount,$msgKey,$fromAccount='')
    {
        $command = [
            'mission' => IM::TENCENT_MISSION_MSG_WITHDRAW,
            'data' => [
                'toAccount'     => $toAccount,
                'fromAccount'   => $fromAccount,
                'msgKey'        => $msgKey
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $execData = $execResult['data'];
            if($execData['code'] == '200')
            {
                return ['code' => '200','desc' => '已成功撤回'];
            }
            return ['code' => '201','desc' => $execData['msg']];
        }
        else
        {
            return ['code' => '-1','desc'=>$execResult['msg']];
        }
    }
}