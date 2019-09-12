<?php
/**
 * 总订单表回调处理服务
 * Created by PhpStorm.
 * Author: 姜子龙
 * Date: 2019/3/15
 * Time: 14:36
 */

namespace common\service;


use common\base\BaseService;
use common\components\helpers\CurlHelper;
use common\entity\OrderEntity;
use common\redis\OrderRedis;
use common\service\payService\exception\InvalidOrderdNotifyException;
use common\service\payService\exception\OrderNotifyBusinessException;

class OrderNotifyService extends BaseService
{

    /**
     * 业务类支付回调 key
     * @var string
     */
    protected $_comBusiNotifyKey = '1111';

    /**
     * 当前订单
     * @var
     */
    protected $orderModel;

    /**
     * 当前订单模型
     * @return OrderEntity
     * @author 姜子龙<jiangzilong@zhibo.tv>
     */
    protected function getOrderModel()
    {
        if($this->orderModel === null)
        {
            $this->orderModel = new OrderEntity();
        }
        return $this->orderModel;
    }

    public function getBusiNotifyKey()
    {
        return $this->_comBusiNotifyKey;
    }

    /**
     * 支付业务回调统一处理
     * @param string    $secaOrderNum           平台订单号
     * @param string    $thirdPaymentOrderNum   第三方支付订单号
     * @param float     $orderFee               订单费用
     * @return bool
     * @author 姜子龙<jiangzilong@zhibo.tv>
     */
    public function thirdPaymentNotifyDispose($secaOrderNum,$thirdPaymentOrderNum,$orderFee)
    {
        if(OrderRedis::self()->orderNotifyGetLock($secaOrderNum))
        {
            //锁未失效 返回失败
            return false;
        }
        else
        {
            //添加并发锁
            OrderRedis::self()->orderNotifyAddLock($secaOrderNum);
        }

        $orderModel = $this->getOrderModel();
        $orderInfo = $orderModel->getOrderInfoByOrderNum($secaOrderNum);

        //------------------------订单校验开始------------------------//
        try{
            //校验订单：订单不存在返回失败，回调服务器端重新发起回调
            if(empty($orderInfo))
            {
                throw new InvalidOrderdNotifyException('订单不存在');
            }

            //校验订单状态：已支付状态，直接返回成功
            if($orderInfo['payStatus'] == OrderEntity::STATUS_SUCCESS)
            {
                return true;
            }

            //校验订单用户
            $userInfo = UserService::instance()->getInfoById((int)$orderInfo['userId']);
            if(empty($userInfo))
            {
                throw new InvalidOrderdNotifyException('订单用户不存在');
            }

            //校验订单金额
            if((float)$orderFee != (float)$orderInfo['amount'])
            {
                throw new InvalidOrderdNotifyException('支付金额与订单金额不符');
            }

//            $orderModel->addLog($secaOrderNum,'支付订单校验成功:开始处理订单业务');

        }catch (InvalidOrderdNotifyException $e){
            //添加失败日志
//            $orderModel->addLog($secaOrderNum,$e->getMessage());
            //清除并发锁
//            OrderRedis::self()->orderNotifyDelLock($secaOrderNum);
            //返回回调服务器发起回调失败
            return false;
        }
        //------------------------订单校验结束------------------------//

        //------------------------订单业务处理开始------------------------//
        try{

            if(!$orderModel->updateStatus($secaOrderNum,$thirdPaymentOrderNum,OrderEntity::STATUS_SUCCESS))
            {
                throw new OrderNotifyBusinessException('修改订单状态失败',-1);
            }

            //业务区分
            if($orderInfo['isBuyPoints'] == OrderEntity::ISBUYPOINTS_YES)
            {
                //播币购买业务
                $this->_execAddPointsOperation($orderInfo,$userInfo,$thirdPaymentOrderNum);
            }
            else if($orderInfo['isBuyPoints'] == OrderEntity::ISBUYPOINTS_NO)
            {
                //暂：其他支付业务，通过回调地址通知
                $this->_execOtherComOperation($orderInfo,$userInfo,$thirdPaymentOrderNum);
            }
            else
            {
                throw new OrderNotifyBusinessException('当前订单业务不支持');
            }

        }catch (OrderNotifyBusinessException $e){
//            $orderModel->addLog($secaOrderNum,$e->getMessage());
//            OrderRedis::self()->orderNotifyDelLock($secaOrderNum);
            if($e->getCode() == -1)
            {
                //修改订单状态失败可通知第三方回调服务器重新回调
                return false;
            }
        }
        //订单已支付成功，并已更新支付状态，不再通知第三方回调服务器回调
        return true;
        //------------------------订单业务处理结束------------------------//
    }

    /**
     * 播币充值业务处理
     * @param $orderInfo
     * @param $userInfo
     * @param $thirdPaymentOrderNum
     * @author 姜子龙<jiangzilong@zhibo.tv>
     * @throws OrderNotifyBusinessException
     */
    private function _execAddPointsOperation($orderInfo,$userInfo,$thirdPaymentOrderNum)
    {

    }

    /**
     * 首充返利处理
     * @param $userInfo
     * @param $orderInfo
     * @return bool
     * @author 姜子龙<jiangzilong@zhibo.tv>
     * @throws OrderNotifyBusinessException
     */
    private function userFirstRechargeDeal($userInfo,$orderInfo)
    {

    }

    /**
     * 针对平台业务订单 做异步回调通知处理
     * @param $orderInfo
     * @param $userInfo
     * @param $thirdPaymentOrderNum
     * @return bool
     * @author 姜子龙<jiangzilong@zhibo.tv>
     * @throws OrderNotifyBusinessException
     */
    private function _execOtherComOperation($orderInfo,$userInfo,$thirdPaymentOrderNum)
    {
        if(!empty($orderInfo['notifyurl']))
        {
            $userId = $orderInfo['userId'];
            $orderId = $orderInfo["orderid"];
            $bankOrderId = $thirdPaymentOrderNum;
            $payType = $orderInfo["payType"];
            $notifyUrl = $orderInfo['notifyurl'];
            //通知业务平台订单成功
            CurlHelper::post($notifyUrl,[
                    "uid"=>$userId,
                    "orderid"=>$orderId,
                    "bankorderid"=>$bankOrderId,
                    "paytype"=>$payType,
                    "sign"=>md5($userId . $orderId . $bankOrderId . $payType . $this->_comBusiNotifyKey)
                ]
            );
//            $this->getOrderModel()->addLog($orderId,'对{'.$notifyUrl.'}已做回调，当前业务结束');
            return true;
        }
        else
        {
            throw new OrderNotifyBusinessException('业务回调地址为空，处理失败');
        }
    }
}