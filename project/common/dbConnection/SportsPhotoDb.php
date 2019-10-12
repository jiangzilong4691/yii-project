<?php


namespace common\dbConnection;


use common\base\BaseDb;

class SportsPhotoDb extends BaseDb
{
    protected function getDb()
    {
        if($this->dbConnection === null)
        {
            $this->dbConnection = \Yii::$app->sportsPhotoDb;
        }
        return $this->dbConnection;
    }
}