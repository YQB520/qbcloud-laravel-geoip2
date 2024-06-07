## 主要用途
使用Laravel 6.x 以上通过Geoip2的GeoLite2 City、GeoLite2 Country、GeoLite2 ASN查询IP信息。

### 下载 GeoIP Databases 文件
PS：需要注册官方账号\
[https://www.maxmind.com/en/accounts/692132/geoip/downloads](https://www.maxmind.com/en/accounts/692132/geoip/downloads)

## 用法
``` bash
composer require qbcloud/laravel-geoip2
```

发布配置文件：
``` bash
php artisan vendor:publish --provider="QbCloud\Geoip2\Providers\GeoipServiceProvider"
```

```php
// config/geoip2.php

return [
    'lang' => 'zh-CN', // zh-CN en ru pt-BR ja fr es de
    'db_type' => 'city', // city country asn  db_type===city则db_path_city必填  db_type===country则db_path_country必填
    'db_path_city' => 'geoip2/GeoLite2-City.mmdb', // 以 storage_path() 为根目录
    'db_path_country' => 'geoip2/GeoLite2-Country.mmdb', // 以 storage_path() 为根目录
    'db_path_asn' => 'geoip2/GeoLite2-ASN.mmdb' // 以 storage_path() 为根目录
];
```

```php
use QbCloud\Geoip2\Facades\IPQuery;

// 查询 ipv4 或者 ipv6
IPQuery::connect('x.x.x.x');

// 或者
use QbCloud\Geoip2\IPQuery;

$client = new IPQuery();
$client->connect('2xx6:4xx0:0:1x3::a0');
```

### 官方文档
[GeoIP2 and GeoLite2 Web Services Documentation](https://dev.maxmind.com/geoip/docs/web-services#request-and-response-api-references)
