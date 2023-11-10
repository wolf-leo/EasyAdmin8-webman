<?php

namespace app\common\services;

use think\facade\Cache;
use think\facade\Db;

/**
 * 系统日志表
 * Class SystemLogService
 * @package app\admin\service
 */
class SystemLogService
{

    /**
     * 当前实例
     */
    protected static ?SystemLogService $_instance = null;

    /**
     * 表前缀
     * @var string
     */
    protected string $tablePrefix;

    /**
     * 表后缀
     * @var string
     */
    protected string $tableSuffix;

    /**
     * 表名
     * @var string
     */
    protected string $tableName;

    /**
     * 构造方法
     * SystemLogService constructor.
     */
    protected function __construct()
    {
        $this->tablePrefix = config('database.connections.mysql.prefix');
        $this->tableSuffix = date('Ym');
        $this->tableName   = "system_log_{$this->tableSuffix}";
        return $this;
    }

    /**
     * 获取实例对象
     */
    public static function instance(): ?SystemLogService
    {
        if (!static::$_instance) static::$_instance = new self();
        return static::$_instance;
    }


    /**
     * 保存数据
     * @param $data
     * @return bool
     */
    public function save($data): bool
    {
        Db::startTrans();
        try {
            $this->detectTable();
            Db::name($this->tableName)->insert($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
        return true;
    }

    /**
     * 检测数据表
     * @return bool
     */
    public function detectTable(): bool
    {
        // 手动删除日志表时候 记得清除缓存
        $key   = md5("systemLog{$this->tableName}Table");
        $isset = Cache::get($key);
        if ($isset) return true;
        $check = Db::query("show tables like '{$this->tablePrefix}{$this->tableName}'");
        if (empty($check)) {
            $sql = $this->getCreateSql();
            Db::execute($sql);
        }
        Cache::set($key, !empty($check));
        return true;
    }

    /**
     * 根据后缀获取创建表的sql
     * @return string
     */
    protected function getCreateSql(): string
    {
        return <<<EOT
CREATE TABLE `{$this->tablePrefix}{$this->tableName}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(10) unsigned DEFAULT '0' COMMENT '管理员ID',
  `url` varchar(1500) NOT NULL DEFAULT '' COMMENT '操作页面',
  `method` varchar(50) NOT NULL COMMENT '请求方法',
  `title` varchar(100) DEFAULT '' COMMENT '日志标题',
  `content` text NOT NULL COMMENT '内容',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) DEFAULT '' COMMENT 'User-Agent',
  `create_time` int(10) DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT COMMENT='后台操作日志表 - {$this->tableSuffix}';
EOT;
    }

}
