<?php


namespace common\service;


use common\base\BaseService;
use common\service\payService\payBusiness\BusinessService;

class OrderService extends BaseService
{
    private function _createOrderNum($business)
    {
        //根据不同业务生成业务对应的订单号
    }

    public function unifiedOrder($business,$amount,$notifyUrl,$specialParam=[])
    {
        try{
            //生成平台订单订单号
            $orderNum = $this->_createOrderNum($business);
            //总订单表添加订单记录
            $commonOrderAdd = $this->add(
                $orderNum,
                $amount,
                $points,
                $userId,
                $payType,
                $platform,
                $qcodeId,
                $isBuyPoints,
                $notifyUrl,
                isset($specialParam['successurl'])?$specialParam['successurl']:''
            );
            if($commonOrderAdd)
            {
                //统一下单
                $unifiedOrderInfo = BusinessService::getInstance($business,array_merge([
                    'payFrom' => $payType,
                    'payType' => $payType,
                    'payAppBag' => $bagType,
                    'tradeNo' => $orderNum,
                    'fee' => $amount,
                ],$specialParam))->unifiedOrder();

                return [
                    'result' => true,
                    'orderNum' => $orderNum,
                    'orderData' => $unifiedOrderInfo['result']
                ];
            }
            throw new \Exception('平台订单错误');
        }catch (\Exception $e){
            //记录错误
//            var_dump($e->getMessage());die;
            return [
                'result' => false,
                'msg' => $e->getMessage()
            ];
        }
    }
}