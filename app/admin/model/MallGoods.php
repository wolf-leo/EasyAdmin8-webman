<?php

namespace app\admin\model;

use app\model\BaseModel;
use think\model\relation\HasOne;

class MallGoods extends BaseModel
{

    public function cate(): HasOne
    {
        return $this->hasOne(MallCate::class, 'id', 'cate_id')->field('id,title');
    }

}
