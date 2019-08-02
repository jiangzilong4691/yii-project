<?php


namespace common\dbConnection;


use common\base\BaseDb;

class PollDb extends BaseDb
{
    protected function getDb()
    {
        if($this->dbConnection === null)
        {
            $this->dbConnection = \Yii::$app->pollDb;
        }
        return $this->dbConnection;
    }
}