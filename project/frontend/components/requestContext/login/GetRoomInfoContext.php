<?php


namespace frontend\components\requestContext\login;


use common\base\RequestContext;

class GetRoomInfoContext extends RequestContext
{
    protected function rules()
    {
        return [
            'method' =>'POST',
            'params' => [
                ['name','string'],
                ['age','int'],
                ['gender','int'],
                ['country','string'],
            ]
        ];
    }
    
    public function validate()
    {
        if(parent::validate())
        {
            //TODO 当前接口参数校验
            return true;
        }
        return false;
    }
}