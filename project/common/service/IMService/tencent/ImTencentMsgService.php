<?php


namespace common\service\IMService\tencent;


use common\service\IM\ImExecutor\IM;
use common\service\IM\InstantMessaging;
use common\service\IMService\ImService;

/**
 * 用户单聊消息管理类
 * Class ImTencentMsgService
 * @package common\service\IMService\tencent
 */
class ImTencentMsgService extends ImService
{
    /**
     * 单发单聊消息
     * @param   array   $sendContentInfo    消息配置
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
    public function msgSend(Array $sendContentInfo)
    {
        $content = [
            '啦啦啦啦啦 卖报小行家',
            '啦啦啦  lidong is dog',
            '哈哈哈  lidong 不知道',
            '来呀 造作呀 ',
            '反正有大把时光',
            '这都是些啥玩应',
            '来来来 接着测',
            '腾讯云 接口全是bug TNND'
        ];
        shuffle($content);
        $sendContentInfo = [
            'sync' => self::MSG_SEND_SYNC_YES,
            'fromAccount' =>'8369',
            'toAccount' => '4701015',//['1497428','548796','369597'],
            'msgType'  => self::MSG_TYPE_TEXT,
            'msgContent' => $content[0]
        ];
        if(is_string($sendContentInfo['toAccount']))
        {
            return $this->_msgSendSingle($sendContentInfo);
        }
        else if(is_array($sendContentInfo['toAccount']))
        {
            return $this->_msgSendBatch($sendContentInfo);
        }
        return ['code' => '-1','desc' => '接收账号仅支持字符串或字符串数组'];

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
                $returnResult = ['code' => '200','desc'=>'信息已发送成功【发送时间：'.$sendResult['result']['msgTime'].',发送记录key为:'.$sendResult['result']['msgKey'].'】'];
            }
            else
            {
                $returnResult = ['code' => '201','desc'=>$sendResult['msg']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
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
                //发送成功后会返回发送信息的时间和标识key用于撤回 需要入库做记录
                //TODO 返回结果入库记录
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