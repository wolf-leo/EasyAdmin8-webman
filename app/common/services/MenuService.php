<?php

namespace app\common\services;

use think\facade\Db;

class MenuService
{

    /**
     * 管理员ID
     * @var integer
     */
    protected int $adminId = 0;

    protected array $adminConfig = [];

    public function __construct($adminId = 0)
    {
        $this->adminId     = $adminId;
        $this->adminConfig = config('admin');
        return $this;
    }

    public function getHomeInfo(): array
    {
        $data = Db::name('system_menu')
            ->whereNull('delete_time')
            ->where('pid', HOME_PID)
            ->field('title,icon,href')->find();
        !empty($data) && $data['href'] = __url('/' . $data['href']);
        return $data ?: [];
    }

    public function getMenuTree(): array
    {
        $authServer = new AuthService($this->adminId);
        return $this->buildMenuChild(0, $this->getMenuData(), $authServer);
    }

    private function buildMenuChild($pid, $menuList, AuthService $authServer): array
    {
        $treeList = [];
        foreach ($menuList as $v) {
            $check = empty($v['href']) || $authServer->checkNode($v['href']);
            !empty($v['href']) && $v['href'] = $this->adminConfig['admin_domain_status'] ? "/{$v['href']}" : '/' . $this->adminConfig['admin_alias_name'] . "/{$v['href']}";
            if ($pid == $v['pid'] && $check) {
                $node  = $v;
                $child = $this->buildMenuChild($v['id'], $menuList, $authServer);
                if (!empty($child)) {
                    $node['child'] = $child;
                }
                if (!empty($v['href']) || !empty($child)) {
                    $treeList[] = $node;
                }
            }
        }
        return $treeList;
    }

    protected function getMenuData()
    {
        $menuData = Db::name('system_menu')
            ->field('id,pid,title,icon,href,target')
            ->whereNull('delete_time')
            ->where('status', 1)
            ->where('pid', '<>', HOME_PID)
            ->order('sort', 'desc')->order('id')
            ->select()->toArray();
        return $menuData;
    }

}
