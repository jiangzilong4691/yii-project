<?php
/**
 * Created by PhpStorm.
 * Author: 姜子龙
 * Date: 2019/3/14
 * Time: 13:46
 */

namespace common\service\payService\pay;

use common\service\payService\payVendor\aliPay\aop\request\AlipayTradeAppPayRequest;
use common\service\payService\exception\InvalidBusinessConfigException;
use common\service\payService\payVendor\aliPay\aop\AlipayConfig;
use common\service\payService\payVendor\aliPay\aop\AopClient;

class AliAppPay extends AliPay
{

    protected function businessOrder(AopClient $aop)
    {
        $request = new AlipayTradeAppPayRequest();

        $content = $this->business->getUnifiedOrderBody();

        //支付信息
        $bizcontentArr = [
            'body' => $content,
            'subject' => $content,
            'out_trade_no' => $this->tradeNo,
            'timeout_express' => '60m',
            'total_amount' => $this->fee,
            'product_code' => 'QUICK_MSECURITY_PAY'
        ];
        $bizcontentJson = json_encode($bizcontentArr);

        $request->setNotifyUrl($this->notifyUrl);
        $request->setBizContent($bizcontentJson);

        $response = $aop->sdkExecute($request);

        return [
            'payInfo' => $response
        ];
    }

    protected function checkBusiPayParams($tradeParams)
    {
        // TODO: Implement checkBusiPayParams() method.
    }
}