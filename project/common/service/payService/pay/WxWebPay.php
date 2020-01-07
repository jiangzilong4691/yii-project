<?php
/**
 * Created by PhpStorm.
 * Author: 姜子龙
 * Date: 2019/3/14
 * Time: 13:49
 */

namespace common\service\payService\pay;


use common\components\ComHelper;
use common\service\payService\exception\InvalidBusinessConfigException;
use common\service\payService\payVendor\wechatApp\WxPayApi;
use common\service\payService\payVendor\wechatApp\WxPayConfig;
use common\service\payService\payVendor\wechatApp\WxPayException;
use common\service\payService\payVendor\wechatApp\WxPayUnifiedOrder;

class WxWebPay extends PayBaseService
{
    //回调地址
    private $notifyUrl;

    //平台订单号
    private $tradeNo;

    //订单时间
    private $timeStart;

    //订单有效期
    private $timeExpire;

    //交易类型 ： 默认
    private $tradeType = 'NATIVE';

    //发起支付IP地址
    private $clientIp;

    //交易金额：当前单位/元
    private $fee;

    //交易商品ID
    private $productId;

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
            $input->SetOut_trade_no($this->tradeNo);
            $input->SetTotal_fee($this->fee*100);
            $input->SetProduct_id($this->productId);
            $input->SetTime_start(date('YmdHis',$this->timeStart));
            $input->SetTime_expire(date('YmdHis',($this->timeStart+$this->timeExpire)));
            $input->SetNotify_url($this->notifyUrl);
            $input->SetTrade_type($this->tradeType);
            $input->SetSpbill_create_ip($this->clientIp);

            $unifiedOrderResutl = WxPayApi::unifiedOrder($input);

            if($unifiedOrderResutl['return_code'] == 'SUCCESS')
            {
                if(isset($unifiedOrderResutl['result_code']) && $unifiedOrderResutl['result_code'] == 'SUCCESS')
                {
                    //返回二维码生成地址
                    return [
                        'qrcode' => REST_URL.'/qrcode/out?'."img=".json_encode(['imgUrl'=>$unifiedOrderResutl['code_url']]),
                    ];
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
     * 校验支付参数|预初始化参数
     * @param $business
     * @throws InvalidBusinessConfigException
     */
    private function _preInit()
    {

        $this->checkPayParams();
        $this->setNotifyUrl();

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

        //平台订单商品ID
        if(isset($tradeParams['productId']) && !empty($tradeParams['productId']))
        {
            $this->productId = $tradeParams['productId'];
        }
        else
        {
            throw new InvalidBusinessConfigException('平台商品ID用必填');
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
     * 当前支付回调地址
     * @param $payFrom
     */
    private function setNotifyUrl()
    {
        $this->notifyUrl = REST_URL . '/pay-notify/wx-gz';
    }
}