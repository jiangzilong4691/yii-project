<?php


namespace common\service\IMService\tencent;


use common\service\IM\ImExecutor\IM;
use common\service\IM\InstantMessaging;
use common\service\IMService\ImService;

/**
 * 用户在线状态管理类
 * Class ImTencentStateService
 * @package common\service\IMService\tencent
 */
class ImTencentStateService extends ImService
{
    /**
     * 查询用户在线状态
     *
     * @param   array   $userIds
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/24
     * @Time: 11:33
     */
    public function checkUserState(Array $userIds)
    {
        if(count($userIds) > 500)
        {
            return ['code' => '-1','desc' => '一次最多查询500个账号'];
        }
        $userIds = array_map('strval',$userIds);
        $command = [
            'mission' => IM::TENCENT_MISSION_CHECK_USER_STATE,
            'data'    => [
                'userIds' => $userIds
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $checkResult = $execResult['data'];
            if($checkResult['code'] == '200')
            {
                $returnResult = ['code' => '200','desc' => $checkResult['result']];
            }
            else
            {
                $returnResult = ['code' =>'201','desc' => $checkResult['msg']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
        }
        return $returnResult;
    }
}