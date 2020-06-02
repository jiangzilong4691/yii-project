<?php


namespace common\service\IM\manage\tencent;


class UserAccountManage extends Tencent
{
    //账号管理接口公共地址
    const REQUEST_URL_MAIN = 'https://console.tim.qq.com/v4/im_open_login_svc/';

    /**
     * 账号管理统一请求接口
     *
     * @param   array   $reqestData     请求数据
     * @param   string  $event          请求接口事件
     * @param   string  $appId
     * @param   string  $managerSig     管理员密码
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 20:23
     */
    private function _request($reqestData,$event,$appId,$managerSig)
    {
        $requestUrl = self::REQUEST_URL_MAIN.$event;
        return $this->comRequest($requestUrl,$reqestData,$appId,$managerSig);
    }

    /**
     * 单条导入
     *
     * @param $importData
     * @param callable $callback
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 16:55
     */
    public function importOne($importData,callable $callback)
    {
        $event = 'account_import';
        $info = call_user_func_array($callback,[$this->managerId]);
        $result = $this->_request($importData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $errCode = 0;
                $msg  = 'success';
            }
            else
            {
                $code = '-2';
                $errCode = $resultData['ErrorCode'];
                $msg  = $resultData['ErrorInfo'];
            }
            return [
                'code' => $code,
                'errCode' => $errCode,
                'msg'  => $msg
            ];
        });
    }

    /**
     * 多条批量导入
     *
     * @param $importData
     * @param callable $callback
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 17:21
     */
    public function importMulti($importData,callable $callback)
    {
        $event = 'multiaccount_import';
        $info = call_user_func_array($callback,[$this->managerId]);
        $result = $this->_request($importData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                if(empty($resultData['FailAccounts']))
                {
                    $code = '200';
                    $msg  = 'success';
                    $data = [];
                }
                else
                {
                    $code = '201';
                    $msg  = 'partial success';
                    $data = $resultData['FailAccounts'];
                }
            }
            else
            {
                $code = '-2';
                $msg  = $resultData['ErrorInfo'];
                $data = $resultData['FailAccounts'];
            }
            return [
                'code' => $code,
                'msg'  => $msg,
                'result' => $data
            ];
        });
    }

    /**
     * 账号查询
     *
     * @param $checkData
     * @param callable $callback
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 20:30
     */
    public function checkUser($checkData,callable $callback)
    {
        $event = 'account_check';
        $info = call_user_func_array($callback,[$this->managerId]);
        $result = $this->_request($checkData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                return [
                    'code' => '200',
                    'msg'  => 'success',
                    'result' => $resultData['ResultItem']
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
     * 批量删除用户
     *
     * @param   array       $deleteData
     * @param   callable    $callback
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 21:07
     */
    public function deleteUser($deleteData,callable $callback)
    {
        $event = 'account_delete';
        $info = call_user_func_array($callback,[$this->managerId]);
        $result = $this->_request($deleteData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
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
     * 强制用户登录状态失效
     *
     * @param $failuerData
     * @param callable $callback
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 21:36
     */
    public function failureUser($failuerData,callable $callback)
    {
        $event = 'kick';
        $info = call_user_func_array($callback,[$this->managerId]);
        $result = $this->_request($failuerData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
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
}