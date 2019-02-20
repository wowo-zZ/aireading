<?php
/**
 *
 * User: guizheng@iflytek.com
 * Date: 2019/2/15 上午9:49
 */
$config = [
    // 设置文件保存路径
    'upload_path' => 'upload',

    // appid
    'appid' => '5c661a16',

    // ocr
    'ocr_api' => 'http://webapi.xfyun.cn/v1/service/v1/ocr/general',
    'ocr_key' => '373ca8185a9b4013a407b1a55561662f',

    // tts app key
    'tts_api' => 'http://api.xfyun.cn/v1/service/v1/tts',
    'tts_key' => '8ad63b0e47f63909a9b8cd33a4ad43f1',

    'db_config' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'password' => '1234',
        'database' => 'aireading'
    ]
];
