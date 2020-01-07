<?php


namespace common\service\IM\manage\tencent;


use common\service\IM\exception\InstantMsgingException;

class MessageManage extends Tencent
{
    const REQUEST_URL_MAIN = 'https://console.tim.qq.com/v4/openim/';

    private function _request($reqestData,$event,$appId,$managerSig)
    {
        $requestUrl = self::REQUEST_URL_MAIN.$event;
        return $this->_comRequest($requestUrl,$reqestData,$appId,$managerSig);
    }

    /**
     * 信息数据拼接
     *
     * @param $missionData
     *
     * @return array
     * @throws InstantMsgingException
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/24
     * @Time: 19:31
     */
    private function _getMsgData($missionData)
    {
        $msgData = [
            'SyncOtherMachine' => $missionData['sync'],
            'To_Account' => $missionData['toAccount'],
            'MsgRandom'  => mt_rand(),
            'MsgTimeStamp' => time(),
            'MsgBody'      => $this->_getMsgBody($missionData)
        ];
        if(isset($missionData['fromAccount'])  && !empty($missionData['fromAccount']))
        {
            $msgData['From_Account'] = $missionData['fromAccount'];
        }
        if(isset($missionData['offlinePush']) && $missionData['offlinePush'])
        {
            $msgData['OfflinePushInfo'] = $this->_formatOfflinePushInfo($missionData);
        }
        return $msgData;
    }

    private function _formatOfflinePushInfo($missionData)
    {

    }

    /**
     * 按类型格式化消息
     *
     * @param $missionData
     *
     * @return array
     *
     * @throws InstantMsgingException
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/24
     * @Time: 17:12
     */
    private function _getMsgBody($missionData)
    {
        $type = $missionData['msgType'];
        switch ($type)
        {
            case 'text':
                $msgBody = $this->_formatTextMsg($missionData['msgContent']);
                break;
            case 'face':
                $msgBody = $this->_formatFaceMsg($missionData['msgContent']);
                break;
            default:
                $msgBody = [];
        }
        if(empty($msgBody))
        {
            throw new InstantMsgingException('发送信息类型错误');
        }
        return $msgBody;
    }

    /**
     * 文本格式
     *
     * @param $content
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/24
     * @Time: 17:11
     */
    private function _formatTextMsg($content)
    {
        return [
            [
                'MsgType' => 'TIMTextElem',
                'MsgContent' => [
                    'Text' => $content
                ]
            ]
        ];
    }

    /**
     * 表情
     * 暂不做处理
     * @param $content
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/24
     * @Time: 17:11
     */
    private function _formatFaceMsg($content)
    {
        return [];
    }

    /**
     * 单聊消息
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array
     * @throws InstantMsgingException
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/24
     * @Time: 19:54
     */
    public function send($missionData,callable $callback)
    {
        $isMulti = $missionData['multi'];
        $sendContentInfo = $missionData['sendContentInfo'];
        $event = $missionData['multi']?'batchsendmsg':'sendmsg';
        $info = call_user_func_array($callback,[$this->managerId]);
        $msgData = $this->_getMsgData($sendContentInfo);
        $requestResult = $this->_request($msgData,$event,$info['appId'],$info['userSig']);
        if($isMulti)
        {
            return $this->_multiSendReply($requestResult);
        }
        else
        {
            return $this->_singleSendReply($requestResult);
        }
    }

    /**
     * 正常应答
     *  {
        "ErrorInfo": "",
        "ActionStatus": "OK",
        "ErrorCode": 0,
        "MsgKey": "128493_903762_1572870301"
        }
     * 异常应答
     * {
        "ActionStatus": "FAIL",
        "ErrorInfo": "",
        "ErrorList": [ // 发送消息失败列表
                {
                    "To_Account": "rong", // 目标帐号
                    "ErrorCode":  90011 // 错误码
                }
            ]
        }
     * @param $requestResult
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/24
     * @Time: 20:59
     */
    private function _multiSendReply($requestResult)
    {
        return $this->_comReturn($requestResult,function($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                return [
                    'code' => '200',
                    'msg'  => 'success',
                    'result' => [
                        'msgKey'  => $resultData['MsgKey']
                    ]
                ];
            }
            return [
                'code' => '201',
                'msg'  => $resultData['ErrorInfo'],
                'result' => [
                    'errorList' => $resultData['ErrorList']
                ]
            ];
        });
    }

    /**
     * 正常应答
     * {
            "ActionStatus": "OK",
            "ErrorInfo": "",
            "ErrorCode": 0,
            "MsgTime": 1497238162,
            "MsgKey": "89541_2574206_1572870301"
        }
     * 异常应答
     * {
            "ActionStatus": "FAIL",
            "ErrorInfo": "Fail to Parse json data of body, Please check it",
            "ErrorCode": 90001
        }
     * @param $requestResult
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/24
     * @Time: 20:59
     */
    private function _singleSendReply($requestResult)
    {
        return $this->_comReturn($requestResult,function ($resultData){

            if($resultData['ActionStatus'] == 'OK')
            {
                return [
                    'code' => '200',
                    'msg'  => 'success',
                    'result' => [
                        'msgTime' => $resultData['MsgTime'],
                        'msgKey'  => $resultData['MsgKey']
                    ]
                ];
            }
            return [
                'code' => '201',
                'msg'  => $resultData['ErrorInfo'],
                'result' => []
            ];
        });
    }

    /**
     * 消息撤回
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/25
     * @Time: 14:00
     */
    public function withdraw($missionData,callable $callback)
    {
        $event = 'admin_msgwithdraw';
        $info = call_user_func_array($callback,[$this->managerId]);
        $withdrawData = [
            'From_Account' => empty($missionData['fromAccount']) ? $this->managerId:$missionData['fromAccount'],
            'To_Account'   => $missionData['toAccount'],
            'MsgKey'       => $missionData['msgKey']
        ];
        $requestResult = $this->_request($withdrawData,$event,$info['appId'],$info['userSig']);
        return $this->_comReturn($requestResult,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                return [
                    'code' => '200',
                    'msg'  => 'success',
                    'result' => []
                ];
            }
            return [
                'code' => '201',
                'msg'  => $resultData['ErrorInfo'],
                'result' => []
            ];
        });
    }

    /**
     * 导入单聊消息
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     * @throws InstantMsgingException
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/25
     * @Time: 15:46
     */
    public function import($missionData,callable $callback)
    {
        $event = 'importmsg';
        $info = call_user_func_array($callback,[$this->managerId]);
        $importData = [
            'SyncFromOldSystem' => 2, // 1:平滑过渡期间，实时消息导入，消息计入未读,2:历史消息导入，消息不计入未读
            'From_Account' => $missionData['fromAccount'],
            'To_Account'   => $missionData['toAccount'],
            'MsgRandom'    => mt_rand(),
            'MsgTimeStamp' => $missionData['msgInfo']['time'],
            'MsgBody'      => $this->_getMsgBody($missionData['msgInfo'])
        ];
        $requestResult = $this->_request($importData,$event,$info['appId'],$info['userSig']);
        return $this->_comReturn($requestResult,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                return [
                    'code' => '200',
                    'msg'  => 'success',
                    'result' => []
                ];
            }
            return [
                'code' => '201',
                'msg'  => $resultData['ErrorInfo'].' errorCode:'.$resultData['ErrorCode'],
                'result' => []
            ];
        });
    }
}