<?php


namespace common\service\IM\exception;


class InstantMsgingException extends \Exception
{
    public function getName()
    {
        return 'IM exception';
    }
}