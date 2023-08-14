<?php

namespace app\admin\model;

use app\model\BaseModel;

class SystemAdmin extends BaseModel
{

    public function getAuthList(): array
    {
        $list = SystemAuth::where('status', 1)->select(['id', 'title'])->get()->toArray();
        return collect($list)->pluck('title', 'id')->toArray();
    }
}
