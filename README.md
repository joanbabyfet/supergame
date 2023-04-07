## About
真人游戏基于前后端分离开发，通过laravel后端与api主体框架，实现运营后台、代理后台、用户端接口与游戏转帐钱包sdk

## Feature
* 整合运营后台、代理后台、用户端接口
* 提供完善的数据统计，包括用户在线、用户活跃、用户留存、用户增长等
* 游戏转帐钱包sdk
* 后端接口动态返回菜单
* 短信/邮件通知支持异步队列，提高用户体验
* 接口支持jwt与参数签名，强化安全性
* 日志支持MongoDB与mysql存储

## Requires
PHP 7.4 or Higher  
Redis
MongoDB 3.2 or Higher

## Install
```
composer install
cp .env.example .env
php artisan app:install
php artisan storage:link
php artisan jwt:secret
```

## Usage
```
# Login Admin
username: admin
password: Bb123456
```

## Change Log
v1.0.0

v1.0.1 - 2023-04-07
* 优化发送telegram模块
* 区分指定任务推送到哪个队列

## Maintainers
Alan

## LICENSE
[MIT License](https://github.com/joanbabyfet/supergame/blob/master/LICENSE)
