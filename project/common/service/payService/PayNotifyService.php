<?php
/**
 * Created by PhpStorm.
 * Author: 姜子龙
 * Date: 2019/3/14
 * Time: 20:25
 */

namespace common\service\payService;


use common\base\BaseService;
use common\service\OrderNotifyService;
use common\service\payService\payBusiness\BusinessService;
use common\service\payService\payVendor\aliPay\aop\AlipayConfig;
use common\service\payService\payVendor\aliPay\aop\AopClient;

class PayNotifyService extends BaseService
{

    /**
     * 微信支付回调
     * @param   int     $payFrom    支付来源回调
     * @param   int     $payAppBag  支付包名标识：只有app支付时参数有效
     * @author 姜子龙<jiangzilong@zhibo.tv>
     */
    public function handleWxPayNotify($payFrom,$payAppBag)
    {
        if($payFrom == BusinessService::PAY_FROM_APP)
        {
            //app支付
            (new WxPayAppNotify())->setPayAppBag($payAppBag)->handle(false);
        }
        else if($payFrom == BusinessService::PAY_FROM_WX_GZ)
        {
            //公众号支付
            (new WxPayGzNotify())->setGzAppId()->handle(false);
        }
        else if($payFrom == BusinessService::PAY_FROM_WEB)
        {
            //与公众号支付同使用appid 业务一致
            (new WxPayGzNotify())->setGzAppId()->handle(false);
        }
    }

    /**
     * 支付宝支付回调
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/9
     * @Time: 11:35
     */
    public function handleAlipayNotify()
    {
        $notifyData = $_POST;
        if(isset($notifyData['app_id']) && !empty($notifyData['app_id']))
        {
            if(isset(AlipayConfig::$appIdKeyMap[$notifyData['app_id']]))
            {
                $aop = new AopClient;
                $aop->alipayrsaPublicKey = AlipayConfig::$appIdKeyMap[$notifyData['app_id']]['notifyKey'];
                $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");

                if($flag)
                {
                    $secaOrderNum = $_POST['out_trade_no'];
                    $aliOrderNum = $_POST['trade_no'];
                    $fee = $_POST['total_amount'];
                    $tradeStatus = $_POST['trade_status'];
                    if($tradeStatus == 'TRADE_SUCCESS' || $tradeStatus == 'TRADE_FINISHED')
                    {
                        $resutl = OrderNotifyService::instance()->thirdPaymentNotifyDispose($secaOrderNum,$aliOrderNum,$fee);
                        if($resutl)
                        {
                            exit('success ');
                        }
                    }
                }
            }
        }
        exit('fail');
    }
}