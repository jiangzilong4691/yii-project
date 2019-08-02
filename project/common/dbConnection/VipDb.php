<?php


namespace common\dbConnection;


use common\base\BaseDb;

class VipDb extends BaseDb
{
    protected function getDb()
    {
        if($this->dbConnection === null)
        {
            $this->dbConnection = \Yii::$app->vipDb;
        }
        return $this->dbConnection;
    }
}