<?php

namespace common\controller;

use common\traits\Curd;
use common\traits\JumpTrait;
use Shopwwi\LaravelCache\Cache;
use support\Response;
use support\View;

class AdminController
{
    use JumpTrait;
    use Curd;

    /**
     * 是否为演示环境
     * @var bool
     */
    protected bool $isDemo = false;

    /**
     * @Model
     */
    protected object $model;

    /**
     * @var string
     */
    public string $order = 'id';

    /**
     * 不导出的字段信息
     * @var array
     */
    protected array $noExportFields = ['delete_time', 'update_time'];

    /**
     * @var string
     */
    public string $secondary = '';

    /**
     * @var string
     */
    public string $controller = '';

    /**
     * @var string
     */
    public string $action = '';

    /**
     * @var array
     */
    public array $adminConfig = [];

    public function __construct()
    {
        $this->initialize();
    }

    protected function initialize()
    {
        $request              = \request();
        $this->adminConfig    = $adminConfig = config('admin', []);
        $this->isDemo         = env('EASYADMIN.IS_DEMO', false);
        $controllerClass      = explode('\\', $request->controller);
        $controller           = strtolower(str_replace('Controller', '', array_pop($controllerClass)));
        $_lastCtr             = array_pop($controllerClass);
        $secondary            = $_lastCtr == 'controller' ? '' : $_lastCtr;
        $action               = $request->action ?? 'index';
        $this->secondary      = $secondary;
        $this->controller     = $controller;
        $this->action         = $action;
        $jsBasePath           = ($secondary ? "{$secondary}/" : '') . strtolower($controller);
        $thisControllerJsPath = "admin/js/{$jsBasePath}.js";
        $autoloadJs           = file_exists($thisControllerJsPath);
        $adminModuleName      = $adminConfig['admin_alias_name'];
        $isSuperAdmin         = session('admin.id') == $adminConfig['super_admin_id'];
        $version              = Cache::get('version');
        if (empty($version)) {
            $version = sysconfig('site', 'site_version');
            Cache::put('site_version', $version);
            Cache::put('version', $version, 3600);
        }
        $data = [
            'adminModuleName'      => $adminModuleName,
            'thisController'       => $controller,
            'thisAction'           => $action,
            'thisRequest'          => "{$adminModuleName}/{$controller}/{$action}",
            'thisControllerJsPath' => $thisControllerJsPath,
            'autoloadJs'           => $autoloadJs,
            'isSuperAdmin'         => $isSuperAdmin,
            'isDemo'               => $this->isDemo,
            'version'              => env('APP_DEBUG') ? time() : $version,
        ];
        $this->assign($data);
    }

    /**
     * @param array $args
     */
    public function assign(array $args = [])
    {
        View::assign($args);
    }

    public function fetch(string $template = '', array $args = []): Response
    {
        if (empty($template)) {
            $basePath = DIRECTORY_SEPARATOR . $this->controller . DIRECTORY_SEPARATOR . $this->action;
            if ($this->secondary) {
                $template = 'admin' . DIRECTORY_SEPARATOR . $this->secondary . $basePath;
            } else {
                $template = 'admin' . $basePath;
            }
        }
        return view($template, $args);
    }

    /**
     * 构建请求参数
     * @param array $excludeFields 忽略构建搜索的字段
     * @return array
     */
    protected function buildTableParams(array $excludeFields = []): array
    {
        $get     = request()->all();
        $page    = !empty($get['page']) ? $get['page'] : 1;
        $limit   = !empty($get['limit']) ? $get['limit'] : 15;
        $filters = !empty($get['filter']) ? htmlspecialchars_decode($get['filter']) : '{}';
        $ops     = !empty($get['op']) ? htmlspecialchars_decode($get['op']) : '{}';
        // json转数组
        $filters  = json_decode($filters, true);
        $ops      = json_decode($ops, true);
        $where    = [];
        $excludes = [];

        foreach ($filters as $key => $val) {
            if (in_array($key, $excludeFields)) {
                $excludes[$key] = $val;
                continue;
            }
            $op = isset($ops[$key]) && !empty($ops[$key]) ? $ops[$key] : '%*%';

            switch (strtolower($op)) {
                case '=':
                    $where[] = [$key, '=', $val];
                    break;
                case '%*%':
                    $where[] = [$key, 'LIKE', "%{$val}%"];
                    break;
                case '*%':
                    $where[] = [$key, 'LIKE', "{$val}%"];
                    break;
                case '%*':
                    $where[] = [$key, 'LIKE', "%{$val}"];
                    break;
                case 'range':
                    [$beginTime, $endTime] = explode(' - ', $val);
                    $where[] = [$key, '>=', strtotime($beginTime)];
                    $where[] = [$key, '<=', strtotime($endTime)];
                    break;
                default:
                    $where[] = [$key, $op, "%{$val}"];
            }
        }
        return [$page, $limit, $where, $excludes];
    }

    /**
     * 下拉选择列表
     * @return Response
     */
    public function selectList(): Response
    {
        $fields = request()->input('selectFields');
        $data   = $this->model->select(explode(',', $fields))->get()->toArray();
        return $this->success('', $data);
    }

}
