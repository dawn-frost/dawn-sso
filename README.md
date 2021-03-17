## 单点登陆-cookie 共享方案

[本代码包配套技术文章链接（内含效果视频及技术方案流程图）](http://jianshu.com)

## 本代码包内容

| 代码包            | 对应设置的本地域名              | 备注                    |
| :---------------- | :------------------------------ | :---------------------- |
| dawn-client-1     | https://dawn.sso-client-1.cn    | 客户端 1                |
| dawn-client-2     | https://dawn.sso-client-2.cn    | 客户端 2                |
| dawn-client-api-1 | http://dawn.sso-client-api-1.cn | 客户端 1 对应的服务端 1 |
| dawn-client-api-2 | http://dawn.sso-client-api-2.cn | 客户端 2 对应的服务端 2 |
| dawn-sso-api      | https://dawn.sso-api.cn         | 认证中心                |

- 两个客户端写的十分简洁，使用的是 jquery，只有一个页面
- 三个服务端 API 框架都是用的 symfony，写的也十分简单，只是达到最终效果，并未在意代码质量，请知悉
- 如有问题，请联系我哦

## 本代码包本地 nginx 配置 - 客户端

- 适用范围是：两个客户端包

```
server {
  listen  443 ssl; # 由于要是用jsonp跨域设置可携带的cookie，所以一定要设置ssl
  server_name  dawn.sso-client-1.cn;
  root /dawn-sso/dawn-client-1/; # 该地址换成本地自有地址

  ssl_certificate /ssl-cert/dawn.sso-client-1.cn.ssl.crt; # 该地址换成本地自有地址
  ssl_certificate_key /ssl-cert/dawn.sso-client-1.cn.ssl.key; # 该地址换成本地自有地址

  location / {
    index index.html index.htm;
  }

  location /api/ {
    proxy_pass http://dawn.sso-client-api-1.cn/;
  }
}
```

## 本代码包本地 nginx 配置 - 服务端包

- 适用范围： 两个服务端包

```
server {
  listen       80; # 只是接口，可以不配ssl
  server_name  dawn.client-api-1.cn;
  root /dawn-sso/dawn-client-api-1/public/; # 该地址换成本地自有地址

  if (!-e $request_filename) {
    rewrite /(.*)$ /index.php/$1 last;
  }

  location / {
    index index.php index.html index.htm;
  }

  location ~ \.php {
    fastcgi_intercept_errors on;
    fastcgi_pass   127.0.0.1:9000; # 替换成自己本地php-fpm的监听地址
    fastcgi_index  index.php;
    fastcgi_split_path_info ^(.+\.php)(.*)$;
    fastcgi_param        PATH_INFO                $fastcgi_path_info;
    fastcgi_param        PATH_TRANSLATED        $document_root$fastcgi_path_info;
    fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
    include        fastcgi_params;
  }
}
```

## 本代码包本地 nginx 配置 - 认证中心服务端包

- 适用范围：认证中心服务端包

```
server {
  listen 443 ssl; # 由于要是用jsonp跨域设置可携带的cookie，所以一定要设置ssl
  server_name  dawn.sso-api.cn;
  root /dawn-sso/dawn-sso-api/public/; # 该地址换成本地自有地址

  ssl_certificate /ssl-cert/dawn.sso-api.cn.ssl.crt; # 该地址换成本地自有地址
  ssl_certificate_key /ssl-cert/dawn.sso-api.cn.ssl.key; # 该地址换成本地自有地址

  if (!-e $request_filename) {
    rewrite /(.*)$ /index.php/$1 last;
  }

  location / {
    index index.php index.html index.htm;
  }

  location ~ \.php {
    fastcgi_intercept_errors on;
    fastcgi_pass   127.0.0.1:9000; # 替换成自己本地php-fpm的监听地址
    fastcgi_index  index.php;
    fastcgi_split_path_info ^(.+\.php)(.*)$;
    fastcgi_param        PATH_INFO                $fastcgi_path_info;
    fastcgi_param        PATH_TRANSLATED        $document_root$fastcgi_path_info;
    fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
    include        fastcgi_params;
  }
}

```

## 本地搭建 https 环境以及证书授权

[本地搭建 https 环境以及证书授权](https://blog.csdn.net/the_fool_/article/details/104697490)
