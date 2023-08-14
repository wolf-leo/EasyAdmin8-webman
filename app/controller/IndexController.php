<?php

namespace app\controller;

use support\Request;
use support\Response;

class IndexController
{

    public function index(Request $request): Response|string
    {
        // 检测 .env 文件，正式环境后可删除
        if (!is_file(base_path() . DIRECTORY_SEPARATOR . ".env")) return '请配置.env文件';
        return redirect(__url());
    }

}
