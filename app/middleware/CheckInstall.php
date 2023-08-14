<?php

namespace app\middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class CheckInstall implements MiddlewareInterface
{

    /**
     * @desc 安装检测，正式环境最好删除该中间件
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        // 检测 .env 文件，正式环境后可删除
        if (!is_file(base_path() . DIRECTORY_SEPARATOR . ".env")) return \response('.env文件不存在');

        // 检测安装控制器，正式环境后可删除相关安装逻辑
        if (in_array('InstallController', explode('\\', $request->controller))) return $handler($request);
        if (!is_file(config_path() . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'lock' . DIRECTORY_SEPARATOR . 'install.lock')) {
            return redirect('/install');
        }
        return $handler($request);
    }
}