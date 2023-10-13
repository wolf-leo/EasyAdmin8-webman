<?php

$admin = config('admin');

return [
    'enable' => $admin['admin_domain_status'],
    // 多应用绑定关系
    'bind'   => [
        // '网站默认地址，例如 www.baidu.com'     => '',
        '127.0.0.1'       => '',
        $admin['admin_domain'] => 'admin', // 绑定到admin应用
    ],
    // 绑定关系，域名，应用的验证逻辑，返回true时认为符合绑定关系，反之不符合返回404
    'check'  => function ($bind, $domain, $app) {
        return isset($bind[$domain]) && $bind[$domain] === $app;
    }
];
