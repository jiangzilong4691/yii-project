<?php


namespace common\service\payService\pay;


use common\components\ComHelper;
use common\service\payService\exception\InvalidBusinessConfigException;
use common\service\payService\payVendor\wechatApp\WxPayApi;
use common\service\payService\payVendor\wechatApp\WxPayConfig;
use common\service\payService\payVendor\wechatApp\WxPayException;
use common\service\payService\payVendor\wechatApp\WxPayUnifiedOrder;

abstract class WxPay extends PayBaseService
{
    //平台订单号
    protected $tradeNo;

    //订单时间
    protected $timeStart;

    //订单有效期
    protected $timeExpire;

    //发起支付IP地址
    protected $clientIp;

    //交易金额：当前单位/元
    protected $fee;

    /**
     * 统一下单
     * @return
     * @throws WxPayException|InvalidBusinessConfigException
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/19
     * @Time: 11:30
     */
    public function unifiedOrder()
    {

        //预处理
        $this->_preInit();

        try{
            $input = new WxPayUnifiedOrder();
            $input->SetAppid(WxPayConfig::XC_GZ_APPID);
            $input->SetMch_id(WxPayConfig::MCHID);
            $input->SetBody($this->business->getUnifiedOrderBody());
            $input->SetAttach("");
            $input->SetOut_trade_no($this->tradeNo);
            $input->SetTotal_fee($this->fee*100);
            $input->SetTime_start(date('YmdHis',$this->timeStart));
            $input->SetTime_expire(date('YmdHis',($this->timeStart+$this->timeExpire)));
            $input->SetGoods_tag('');
            $input->SetNotify_url($this->getNotifyUrl());
            $input->SetTrade_type($this->getTradeType());
            $input->SetSpbill_create_ip($this->clientIp);

            $this->setPayBusiConfig($input);

            $unifiedOrderResutl = WxPayApi::unifiedOrder($input);

            if($unifiedOrderResutl['return_code'] == 'SUCCESS')
            {
                if(isset($unifiedOrderResutl['result_code']) && $unifiedOrderResutl['result_code'] == 'SUCCESS')
                {
                    return $unifiedOrderResutl;
                }
                else
                {
                    throw new WxPayException($unifiedOrderResutl['err_code_des']);
                }
            }
            else
            {
                //统一下单异常
                throw new WxPayException($unifiedOrderResutl['return_msg']);
            }
        }catch (WxPayException $e){
            throw $e;
        }
    }

    /**
     * @throws InvalidBusinessConfigException
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/17
     * @Time: 16:16
     */
    private function _preInit()
    {
        //基础支付信息初始化
        $this->basicInit();

        //业务支付参数初始化
        $this->busiInit();
    }

    /**
     * @throws InvalidBusinessConfigException
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/17
     * @Time: 16:16
     */
    private function basicInit()
    {
        $tradeParams = $this->business->params;

        //平台订单号
        if(isset($tradeParams['tradeNo']) && !empty($tradeParams['tradeNo']))
        {
            $this->tradeNo = $tradeParams['tradeNo'];
        }
        else
        {
            throw new InvalidBusinessConfigException('平台订单号必填');
        }

        //平台订单费用
        if(isset($tradeParams['fee']) && (float)$tradeParams['fee']>0)
        {
            $this->fee = $tradeParams['fee'];
        }
        else
        {
            throw new InvalidBusinessConfigException('平台订单费用必填');
        }

        //订单时间
        if(isset($tradeParams['timeStart']))
        {
            $this->timeStart = $tradeParams['timeStart'];
        }
        else
        {
            $this->timeStart = time();
        }

        //订单过期时间
        if(isset($tradeParams['timeExpire']))
        {
            $this->timeExpire = $tradeParams['timeExpire'];
        }
        else
        {
            //给默认值 两个小时
            $this->timeExpire = 7200;
        }

        //订单发起IP
        if(isset($tradeParams['clientIp']))
        {
            $this->clientIp = $tradeParams['clientIp'];
        }
        else
        {
            $this->clientIp = ComHelper::getClientIP();
        }
    }

    /**
     * 业务 参数初始化
     * @return mixed
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/17
     * @Time: 16:20
     */
    abstract protected function busiInit();

    /**
     * 支付回调地址
     * @return mixed
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/17
     * @Time: 16:24
     */
    abstract protected function getNotifyUrl();

    /**
     * 获取业务类型
     * @return mixed
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/17
     * @Time: 16:38
     */
    abstract protected function getTradeType();

    /**
     * 业务支付参数设置
     * @param WxPayUnifiedOrder $inputObject
     * @return mixed
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/17
     * @Time: 16:34
     */
    abstract protected function setPayBusiConfig(WxPayUnifiedOrder $inputObject);
}