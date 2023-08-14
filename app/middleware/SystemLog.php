<?php

namespace app\middleware;

use app\common\services\SystemLogService;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * 系统操作日志中间件
 * Class SystemLog
 * @package app\admin\middleware
 */
class SystemLog implements MiddlewareInterface
{

    /**
     * 敏感信息字段，日志记录时需要加密
     * @var array
     */
    protected array $sensitiveParams = [
        'password',
        'password_again',
        'phone',
        'mobile',
    ];

    public function process(Request $request, callable $handler): Response
    {
        if ($request->isAjax()) {
            $params = $request->all();
            if (isset($params['s'])) unset($params['s']);
            foreach ($params as $key => $val) {
                in_array($key, $this->sensitiveParams) && $params[$key] = "***********";
            }
            $method = strtolower($request->method());
            $url    = $request->path();
            if (in_array($method, ['post', 'put', 'delete'])) {
                $ip   = $request->getRealIp(true);
                $data = [
                    'admin_id'    => $request->session()->get('admin.id'),
                    'url'         => $url,
                    'method'      => $method,
                    'ip'          => $ip,
                    'content'     => json_encode($params, JSON_UNESCAPED_UNICODE),
                    'useragent'   => $request->header('HTTP_USER_AGENT'),
                    'create_time' => time(),
                ];
                SystemLogService::instance()->save($data);
            }
        }
        return $handler($request);
    }

}
