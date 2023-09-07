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
        // 检测安装控制器，正式环境后可删除相关安装逻辑
        if (in_array('InstallController', explode(DIRECTORY_SEPARATOR, $request->controller))) return $handler($request);
        if (!is_file(config_path() . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'lock' . DIRECTORY_SEPARATOR . 'install.lock')) {
            return redirect('/install');
        }
        return $handler($request);
    }
}