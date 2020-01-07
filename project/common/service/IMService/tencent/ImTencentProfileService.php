<?php


namespace common\service\IMService\tencent;


use common\service\IM\ImExecutor\IM;
use common\service\IM\InstantMessaging;
use common\service\IMService\ImService;

/**
 * 用户资料管理类
 * Class ImTencentProfileService
 * @package common\service\IMService\tencent
 */
class ImTencentProfileService extends ImService
{
    /**
     * 拉取用户资料
     *
     * @param   array   $toAccounts     用户ID集合 e.g. ['1497428','23658']
     * @param   array   $lists          获取用户信息字段集合 e.g.
         [
            'nick'      选填  用户昵称
            'gender'    选填  用户性别
            'birthDay'  选填  用户生日
            'location'  选填  所在地
            'signature' 选填  个性签名
            'allowType' 选填  加好友方式   AllowType_Type_NeedConfirm：需要经过自己确认才能添加自己为好友
                                        AllowType_Type_AllowAny：允许任何人添加自己为好友
                                        AllowType_Type_DenyAny：不允许任何人添加自己为好友
            'language'  选填  语言
            'image'     选填  头像URL
            'msgSetting' 选填  消息设置 Bit0：置0表示接收消息，置1则不接收消息
            'adminForbidType'
         ]
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/30
     * @Time: 11:42
     */
    public function pullProfile(Array $toAccounts,Array $lists)
    {
        $command = [
            'mission' => IM::TENCENT_MISSION_PROFILE_PULL,
            'data' => [
                'toAccounts' => $toAccounts,
                'tags' => $lists
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {
                $returnResult = ['code' => '-1','desc' => $resultData['result']];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $resultData['msg']];
            }
        }
        else
        {
            $returnResult = ['code' =>  '-1','desc' => $execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 设置用户资料
     *
     * @param   string  $fromAccount    平台用户ID
     * @param   array   $setInfo        待修改信息集合
     * e.g.
         [
            'nick'      => 'dddd',  string  选填  secatest
            'gender'    => 1,       int     选填  ImService::ACCOUNT_PROFILE_GENDER_NONE
            'birthDay'  => '',      int     选填  生日
            'location'  => '',      string  选填  地址
            'signature' => '',      string  选填  个性签名
            'allowType' => 1,       int     选填  是否允许加好友 ImService::ACCOUNT_ADD_FRIENDS_TYPE_ALLOW_ANY
            'language'  => 0,       int     选填  语言
            'image'     => 'http://path/img.png',   选填   头像
            'msgSetting'=> 0,       int     选填  接收消息 Bit0：置0表示接收消息，置1则不接收消息
            'adminForbidType' => 0, int     选填  管理员禁止加好友 ImService::ACCOUNT_ADD_FRIENDS_ADMIN_ALLOW

        ]
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/30
     * @Time: 14:44
     */
    public function setProfile($fromAccount,$setInfo)
    {
        $command = [
            'mission' => IM::TENCENT_MISSION_PROFILE_SET,
            'data' => [
                'fromAccount' => $fromAccount,
                'setInfo'     => $setInfo
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {
                $returnResult = ['code' => '-1','desc' => '修改成功'];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $resultData['msg']];
            }
        }
        else
        {
            $returnResult = ['code' =>  '-1','desc' => $execResult['msg']];
        }
        return $returnResult;
    }
}