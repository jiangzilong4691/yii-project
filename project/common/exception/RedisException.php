<?php


namespace common\exception;


class RedisException extends \Exception
{
    public function getName()
    {
        return 'redis exception';
    }
}