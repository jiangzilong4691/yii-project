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
    //回调地址
    private $notifyUrl;

    //当前APPID
    private $appId;

    //平台订单号
    private $tradeNo;

    //交易金额：当前单位/元
    private $fee;


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
                'result' => $response
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
        if(isset($tradeParams['tradeNo'])) {
            $this->tradeNo = $tradeParams['tradeNo'];
        }else{
            throw new InvalidBusinessConfigException('平台订单号必填');
        }

        //平台订单费用
        if(isset($tradeParams['fee'])) {
            $this->fee = $tradeParams['fee'];
        }else{
            throw new InvalidBusinessConfigException('平台订单费用必填');
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
     */
    private function setAppId()
    {
        $this->appId = AlipayConfig::ZT_APP_ID;
    }
}