<?php


namespace common\base\redisSentinel;


use common\exception\redisSentinel\RedisSentinelConnectException;

class SentinelPool
{
    //主节点名称（sentinel群组名称）
    private $masterName ;

    //连接redis参数配置
    private $redisConfig;

    //sentinel群组
    private $sentinelGroup = [];

    private $_sentinel;

    /**
     * SentinelPool constructor.
     * @param   string      $masterName     主节点分组名称
     * @param   array       $redisConfig    redis连接配置
     * @param   array       $sentinelGroup  哨兵群组
     */
    private function __construct($masterName,$redisConfig,$sentinelGroup)
    {
        $this->masterName = $masterName;
        $this->redisConfig = $redisConfig;
        $this->sentinelGroup = $sentinelGroup;
    }

    /**
     * 可连接哨兵群组
     * e.g. ['masterName'=>'user','redisConfig'=>[],'group'=>[['host'=>'123.12.1.23','port'=>'26379'],['host'=>'12.123.26.12','port'=>'26380']]]
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/12
     * @Time: 10:55
     */
    public static function instance(Array $sentinelConfig)
    {
        return new self($sentinelConfig['masterName'],$sentinelConfig['redisConfig'],$sentinelConfig['group']);
    }

    /**
     * 获取sentinel实例
     *
     * @return Sentinel|null
     * @throws RedisSentinelConnectException
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/12
     * @Time: 14:17
     */
    private function getSentinel()
    {
        if($this->_sentinel === null)
        {
            if(!empty($this->sentinelGroup))
            {
                $goOn = false;
                do{
                    if(!empty($this->sentinelGroup))
                    {
                        $key = array_rand($this->sentinelGroup);
                        $config = $this->sentinelGroup[$key];
                        try{
                            $this->_sentinel = new Sentinel($config['host'],$config['port']);
                        }catch (RedisSentinelConnectException $exception){
                            unset($this->sentinelGroup[$key]);
                            $goOn = true;
                        }
                    }
                }while($goOn);

                if($this->_sentinel == null)
                {
                    throw new RedisSentinelConnectException("$this->masterName 无可连接哨兵");
                }
            }
            else
            {
                throw new RedisSentinelConnectException($this->masterName.'哨兵群组配置不能为空');
            }
        }
        return $this->_sentinel;
    }

    /**
     * 服务器状态
     * @param   string  $flags  redis实例状态 正常为role标识 master/slave 异常 s_down/o_down,role,disconnected
     * @return false|int
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/12
     * @Time: 14:55
     */
    private function checkServerDown($flags)
    {
        return preg_match('/(down)|(disconnected)/',$flags);
    }

    /**
     * 获取主库配置
     * 注：暂未做库异常校验
     * @return array
     * @throws RedisSentinelConnectException
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/12
     * @Time: 14:30
     */
    public function getMasterConfig()
    {
        $master = $this->getSentinel()->getMaster($this->masterName);
        return array_merge(
            [
                'host' => $master['ip'],
                'port' => $master['port']
            ],
            $this->redisConfig
        );
    }

    /**
     *
     * @return array
     * @throws RedisSentinelConnectException
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/12
     * @Time: 14:55
     */
    public function getSlavesConfig()
    {
        $slaves = $this->getSentinel()->getSlaves($this->masterName);
        $validSlaves = [];
        foreach ($slaves as $slave)
        {
            if(!$this->checkServerDown($slave['flags']))
            {
                $validSlaves[] = [
                    'host' => $slave['ip'],
                    'port' => $slave['port']
                ];
            }
        }
        if(count($validSlaves)>1)
        {
            shuffle($validSlaves);
        }
        return array_merge(
            $validSlaves[0],
            $this->redisConfig
        );
    }

    /**
     * 拦截器 对象委托
     * @param   string  $name       方法名
     * @param   array   $arguments  方法参数
     * @return mixed
     * @throws RedisSentinelConnectException
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/12
     * @Time: 14:59
     */
    public function __call($name, $arguments)
    {
        $sentinel = $this->getSentinel();
        if(!method_exists($sentinel,$name))
        {
            throw new RedisSentinelConnectException("方法${name}不存在");
        }
        return call_user_func_array([$sentinel,$name],$arguments);
    }
}