<?php

namespace app\admin\model;

use app\model\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MallGoods extends BaseModel
{

    public function cate(): HasOne
    {
        return $this->hasOne(MallCate::class, 'id', 'cate_id')->select('id', 'title');
    }

}
