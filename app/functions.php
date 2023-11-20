<?php

use think\facade\Cache;

/**
 * Here is your custom functions.
 */
/**
 * 首页的PID
 */
const HOME_PID       = 99999999;
const SUPER_ADMIN_ID = 1;
/**
 * @param string $url
 * @param array $vars
 * @param false $suffix
 * @return string
 */
function __url(string $url = '', array $vars = [], bool $suffix = false): string
{
    if (!config('admin.admin_domain_status')) {
        $url = "/" . (config('admin')['admin_alias_name'] . (str_starts_with($url, '/') ? $url : "/{$url}"));
    }
    return $url ?: '/';

}

if (!function_exists('parse_name')) {
    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name 字符串
     * @param int $type 转换类型
     * @param bool $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    function parse_name(string $name, int $type = 0, bool $ucfirst = true): string
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            },                            $name);

            return $ucfirst ? ucfirst($name) : lcfirst($name);
        }

        return strtolower(trim(preg_replace('/[A-Z]/', '_\\0', $name), '_'));
    }
}

if (!function_exists('sysconfig')) {

    /**
     * 获取系统配置信息
     * @param $group
     * @param null $name
     * @return mixed
     */
    function sysconfig($group, $name = null): mixed
    {
        $where = ['group' => $group];
        $value = empty($name) ? Cache::get("sysconfig_{$group}") : Cache::get("sysconfig_{$group}_{$name}");
        if (empty($value)) {
            if (!empty($name)) {
                $where['name'] = $name;
                $value         = \app\admin\model\SystemConfig::where($where)->value('value');
                Cache::set("sysconfig_{$group}_{$name}", $value, 3600);
            } else {
                $value = \app\admin\model\SystemConfig::where($where)->column('value', 'name');
                Cache::set("sysconfig_{$group}", $value, 3600);
            }
        }
        return $value;
    }
}

if (!function_exists('auths')) {

    /**
     * auth权限验证
     * @param $node
     * @return bool
     */
    function auths($node = null): bool
    {
        $authService = new \app\common\services\AuthService(session('admin.id'));
        return $authService->checkNode($node);
    }

}

if (!function_exists('password')) {

    /**
     * 密码加密算法
     * @param string $value 需要加密的值
     * @return string
     */
    function password(string $value): string
    {
        $value = sha1('blog_') . md5($value) . md5('_encrypt') . sha1($value);
        return sha1($value);
    }

}

if (!function_exists('json')) {

    function json(array $data = []): \support\Response
    {
        return response()->json($data);
    }

}

/**
 * @param string $detail
 * @param string $name
 * @param string $placeholder
 * @return string
 */
function editor_textarea(string $detail, string $name = 'desc', string $placeholder = '请输入'): string
{
    $editor_type = sysconfig('site', 'editor_type');
    return match ($editor_type) {
        'ckeditor' => "<textarea name='{$name}' rows='20' class='layui-textarea editor' placeholder='{$placeholder}'>{$detail}</textarea>",
        default    => "<script type='text/plain' id='{$name}' name='{$name}' class='editor' data-content='{$detail}'></script>",
    };
}