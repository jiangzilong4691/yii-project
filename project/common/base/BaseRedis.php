<?php


namespace common\base;


use common\exception\RedisException;
use common\service\exception\ExceptionService;

abstract class BaseRedis extends BaseService
{
    /**
     * redis 连接配置项
     * @var
     */
    abstract protected function getConfig($getMaster);

    /**
     * redis 连接池
     *
     * 配置连接层 实例对象静态存储
     *
     * @var array
     */
    protected static $_redisConnPool = [];

    /**
     * 主连接
     *
     * 业务连接层 实例对象存储
     *
     * @var
     */
    protected $masterConn;

    /**
     * 主连接 已选库池
     * @var array
     */
    protected $masterDatabase = [];

    /**
     * 从连接
     *
     * 业务连接层 实例对象存储
     *
     * @var
     */
    protected $slaveConn;

    /**
     * 从连接 已选库池
     * @var array
     */
    protected $slaveDatabase = [];

    /**
     * redis 连接
     *
     * @param   array  $config      连接配置
     * @param   string $role        所属角色信息
     *
     * @return \Redis|bool
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/29
     * @Time: 18:03
     */
    protected function getConnInstance($config,$role)
    {
        try{
            if($config === null || !is_array($config) ||!isset($config['host'],$config['port'],$config['timeout']))
            {
                throw new RedisException(get_class($this).'【'.$role.'】库连接配置错误');
            }
            //由配置设置key 相同配置只做一次连接操作
            $connCacheKey = md5(json_encode($config));
            if(!isset(self::$_redisConnPool[$connCacheKey]))
            {
                ini_set('default_socket_timeout',-1);
                $redis = new \Redis();
                if($redis->connect($config['host'],$config['port'],$config['timeout']))
                {
                    if(isset($config['password']) && !empty($config['password']))
                    {
                        if(!$redis->auth($config['password']))
                        {
                            throw new RedisException('redis【'.$role.'】'.$config['host'].':'.$config['port'].' 密码错误');
                        }
                    }
                    //当前配置实例化静态存储
                    self::$_redisConnPool[$connCacheKey] = $redis;
                }
                else
                {
                    throw new RedisException('redis【'.$role.'】'.$config['host'].':'.$config['port'].' 连接失败');
                }
            }
            return self::$_redisConnPool[$connCacheKey];
        }catch (RedisException $e){
            //记录连接错误
            ExceptionService::instance()->recordException($e);
            return false;
        }
    }

    /**
     * 获取redis连接实例
     *
     * @param int $db           选取的db库 0-15
     * @param bool $useMaster   是否主库连接
     *
     * @return bool|\Redis
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/28
     * @Time: 12:59
     */
    protected function getRedis($db = 0,$useMaster = false)
    {
        return $useMaster ? $this->master($db) : $this->slave($db);
    }

    /**
     * redis 获取主库连接
     *
     * @param   int     $db     选取的库 e.g. [0-15]
     *
     * @return bool|\Redis
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/29
     * @Time: 18:16
     */
    private function master($db)
    {
        if($this->masterConn == null)
        {
            $configs = $this->getConfig(true);
            $masterConfig = is_array($configs) && !empty($configs) ? $configs : [];
            $this->masterConn = $this->getConnInstance($masterConfig,'master');
        }
        if($this->masterConn instanceof \Redis)
        {
            $this->masterConn->select($db);
        }
        return $this->masterConn;
    }

    /**
     * redis 获取从库连接
     *
     * @param   int     $db     选取的库 e.g. [0-16]
     *
     * @return bool|\Redis
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/29
     * @Time: 18:16
     */
    private function slave($db)
    {
        if($this->slaveConn == null)
        {
            $slaveConfig = $this->getSlaveConfig();
            if(empty($slaveConfig))
            {
                //从库未配置 选取主库
                return $this->master($db);
            }
            $this->slaveConn = $this->getConnInstance($slaveConfig,'slave');
        }
        if($this->slaveConn instanceof \Redis)
        {
            $this->slaveConn->select($db);
        }
        return $this->slaveConn;
    }

    /**
     * 随机选取一个从库配置
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/29
     * @Time: 18:20
     */
    private function getSlaveConfig()
    {
        $configs = $this->getConfig(false);
        if(is_array($configs) && !empty($configs))
        {
            if(count($configs)>1)
            {
                shuffle($configs);
            }
            return $configs[0];
        }
        return [];
    }
}