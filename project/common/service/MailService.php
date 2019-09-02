<?php


namespace common\service;


use common\base\BaseService;

class MailService extends BaseService
{

    /**
     * @var \yii\mail\MailerInterface
     */
    private $_mailer;

    /**
     * 自定义模板路径
     * @var
     */
    private $customViewPath;

    /**
     * 自定义布局
     * @var
     */
    private $customLayout;

    /**
     * 自定义邮件发送方
     * @var
     */
    private $customMailerFrom = [];

    /**
     * 内置模板路径
     * @var string
     */
    private $viewPath = '@common/mail/template';

    public function __construct()
    {
        $this->_mailer = \Yii::$app->mailer;
        $this->_initMailer();
    }

    //发送账号
    private $sendFrom;

    //默认发送方名称
    private $senderName = '中国体育';

    /**
     * 发送邮件账号 应走后台配置
     * @var array
     */
    private $_mailerFrom = [

    ];

    /**
     * SMTP 参数
     * @var array
     */
    private $_transport = [
        'class' => 'Swift_SmtpTransport',
        'host' => '',
        'username' => '',
        'password' => '',
        'port' => '465',
        'encryption' => 'ssl'
    ];

    /**
     * 随机选取发送账号
     * @return mixed
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/31
     * @Time: 16:49
     */
    private function getRandMailerFrom()
    {
        shuffle($this->_mailerFrom);
        $sendFrom = $this->_mailerFrom[0];
        $this->sendFrom = $sendFrom;
        return $sendFrom;
    }

    /**
     * transport
     * @return array
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/31
     * @Time: 18:24
     */
    private function _getTransport()
    {
        $this->_transport['username'] = $this->getRandMailerFrom();
        return $this->_transport;
    }

    /**
     * 初始化参数
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/31
     * @Time: 16:46
     */
    private function _initMailer()
    {
        //设置true将不会发送邮件而是保存邮件信息到文件
        $this->_mailer->useFileTransport = false;
        //默认模板路径
        $this->_mailer->viewPath = $this->viewPath;
        //默认关闭模板布局
        $this->_mailer->htmlLayout = false;
        $this->_mailer->transport = $this->_getTransport();
        $this->_mailer->messageConfig = [
            'charset' => 'UTF-8'
        ];
    }

    /**
     * 自定义参数信息
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/1
     * @Time: 16:02
     */
    private function _setCustomParam()
    {
        //自定义模板路径
        if($this->customViewPath !== null)
        {
            $this->_mailer->viewPath = $this->customViewPath;
        }
        //设置模板布局
        if($this->customLayout !== null)
        {
            $this->_mailer->htmlLayout = $this->customLayout;
        }

        //设置自定义发送方
        if(is_array($this->customMailerFrom) && !empty($this->customMailerFrom))
        {
            $this->_mailerFrom = $this->customMailerFrom;
            $this->_mailer->transport = $this->_getTransport();
        }
    }

    /**
     * 客户端自定义参数
     * @param array $data   自定义参数数组 e.g.: ['view'=>'视图','viewParams'=>['name'=>'测试','purpose'=>'玩呀']]
     * @return $this
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/31
     * @Time: 15:55
     */
    public function setCustomParams(Array $data)
    {
        if(!empty($data))
        {
            foreach ($data as $key=>$val)
            {
                if(property_exists($this,$key))
                {
                    $this->$key = $val;
                }
            }
        }
        $this->_setCustomParam();
        return $this;
    }

    /**
     * 非模板 单条邮件发送
     * @param   string      $mailTo     收件人地址
     * @param   string      $subject    邮件标题
     * @param   string      $body       邮件内容
     * @param   string      $sender     发送方名称
     * @return bool
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/1
     * @Time: 16:12
     */
    public function send($mailTo,$subject,$body,$sender='')
    {
        return $this->_mailer->compose()
            ->setFrom([$this->sendFrom=>empty($sender)? $this->senderName:$sender])
            ->setTo($mailTo)
            ->setSubject($subject)
            ->setHtmlBody($body)
            ->send();
    }

    /**
     * 非模板 多邮件发送
     * @param array     $mailTo     接收方地址数组 e.g.: ['jiangzilong@zhibo.tv','liutiesuo@zhibo.tv']
     * @param string    $subject    邮件标题
     * @param string    $body       邮件内容
     * @param string    $sender     发送方名称
     * @return bool|int
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/1
     * @Time: 16:21
     */
    public function multiSend(Array $mailTos,$subject,$body,$sender='')
    {
        if(is_array($mailTos) && !empty($mailTos))
        {
            $messages = [];
            foreach ($mailTos as $receiver)
            {
                $messages[] = $this->_mailer->compose()
                                ->setFrom([$this->sendFrom=>empty($sender)?$this->senderName:$sender])
                                ->setTo($receiver)
                                ->setSubject($subject)
                                ->setHtmlBody($body);
            }
            return $this->_mailer->sendMultiple($messages);
        }
        return false;
    }

    /**
     * 模板   单条邮件发送
     * @param   string      $mailTo     邮件接收方
     * @param   string      $subject    邮件标题
     * @param   string      $tplName    模板名称 必须是在模板路径下的不带扩展名的文件名称 e.g.: error
     * @param   array       $tplParams  模板需要的参数
     * @param   string      $sender     发送方名称
     * @return bool
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/1
     * @Time: 16:30
     */
    public function tplSend($mailTo,$subject,$tplName,Array $tplParams=[],$sender='')
    {
        return $this->_mailer->compose($tplName,$tplParams)
                            ->setFrom([$this->sendFrom=>empty($sender)?$this->senderName:$sender])
                            ->setTo($mailTo)
                            ->setSubject($subject)
                            ->send();
    }

    /**
     * 模板   多条邮件发送
     * @param array         $mailTos     邮件接收方
     * @param   string      $subject    邮件标题
     * @param   string      $tplName    模板名称 必须是在模板路径下的不带扩展名的文件名称 e.g.: error
     * @param   array       $tplParams  模板需要的参数
     * @param   string      $sender     发送方名称
     * @return bool|int
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/1
     * @Time: 16:41
     */
    public function tplMultiSend(Array $mailTos,$subject,$tplName,Array $tplParams=[],$sender='')
    {
        if(is_array($mailTos) && !empty($mailTos))
        {
            $messages = [];
            foreach ($mailTos as $receiver)
            {
                $messages[] = $this->_mailer->compose($tplName,$tplParams)
                                ->setFrom([$this->sendFrom=>empty($sender)?$this->senderName:$sender])
                                ->setTo($receiver)
                                ->setSubject($subject);
            }
            return $this->_mailer->sendMultiple($messages);
        }
        return false;
    }
}