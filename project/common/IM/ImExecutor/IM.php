<?php


namespace common\service\IM\ImExecutor;


use common\service\IM\exception\InstantMsgingException;
use yii\base\Object;

abstract class IM extends Object
{

    //---------------------腾讯云 action---------------------//

    //生成用户登录即时通信IM的密码
    const TENCENT_MISSION_GET_USERSIG       = 'createUserSig';

    //导入平台用户：批量导入
    const TENCENT_MISSION_IMPORT_USER_MULTI = 'importUserMulti';

    //导入平台用户：单个导入
    const TENCENT_MISSION_IMPORT_USER_ONE   = 'importUserOne';

    //查询用户
    const TENCENT_MISSION_ACCOUNT_CHECK     = 'accountCheck';

    //删除用户
    const TENCENT_MISSION_ACCOUNT_DELETE    = 'accountDelete';

    //强制用户登录态失效
    const TENCENT_MISSION_ACCOUNT_FAILURE   = 'accountFailure';

    //查询用户在线状态
    const TENCENT_MISSION_CHECK_USER_STATE  = 'checkUserState';

    //单发单聊消息
    const TENCENT_MISSION_MSG_SEND          = 'msgSend';

    //单发单聊消息撤回
    const TENCENT_MISSION_MSG_WITHDRAW      = 'msgWithdraw';

    //消息导入
    const TENCENT_MISSION_MSG_IMPORT        = 'msgImport';

    //添加好友
    const TENCENT_MISSION_FRIENDS_ADD       = 'addFriends';

    //校验好友
    const TENCENT_MISSION_FRIENDS_CHECK     = 'checkFriends';

    //导入好友
    const TENCENT_MISSION_FRIENDS_IMPORT    = 'importFriends';

    //更新好友
    const TENCENT_MISSION_FRIENDS_UPDATE    = 'updateFriends';

    //删除好友
    const TENCENT_MISSION_FRIENDS_DELETE    = 'deleteFriends';

    //删除全部好友
    const TENCENT_MISSION_FRIENDS_DELETE_ALL = 'deleteFriendsAll';

    //拉取好友
    const TENCENT_MISSION_FRIENDS_PULL      = 'pullFriends';

    //添加黑名单
    const TENCENT_MISSION_FRIENDS_BLACKLIST_ADD = 'blackListAdd';

    //删除黑名单
    const TENCENT_MISSION_FRIENDS_BLACKLIST_DELETE = 'blackListDelete';

    //校验黑名单
    const TENCENT_MISSION_FRIENDS_BLACKLIST_CHECK  = 'blackListCheck';

    //拉取黑名单
    const TENCENT_MISSION_FRIENDS_BLACKLIST_PULL  = 'blackListPull';

    //拉取资料
    const TENCENT_MISSION_PROFILE_PULL            = 'pullProfile';

    //设置资料
    const TENCENT_MISSION_PROFILE_SET             = 'setProfile';

    //---------------------腾讯云 action---------------------//

    /**
     * 执行任务调度
     * @param   array   $missionParams    任务调度参数
     * @return array|mixed
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/21
     * @Time: 14:55
     */
    public function execMission($missionParams)
    {
        try{
            if(isset($missionParams['mission']) && !empty($missionParams['mission']))
            {
                $mission = $missionParams['mission'];
                if(method_exists($this,$mission))
                {
                    $missionData = isset($missionParams['data'])?$missionParams['data']:[];
                    $this->comMissionParamsCheck($missionData);
                    $info = call_user_func_array([$this,$mission],[$missionData]);
                    return [
                        'status' => true,
                        'msg' => 'success',
                        'data' => $info
                    ];
                }
                throw new InstantMsgingException("执行任务${mission}不存在");
            }
            throw new InstantMsgingException('任务调度参数必填');
        }catch (\Exception $exception){
            return [
                'status' => false,
                'msg'    => $exception->getMessage(),
                'data'   => []
            ];
        }
    }

    /**
     * 通用参数校验
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/21
     * @Time: 15:28
     */
    protected function comMissionParamsCheck($missionData)
    {
        //TODO
    }
}