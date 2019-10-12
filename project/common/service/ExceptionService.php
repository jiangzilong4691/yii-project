<?php


namespace common\service;


use common\base\BaseService;
use common\entity\ExceptionErrorLogEntity;

class ExceptionService extends BaseService
{

    public function __construct()
    {

    }

    /**
     * 网站系统异常入库
     * @param $exception \Exception
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/25
     * @Time: 20:39
     */
    public function recordException($exception)
    {
        $appId = \Yii::$app->id;
        $serverInfo = serialize($_SERVER);
        $result = ExceptionErrorLogEntity::model()->recordError($exception->getCode(),$exception->getFile(),$exception->getLine(),$exception->getMessage(),$exception->getTraceAsString(),$appId,$serverInfo);
        if($result)
        {
//            MailService::instance()->send('jiangzilong@zhibo.tv','出错啦','你看这错出的','直播TV哦');
            try{
                MailService::instance()->tplSend('jiangzilong@zhibo.tv','网站错误报告【'.date('Y-m-d H:i:s').'】','error',[
                    'file' => $exception->getFile(),
                    'code' => $exception->getCode(),
                    'line' => $exception->getLine(),
                    'id' => $appId,
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString()
                ],'中国体育');
            }catch (\Exception $exception){
                ExceptionErrorLogEntity::model()->recordError($exception->getCode(),$exception->getFile(),$exception->getLine(),$exception->getMessage(),$exception->getTraceAsString(),$appId,$serverInfo);
            }
        }
    }
}