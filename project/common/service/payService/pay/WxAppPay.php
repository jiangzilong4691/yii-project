<?php
/**
 * Created by PhpStorm.
 * User: seca
 * Date: 2019/3/13
 * Time: 16:31
 */

namespace common\service\payService\pay;

use common\components\helpers\ComHelper;
use common\service\payService\exception\InvalidBusinessConfigException;
use common\service\payService\payBusiness\BusinessService;
use common\service\payService\payVendor\wechatApp\WxPayApi;
use common\service\payService\payVendor\wechatApp\WxPayConfig;
use common\service\payService\payVendor\wechatApp\WxPayException;
use common\service\payService\payVendor\wechatApp\WxPayUnifiedOrder;

class WxAppPay extends WxPay
{
    //回调地址
    private $notifyUrl;

    //当前APPID
    private $appId;

    //平台订单号
    private $tradeNo;

    //订单时间
    private $timeStart;

    //订单有效期
    private $timeExpire;

    //交易类型 ： 默认
    private $tradeType = 'APP';

    //发起支付IP地址
    private $clientIp;

    //交易金额：当前单位/元
    private $fee;

    /**
     * 统一下单
     * @return array
     * @throws InvalidBusinessConfigException
     * @throws WxPayException
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/9/12
     * @Time: 11:32
     */
    public function unifiedOrder()
    {
        //预处理
        $this->_preInit();

        try{
            //统一下单数据对象
            $orderInput = new WxPayUnifiedOrder();
            //统一下单配置
            $orderInput->SetAppid($this->appId);
            $orderInput->SetMch_id(WxPayConfig::MCHID);
            $orderInput->SetDevice_info('WEB');
            $orderInput->SetBody($this->business->getUnifiedOrderBody());
            $orderInput->SetAttach('');
            $orderInput->SetOut_trade_no($this->tradeNo);
            $orderInput->SetTotal_fee($this->fee*100);
            $orderInput->SetTime_start(date('YmdHis',$this->timeStart));
            $orderInput->SetTime_expire(date('YmdHis',($this->timeStart+$this->timeExpire)));
            $orderInput->SetNotify_url($this->notifyUrl);
            $orderInput->SetTrade_type($this->tradeType);
            $orderInput->SetSpbill_create_ip($this->clientIp);
            //统一下单
            $orderReturn = WxPayApi::unifiedOrder($orderInput);

            if($orderReturn['return_code'] == 'SUCCESS' && $orderReturn['result_code'] == 'SUCCESS')
            {
                $data2App = [
                    'appid'     => $orderReturn['appid'],
                    'partnerid' => $orderReturn['mch_id'],
                    'prepayid'  => $orderReturn['prepay_id'],
                    'noncestr'  => WxPayApi::getNonceStr(),
                    'timestamp' => (string)time(),
                    'package'   => 'Sign=WXPay'
                ];
                //二次签名
                $data2App['sign'] = $this->getSign($data2App);
                return ['result' => $data2App];
            }
            //统一下单异常
            throw new WxPayException($orderReturn['return_msg']);
        }catch (WxPayException $e){
            throw $e;
        }
    }

    /**
     * 校验支付参数|预初始化参数
     * @param $business
     * @throws InvalidBusinessConfigException
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/9/12
     * @Time: 11:32
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

        //订单时间
        if(isset($tradeParams['timeStart']))
        {
            $this->timeStart = $tradeParams['timeStart'];
        }else{
            $this->timeStart = time();
        }

        //订单过期时间
        if(isset($tradeParams['timeExpire']))
        {
            $this->timeExpire = $tradeParams['timeExpire'];
        }else{
            //给默认值 两个小时
            $this->timeExpire = 7200;
        }

        //订单发起IP
        if(isset($tradeParams['clientIp'])){
            $this->clientIp = $tradeParams['clientIp'];
        }else{
            $this->clientIp = ComHelper::getClientIP();
        }
    }

    /**
     * 返回参数签名
     * @param $params
     * @return string
     * @author 姜子龙 <jiangzilong@zhibo.tv>
     */
    private function getSign($params)
    {
        ksort($params);
        $signString = '';
        foreach ($params as $key=>$val)
        {
            if(!empty($val))
            {
                $signString .= $key.'='.$val.'&';
            }
        }
        $signString = $signString.'key='.WxPayConfig::$MCHID_INFO_MAP[$params['partnerid']]['KEY'];
        return strtoupper(md5($signString));
    }

    /**
     * 当前支付回调地址
     * @param $payFrom
     */
    private function setNotifyUrl()
    {
        if($this->business->payAppBag == BusinessService::PAY_APP_BAG_ZBTV)
        {
            //直播TV回调地址
            $this->notifyUrl = 'notify_url';
        }
        else
        {
            //中国体育回调地址
            $this->notifyUrl = 'notify_url';
        }
    }

    /**
     * 当前支付APPID
     * @param $payFrom
     */
    private function setAppId()
    {
        if($this->business->payAppBag == BusinessService::PAY_APP_BAG_ZBTV)
        {
            //直播TV APPID
            $this->appId = WxPayConfig::ZBTVAPPID;
        }
        else
        {
            //中国体育APPID
            $this->appId = WxPayConfig::ZGTYAPPID;
        }
    }
}