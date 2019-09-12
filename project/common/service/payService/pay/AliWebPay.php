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
    //回调地址
    private $notifyUrl;

    //客户端支付完成后跳转地址
    private $returnUlr = '';

    //当前APPID
    private $appId;

    //平台订单号
    private $tradeNo;

    //交易金额：当前单位/元
    private $fee;

    /**
     * 请求方式 e.g.: GET,POST
     * GET方式返回支付请求URL
     * POST方式返回支付form表单
     * @var string
     */
    private $method = 'POST';


    /**
     * APP支付下单
     * @return array
     * @throws \Exception
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/7
     * @Time: 17:04
     */
    public function unifiedOrder()
    {
        try{
            //预处理
            $this->_preInit();
            $aop = new AopClient;
            $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
            $aop->appId = $this->appId;
            $aop->rsaPrivateKey = AlipayConfig::$appIdKeyMap[$this->appId]['privateKey'];
            $aop->alipayrsaPublicKey = AlipayConfig::$appIdKeyMap[$this->appId]['publicKey'];
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->format='json';

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

            $request->setReturnUrl($this->returnUlr);
            $request->setNotifyUrl($this->notifyUrl);
            $request->setBizContent($bizcontentJson);

            $response = $aop->pageExecute($request,$this->method);

            return [
                'orderInfo' => $response
            ];
        }catch (\Exception $e){
            throw $e;
        }
    }

    /**
     * @throws InvalidBusinessConfigException
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/7
     * @Time: 16:59
     */
    private function _preInit()
    {
        $this->checkPayParams();
        $this->setNotifyUrl();
        $this->setAppId();
    }

    /**
     * 支付参数校验
     * @param $business
     * @throws InvalidBusinessConfigException
     */
    private function checkPayParams()
    {
        $tradeParams = $this->business->params;

        //平台订单号
        if(isset($tradeParams['tradeNo']) && trim($tradeParams['tradeNo']) !== false) {
            $this->tradeNo = $tradeParams['tradeNo'];
        }else{
            throw new InvalidBusinessConfigException('平台订单号必填');
        }

        //平台订单费用
        if(isset($tradeParams['fee']) && $tradeParams['fee']>0) {
            $this->fee = $tradeParams['fee'];
        }else{
            throw new InvalidBusinessConfigException('平台订单费用必填');
        }

        //需要按对应请求方式返回数据的方法
        if(isset($tradeParams['method']))
        {
            $this->method = $tradeParams['method'];
        }
    }

    /**
     * 当前支付回调地址
     */
    private function setNotifyUrl()
    {
        $this->notifyUrl = 'notify_url';
    }

    /**
     * 当前支付APPID
     * PC端扫码 默认使用中国体育应用
     */
    private function setAppId()
    {
        $this->appId = AlipayConfig::ZT_APP_ID;
    }
}