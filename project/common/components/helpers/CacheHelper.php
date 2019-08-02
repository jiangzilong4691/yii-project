<?php


namespace common\components\helpers;


use common\base\BaseHelper;

class CacheHelper extends BaseHelper
{

    //-------------------------------------缓存时长基数,自定义时长以基数计算-------------------------------------//

    /**
     * 缓存时长 ：1天
     */
    const CACHE_1_DAY = 86400;

    /**
     * 缓存时长 ： 1小时
     */
    const CACHE_ONE_HOUR = 3600;

    /**
     * 缓存时长 ：1分钟
     */
    const CACHE_TEN_ONE_MINUTE = 60;

    //-------------------------------------缓存时长基数,自定义时长以基数计算-------------------------------------//
}