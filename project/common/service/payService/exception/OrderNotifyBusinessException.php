<?php
/**
 * Created by PhpStorm.
 * Author: 姜子龙
 * Date: 2019/3/15
 * Time: 17:23
 */

namespace common\service\payService\exception;


class OrderNotifyBusinessException extends \Exception
{
    public function getName()
    {
        return 'Order Notfiy Business Exception';
    }
}