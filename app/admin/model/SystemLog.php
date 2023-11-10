<?php

namespace app\admin\model;

use app\common\services\SystemLogService;
use app\model\BaseModel;
use think\model\relation\HasOne;

class SystemLog extends BaseModel
{

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->name = 'system_log_' . date('Ym');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(SystemAdmin::class, 'id', 'admin_id')->field('id,username');
    }

    public function setMonth($month): static
    {
        SystemLogService::instance()->detectTable();
        $this->name = 'system_log_' . $month;
        return $this;
    }
}
