<?php


namespace common\service\IM\manage\tencent;


class FriendsManage extends Tencent
{

    const REQUEST_URL_MAIN = 'https://console.tim.qq.com/v4/sns/';

    /**
     * 好友管理统一请求接口
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
     * 添加好友
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/26
     * @Time: 16:46
     */
    public function add($missionData,callable $callback)
    {
        $event = 'friend_add';
        $addFriendsData = [
            'From_Account'  => $missionData['fromAccount'],
            'AddFriendItem' => $this->_getAddFriendsItem($missionData['friendsInfo']),
            'AddType'       => $missionData['addType'] == 1 ? 'Add_Type_Both' : 'Add_Type_Single',
            'ForceAddFlags' => $missionData['forceAddFlags'] ? 1 : 0 //管理员强制加好友标记 1表示强制加好友，0表示常规加好友方式
        ];
        $info = call_user_func_array($callback,[$this->managerId]);
        $result = $this->_request($addFriendsData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $msg  = 'success';
                $errno = 0;
                $result = [
                    'item' => [
                        'desc'  => '批量加好友结果',
                        'items' => $resultData['ResultItem']
                    ],
                    'failedAccounts' => [
                        'desc'  =>  '处理失败用户列表',
                        'items' => isset($resultData['Fail_Account'])?$resultData['Fail_Account']:[],
                    ],
                    'invalidAccounts' => [
                        'desc'  => '非法用户列表',
                        'items' => isset($resultData['Invalid_Account'])?$resultData['Invalid_Account']:[]
                    ]
                ];
            }
            else
            {
                $code = '201';
                $msg  = $resultData['ErrorInfo'];
                $errno = $resultData['ErrorCode'];
                $result = [];
            }
            return [
                'code' => $code,
                'msg'  => $msg,
                'errno' => $errno,
                'result' => $result
            ];
        });
    }

    /**
     * 添加好友信息填充
     *
     * @param $friendsInfo
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/26
     * @Time: 16:30
     */
    private function _getAddFriendsItem($friendsInfo)
    {
        return array_map(function ($friendInfo){
            $info = [
                'To_Account' => (string)$friendInfo['userId'],
                'AddSource'  => 'AddSource_Type_admin'
            ];
            if(isset($friendInfo['remark']) && !empty($friendInfo['remark']))
            {
                $info['Remark'] = $friendInfo['remark'];
            }
            if(isset($friendInfo['groupName']) && !empty($friendInfo['groupName']))
            {
                $info['GroupName'] = $friendInfo['groupName'];
            }
            if(isset($friendInfo['addWording']) && !empty($friendInfo['addWording']))
            {
                $info['AddWording'] = $friendInfo['addWording'];
            }
            return $info;
        },$friendsInfo);
    }

    /**
     * 校验好友关系
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/26
     * @Time: 19:50
     */
    public function check($missionData,callable $callback)
    {
        $event = 'friend_check';
        $info = call_user_func_array($callback,[$this->managerId]);
        $checkData = [
            'From_Account' => $missionData['fromAccount'],
            'To_Account'   => $missionData['toAccount'],
            'CheckType'    => $missionData['checkType'] == 1 ? 'CheckResult_Type_Both' : 'CheckResult_Type_Single'
        ];
        $result = $this->_request($checkData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $msg  = 'success';
                $result = [
                    'item' => [
                        'desc'  => '校验结果',
                        'items' => $resultData['InfoItem']
                    ],
                    'failAcounts' => [
                        'desc'  => '处理失败用户列表',
                        'items' => isset($resultData['Fail_Account']) ? $resultData['Fail_Account'] : []
                    ]
                ];
            }
            else
            {
                $code = '201';
                $msg  = $resultData['ErrorInfo'];
                $result = [];
            }
            return [
                'code' => $code,
                'msg'  => $msg,
                'result' => $result
            ];
        });
    }

    /**
     * 导入好友
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/27
     * @Time: 16:27
     */
    public function import($missionData,callable $callback)
    {
        $event = 'friend_import';
        $info = call_user_func_array($callback,[$this->managerId]);
        $importData = [
            'From_Account' => $missionData['fromAccount'],
            'AddFriendItem' => $this->_formatImportFriendsInfo($missionData['friendsInfo'])
        ];
        $result = $this->_request($importData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $msg  = 'success';
                $result = [
                    'item' => [
                        'desc'  => '导入好友结果列表',
                        'items' => $resultData['ResultItem']
                    ],
                    'failedAccounts' => [
                        'desc'  =>  '导入好友失败列表',
                        'items' => isset($resultData['Fail_Account'])?$resultData['Fail_Account']:[],
                    ],
                    'invalidAccounts' => [
                        'desc'  => '非法用户列表',
                        'items' => isset($resultData['Invalid_Account'])?$resultData['Invalid_Account']:[]
                    ]
                ];
            }
            else
            {
                $code = '201';
                $msg  = $resultData['ErrorInfo'];
                $result = [];
            }
            return [
                'code' => $code,
                'msg'  => $msg,
                'result' => $result
            ];
        });
    }

    /**
     * 导入好友数据格式化
     *
     * @param $friendsInfo
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/27
     * @Time: 16:20
     */
    private function _formatImportFriendsInfo($friendsInfo)
    {
        return array_map(function ($friendInfo){
            $info = [
                'To_Account' => (string)$friendInfo['userId'],
                'AddSource'  => 'AddSource_Type_admin'
            ];
            if(isset($friendInfo['remark']) && !empty($friendInfo['remark']))
            {
                $info['Remark'] = $friendInfo['remark'];
                if(isset($friendInfo['remarkTime']) && is_int($friendInfo['remarkTime']) && $friendInfo['remarkTime'] >0 )
                {
                    $info['RemarkTime'] = $friendInfo['remarkTime'];
                }
            }
            if(isset($friendInfo['groupName']) && is_array($friendInfo['groupName']) && !empty($friendInfo['groupName']))
            {
                $info['GroupName'] = $friendInfo['groupName'];
            }
            if(isset($friendInfo['addWording']) && !empty($friendInfo['addWording']))
            {
                $info['AddWording'] = $friendInfo['addWording'];
            }
            if(isset($friendInfo['addTime']) && is_int($friendInfo['addTime']) && $friendInfo['addTime']>0)
            {
                $info['AddTime'] = $friendInfo['addTime'];
            }
            return $info;
        },$friendsInfo);
    }

    /**
     * 更新好友信息
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 14:48
     */
    public function update($missionData,callable $callback)
    {
        $event = 'friend_update';
        $info = call_user_func_array($callback,[$this->managerId]);
        $updateData = [
            'From_Account' => $missionData['fromAccount'],
            'UpdateItem' => $this->_formatUpdateFriendsInfo($missionData['friendsInfo'])
        ];
        $result = $this->_request($updateData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $msg  = 'success';
                $result = [
                    'item' => [
                        'desc'  => '更新好友结果列表',
                        'items' => $resultData['ResultItem']
                    ],
                    'failedAccounts' => [
                        'desc'  =>  '更新好友失败列表',
                        'items' => isset($resultData['Fail_Account'])?$resultData['Fail_Account']:[],
                    ],
                    'invalidAccounts' => [
                        'desc'  => '非法用户列表',
                        'items' => isset($resultData['Invalid_Account'])?$resultData['Invalid_Account']:[]
                    ]
                ];
            }
            else
            {
                $code = '201';
                $msg  = $resultData['ErrorInfo'];
                $result = [];
            }
            return [
                'code' => $code,
                'msg'  => $msg,
                'result' => $result
            ];
        });
    }

    /**
     * 更新好友数据格式化
     *
     * @param $friendsInfo
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 14:43
     */
    private function _formatUpdateFriendsInfo($friendsInfo)
    {
        return array_map(function($friendInfo){
            $snsItem = [];
            if(isset($friendInfo['remark']) && is_string($friendInfo['remark']) && !empty($friendInfo['remark']))
            {
                $snsItem[] = [
                    'Tag'   => 'Tag_SNS_IM_Remark',
                    'Value' => $friendInfo['remark']
                ];
            }
            if(isset($friendInfo['group']) && is_array($friendInfo['group']) && !empty($friendInfo['group']))
            {
                $snsItem[] = [
                    'Tag'   => 'Tag_SNS_IM_Group',
                    'Value' => $friendInfo['group']
                ];
            }
            $info = [
                'To_Account' => (string)$friendInfo['userId'],
                'SnsItem'    => $snsItem
            ];
            return $info;
        },$friendsInfo);
    }

    /**
     * 删除好友
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 15:33
     */
    public function delete($missionData,callable $callback)
    {
        $event = 'friend_delete';
        $info = call_user_func_array($callback,[$this->managerId]);
        $deleteData = [
            'From_Account' => $missionData['fromAccount'],
            'To_Account'   => $missionData['deleteAccounts'],
            'DeleteType'   => $missionData['deleteType'] == 1 ? 'Delete_Type_Both' : 'Delete_Type_Single'
        ];
        $result = $this->_request($deleteData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $msg  = 'success';
                $errno = 0;
                $result = $resultData['ResultItem'];
            }
            else
            {
                $code = '201';
                $msg  = $resultData['ErrorInfo'];
                $errno = $resultData['ErrorCode'];
                $result = [];
            }
            return [
                'code' => $code,
                'msg'  => $msg,
                'errno' => $errno,
                'result' => $result
            ];
        });
    }

    /**
     * 删除全部好友
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 15:59
     */
    public function deleteAll($missionData,callable $callback)
    {
        $event = 'friend_delete_all';
        $info = call_user_func_array($callback,[$this->managerId]);
        $deleteAllData = [
            'From_Account' => $missionData['fromAccount'],
            'DeleteType'   => $missionData['deleteType'] == 1 ? 'Delete_Type_Both' : 'Delete_Type_Single'
        ];
        $result = $this->_request($deleteAllData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $msg  = 'success';
            }
            else
            {
                $code = '201';
                $msg  = $resultData['ErrorInfo'];
            }
            return [
                'code' => $code,
                'msg'  => $msg,
            ];
        });
    }

    /**
     * 拉取好友
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 16:31
     */
    public function pull($missionData,callable $callback)
    {
        $event = 'friend_get';
        $info = call_user_func_array($callback,[$this->managerId]);
        $pullData = [
            'From_Account' => $missionData['fromAccount'],
            'StartIndex'   => $missionData['startIndex'],
            'StandardSequence' => $missionData['standardSequence'],
            'CustomSequence' => $missionData['customSequence']
        ];
        $result = $this->_request($pullData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $msg  = 'success';
                $result = [
                    'friendsList' => [
                        'desc' => '好友信息列表',
                        'items' => isset($resultData['UserDataItem'])?$resultData['UserDataItem']:[]
                    ],
                    'standardSequence' => $resultData['StandardSequence'],
                    'customSequence'   => $resultData['CustomSequence'],
                    'friendNum'        => $resultData['FriendNum'],
                    'completeFlag'     => $resultData['CompleteFlag'],
                    'nextStartIndex'   => $resultData['NextStartIndex']
                ];
            }
            else
            {
                $code = '201';
                $msg  = $resultData['ErrorInfo'];
                $result = [];
            }
            return [
                'code' => $code,
                'msg'  => $msg,
                'result' => $result
            ];
        });
    }

    /**
     * 添加黑名单
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 17:36
     */
    public function blackListAdd($missionData,callable $callback)
    {
        $event = 'black_list_add';
        $info = call_user_func_array($callback,[$this->managerId]);
        $blackListAddData = [
            'From_Account' => $missionData['fromAccount'],
            'To_Account'   => $missionData['toAccounts']
        ];
        $result = $this->_request($blackListAddData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $msg  = 'success';
                $errno = 0;
                $result = [
                    'list' => [
                        'desc' => '添加列表',
                        'items' => isset($resultData['ResultItem']) ? $resultData['ResultItem'] : []
                    ],
                    'failList' => [
                        'desc' => '添加失败列表',
                        'items' => isset($resultData['Fail_Account']) ? $resultData['Fail_Account'] : []
                    ],
                    'invalidList' => [
                        'desc' => '非法用户列表',
                        'items' => isset($resultData['Invalid_Account']) ? $resultData['Invalid_Account'] : []
                    ]
                ];
            }
            else
            {
                $code = '201';
                $msg  = $resultData['ErrorInfo'];
                $errno = $resultData['ErrorCode'];
                $result = [];
            }
            return [
                'code' => $code,
                'msg'  => $msg,
                'errno' => $errno,
                'result' => $result
            ];
        });
    }

    /**
     * 删除黑名单
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 17:47
     */
    public function blackListDelete($missionData,callable $callback)
    {
        $event = 'black_list_delete';
        $info = call_user_func_array($callback,[$this->managerId]);
        $blackListDeleteData = [
            'From_Account' => $missionData['fromAccount'],
            'To_Account'   => $missionData['toAccounts']
        ];
        $result = $this->_request($blackListDeleteData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $msg  = 'success';
                $errno = 0;
                $result = [
                    'list' => [
                        'desc' => '删除列表',
                        'items' => isset($resultData['ResultItem']) ? $resultData['ResultItem'] : []
                    ],
                    'failList' => [
                        'desc' => '删除失败列表',
                        'items' => isset($resultData['Fail_Account']) ? $resultData['Fail_Account'] : []
                    ],
                ];
            }
            else
            {
                $code = '201';
                $msg  = $resultData['ErrorInfo'];
                $errno = $resultData['ErrorCode'];
                $result = [];
            }
            return [
                'code' => $code,
                'msg'  => $msg,
                'errno' => $errno,
                'result' => $result
            ];
        });
    }

    /**
     * 校验黑名单
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 18:06
     */
    public function blackListCheck($missionData,callable $callback)
    {
        $event = 'black_list_check';
        $info = call_user_func_array($callback,[$this->managerId]);
        $blackListCheckData = [
            'From_Account' => $missionData['fromAccount'],
            'To_Account'   => $missionData['toAccounts'],
            'CheckType'    => $missionData['checkType'] == 1 ? 'BlackCheckResult_Type_Both' : 'BlackCheckResult_Type_Single'
        ];
        $result = $this->_request($blackListCheckData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $msg  = 'success';
                $result = [
                    'list' => [
                        'desc' => '校验列表',
                        'items' => isset($resultData['BlackListCheckItem']) ? $resultData['BlackListCheckItem'] : []
                    ],
                    'failList' => [
                        'desc' => '校验失败列表',
                        'items' => isset($resultData['Fail_Account']) ? $resultData['Fail_Account'] : []
                    ],
                ];
            }
            else
            {
                $code = '201';
                $msg  = $resultData['ErrorInfo'];
                $result = [];
            }
            return [
                'code' => $code,
                'msg'  => $msg,
                'result' => $result
            ];
        });
    }

    /**
     * 黑名单列表
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 20:41
     */
    public function blackListPull($missionData,callable $callback)
    {
        $event = 'black_list_get';
        $info = call_user_func_array($callback,[$this->managerId]);
        $blackListPullData = [
            'From_Account' => $missionData['fromAccount'],
            'StartIndex'   => $missionData['startIndex'],
            'MaxLimited'   => $missionData['limit'],
            'LastSequence' => $missionData['lastSequence']
        ];
        $result = $this->_request($blackListPullData,$event,$info['appId'],$info['userSig']);
        return $this->comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $msg  = 'success';
                $result = [
                    'blackList' => [
                        'desc' => '黑名单列表',
                        'items' => isset($resultData['BlackListItem'])?$resultData['BlackListItem']:[]
                    ],
                    'startIndex' => $resultData['StartIndex'],
                    'curruentSequence'   => $resultData['CurruentSequence'],
                ];
            }
            else
            {
                $code = '201';
                $msg  = $resultData['ErrorInfo'];
                $result = [];
            }
            return [
                'code' => $code,
                'msg'  => $msg,
                'result' => $result
            ];
        });
    }
}