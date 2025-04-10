<?php

namespace QbCloud\Geoip2;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Support\Facades\Cache;
use MaxMind\Db\Reader\InvalidDatabaseException;

class IPQuery
{
    private static $ttl = 24; // 缓存时长---小时

    private static $defaultLocation = [
        'ip' => '127.0.0.1',
        'type' => '无效IP',
        'continent_code' => '',
        'continent' => '',
        'country_code' => '',
        'country' => '',
        'province_code' => '',
        'province' => '',
        'city' => '',
        'address' => '',
        'timezone' => '',
        'latitude' => '',
        'longitude' => '',
        'asn_number' => '',
        'asn_name' => ''
    ];

    /**
     * 查询IP
     * return self::$defaultLocation
     */
    public static function connect($ip = null)
    {
        if (empty($ip)) {
            $ip = self::getClientIP();
        }

        $cacheKey = 'ip_query_' . md5($ip);

        $cache = Cache::get($cacheKey);

        if (!empty($cache)) {
            return $cache;
        }

        self::$defaultLocation['ip'] = $ip;
        self::$defaultLocation['type'] = self::ipType($ip);
        self::$defaultLocation['address'] = self::$defaultLocation['type'];

        if (!self::isValid($ip)) {
            Cache::add($cacheKey, self::$defaultLocation, 60 * 60 * self::$ttl);

            return self::$defaultLocation;
        }

        $dbCity = storage_path('app/geoip/GeoLite2-City.mmdb');
        $dbAsn = storage_path('app/geoip/GeoLite2-ASN.mmdb');

        try {
            $cityReader = new Reader($dbCity);

            $asnReader = new Reader($dbAsn);

            $cityResult = $cityReader->city($ip)->jsonSerialize();

            $asnResult = $asnReader->asn($ip)->jsonSerialize();
        } catch (AddressNotFoundException|InvalidDatabaseException $e) {
            Cache::add($cacheKey, self::$defaultLocation, 60 * 60 * self::$ttl);

            return self::$defaultLocation;
        }

        $lang = 'zh-CN';
        $data = self::$defaultLocation;
        $data['continent_code'] = $cityResult['continent']['code'] ?? '';
        $data['continent'] = $cityResult['continent']['names'][$lang] ?? '';
        $data['country_code'] = $cityResult['country']['iso_code'] ?? '';
        $data['country'] = $cityResult['country']['names'][$lang] ?? '';
        $data['province_code'] = $cityResult['subdivisions'][0]['iso_code'] ?? '';
        $data['province'] = $cityResult['subdivisions'][0]['names'][$lang] ?? '';
        $data['city'] = $cityResult['city']['names'][$lang] ?? '';
        $data['timezone'] = $cityResult['location']['time_zone'] ?? '';
        $data['latitude'] = $cityResult['location']['latitude'] ?? '';
        $data['longitude'] = $cityResult['location']['longitude'] ?? '';
        $data['asn_number'] = $asnResult['autonomous_system_number'] ?? '';
        $data['asn_name'] = $asnResult['autonomous_system_organization'] ?? '';
        $data['address'] = self::format([$data['country'], $data['province'], $data['city']]);

        Cache::add($cacheKey, $data, 60 * 60 * self::$ttl);

        return $data;
    }

    /**
     * 格式化 [country, province, city] 的组合
     * return 中国-广东-广州
     */
    public static function format(array $address, string $symbol = '-')
    {
        return implode($symbol, array_unique(array_filter($address)));
    }

    public static function getClientIP()
    {
        $remotes_keys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
            'HTTP_X_FORWARDED_IP',
            'HTTP_X_CLUSTER_CLIENT_IP'
        ];

        $ip = false;

        foreach ($remotes_keys as $key) {
            if ($address = getenv($key)) {
                foreach (explode(',', $address) as $value) {
                    if (self::isValid($value)) {
                        $ip = $value;
                        break;
                    }
                }
            }
            if ($ip) break;
        }

        if (empty($ip)) {
            $ip = request()->header('CF-Connecting-IP') ?: request()->ip();
        }

        return $ip;
    }

    /**
     * 判断IP是否有效
     * return true or false
     */
    public static function isValid($ip)
    {
        return in_array(self::ipType($ip), ['IPv4', 'IPv6']);
    }

    /**
     * FILTER_FLAG_NO_RES_RANGE 回环地址
     * ['127.0.0.0', '127.255.255.255']
     *
     * FILTER_FLAG_NO_PRIV_RANGE 私有地址
     * ['10.0.0.0', '10.255.255.255'], // A类私有地址
     * ['172.16.0.0', '172.31.255.255'], // B类私有地址
     * ['192.168.0.0', '192.168.255.255'], // C类私有地址
     */
    public static function ipType($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return 'IPv4';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return 'IPv6';
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE)) {
            return '本机地址';
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            return '局域网地址';
        }

        return '无效IP';
    }
}
