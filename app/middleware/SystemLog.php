<?php

namespace app\middleware;

use app\common\services\annotation\ControllerAnnotation;
use app\common\services\annotation\NodeAnnotation;
use app\common\services\SystemLogService;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
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
        $response = $handler($request);
        if ($request->isAjax()) {
            $params = $request->all();
            if (isset($params['s'])) unset($params['s']);
            foreach ($params as $key => $val) {
                in_array($key, $this->sensitiveParams) && $params[$key] = "***********";
            }
            $method = strtolower($request->method());
            $url    = $request->path();
            if (in_array($method, ['post', 'put', 'delete'])) {
                $title = '';
                try {
                    $pathInfoExp = explode('/', $url);
                    $_controller = $pathInfoExp[2] ?? '';
                    $_action     = ucfirst($pathInfoExp[3] ?? '');
                    if ($_controller && $_action) {
                        $className       = "app\admin\controller\\{$_controller}\\{$_action}Controller";
                        $reflectionClass = new \ReflectionClass($className);
                        $parser          = new DocParser();
                        $parser->setIgnoreNotImportedAnnotations(true);
                        $reader               = new AnnotationReader($parser);
                        $controllerAnnotation = $reader->getClassAnnotation($reflectionClass, ControllerAnnotation::class);
                        $reflectionAction     = $reflectionClass->getMethod(end($pathInfoExp) ?? '');
                        $nodeAnnotation       = $reader->getMethodAnnotation($reflectionAction, NodeAnnotation::class);
                        $title                = $controllerAnnotation->title . ' - ' . $nodeAnnotation->title;
                    }
                }catch (\Throwable $exception) {
                }
                $ip = $request->getRealIp(true);
                // 限制记录的响应内容，避免过大
                $_response = $response->rawBody();
                $_response = mb_substr($_response, 0, 3000, 'utf-8');
                $data      = [
                    'admin_id'    => session('admin.id'),
                    'title'       => $title,
                    'url'         => $url,
                    'method'      => $method,
                    'ip'          => $ip,
                    'content'     => json_encode($params, JSON_UNESCAPED_UNICODE),
                    'response'    => $_response,
                    'useragent'   => $request->header('user-agent'),
                    'create_time' => time(),
                ];
                SystemLogService::instance()->setTableName()->save($data);
            }
        }
        return $response;
    }

}
