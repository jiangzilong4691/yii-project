<?php


namespace common\service\IM\exception;


class InstantMsgingNotFoundException extends InstantMsgingException
{
    public function getName()
    {
        return 'IM Object Not Found';
    }
}