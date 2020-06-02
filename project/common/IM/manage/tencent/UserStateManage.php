<?php


namespace common\service\IM\manage\tencent;


class UserStateManage extends Tencent
{
    //用户在线状态公共接口
    const REQUEST_URL_MAIN = 'https://console.tim.qq.com/v4/openim/';

    private function _request($requestData,$event,$appId,$managerSig)
    {
        $requestUrl = self::REQUEST_URL_MAIN.$event;
        return $this->comRequest($requestUrl,$requestData,$appId,$managerSig);
    }

    /**
     * 用户状态查询
     * {
        "ActionStatus": "OK",
        "ErrorInfo": "",
        "ErrorCode": 0,
        "QueryResult": [
                {
                    "To_Account": "id1",
                    "State": "Offline"
                },
                {
                "To_Account": "id2",
                "State": "Online"
                },
                {
                "To_Account": "id3",
                "State": "PushOnline"
                }
            ]
        }
     *
     * state说明 返回的用户状态，目前支持的状态有：
                1."Online"：客户端登录后和即时通信 IM 后台有长连接
                2."PushOnline"：iOS 和 Android 进程被 kill 或因网络问题掉线，进入 PushOnline 状态，此时仍然可以接收消息的离线推送。（客户端切到后                 台，但是进程未被手机操作系统 kill 掉时，此时状态仍是 Online。）
                3."Offline"：客户端主动退出登录或者客户端自上一次登录起7天之内未登录过
     *
     * @param $checkStateData
     * @param callable $callback
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/24
     * @Time: 11:04
     */
    public function checkState($checkStateData,callable $callback)
    {
        $event = 'querystate';
        $info = call_user_func_array($callback,[$this->managerId]);
        $result = $this->_request($checkStateData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $msg  = 'success';
                $data = $resultData['QueryResult'];
            }
            else
            {
                $code = '201';
                $msg  = $resultData['ErrorInfo'];
                $data = [];
            }
            return [
                'code' => $code,
                'msg'  => $msg,
                'result' => $data
            ];
        });
    }
}