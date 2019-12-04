<?php


namespace common\components\helpers;


class CurlUploadHelper
{

    /**
     * 实例静态存储
     * @var
     */
    private static $instancePool;

    //远程上传校验
    private $signKey = 'Z2mSaeW5HiZPPOs9';

    //远程上传目录
    private $uploadDir;

    //上传的文件信息
    private $fileInfo;

    //文件大小
    private $maxSize;

    //合法文件扩展名
    private $legalExt = ['jpeg','jpg','png','gif','apk','gif','zip','swf'];

    //上传错误信息
    private $uploadErrMsgMap = [
        UPLOAD_ERR_OK => '上传操作正常',
        UPLOAD_ERR_INI_SIZE => '文件大小超过服务器限制',
        UPLOAD_ERR_FORM_SIZE => '文件大小超过客户端限制',
        UPLOAD_ERR_PARTIAL => '文件上传不完整',
        UPLOAD_ERR_NO_FILE => '文件不能为空',
        UPLOAD_ERR_NO_TMP_DIR => '上传配置未指定临时目录',
        UPLOAD_ERR_CANT_WRITE => '写入磁盘失败',
        UPLOAD_ERR_EXTENSION => '文件上传扩展没有打开'
    ];

    /**
     * 单例
     *
     * @param $name
     *
     * @return CurlUploadHelper
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/5
     * @Time: 16:14
     */
    public static function instance($name,$dir)
    {
        $key = md5($name.$dir);
        if(!isset(self::$instancePool[$key]))
        {
            self::$instancePool[] = new self($name,$dir);
        }
        return self::$instancePool[$key];
    }

    private function __construct($name,$dir)
    {
        if(isset($_FILES[$name]))
        {
            $this->fileInfo = $_FILES[$name];
        }
        $this->uploadDir = $dir;
    }

    /**
     * 远程服务器上传接口
     *
     * @return string
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/5
     * @Time: 21:44
     */
    private function getUploadUrl()
    {
        return UPLOAD_URL.'/cross-up/upload';
    }

    /**
     * 上传文件错误信息检测
     *
     * @throws \Exception
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/5
     * @Time: 21:26
     */
    private function checkUploadedFile()
    {
        //上传文件检测
        if($this->fileInfo === null)
        {
            throw new \Exception('未获取到上传文件');
        }

        //上传状态检测
        if($this->fileInfo['error'] !== UPLOAD_ERR_OK)
        {
            $msg = isset($this->uploadErrMsgMap[$this->fileInfo['error']])?$this->uploadErrMsgMap[$this->fileInfo['error']]:'未知上传错误';
            throw new \Exception($msg);
        }

        //上传文件扩展检测
        $ext = strtolower(pathinfo($this->fileInfo['name'],PATHINFO_EXTENSION));
        if(!in_array($ext,$this->legalExt))
        {
            throw new \Exception('上传文件格式不符合要求');
        }

        //上传文件大小检测
        if($this->maxSize !== null)
        {
            $fileSize = $this->fileInfo['size'];
            if($fileSize > $this->maxSize)
            {
                throw new \Exception('上传文件过大，文件大小不得大于'.($this->maxSize/1024).'M');
            }
        }

        //上传目录设置检测
        if($this->uploadDir === null)
        {
            throw new \Exception('上传目标目录不能为空');
        }

    }

    /**
     * 设置上传要求信息
     *
     * @param   array   $uploadInfo     配置信息 e.g.: ['maxSize'=>1024,'']
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/5
     * @Time: 17:57
     */
    public function setUploadInfo($uploadInfo)
    {
        foreach ($uploadInfo as $key=>$value)
        {
            if(property_exists($this,$key))
            {
                $this->$key = $value;
            }
        }
    }

    /**
     * 上传对外接口 单一文件上传
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/5
     * @Time: 21:53
     */
    public function upload()
    {
        try{
            $this->checkUploadedFile();

            $info = $this->uploadRun();
            if($info !== false)
            {
                $info = json_decode($info,true);
                if(json_last_error() == JSON_ERROR_NONE)
                {
                    if($info['status']=='200')
                    {
                        return [
                            'code' => '200',
                            'msg' => 'success',
                            'data' => $info['data']
                        ];
                    }
                    else
                    {
                        return [
                            'code' => '-2',
                            'msg' => $info['msg'],
                            'data' => []
                        ];
                    }
                }
                throw new \Exception('返回数据json解析错误：'.json_last_error_msg());
            }
            throw new \Exception('curl 错误');
        }catch (\Exception $e){
//            var_dump($e->getMessage());die;
            return [
                'code' => '-1',
                'msg' => '上传服务器内部错误',
                'data' =>[]
            ];
        }
    }

    /**
     * 远程上传
     *
     * @return bool|string
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/8/5
     * @Time: 21:46
     */
    private function uploadRun()
    {
        $file = new \CURLFile($this->fileInfo['tmp_name'],$this->fileInfo['type'],$this->fileInfo['name']);
        $t = time();
        $data = [
            'sign' => md5($this->signKey.$t.$this->uploadDir),
            'dir' => $this->uploadDir,
            't' => $t,
            'cfile' => $file
        ];
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$this->getUploadUrl());
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }
}