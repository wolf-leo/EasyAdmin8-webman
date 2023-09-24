<?php

return [
    'default'     => 'mysql',
    'connections' => [
        'mysql' => [
            // 数据库类型
            'type'            => 'mysql',
            // 服务器地址
            'hostname'        => env('DB_HOST', '127.0.0.1'),
            // 数据库名
            'database'        => env('DB_DATABASE', 'test'),
            // 数据库用户名
            'username'        => env('DB_USERNAME', 'root'),
            // 数据库密码
            'password'        => env('DB_PASSWORD', ''),
            // 数据库连接端口
            'hostport'        => env('DB_PORT', 3306),
            // 数据库连接参数
            'params'          => [
                // 连接超时3秒
                \PDO::ATTR_TIMEOUT => 3,
            ],
            // 数据库编码默认采用utf8
            'charset'         => 'utf8mb4',
            // 数据库表前缀
            'prefix'          => env('DB_PREFIX', ''),
            // 断线重连
            'break_reconnect' => true,
            // 关闭SQL监听日志
            'trigger_sql'     => false,
            // 自定义分页类
            'bootstrap'       => ''
        ],
    ],
];
