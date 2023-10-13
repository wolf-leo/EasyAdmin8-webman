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
        if (in_array('InstallController', explode('\\', $request->controller))) return $handler($request);
        if (!is_file(config_path() . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'lock' . DIRECTORY_SEPARATOR . 'install.lock')) {
            if (config('admin.admin_domain_status')) {
                $html = <<<EOF
<div style="padding-top: 12%;text-align: center;font-size: 1.8rem">
请先将 .env 中的 EASYADMIN.ADMIN_DOMAIN_STATUS 值设置为 false<br><br>使用默认 admin 地址访问完成系统安装后再进行后台域名配置
</div>
EOF;
                return \response($html);
            }
            return redirect('/install');
        }
        return $handler($request);
    }
}