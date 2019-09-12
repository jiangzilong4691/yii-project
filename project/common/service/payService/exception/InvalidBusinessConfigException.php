<?php
/**
 * Created by PhpStorm.
 * User: seca
 * Date: 2019/3/13
 * Time: 14:42
 */

namespace common\service\payService\exception;


class InvalidBusinessConfigException extends \Exception
{
    public function getName()
    {
        return 'Invalid Business Configuration';
    }
}