<?php


namespace common\base;


use common\components\helpers\ComHelper;
use yii\data\Pagination;

abstract class BaseDb
{

    protected $dbConnection;

    /**
     * 选取数据库 子类实现
     * @return mixed
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/24
     * @Time: 16:55
     */
    abstract protected function getDb();

    /**
     * @var array
     * @author 姜海强 <jianghaiqiang@zhibo.tv>
     */
    private $_specialMark=[
        '`',
        '{'
    ];

    /**格式化字段或表名称
     * @param string    $key   字段或表名
     * @return string
     * @author 姜海强 <jianghaiqiang@zhibo.tv>
     */
    public function formatMysqlKey($key)
    {
        $needle=substr($key,0,1);
        if(in_array($needle,$this->_specialMark))
        {
            return $key;
        }
        return '`'.$key.'`';
    }

    /**写操作
     * @param string $sql       SQL语句
     * @param array  $params    参数
     *
     * @return int
     */
    protected function execute($sql,$params=[])
    {
        return $this->getDb()->createCommand($sql,$params)->execute();
    }

    /**向表中插入数据，插入成功返回插入ID，失败返回false
     * @param string $table             表名
     * @param array  $colums      键值对，键为字段名称，值为字段值
     * @return int|bool
     */
    protected function insert($table,array $colums)
    {
        $fields=[];
        $params=[];
        foreach ($colums as $k=>$v)
        {
            array_push($fields,'`'.$k.'`');
            $params[':'.$k]=$v;
        }
        $sql='INSERT INTO '.$this->formatMysqlKey($table).'('.implode(',',$fields).')VALUES('.implode(',',array_keys($params)).')';
        $conn=$this->getDb();
        $res=$conn->createCommand($sql,$params)->execute();
        unset($fields,$params);
        if($res)
        {
            return $conn->lastInsertID;
        }
        return false;
    }

    /**从库查询
     * @param string $sql       SQL语句
     * @param array  $params    参数
     * @return array
     */
    private function slaveQuery($sql,$params=[])
    {
        return $this->getDb()->createCommand($sql,$params)->queryAll();
    }

    private function slaveQueryOne($sql,$params=[])
    {
        return $this->getDb()->createCommand($sql, $params)->queryOne();
    }

    /**主库查询
     * @param string  $sql          SQL语句
     * @param array   $params       参数
     * @return array
     */
    private function masterQuery($sql,$params=[])
    {
        return $this->getDb()->useMaster(function ($db) use($sql,$params){
            return $db->createCommand($sql,$params)->queryAll();
        });
    }

    private function masterQueryOne($sql,$params=[])
    {
        return $this->getDb()->useMaster(function ($db) use($sql,$params){
            return $db->createCommand($sql,$params)->queryOne();
        });
    }

    /**
     *  查询单条记录
     * @param $sql
     * @param array $params
     * @param bool $useMaster
     * @return array|false|mixed
     * @author 王浩
     */
    protected function querySingle($sql,$params=[],$useMaster=false)
    {
        return $useMaster?$this->masterQueryOne($sql,$params):$this->slaveQueryOne($sql,$params);
    }

    /**查询
     * @param string $sql            SQL语句
     * @param array  $params         参数
     * @param bool   $useMaster      是否用主库查询
     * @return array
     */
    protected function query($sql,$params=[],$useMaster=false)
    {
        return $useMaster?$this->masterQuery($sql,$params):$this->slaveQuery($sql,$params);
    }

    /**
     * 查询一个结果
     * @param string $sql       SQL语句
     * @param array $params     参数
     * @param bool $userMaster  是否用主库查询
     * @return array
     * @author 高玉龙
     */
    protected function queryOne($sql,$params=[],$userMaster=false)
    {
        $result=$this->query($sql.' LIMIT 1',$params,$userMaster);
        if(!empty($result))
        {
            return $result[0];
        }
        return $result;
    }

    /**格式化字段信息为驼峰
     * @param array $fields   一维字段['zhibo_id','insert_time']
     * @return string
     * @author 姜海强 <jianghaiqiang@zhibo.tv>
     */
    protected function formatFields(array $fields)
    {
        return ComHelper::formatFields($fields);
    }

    /**得到分页数据
     * @param    string   $countSql    得到数量sql
     * @param    string   $listSql     得到列表sql
     * @param    array    $params      参数
     * @param    bool     $useMaster      是否用主库查询
     *
     * @return array
     * @author 姜海强 <jianghaiqiang@zhibo.tv>
     */
    public function getPageData($countSql,$listSql,$params=[],$pageSize=12,$useMaster=false)
    {
        $count=$this->query($countSql,$params,$useMaster);

        if(!empty($count))
        {
            $count=$count[0]['num'];
        }

        $pagination = new Pagination([
            'totalCount' => $count,
            'defaultPageSize'=>$pageSize
        ]);
        $list=[];
        if(!empty($count))
        {
            $list=$this->query(
                $listSql.' LIMIT '.$pagination->offset.','.$pagination->limit,
                $params,
                $useMaster
            );
        }

        return [
            'list'=>$list,
            'pages'=>$pagination
        ];
    }

    /**
     * model池
     * @var array
     */
    protected static $modelPool = [];

    /**
     * 获取表model
     * @return static
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/30
     * @Time: 14:03
     */
    public static function model()
    {
        $class = get_called_class();
        if(!isset(self::$modelPool[$class]))
        {
            self::$modelPool[$class] = new static();
        }
        return self::$modelPool[$class];
    }
}