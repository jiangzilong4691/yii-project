<?php


namespace common\entity;


use common\dbConnection\Bo8tvDb;

class UserEntity extends Bo8tvDb
{
    const TABLE_NAME = '{{%user}}';

    /**
     * 可redis缓存信息
     * @var array
     */
    private static $USER_INFO_FIELDS_FOR_REDIS = [
        'user_id',
        'user_name',
        'user_passwd',
        'user_token',
        'user_head_img',
        'user_mobile',
        'user_email',
        'room_num',
        'room_is_live',
        'room_password',
        'room_level',
        'user_points',
        'user_credit',
        'user_is_hoster',
        'user_rich_exp',
        'user_rich_level',
        'user_car_level',
        'manager_level',
        'room_status',
        'fans_count',
        'ifkill',
        'user_room_type',
        'auth_info',
        'open_id'
    ];

    /**
     * 查询用户信息
     * 注：当前方法查询用户非敏感信息
     * @param   int     $userId        用户ID
     * @param   bool    $useMaster     是否使用主库
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/30
     * @Time: 14:23
     */
    public function getUserInfoByUserIdNotSens($userId,$useMaster=false)
    {
        $sql = 'SELECT ' . $this->formatFields(self::$USER_INFO_FIELDS_FOR_REDIS) . ' FROM ' . self::TABLE_NAME . ' WHERE `user_id`=:UID';
        return $this->queryOne($sql,[':UID'=>$userId],$useMaster);
    }

    public function getModels()
    {
        var_dump(self::$modelPool);die;
    }
}