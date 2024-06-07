<?php


return [
    'lang' => 'zh-CN', // zh-CN en ru pt-BR ja fr es de
    'db_type' => 'city', // city country asn  db_type===city则db_path_city必填  db_type===country则db_path_country必填
    'db_path_city' => 'geoip2/GeoLite2-City.mmdb', // 以 storage_path() 为根目录
    'db_path_country' => 'geoip2/GeoLite2-Country.mmdb', // 以 storage_path() 为根目录
    'db_path_asn' => 'geoip2/GeoLite2-ASN.mmdb' // 以 storage_path() 为根目录
];
