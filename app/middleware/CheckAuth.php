<?php

namespace app\middleware;

use app\common\traits\JumpTrait;
use app\common\services\AuthService;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class CheckAuth implements MiddlewareInterface
{
    use JumpTrait;

    /**
     * @desc
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        // 检测 .env 文件，正式环境后可删除
        if (!is_file(base_path() . DIRECTORY_SEPARATOR . ".env")) return $this->error('请配置.env文件');

        $adminId         = session('admin.id', 0);
        $adminConfig     = config('admin');
        $controllerClass = explode(DIRECTORY_SEPARATOR, $request->controller);
        $controller      = strtolower(str_replace('Controller', '', array_pop($controllerClass)));
        $action          = $request->action ?? 'index';
        if ($controller == 'login') {
            if ($request->method() == 'GET' && !empty($adminId) && $action != 'out') {
                return redirect(__url());
            }
        }
        if (!in_array($controller, $adminConfig['no_login_controller'])) {
            $expireTime = session('admin.expire_time');
            if (empty($adminId)) {
                return $this->responseView('请先登录后台', [], __url("/login"));
            }
            // 判断是否登录过期
            if ($expireTime !== true && time() > $expireTime) {
                $request->session()->forget('admin');
                return $this->responseView('登录已过期，请重新登录', [], __url("/login"));
            }
        }
        // 验证权限
        if ($adminId) {
            $authService = new AuthService($adminId);
            $currentNode = $authService->getCurrentNode();
            if (!in_array($controller, $adminConfig['no_auth_controller']) && !in_array($controller, $adminConfig['no_auth_node'])) {
                $check = $authService->checkNode($currentNode);
                if (!$check) return (request()->ajax() || request()->method() == 'POST') ? $this->error('无权限访问') : $this->responseView('无权限访问');
                // 判断是否为演示环境
                if (env('EASYADMIN.IS_DEMO', false) && \request()->method() == 'POST') {
                    return (request()->ajax() || request()->method() == 'POST') ? $this->error('演示环境下不允许修改') : $this->responseView('无权限访问');
                }
            }
        }
        return $handler($request);
    }
}
