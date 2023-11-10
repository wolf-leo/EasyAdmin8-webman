<?php

namespace app\common\services;

use think\facade\Cache;

class TriggerService
{

    /**
     * 更新菜单缓存
     * @param null $adminId
     * @return bool
     */
    public static function updateMenu($adminId = null): bool
    {
        if (empty($adminId)) {
            Cache::clear();
        } else {
            Cache::delete('initAdmin_' . $adminId);
        }
        return true;
    }

    /**
     * 更新节点缓存
     * @param null $adminId
     * @return bool
     */
    public static function updateNode($adminId = null): bool
    {
        if (empty($adminId)) {
            Cache::clear();
        } else {
            Cache::delete('allAuthNode_' . $adminId);
        }
        return true;
    }

    /**
     * 更新系统设置缓存
     * @return bool
     */
    public static function updateSysConfig(): bool
    {
        Cache::clear();
        return true;
    }

}
