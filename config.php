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
    'appid' => '',

    // ocr
    'ocr_api' => 'http://webapi.xfyun.cn/v1/service/v1/ocr/recognize_document',
    'ocr_key' => '',

    // tts app key
    'tts_api' => 'http://api.xfyun.cn/v1/service/v1/tts',
    'tts_key' => '',

    // ws-tts app key
    'ws-tts-key' => '',
    'ws-tts-secret' => '',

    'db_config' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'password' => '',
        'database' => 'aireading'
    ]
];
