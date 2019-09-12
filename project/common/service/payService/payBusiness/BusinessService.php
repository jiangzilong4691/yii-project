<?php
/**
 * Created by PhpStorm.
 * User: seca
 * Date: 2019/3/13
 * Time: 14:03
 */

namespace common\service\payService\payBusiness;


use common\service\payService\exception\InvalidBusinessConfigException;
use common\service\payService\exception\InvalidBusinessExcetion;
use yii\base\BaseObject;

abstract class BusinessService extends BaseObject
{
    //--------------------------------业务ID--------------------------------//
    /**
     * 播币充值业务
     */
    const BUSINESS_BOBI = 1;

    /**
     * vip(会员)充值业务
     */
    const BUSINESS_VIP = 2;

    /**
     * shop(商城)购买业务
     */
    const BUSINESS_SHOP = 3;

    /**
     * 课程（教程）购买业务
     */
    const BUSINESS_CURRICULUM = 4;
    //--------------------------------业务ID--------------------------------//

    //--------------------------------支付来源--------------------------------//

    /**
     * APP支付
     */
    const PAY_FROM_APP = 1;

    /**
     * web端支付
     */
    const PAY_FROM_WEB =2;

    /**
     * 微信公众号支付
     */
    const PAY_FROM_WX_GZ = 3;

    //--------------------------------支付来源--------------------------------//

    //--------------------------------支付方式--------------------------------//

    /**
     * 微信支付
     */
    const PAY_TYPE_WX = 1;

    /**
     * 支付宝支付
     */
    const PAY_TYPE_ALI = 2;

    //--------------------------------支付方式--------------------------------//

    //--------------------------------APP支付包名--------------------------------//
    /**
     * 支付包名：直播TV
     */
    const PAY_APP_BAG_ZBTV = 1;

    /**
     * 支付包名：中国体育
     */
    const PAY_APP_BAG_ZGTY = 2;

    //--------------------------------APP支付包名--------------------------------//

    //--------------------------------发起业务支付传参--------------------------------//

    /**
     * 发起服务支付的参数集合
     * @var
     */
    public $params;

    /**
     * 发起支付来源
     * @var
     */
    public $payFrom;

    /**
     * 支付类型
     * @var
     */
    public $payType;

    /**
     * APP应用包标识
     * @var
     */
    public $payAppBag;

    /**
     * 支付业务名称，不传默认当前payAppPay商户
     * @var
     */
    public $nameBody;

    /**
     * 支付业务全名
     * @var
     */
    public $fullBody;

    //--------------------------------发起业务支付传参--------------------------------//

    /**
     * 业务实例集合
     * @var array
     */
    private static $instance = [];

    /**
     * 获取当前业务实例
     * @param   int     $businessId     业务ID
     * @param   array   $params         业务参数
     * @return mixed
     * @author 姜子龙<jiangzilong@zhibo.tv>
     * @throws InvalidBusinessConfigException
     * @throws InvalidBusinessExcetion
     */
    public static function getInstance($businessId,Array $params)
    {
        $businessMap = self::_businessMap();
        if(isset($businessMap[$businessId]))
        {
            if(isset($businessMap[$businessId]['class']) && !empty($businessMap[$businessId]['class']))
            {
                if(!isset(self::$instance[$businessId]))
                {
                    self::$instance[$businessId] = \Yii::createObject(array_merge($businessMap[$businessId],['params'=>$params]));
                }
                return self::$instance[$businessId];
            }
            throw new InvalidBusinessConfigException('业务配置数组信息缺少class');
        }
        throw new InvalidBusinessExcetion('当前业务服务不存在');
    }

    /**
     * 预处理
     * @throws InvalidBusinessConfigException
     */
    public function init()
    {
        parent::init();
        $this->_setPayFrom();
        $this->_setPayType();
        $this->_setAppPayBag();
        $this->_setPayNameBody();
    }

    /**
     * 初始化支付来源
     * @throws InvalidBusinessConfigException
     */
    private function _setPayFrom()
    {
        if(isset($this->params['payFrom'])
            &&
            in_array($this->params['payFrom'],[self::PAY_FROM_APP,self::PAY_FROM_WEB,self::PAY_FROM_WX_GZ]))
        {
            $this->payFrom = $this->params['payFrom'];
            unset($this->params['payFrom']);
        }
        else
        {
            throw new InvalidBusinessConfigException('支付来源必填：App或Web');
        }
    }

    /**
     * 初始化支付方式
     * @throws InvalidBusinessConfigException
     */
    private function _setPayType()
    {
        if(isset($this->params['payType'])
            && in_array($this->params['payType'],[self::PAY_TYPE_WX,self::PAY_TYPE_ALI])
        )
        {
            $this->payType = $this->params['payType'];
            unset($this->params['payType']);
        }
        else
        {
            throw new InvalidBusinessConfigException('支付方式必填：WeChat或Alipay');
        }
    }

    /**
     * App支付，app包名初始化
     * @throws InvalidBusinessConfigException
     */
    private function _setAppPayBag()
    {
        if($this->payFrom == self::PAY_FROM_APP)
        {
            if(isset($this->params['payAppBag'])
                && in_array($this->params['payAppBag'],[self::PAY_APP_BAG_ZBTV,self::PAY_APP_BAG_ZGTY])
            )
            {
                $this->payAppBag = $this->params['payAppBag'];
                unset($this->params['payAppBag']);
            }
            else
            {
                throw new InvalidBusinessConfigException('App支付方式app包标识必填：直播TV或中国体育');
            }
        }
    }

    /**
     * 设置支付名称
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/9
     * @Time: 17:15
     */
    private function _setPayNameBody()
    {
        //支付名称
        if(isset($this->params['nameBody']))
        {
            $this->nameBody = $this->params['nameBody'];
        }
        //支付全名
        if(isset($this->params['fullBody']))
        {
            $this->fullBody = $this->params['fullBody'];
        }
    }

    /**
     * 业务关系类映射
     * @return array
     */
    private static function _businessMap()
    {
        return [
            self::BUSINESS_BOBI =>[
                'class' => BusinessBobiService::className()
            ],
            self::BUSINESS_VIP => [
                'class' => BusinessVipService::className(),
            ],
            self::BUSINESS_SHOP => [
                'class' => BusinessShopService::className()
            ],
            self::BUSINESS_CURRICULUM => [
                'class' => BusinessCurriculumService::className()
            ]
        ];
    }

    /**
     * 商户标识/名称映射
     * @return array
     */
    protected function sogoMap()
    {
        return [
            self::PAY_FROM_APP =>[
                self::PAY_APP_BAG_ZBTV => '直播TV',
                self::PAY_APP_BAG_ZGTY => '中国体育'
            ],
            self::PAY_FROM_WEB => '中国体育',
            self::PAY_FROM_WX_GZ => '中国体育'
        ];
    }

    /**
     * 获取当前支付商户名称
     * @return mixed
     */
    protected function getSogoName()
    {
        $sogoMap = $this->sogoMap();
        if(is_array($sogoMap[$this->payFrom]))
        {
            return $sogoMap[$this->payFrom][$this->payAppBag];
        }
        else
        {
            return $sogoMap[$this->payFrom];
        }
    }

    /**
     * 支付统一下单
     * @return mixed
     */
    abstract public function unifiedOrder();
}