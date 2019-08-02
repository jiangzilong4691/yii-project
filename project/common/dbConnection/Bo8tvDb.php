<?php


namespace common\dbConnection;


use common\base\BaseDb;

class Bo8tvDb extends BaseDb
{
    protected function getDb()
    {
        if($this->dbConnection === null)
        {
            $this->dbConnection = \Yii::$app->db;
        }
        return $this->dbConnection;
    }
}