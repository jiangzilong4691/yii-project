<?php


namespace common\service;


use common\base\BaseService;
use common\entity\UserEntity;
use common\redis\UserRedis;

class UserService extends BaseService
{
    /**
     * 框架测试用 注：任何跟用户相关信息以zhibo-v2平台为准
     * @param   int     $userId     用户ID
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/30
     * @Time: 15:10
     */
    public function getUserInfoById($userId)
    {
        if(is_int($userId) && $userId > 0)
        {
            $userInfo = UserRedis::instance()->getUserInfoById($userId);
            if(empty($userInfo))
            {
                $userInfo = UserEntity::model()->getUserInfoByUserIdNotSens($userId);
                if(!empty($userInfo))
                {
                    UserRedis::instance()->setUserInfoById($userInfo);
                }
            }
            return $userInfo;
        }
        return [];
    }
}