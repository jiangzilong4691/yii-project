<?php


namespace common\service\payService\pay;

use common\service\payService\exception\InvalidBusinessConfigException;
use common\service\payService\payVendor\wechatApp\WxPayException;
use common\service\payService\payVendor\wechatApp\WxPayUnifiedOrder;

class WxH5Pay extends WxPay
{
    /**
     * @return array|string
     * @throws InvalidBusinessConfigException
     * @throws WxPayException
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/17
     * @Time: 16:32
     */
    public function unifiedOrder()
    {
        $unifiedOrder = parent::unifiedOrder();
        return [
            'payInfo' => isset($unifiedOrder['mweb_url'])? $unifiedOrder['mweb_url'] :'',
        ];
    }

    protected function busiInit()
    {
        // TODO: Implement busiInit() method.
    }

    protected function setPayBusiConfig(WxPayUnifiedOrder $inputObject)
    {
        // TODO: Implement setPayBusiConfig() method.
    }

    /**
     * 当前支付回调地址
     * @return mixed|string
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/19
     * @Time: 15:03
     */
    protected function getNotifyUrl()
    {
        return REST_URL . '/pay-notify/wx-gz';
    }

    /**
     * 支付类型 ：H5支付的交易类型为MWEB
     * @return mixed|string
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/17
     * @Time: 16:39
     */
    protected function getTradeType()
    {
        return 'MWEB';
    }
}