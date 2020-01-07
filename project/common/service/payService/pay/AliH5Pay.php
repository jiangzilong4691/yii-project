<?php


namespace common\service\payService\pay;


use common\service\payService\payVendor\aliPay\aop\AopClient;
use common\service\payService\payVendor\aliPay\aop\request\AlipayTradeWapPayRequest;

class AliH5Pay extends AliPay
{

    //用户付款中途退出返回商户网站的地址
    private $quitUrl;

    /**
     * 独立业务返回支付参数
     *
     * @param AopClient $aop
     *
     * @return array
     * @throws \Exception
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/19
     * @Time: 17:12
     */
    protected function businessOrder(AopClient $aop)
    {
        $request = new AlipayTradeWapPayRequest ();

        $content = $this->business->getUnifiedOrderBody();

        //支付信息
        $bizcontentArr = [
            'body' => $content,
            'subject' => $content,
            'out_trade_no' => $this->tradeNo,
            'quit_url' => $this->quitUrl,
            'product_code' => 'QUICK_WAP_WAY',
            'total_amount' => $this->fee,
            'timeout_express' => '60m',
        ];
        $bizcontentJson = json_encode($bizcontentArr);

        $request->setReturnUrl($this->getReturnUrl());
        $request->setNotifyUrl($this->getNotifyUrl());
        $request->setBizContent($bizcontentJson);

        $response = $aop->pageExecute($request, $this->method);

        return [
            'payInfo' => $response
        ];
    }

    /**
     * 独立业务参数初始化
     *
     * @param $tradeParams
     *
     * @throws \Exception
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/19
     * @Time: 17:10
     */
    protected function checkBusiPayParams($tradeParams)
    {
        if (isset($tradeParams['quitUrl']) && !empty(trim($tradeParams['quitUrl']))) {
            $this->quitUrl = trim($tradeParams['quitUrl']);
        } else {
            throw new \Exception('用户付款中途退出返回商户网站的地址必填且不能为空');
        }
    }
}