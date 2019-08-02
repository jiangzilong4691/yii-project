<?php
namespace common\exception;

use common\service\ExceptionService;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ExceptionController extends Controller
{
    public $enableCsrfValidation = false;

    protected $msgToClient = '服务器内部错误';

    /**
     * 异常记录
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/25
     * @Time: 21:19
     */
    public function beforeAction($action)
    {
        $exception = \Yii::$app->getErrorHandler()->exception;
        //暂时只排除 404
        /*if(!$exception instanceof NotFoundHttpException)
        {
            ExceptionService::instance()->recordException($exception);
        }*/
        ExceptionService::instance()->recordException($exception);
        if(YII_DEBUG)
        {
            $this->msgToClient = $exception->getMessage();
        }
        return parent::beforeAction($action);
    }

    /**
     * 错误处理
     * @return string
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/25
     * @Time: 21:19
     */
    public function actionError()
    {
        if(\Yii::$app->request->isAjax)
        {
            return $this->msgToClient;
        }
        exit($this->render('@common/views/error'));
    }
}