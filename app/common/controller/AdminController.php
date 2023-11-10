<?php

namespace app\common\controller;

use app\common\traits\Curd;
use app\common\traits\JumpTrait;
use think\facade\Cache;
use support\Response;
use support\View;
use think\Exception;
use think\Validate;

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
     * @var array
     */
    public array $order = [
        'id' => 'desc',
    ];

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
        $adminModuleName      = $adminConfig['admin_domain_status'] ? '' : $adminConfig['admin_alias_name'];
        $isSuperAdmin         = session('admin.id') == $adminConfig['super_admin_id'];
        $version              = Cache::get('version');
        if (empty($version)) {
            $version = sysconfig('site', 'site_version');
            Cache::set('site_version', $version);
            Cache::set('version', $version, 3600);
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
    public function assign(array $args = []): void
    {
        View::assign($args);
    }

    public function fetch(string $template = '', array $args = []): Response
    {
        if (empty($template)) {
            $basePath = $this->controller . DIRECTORY_SEPARATOR . $this->action;
            if ($this->secondary) {
                $template = $this->secondary . DIRECTORY_SEPARATOR . $basePath;
            } else {
                $template = $basePath;
            }
        }
        return view($template, $args);
    }

    /**
     * 重写验证规则
     * @param array $data
     * @param array $rule
     * @param array $message
     * @param bool $batch
     * @return bool
     * @throws Exception
     */
    public function validate(array $data, array $rule, array $message = [], bool $batch = false): bool
    {
        $validate = new Validate;
        $validate->rule($rule)->message($message)->batch($batch);
        if (!$validate->check($data)) {
            throw  new Exception($validate->getError());
        }
        return true;
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
            $op = !empty($ops[$key]) ? $ops[$key] : '%*%';

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
        $data   = $this->model->field($fields)->select()->toArray();
        return $this->success('', $data);
    }

}
