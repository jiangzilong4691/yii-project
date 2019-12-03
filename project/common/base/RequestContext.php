<?php


namespace common\base;


abstract class RequestContext
{

    //字符串 ：转义
    const FILTER_STRING = 'string';

    //整形
    const FILTER_INT = 'int';

    //浮点型
    const FILTER_FLOAT = 'float';

    //过滤后参数
    public $params =[];

    //请求参数
    protected $requestParams = [];

    //请求方法
    protected $requestMethod;

    //错误信息
    private $error;

    //参数规则 e.g. ['method'=>['POST','GET'],'params'=>[['name','string'],['age','int'],['money','float']]]
    abstract protected function rules();

    private function __construct()
    {
        $this->requestParams = $_REQUEST;
        $this->requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->init();
    }

    private function init()
    {
        $this->filterRequestParams();
    }

    /**
     * 根据规则过滤参数
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/11/29
     * @Time: 14:39
     */
    private function filterRequestParams()
    {
        $rules = $this->rules();
        if(is_array($rules) && isset($rules['params']))
        {
            $params = $rules['params'];
            if(is_array($params) && !empty($params))
            {
                foreach ($params as $value)
                {
                    if(is_array($value))
                    {
                        $this->filterParam($value[0],isset($value[1])?$value[1]:'');
                    }
                    else
                    {
                        $this->filterParam($value,self::FILTER_STRING);
                    }
                }
            }
        }
    }

    /**
     *
     * @param   string  $param  参数名
     * @param   string  $filter 过滤规则
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/2
     * @Time: 16:52
     */
    private function filterParam($param,$filter)
    {
        if(!isset($this->requestParams[$param]))
        {
            $this->addParam($param,null);
            return;
        }
        switch ($filter)
        {
            case self::FILTER_STRING:
                $this->addParam($param,addslashes($this->requestParams[$param]));
                break;
            case self::FILTER_INT:
                $this->addParam($param,(int)$this->requestParams[$param]);
                break;
            case self::FILTER_FLOAT:
                $this->addParam($param,(float)$this->requestParams[$param]);
                break;
            default:
                $this->addParam($param,addslashes($this->requestParams[$param]));
        }
    }

    private static $instance = [];

    /**
     * 请求实例
     *
     * @return static
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/11/29
     * @Time: 13:34
     */
    public static function instance()
    {
        $class = get_called_class();
        if(!isset(self::$instance[$class]))
        {
            self::$instance[$class] = new $class();
        }
        return self::$instance[$class];
    }

    /**
     * 添加错误信息
     *
     * @param string|array $msg    错误信息 e.g. '参数错误' or ['code'=>'403','desc'=>'禁止访问']
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/11/29
     * @Time: 13:54
     */
    protected function setError($msg)
    {
        $this->error = $msg;
    }

    /**
     * 获取错误信息
     *
     * @return string
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/11/29
     * @Time: 13:54
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 添加参数
     *
     * @param   string  $param   参数名称
     * @param   mixed   $value   参数值
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/11/29
     * @Time: 13:38
     */
    protected function addParam($param,$value)
    {
        $this->params[$param] = $value;
    }

    /**
     * 获取参数
     *
     * @param   string  $param  参数名
     * @param   mixed   $default    默认值
     *
     * @return mixed|null
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/11/29
     * @Time: 13:42
     */
    protected function getParam($param,$default = null)
    {
        return isset($this->params[$param]) ? $this->params[$param] : $default;
    }

    /**
     * 参数校验
     *
     * @return bool
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/11/29
     * @Time: 13:58
     */
    public function validate()
    {
        $rules = $this->rules();
        if(isset($rules['method']) && !empty($rules['method']))
        {
            $validMethod = $rules['method'];
            if(is_array($validMethod))
            {
                if(!in_array($this->requestMethod,array_map(function ($method){return strtoupper($method);},$validMethod)))
                {
                    $this->setError('非法访问方式，当前只允许 '.implode(',',$validMethod).'方式访问');
                    return false;
                }
            }
            else
            {
                if(strtoupper($validMethod) !== $this->requestMethod)
                {
                    $this->setError("非法访问方式，当前只允许${validMethod}方式访问");
                    return false;
                }
            }
        }
        return true;
    }
}