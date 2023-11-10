<?php

namespace app\common\services;

use Shopwwi\LaravelCache\Cache;

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
            Cache::flush();
        } else {
            Cache::forget('initAdmin_' . $adminId);
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
            Cache::flush();
        } else {
            Cache::forget('allAuthNode_' . $adminId);
        }
        return true;
    }

    /**
     * 更新系统设置缓存
     * @return bool
     */
    public static function updateSysConfig(): bool
    {
        Cache::flush();
        return true;
    }

}
