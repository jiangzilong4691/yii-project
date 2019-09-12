<?php
/**
 * Created by PhpStorm.
 * User: seca
 * Date: 2019/3/13
 * Time: 14:34
 */

namespace common\service\payService\exception;


class InvalidBusinessExcetion extends \Exception
{
    public function getName()
    {
        return 'Invalid Business';
    }
}