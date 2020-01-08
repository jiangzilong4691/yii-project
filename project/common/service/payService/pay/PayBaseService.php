<?php
/**
 * Created by PhpStorm.
 * User: seca
 * Date: 2019/3/13
 * Time: 15:27
 */

namespace common\service\payService\pay;


use common\service\payService\payBusiness\BusinessService;
use yii\base\BaseObject;

class PayBaseService extends BaseObject
{
    /**
     * 支付实例
     * @var
     */
    private static $instance;

    /**
     * 业务对象
     * @var object
     */
    protected $business;

    public function __construct($business,array $config = [])
    {
        $this->business = $business;
        parent::__construct($config);
    }

    /**
     * 获取支付实例
     * @param BusinessService $business
     * @return mixed
     * @author 姜子龙<jiangzilong@zhibo.tv>
     */
    public static function getPayInstance(BusinessService $business)
    {
        if(!isset(self::$instance))
        {
            $class = self::$payTypeMap[$business->payType][$business->payFrom];
            self::$instance = new $class($business);
        }
        return self::$instance;
    }

    /**
     * 支付方式类映射
     * @var array
     */
    private static $payTypeMap = [
        BusinessService::PAY_TYPE_WX => [
            BusinessService::PAY_FROM_APP   =>  WxAppPay::class,
            BusinessService::PAY_FROM_WEB   =>  WxWebPay::class,
            BusinessService::PAY_FROM_WX_GZ =>  WxGzPay::class,
            BusinessService::PAY_FROM_WX_H5 =>  WxH5Pay::class,
        ],
        BusinessService::PAY_TYPE_ALI => [
            BusinessService::PAY_FROM_APP   => AliAppPay::class,
            BusinessService::PAY_FROM_WEB   => AliWebPay::class,
            BusinessService::PAY_FROM_ALI_H5 => AliH5Pay::class,
        ]
    ];

}