## 主要用途
使用Laravel 6.x 以上通过Geoip2的GeoLite2 City、GeoLite2 ASN查询IP信息。

## 下载 GeoIP Databases 文件
PS：需要注册官方账号\
[https://www.maxmind.com/en/accounts/692132/geoip/downloads](https://www.maxmind.com/en/accounts/692132/geoip/downloads) 

## 文件放置路径
``` bash
storage/app/geoip
```

## 用法
``` bash
composer require qbcloud/laravel-geoip2
```

```php
use QbCloud\Geoip2\Facades\IPQuery;

// 查询 ipv4 或者 ipv6
IPQuery::connect('x.x.x.x');

// 格式化地址
IPQuery::format(['中国', '广东', '广东']); // 中国-广东
IPQuery::format(['中国', '广东', '广州']); // 中国-广东-广州

// 查询IP类型
IPQuery::ipType('x.x.x.x'); // IPv4

// 查询是否是有效IP
IPQuery::isValid('x.x.x.x'); // false

// 或者
use QbCloud\Geoip2\IPQuery;

$client = new IPQuery();
$client->connect('2xx6:4xx0:0:1x3::a0');
```

### 官方文档
[GeoIP2 and GeoLite2 Web Services Documentation](https://dev.maxmind.com/geoip/docs/web-services#request-and-response-api-references)
