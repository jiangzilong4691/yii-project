<?php


namespace common\service\IM\manage\tencent;


class ProfileManage extends Tencent
{
    const REQUEST_URL_MAIN = 'https://console.tim.qq.com/v4/profile/';

    /**
     * 资料管理统一请求接口
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
        return $this->_comRequest($requestUrl,$reqestData,$appId,$managerSig);
    }

    /**
     * 拉取用户资料
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/30
     * @Time: 11:38
     */
    public function pull($missionData,callable $callback)
    {
        $event = 'portrait_get';
        $profilePullData = [
            'To_Account'  => $missionData['toAccounts'],
            'TagList'     => $this->_formatPullTagList($missionData['tags'])
        ];
        $info = call_user_func_array($callback,[$this->managerId]);
        $result = $this->_request($profilePullData,$event,$info['appId'],$info['userSig']);
        return $this->_comReturn($result,function ($resultData){
            if($resultData['ActionStatus'] == 'OK')
            {
                $code = '200';
                $msg  = 'success';
                $result = [
                        'profileItem' => [
                            'desc' => '用户资料列表',
                            'items' => isset($resultData['UserProfileItem']) ? $resultData['UserProfileItem'] : []
                        ],
                        'failAccounts' =>[
                            'desc' => '处理失败列表',
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
     * 格式化拉取资料字段信息
     *
     * @param $tags
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/30
     * @Time: 11:34
     */
    private function _formatPullTagList($tags)
    {
        $allowedTags = [
            'nick'      => 'Tag_Profile_IM_Nick',
            'gender'    => 'Tag_Profile_IM_Gender',
            'birthDay'  => 'Tag_Profile_IM_BirthDay',
            'location'  => 'Tag_Profile_IM_Location',
            'signature' => 'Tag_Profile_IM_SelfSignature',
            'allowType' => 'Tag_Profile_IM_AllowType',
            'language'  => 'Tag_Profile_IM_Language',
            'image'     => 'Tag_Profile_IM_Image',
            'msgSetting'=> 'Tag_Profile_IM_MsgSettings',
            'adminForbidType' => 'Tag_Profile_IM_AdminForbidType',
        ];
        $tagLists = [];
        foreach ($tags as $tag)
        {
            if(isset($allowedTags[$tag]))
            {
                $tagLists[] = $allowedTags[$tag];
            }
        }
        return $tagLists;
    }

    /**
     * 设置用户资料
     *
     * @param $missionData
     * @param callable $callback
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/30
     * @Time: 14:41
     */
    public function set($missionData,callable $callback)
    {
        $event = 'portrait_set';
        $profileSetData = [
            'From_Account'  => $missionData['fromAccount'],
            'ProfileItem'     => $this->_formatSetProfileItem($missionData['setInfo'])
        ];
        $info = call_user_func_array($callback,[$this->managerId]);
        $result = $this->_request($profileSetData,$event,$info['appId'],$info['userSig']);
        return $this->_comReturn($result,function ($resultData){
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
                'msg'  => $msg
            ];
        });
    }

    /**
     * 格式化要设置的资料
     *
     * @param $setInfo
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/30
     * @Time: 14:39
     */
    private function _formatSetProfileItem($setInfo)
    {
        $setIndexMap = [
            'nick'      => [
                'Tag'   =>'Tag_Profile_IM_Nick',
                'Value' => ''
            ],
            /*'gender'    => [
                'Tag'   =>'Tag_Profile_IM_Gender',
                'Value' => ''
            ],*/
            'birthDay'  => [
                'Tag'   => 'Tag_Profile_IM_BirthDay',
                'Value' => 0
            ],
            'location'  => [
                'Tag'   => 'Tag_Profile_IM_Location',
                'Value' => ''
            ],
            'signature' => [
                'Tag'   => 'Tag_Profile_IM_SelfSignature',
                'Value' => ''
            ],
            /*'allowType' => [
                'Tag'   => 'Tag_Profile_IM_AllowType',
                'Value' => 0
            ],*/
            'language'  => [
                'Tag'   => 'Tag_Profile_IM_Language',
                'Value' => 0
            ],
            'image'     => [
                'Tag'   => 'Tag_Profile_IM_Image',
                'Value' => ''
            ],
            'msgSetting'=> [
                'Tag'   => 'Tag_Profile_IM_MsgSettings',
                'Value' => 0
            ],
            /*'adminForbidType' => [
                'Tag'   => 'Tag_Profile_IM_AdminForbidType',
                'Value' => ''
            ]*/
        ];
        $itemsInfo = [];
        foreach ($setInfo as $name=>$value)
        {
            if(isset($setIndexMap[$name]))
            {
                $info = $setIndexMap[$name];
                $info['Value'] = $value;
                $itemsInfo[] = $info;
            }
        }
        //以下为特殊参数处理
        if(isset($setInfo['gender']) && is_int($setInfo['gender']))
        {
            $numMap = [
                0 => 'Gender_Type_Unknown',
                1 => 'Gender_Type_Female',
                2 => 'Gender_Type_Male'
            ];
            $itemsInfo[] = [
                'Tag' => 'Tag_Profile_IM_Gender',
                'Value' => isset($numMap[$setInfo['gender']]) ? $numMap[$setInfo['gender']] : $numMap[0]
            ];
        }
        if(isset($setInfo['allowType']) && is_int($setInfo['allowType']))
        {
            $typeMap = [
                0 => 'AllowType_Type_AllowAny',
                1 => 'AllowType_Type_NeedConfirm',
                2 => 'AllowType_Type_DenyAny'
            ];
            $itemsInfo[] = [
                'Tag' => 'Tag_Profile_IM_AllowType',
                'Value' => isset($typeMap[$setInfo['allowType']]) ? $typeMap[$setInfo['allowType']]:$typeMap[0]
            ];
        }
        if(isset($setInfo['adminForbidType']) && is_int($setInfo['adminForbidType']))
        {
            $adminForbidTypeMap = [
                0 => 'AdminForbid_Type_None',
                1 => 'AdminForbid_Type_SendOut'
            ];
            $itemsInfo[] = [
                'Tag' => 'Tag_Profile_IM_AdminForbidType',
                'Value' => isset($adminForbidTypeMap[$setInfo['adminForbidType']]) ? $adminForbidTypeMap[$setInfo['adminForbidType']]:$adminForbidTypeMap[0]
            ];
        }
        return $itemsInfo;
    }
}