<?php


namespace common\base\redisSentinel;


use common\exception\redisSentinel\RedisSentinelConnectException;

class Sentinel
{
    /**
     * @var \Redis
     */
    private $sentinel;

    //sentinel地址
    private $sentinelHost;

    //sentinel端口号
    private $sentinelPort;

    /**
     * Sentinel constructor.
     * @param $host
     * @param $port
     * @throws RedisSentinelConnectException
     */
    public function __construct($host,$port)
    {
        $this->sentinelHost = $host;
        $this->sentinelPort = $port;
        $this->sentinelConn();
    }

    /**
     * 哨兵连接实例
     *
     * @throws RedisSentinelConnectException
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/11
     * @Time: 15:55
     */
    private function sentinelConn()
    {
        $redis = new \Redis();
        if($redis->connect($this->sentinelHost,$this->sentinelPort,30))
        {
            $this->sentinel = $redis;
        }
        else
        {
            throw new RedisSentinelConnectException("连接失败");
        }
    }

    /**
     * 返回解析后的redis服务器信息
     *
     * @param $redisServersInfo
     *
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/11
     * @Time: 15:35
     */
    private function parseRedisServersInfo($redisServersInfo)
    {
        $servers = [];
        if(!empty($redisServersInfo))
        {
            for($i=0;$i<count($redisServersInfo);)
            {
                $infoKey = $redisServersInfo[$i];
                if(is_array($redisServersInfo[$i]))
                {
                    $servers[] = $this->parseRedisServersInfo($redisServersInfo[$i]);
                    $i++;
                }
                else
                {
                    $servers[$infoKey] = $redisServersInfo[$i+1];
                    $i += 2;
                }
            }
        }
        return $servers;
    }

    /**
     * 连接测试  成功是返回 PONG
     * @return string
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/11
     * @Time: 16:03
     */
    public function ping()
    {
        return $this->sentinel->ping();
    }

    /**
     * 当前哨兵监视所有redis主节点信息
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/11
     * @Time: 15:57
     */
    public function getMasters()
    {
        return $this->parseRedisServersInfo($this->sentinel->rawCommand('SENTINEL','masters'));
    }

    /**
     * 指定分组名称下的主节点信息
     *
     * @param   string  $masterName  分组名称：配置中 monitor 后自定义分组名
     *
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/11
     * @Time: 15:58
     */
    public function getMaster($masterName)
    {
        return $this->parseRedisServersInfo($this->sentinel->rawCommand('SENTINEL','master',$masterName));
    }

    /**
     * 指定分组名称下的从节点信息
     *
     * @param   string  $masterName  分组名称：配置中 monitor 后自定义分组名
     *
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/11
     * @Time: 16:02
     */
    public function getSlaves($masterName)
    {
        return $this->parseRedisServersInfo($this->sentinel->rawCommand('SENTINEL','slaves',$masterName));
    }

    /**
     * Show a list of sentinel instances for this master, and their state.
     *
     * @param $masterName
     * @return mixed
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/11
     * @Time: 16:07
     */
    public function getSentinels($masterName)
    {
        return $this->sentinel->rawCommand('SENTINEL','sentinels',$masterName);
    }

    /**
     * 获取分组主节点地址
     *
     * @param   string  $masterName     主机分组名称
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/10/11
     * @Time: 17:56
     */
    public function getMasterAddrByName($masterName)
    {
        $info = $this->sentinel->rawCommand('SENTINEL','get-master-addr-by-name',$masterName);
        if(!empty($info))
        {
            return [
                'ip' => $info[0],
                'port' => $info[1]
            ];
        }
    }

    /**
     * This command will reset all the masters with matching name.
     * The pattern argument is a glob-style pattern.
     * The reset process clears any previous state in a master
     * (including a failover in progress), and removes every slave
     * and sentinel already discovered and associated with the master.
     *
     * @param string $pattern
     * @return int
     */
    public function reset($pattern)
    {
        return $this->sentinel->rawCommand('SENTINEL', 'reset', $pattern);
    }
    /**
     * Force a failover as if the master was not reachable,
     * and without asking for agreement to other Sentinels
     * (however a new version of the configuration will be published
     * so that the other Sentinels will update their configurations).
     *
     * @param string $master_name
     * @return boolean
     */
    public function failOver($master_name)
    {
        return $this->sentinel->rawCommand('SENTINEL', 'failover', $master_name) === 'OK';
    }
    /**
     * @param string $master_name
     * @return boolean
     */
    public function ckquorum($master_name)
    {
        return $this->checkQuorum($master_name);
    }
    /**
     * Check if the current Sentinel configuration is able to
     * reach the quorum needed to failover a master, and the majority
     * needed to authorize the failover. This command should be
     * used in monitoring systems to check if a Sentinel deployment is ok.
     *
     * @param string $master_name
     * @return boolean
     */
    public function checkQuorum($master_name)
    {
        return $this->sentinel->rawCommand('SENTINEL', 'ckquorum', $master_name);
    }
    /**
     * Force Sentinel to rewrite its configuration on disk,
     * including the current Sentinel state. Normally Sentinel rewrites
     * the configuration every time something changes in its state
     * (in the context of the subset of the state which is persisted on disk across restart).
     * However sometimes it is possible that the configuration file is lost because of
     * operation errors, disk failures, package upgrade scripts or configuration managers.
     * In those cases a way to to force Sentinel to rewrite the configuration file is handy.
     * This command works even if the previous configuration file is completely missing.
     *
     * @return boolean
     */
    public function flushConfig()
    {
        return $this->sentinel->rawCommand('SENTINEL', 'flushconfig');
    }
    /**
     * This command tells the Sentinel to start monitoring a new master with the specified name,
     * ip, port, and quorum. It is identical to the sentinel monitor configuration directive
     * in sentinel.conf configuration file, with the difference that you can't use an hostname in as ip,
     * but you need to provide an IPv4 or IPv6 address.
     *
     * @param $master_name
     * @param $ip
     * @param $port
     * @param $quorum
     * @return boolean
     */
    public function monitor($master_name, $ip, $port, $quorum)
    {
        return $this->sentinel->rawCommand('SENTINEL', 'monitor', $master_name, $ip, $port, $quorum);
    }
    /**
     * is used in order to remove the specified master: the master will no longer be monitored,
     * and will totally be removed from the internal state of the Sentinel,
     * so it will no longer listed by SENTINEL masters and so forth.
     *
     * @param $master_name
     * @return boolean
     */
    public function remove($master_name)
    {
        return $this->sentinel->rawCommand('SENTINEL', 'remove', $master_name);
    }
    /**
     * The SET command is very similar to the CONFIG SET command of Redis,
     * and is used in order to change configuration parameters of a specific master.
     * Multiple option / value pairs can be specified (or none at all).
     * All the configuration parameters that can be configured via sentinel.conf
     * are also configurable using the SET command.
     *
     * @param $master_name
     * @param $option
     * @param $value
     * @return boolean
     */
    public function set($master_name, $option, $value)
    {
        return $this->sentinel->rawCommand('SENTINEL', 'set', $master_name, $option, $value);
    }
    /**
     * get last error
     *
     * @return string
     */
    public function getLastError()
    {
        return $this->sentinel->getLastError();
    }
    /**
     * clear last error
     *
     * @return boolean
     */
    public function clearLastError()
    {
        return $this->sentinel->clearLastError();
    }
    /**
     * sentinel server info
     *
     * @return string
     */
    public function info()
    {
        return $this->sentinel->info();
    }

}