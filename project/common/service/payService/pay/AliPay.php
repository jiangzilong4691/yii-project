<?php


namespace common\service\payService\pay;


use common\service\payService\exception\InvalidBusinessConfigException;
use common\service\payService\payVendor\aliPay\aop\AlipayConfig;
use common\service\payService\payVendor\aliPay\aop\AopClient;

abstract class AliPay extends PayBaseService
{
    //平台订单号
    protected $tradeNo;

    //交易金额：当前单位/元
    protected $fee;

    /**
     * 请求方式 e.g.: GET,POST
     * GET方式返回支付请求URL
     * POST方式返回支付form表单
     * @var string
     */
    protected $method = 'POST';


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
            $aop->appId = $this->getAppId();
            $aop->rsaPrivateKey = AlipayConfig::$appIdKeyMap[$this->appId]['privateKey'];
            $aop->alipayrsaPublicKey = AlipayConfig::$appIdKeyMap[$this->appId]['publicKey'];
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->format='json';

            return $this->businessOrder($aop);

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
        $tradeParams = $this->business->params;
        $this->checkPayParams($tradeParams);
        //独立业务参数校验
        $this->checkBusiPayParams($tradeParams);
    }

    /**
     * 支付参数校验
     * @param $business
     * @throws InvalidBusinessConfigException
     */
    private function checkPayParams($tradeParams)
    {
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
     * 支付异步回调通知地址
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/19
     * @Time: 16:32
     */
    protected function getNotifyUrl()
    {
        return REST_URL.'/pay-notify/ali';
    }

    /**
     * 支付同步回调通知地址
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/19
     * @Time: 16:28
     */
    protected function getReturnUrl()
    {
        //当前同步通知地址为www主站地址
        return OLD_URL.'/pay/upgraderesult';
    }

    /**
     * 当前支付APPID
     * PC端扫码 默认使用中国体育应用
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/19
     * @Time: 16:35
     */
    protected function getAppId()
    {
        return AlipayConfig::ZT_APP_ID;
    }

    //独立业务参数校验
    abstract protected function checkBusiPayParams($tradeParams);

    //独立业务支付下单请求
    abstract protected function businessOrder(AopClient $aop);
}