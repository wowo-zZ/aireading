<?php
include "config.php";

if (!isset($config)) {
    die('配置错误。');
}

// 检查上传文件
if (empty($_FILES['input_image'])) {
    die('请上传文件后重试!');
}

// 校验文件大小
if ($_FILES['input_image']['size'] > 3145728) {
    die('图片文件过大,请压缩后重试!');
}

// 上传文件
$uploaded_path = $_FILES['input_image']['tmp_name'];
$save_path = $config['upload_path'] . '/' . time() . '.jpeg';
move_uploaded_file($uploaded_path, $save_path);

echo ocrRequest($save_path);
exit;

// ocr

// tts

// 存数据库

function ocrRequest($pic_path)
{
    global $config;
    $xParam = base64_encode(json_encode(array(
        "language" => "cn|en",
        "location" => "false",
    )));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $config['ocr_api']);
    $curTime = time();
    $headers = [
        'X-Appid:' . $config['appid'],
        'X-CurTime:' . $curTime,
        'X-Param:' . $xParam,
        'X-CheckSum:' . md5($config['ocr_key'] . $curTime . $xParam),
        'Content-Type:application/x-www-form-urlencoded; charset=utf-8'
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'image' => base64EncodeImage($pic_path)
    ]));

    $data = curl_exec($ch);
    return $data;
}

function base64EncodeImage($image_file)
{
    $image_data = file_get_contents($image_file);
    $base64_image = base64_encode($image_data);
    return $base64_image;
}