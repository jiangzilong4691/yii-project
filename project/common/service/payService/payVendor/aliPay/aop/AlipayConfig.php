<?php


namespace   common\service\payService\payVendor\aliPay\aop;


class AlipayConfig
{
    //------------------------------------新传在线配置------------------------------------//
    /**
     * 中国体育APPID
     */
    const ZT_APP_ID = '111';

    /**
     * 中国体育
     * 支付公钥
     */
    const ZT_PUBLIC_KEY = '111';

    /**
     * 中国体育
     * 支付私钥
     */
    const ZT_PRIVATE_KEY = '123';

    /**
     * 中国体育
     * 支付回调验证公钥
     */
    const ZT_NOTIFY_KEY = '123';

    /**
     * 直播TV APPID
     */
    const ZB_APP_ID = '222';

    /**
     * 直播TV
     * 支付公钥
     */
    const ZB_PUBLIC_KEY = '123';

    /**
     * 直播TV
     * 支付私钥
     */
    const ZB_PRIVATE_KEY = '123';

    //------------------------------------新传在线配置------------------------------------//

    /**
     * 应用APPID=>key关系映射
     * @var array
     */
    public static $appIdKeyMap = [
        self::ZT_APP_ID => [
            'publicKey' => self::ZT_PUBLIC_KEY,
            'privateKey' => self::ZT_PRIVATE_KEY,
            'notifyKey' => self::ZT_NOTIFY_KEY
        ],
        self::ZB_APP_ID => [
            'publicKey' => self::ZB_PUBLIC_KEY,
            'privateKey' => self::ZB_PRIVATE_KEY,
            'notifyKey' => ''
        ]
    ];
}