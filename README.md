<p>
<span><img src="https://img.shields.io/badge/php-%3E=8.0.0-brightgreen.svg?maxAge=2592000&amp;" alt="php"></span>
<span><img src="https://img.shields.io/badge/mysql-%3E=5.7-brightgreen.svg?maxAge=2592000&amp;color=blue" alt="MySQL"></span>
<span><img src="https://img.shields.io/badge/webman-%3E=1.5.0-brightgreen.svg?maxAge=2592000" alt="webman"></span>
<span><img src="https://img.shields.io/badge/layui-%3E=2.9.0-brightgreen.svg?maxAge=2592000&amp;color=critical" alt="layui"></span>
<span><img src="https://img.shields.io/badge/license-MIT-green?maxAge=2592000&amp;color=yellow" alt="License"></span>
</p>

## `EasyAdmin8`所有版本 (当前项目为`webman`版本)

|          |                                Github                                |                               Gitee                               |
|----------|:--------------------------------------------------------------------:|:-----------------------------------------------------------------:|
| ThinkPHP |         [EasyAdmin8](https://github.com/wolf-leo/EasyAdmin8)         |         [EasyAdmin8](https://gitee.com/wolf18/EasyAdmin8)         |
| Laravel  | [EasyAdmin8-Laravel](https://github.com/wolf-leo/EasyAdmin8-Laravel) | [EasyAdmin8-Laravel](https://gitee.com/wolf18/EasyAdmin8-Laravel) |
| webman   |  [EasyAdmin8-webman](https://github.com/wolf-leo/EasyAdmin8-webman)  |  [EasyAdmin8-webman](https://gitee.com/wolf18/EasyAdmin8-webman)  |

## 项目介绍

> `EasyAdmin8-webman` 在 [`EasyAdmin`](https://gitee.com/zhongshaofa/easyadmin) 的基础上使用 webman 最新版重构，PHP 最低版本要求不低于 8.0
>
> webman 和 layui v2.9.x 的快速开发的后台管理系统。
>
> 请注意，该分支为`thinkphp`，主要用到了`ThinkPHP`相关组件
>
> 项目地址：[http://easyadmin8.top](http://easyadmin8.top)
>
> 演示地址：[http://webman-tp.easyadmin8.top/admin](http://webman-tp.easyadmin8.top/admin)
>
>【如果不能访问，可以自行本地搭建预览或参考下方界面预览图】

## 安装教程

> EasyAdmin8-webman 使用 Composer 来管理项目依赖。因此，在使用 EasyAdmin8-webman 之前，请确保你的机器已经安装了 Composer。

### 通过一键安装命令

```
if [ -f /usr/bin/curl ];then curl -sSO https://easyadmin8.top/auto-install-EasyAdmin8-webman.sh;else wget -O auto-install-EasyAdmin8-webman.sh https://easyadmin8.top/auto-install-EasyAdmin8-webman.sh;fi;bash auto-install-EasyAdmin8-webman.sh
```

### 通过`git`下载安装包，`composer`安装依赖包

```
1.下载安装包

  git clone -b thinkphp https://github.com/wolf-leo/EasyAdmin8-webman EasyAdmin8-webman-thinkphp

  或者

  git clone -b thinkphp https://gitee.com/wolf18/EasyAdmin8-webman EasyAdmin8-webman-thinkphp

2.安装依赖包（确保 PHP 版本 >= 8.0）

  在根目录下 composer install ，如果有报错信息可以使用命令 composer install --ignore-platform-reqs
  
3.拷贝 .example.env 文件重命名为 .env ，命令 cp .example.env .env ，修改数据库账号密码参数

4.命令启动(php start.php start 或者 php start.php start -d)
详细启动配置区别请点击：https://www.workerman.net/doc/webman/install.html#2.%20%E8%BF%90%E8%A1%8C

或者反向代理(以 Nginx 为例，其中8787端口号可以在 .env 配置中修改)
  
upstream webman {
    server 127.0.0.1:8787;
    keepalive 10240;
}

server {
  server_name 站点域名;
  listen 80;
  access_log off;
  root /your/webman/public;

  location ^~ / {
      proxy_set_header X-Real-IP $remote_addr;
      proxy_set_header Host $host;
      proxy_set_header X-Forwarded-Proto $scheme;
      proxy_http_version 1.1;
      proxy_set_header Connection "";
      if (!-f $request_filename){
          proxy_pass http://webman;
      }
  }
}

```

## CURD命令大全

> 参考 [CURD命令大全](CURD.md)

## 配置自定义域名

> 参考 [自定义域名配置](CUSTOM_DOMAIN.md)

## 界面预览

![](public/static/common/images/easyadmin8-01.png)

![](public/static/common/images/easyadmin8-02.png)

![](public/static/common/images/easyadmin8-03.png)

## 交流群

<center>

![EasyAdmin8-webman 交流群](public/static/common/images/EasyAdmin8-webman.png)

</center>

## 相关文档

* [webman](https://www.workerman.net/doc/webman/README.html)

* [EasyAdmin](http://easyadmin.99php.cn/docs)

* [Layui 2.8.x](https://layui.dev/docs/2.8/)

* [Layuimini](https://github.com/zhongshaofa/layuimini)

* [Annotations](https://github.com/doctrine/annotations)

* [Jquery](https://github.com/jquery/jquery)

* [RequireJs](https://github.com/requirejs/requirejs)

* [CKEditor](https://github.com/ckeditor/ckeditor4)

* [Echarts](https://github.com/apache/incubator-echarts)

* [UEditorPlus](https://github.com/modstart-lib/ueditor-plus)

## 免责声明

> 任何用户在使用 `EasyAdmin8-webman` 后台框架前，请您仔细阅读并透彻理解本声明。您可以选择不使用`EasyAdmin8-webman`后台框架，若您一旦使用`EasyAdmin8-webman`后台框架，您的使用行为即被视为对本声明全部内容的认可和接受。

* 请留意`EasyAdmin8-webman` 对应的协议，个人或企业商用请遵循协议或得到相应授权。

* `EasyAdmin8-webman`后台框架是一款开源的后台快速开发框架 ，主要用于更便捷地开发后台管理；其尊重并保护所有用户的个人隐私权，不窃取任何用户计算机中的信息。更不具备用户数据存储等网络传输功能。

* 您承诺秉着合法、合理的原则使用`EasyAdmin8-webman`后台框架，不利用`EasyAdmin8-webman`后台框架进行任何违法、侵害他人合法利益等恶意的行为，亦不将`EasyAdmin8-webman`后台框架运用于任何违反我国法律法规的 Web 平台。

* 任何单位或个人因下载使用`EasyAdmin8-webman`后台框架而产生的任何意外、疏忽、合约毁坏、诽谤、版权或知识产权侵犯及其造成的损失 (包括但不限于直接、间接、附带或衍生的损失等)，本开源项目不承担任何法律责任。

* 用户明确并同意本声明条款列举的全部内容，对使用`EasyAdmin8-webman`后台框架可能存在的风险和相关后果将完全由用户自行承担，本开源项目不承担任何法律责任。

* 任何单位或个人在阅读本免责声明后，应在《MIT 开源许可证》所允许的范围内进行合法的发布、传播和使用`EasyAdmin8-webman`后台框架等行为，若违反本免责声明条款或违反法律法规所造成的法律责任(包括但不限于民事赔偿和刑事责任），由违约者自行承担。

* 如果本声明的任何部分被认为无效或不可执行，其余部分仍具有完全效力。不可执行的部分声明，并不构成我们放弃执行该声明的权利。

* 本开源项目有权随时对本声明条款及附件内容进行单方面的变更，并以消息推送、网页公告等方式予以公布，公布后立即自动生效，无需另行单独通知；若您在本声明内容公告变更后继续使用的，表示您已充分阅读、理解并接受修改后的声明内容。

