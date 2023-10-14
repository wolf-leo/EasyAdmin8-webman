# 为何要配置自定义域名

- **`EasyAdmin8-webman` 默认的后台地址为 `域名+/admin` ，可能存在普遍性和不安全性，为了防止对外暴露的风险，建议开发人员进行自定义域名配置**

# 配置提醒

- **请在后台管理系统成功安装后再进行自定义域名配置**

- **配置自定义域名访问后，原有的 `域名/admin` 方式将自动失效，反之亦然**

# 如何配置自定义域名

- 进入 `.env` 文件中,修改 `EASYADMIN.ADMIN_DOMAIN_STATUS` 参数值为 `true`、修改 `EASYADMIN.ADMIN_DOMAIN` 参数值为你要配置的域名，例如 `admin0x5Uy.xxx.com`

- 如果框架还存在项目首页或者其他应用页面，请自行把每个应用对应的域名也配置上。需要在 `config/plugin/webman/domain/app.php` 中配置

- 然后在 `Nginx` 中配置域名的反向代理 [*根据实际项目自行修改*]

```shell
    # 如果需要SSL请自行配置证书即可
    # admin0x5Uy 这个参数可以自定义
    upstream admin0x5Uy {
        server 127.0.0.1:8787; # 此端口对应 .env 中的 APP_PORT
        keepalive 10240;
    }
    
    server {
        listen 80;
        server_name admin0x5Uy.xxx.com; # 此地址对应 .env 中的 EASYADMIN.ADMIN_DOMAIN
        access_log off;
        root /部署的项目地址/EasyAdmin8-webman/public;
    
    location / {
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header Host $host;
        proxy_http_version 1.1;
        proxy_set_header Connection "";
        if (!-f $request_filename){
            rewrite ^/(.*)$ /admin/$1 break;
            proxy_pass http://admin0x5Uy;
        }
      }
      
    }
```

- 相关文档参考 [https://www.workerman.net/plugin/11](https://www.workerman.net/plugin/11)