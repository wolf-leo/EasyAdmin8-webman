<?php

namespace app\model;

use Illuminate\Database\Eloquent\Model;
use support\Db;

class BaseModel extends Model
{
    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = "";

    /**
     * 指示模型是否主动维护时间戳。
     *
     * @var bool
     */
    public $timestamps = false;

    protected $casts = [
        'create_time' => 'datetime:Y-m-d H:i:s',
        'update_time' => 'datetime:Y-m-d H:i:s',
        'delete_time' => 'datetime:Y-m-d H:i:s',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $calledClass = get_called_class();
        $className   = substr(strrchr($calledClass, '\\'), 1);
        $this->table = $this->getTableName($className);
    }

    /**
     * @param string $className
     * @return string
     */
    public function getTableName(string $className): string
    {
        return parse_name($className);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function addAll(array $data = []): bool
    {
        return Db::table($this->getTable())->insert($data);
    }

}
