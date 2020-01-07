<?php
/**
 * Created by PhpStorm.
 * Author: 姜子龙
 * Date: 2019/3/14
 * Time: 13:51
 */

namespace common\service\payService\pay;


use common\service\payService\exception\InvalidBusinessConfigException;
use common\service\payService\payVendor\aliPay\aop\AlipayConfig;
use common\service\payService\payVendor\aliPay\aop\AopClient;
use common\service\payService\payVendor\aliPay\aop\request\AlipayTradePagePayRequest;

class AliWebPay extends AliPay
{
    /**
     * @param AopClient $aop
     * @return array
     * @throws \Exception
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/1/7
     * @Time: 16:21
     */
    protected function businessOrder(AopClient $aop)
    {
        $request = new AlipayTradePagePayRequest();

        $content = $this->business->getUnifiedOrderBody();

        //支付信息
        $bizcontentArr = [
            'out_trade_no' => $this->tradeNo,
            'product_code' => 'FAST_INSTANT_TRADE_PAY',
            'total_amount' => $this->fee,
            'body' => $content,
            'subject' => $content,
            'timeout_express' => '60m',
        ];
        $bizcontentJson = json_encode($bizcontentArr);

        $request->setReturnUrl($this->getReturnUrl());
        $request->setNotifyUrl($this->getNotifyUrl());
        $request->setBizContent($bizcontentJson);

        $response = $aop->pageExecute($request,$this->method);

        return [
            'payInfo' => $response
        ];
    }

    protected function checkBusiPayParams($tradeParams)
    {
        // TODO: Implement checkBusiPayParams() method.
    }
}