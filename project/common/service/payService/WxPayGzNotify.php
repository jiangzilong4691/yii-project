<?php


namespace common\service\payService;


use common\service\OrderNotifyService;
use common\service\payService\payVendor\wechatApp\WxPayApi;
use common\service\payService\payVendor\wechatApp\WxPayConfig;
use common\service\payService\payVendor\wechatApp\WxPayNotify;
use common\service\payService\payVendor\wechatApp\WxPayOrderQuery;

class WxPayGzNotify extends WxPayNotify
{
    /**
     * 公众号支付appId
     * @var
     */
    private $_wxGzAppId;

    public function setGzAppId()
    {
        $this->_wxGzAppId = WxPayConfig::XC_GZ_APPID;
        return $this;
    }

    //查询订单
    public function Queryorder($transaction_id,$mch_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $input->SetAppid($this->_wxGzAppId);
        $input->SetMch_id($mch_id);
        $result = WxPayApi::orderQuery($input);

        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            //商户订单号
            $secaOrderNum = $result['out_trade_no'];
            //微信订单号
            $wechatOrderNum = $result['transaction_id'];
            //订单费用
            $orderFee = ((int)$result['total_fee'])/100;
            //支付查询结果平台订单处理
            return OrderNotifyService::instance()->thirdPaymentNotifyDispose($secaOrderNum,$wechatOrderNum,$orderFee);
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        $notfiyOutput = array();

        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"],$data['mch_id'])){
            $msg = "订单查询失败";
            return false;
        }
        return true;
    }
}