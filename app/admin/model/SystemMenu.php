<?php

namespace app\admin\model;

use app\model\BaseModel;

class SystemMenu extends BaseModel
{
    public function getPidMenuList(): array
    {
        $list        = $this->field('id,pid,title')
            ->where([
                        ['pid', '<>', HOME_PID],
                        ['status', '=', 1],
                    ])->select()->toArray();
        $pidMenuList = $this->buildPidMenu(0, $list);
        return array_merge([['id' => 0, 'pid' => 0, 'title' => '顶级菜单']], $pidMenuList);
    }

    protected function buildPidMenu($pid, $list, $level = 0): array
    {
        $newList = [];
        foreach ($list as $vo) {
            if ($vo['pid'] == $pid) {
                $level++;
                foreach ($newList as $v) {
                    if ($vo['pid'] == $v['pid'] && isset($v['level'])) {
                        $level = $v['level'];
                        break;
                    }
                }
                $vo['level'] = $level;
                if ($level > 1) {
                    $repeatString = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    $markString   = str_repeat("{$repeatString}├{$repeatString}", $level - 1);
                    $vo['title']  = $markString . $vo['title'];
                }
                $newList[] = $vo;
                $childList = $this->buildPidMenu($vo['id'], $list, $level);
                !empty($childList) && $newList = array_merge($newList, $childList);
            }

        }
        return $newList;
    }
}
