<?php
/**
 * Created by PhpStorm.
 * Author: 姜子龙
 * Date: 2019/3/20
 * Time: 11:01
 */

namespace common\service\payService\payBusiness;


use common\service\payService\pay\PayBaseService;

class BusinessBobiService extends BusinessService
{

    //当前业务
    private $unifiedOrderName = ' 购买播币';

    /**
     * 统一下单
     * @return mixed
     * @author 姜子龙<jiangzilong@zhibo.tv>
     */
    public function unifiedOrder()
    {
        return PayBaseService::getPayInstance($this)->unifiedOrder();
    }

    /**
     * 带平台应用前缀的支付业务名称
     * @return string
     * @author 姜子龙<jiangzilong@zhibo.tv>
     */
    public function getUnifiedOrderBody()
    {
        if($this->fullBody !== null)
        {
            return $this->fullBody;
        }
        else
        {
            return $this->nameBody ? $this->getSogoName().' '.$this->nameBody : $this->getSogoName().$this->unifiedOrderName;
        }
    }
}