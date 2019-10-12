<?php


namespace common\entity;


use common\dbConnection\SportsPhotoDb;

class ImginfoEntity extends SportsPhotoDb
{
    const TABLE_NAME = 'imginfo';

    protected static $LIST_FIELDS = [
        'id',
        'imgname',
        'resourcefile',
        'imgmark',
        'keyword',
        'author'
    ];

    public function getListByIds(Array $ids)
    {
        if(!empty($ids))
        {
            return $this->query('SELECT '.$this->formatFields(self::$LIST_FIELDS)).' FROM '.self::TABLE_NAME.' WHERE id IN('.implode(',',$ids);
        }
        return [];
    }
}