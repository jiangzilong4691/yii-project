<?php


namespace common\service\IMService;


use common\base\BaseService;

class ImService extends BaseService
{

    //userSig 过期时间 默认半年
    const USERSIG_EXPIRE_TIME = 15552000;

    //----------------好友系统----------------//

    //加好友：双向
    const FRIENDS_ADD_TYPE_BOTH = 1;
    //加好友：单向
    const FRIENDS_ADD_TYPE_SINGLE = 2;

    //校验好友：双向
    const FRIENDS_CHECK_TYPE_BOTH = 1;
    //校验好友：单向
    const FRIENDS_CHECK_TYPE_SINGLE = 2;

    //删除好友：双向
    const FRIENDS_DELETE_TYPE_BOTH = 1;
    //删除好友：单向
    const FRIENDS_DELETE_TYPE_SINGLE = 2;

    //校验黑名单：双向
    const FRIENDS_BLACK_LIST_CHECK_BOTH = 1;
    //校验黑名单：单向
    const FRIENDS_BLACK_LIST_CHECK_SINGLE = 2;

    //----------------好友系统----------------//

    //----------------资料系统----------------//

    //性别：未设置
    const ACCOUNT_PROFILE_GENDER_NONE   = 0;
    //性别：女性
    const ACCOUNT_PROFILE_GENDER_FEMALE = 1;
    //性别：男性
    const ACCOUNT_PROFILE_GENDER_MALE   = 2;

    //加好友：允许任何人
    const ACCOUNT_ADD_FRIENDS_TYPE_ALLOW_ANY = 0;
    //加好友：需要确认
    const ACCOUNT_ADD_FRIENDS_TYPE_NEED_CONFIRM = 1;
    //加好友：禁止
    const ACCOUNT_ADD_FRIENDS_TYPE_DENY_ANY = 2;

    //管理员允许用户加好友
    const ACCOUNT_ADD_FRIENDS_ADMIN_ALLOW = 0;
    //管理员禁止用户加好友
    const ACCOUNT_ADD_FRIENDS_ADMIN_DENY = 1;

    //消息设置：接收消息
    const ACCOUNT_MSG_SETTING_ALLOW = 0;
    //消息设置：不接收消息
    const ACCOUNT_MSG_SETTING_DENY = 1;

    //----------------资料系统----------------//
    
}