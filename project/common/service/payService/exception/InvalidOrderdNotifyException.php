<?php
/**
 * Created by PhpStorm.
 * Author: 姜子龙
 * Date: 2019/3/15
 * Time: 16:19
 */

namespace common\service\payService\exception;


class InvalidOrderdNotifyException extends \Exception
{
    public function getName()
    {
        return 'Invalid Order Pay Notify';
    }
}