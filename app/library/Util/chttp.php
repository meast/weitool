<?php
/*
    @charset:utf-8
*/
namespace Util;

class chttp
{
    public static function httpget($url, $header = '')
    {
        if(empty($header)) $header = "User-Agent:Mozilla/5.0 (Windows NT 6.2; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0\r\nAccept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\nAccept-language:zh-cn,zh\r\n";
        if(!is_array($header)) $header = array($header);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
