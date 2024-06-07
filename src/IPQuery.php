<?php

namespace QbCloud\Geoip2;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;

class IPQuery
{
    public $reader;

    private $lang;

    private $type;

    public function __construct()
    {
        $this->lang = config('geoip2.lang');
        $this->type = config('geoip2.db_type');
        $path = config("geoip2.db_path_$this->type");
        $dbFilePath = storage_path($path);
        try {
            $this->reader = new Reader($dbFilePath);
        } catch (InvalidDatabaseException $e) {
            $this->reader = null;
        }
    }

    public function connect(string $ip = ''): ?array
    {
        if (empty($this->reader)) {
            return null;
        }
        if (empty($ip)) {
            $ip = request()->header('CF-Connecting-IP') ?: request()->ip();
        }
        $result = null;
        switch ($this->type) {
            case 'city':
                $result = $this->city($ip);
                break;
            case 'country':
                $result = $this->country($ip);
                break;
            case 'asn':
                $result = $this->asn($ip);
                break;
            default:
                break;
        }
        return $result;
    }

    private function country($ip): ?array
    {
        try {
            $result = $this->reader->country($ip)->jsonSerialize();
        } catch (AddressNotFoundException|InvalidDatabaseException $e) {
            return null;
        }
        return [
            'ip' => $ip,
            'type' => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 'IPV4' : 'IPV6',
            'continent' => $result['continent']['names'][$this->lang] ?? null,
            'country' => $result['country']['names'][$this->lang] ?? null,
            'country_code' => $result['country']['iso_code'] ?? null
        ];
    }

    private function city($ip): ?array
    {
        try {
            $result = $this->reader->city($ip)->jsonSerialize();
        } catch (AddressNotFoundException|InvalidDatabaseException $e) {
            return null;
        }
        $country = $result['country']['names'][$this->lang] ?? null;
        $province = $result['subdivisions'][0]['names'][$this->lang] ?? null;
        $city = $result['city']['names'][$this->lang] ?? null;
        return [
            'ip' => $ip,
            'type' => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 'IPV4' : 'IPV6',
            'content' => $country . $province . $city,
            'continent' => $result['continent']['names'][$this->lang] ?? null,
            'country' => $country,
            'province' => $province,
            'city' => $city,
            'country_code' => $result['country']['iso_code'] ?? null,
            'location' => $result['location'] ?? null
        ];
    }

    private function asn($ip): ?array
    {
        try {
            $result = $this->reader->asn($ip)->jsonSerialize();
        } catch (AddressNotFoundException|InvalidDatabaseException $e) {
            return null;
        }
        return [
            'ip' => $ip,
            'type' => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 'IPV4' : 'IPV6',
            'autonomous_system_number' => $result['autonomous_system_number'] ?? null,
            'autonomous_system_organization' => $result['autonomous_system_organization'] ?? null
        ];
    }
}
