<?php


namespace common\entity;


use common\dbConnection\PollDb;

class ExceptionErrorLogEntity extends PollDb
{
    const TABLE_NAME = '{{%exception_error_log}}';

    /**
     * 记录错误日志
     * @param   int     $code       报错码
     * @param   string  $file       报错文件
     * @param   int     $line       报错行号
     * @param   string  $message    报错信息
     * @param   string  $traceAsString  错误追踪
     * @param   string  $appId      所属应用
     * @param   string  $serverInfo 服务器信息
     * @return int
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/7/25
     * @Time: 20:26
     */
    public function recordError($code,$file,$line,$message,$traceAsString,$appId,$serverInfo)
    {
        $insertSql = 'INSERT INTO ' . self::TABLE_NAME . ' (`code`,`file`,`line`,`message`,`trace_as_string`,`app_id`,`other_data`)VALUES(:CODE, :FILE, :LINE, :MESSAGE, :TRACE_AS_STRING, :APP_ID, :OTHER_DATA)';
        $params = [
            ':CODE' => $code,
            ':FILE' => $file,
            ':LINE' => $line,
            ':MESSAGE' => $message,
            ':TRACE_AS_STRING' => $traceAsString,
            ':APP_ID' => $appId,
            ':OTHER_DATA' => $serverInfo
        ];
        return $this->execute($insertSql, $params);
    }
}