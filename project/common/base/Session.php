<?php


namespace common\base;

use yii\redis\Session;

class RedisSession extends Session
{
    /**
     * session 有效时间
     * @var
     */
    protected $timeout = null;

    /**
     * session 获取超时时间
     *
     * @return int
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/25
     * @Time: 10:17
     */
    public function getTimeout()
    {
        if($this->timeOut == null)
        {
            return parent::getTimeout();
        }
        return $this->timeout;
    }

    /**
     * session 自定义超时时间
     *
     * @param int $value
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/25
     * @Time: 10:17
     */
    public function setTimeout($value)
    {
        if(is_int($value) && $value>0)
        {
            $this->timeout = $value;
        }
    }
}