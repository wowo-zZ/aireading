<?php
include "config.php";
include "db.php";

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

// ocr
$content = ocrRequest($save_path);

// tts
$tts_save_path = ttsRequest($content);
if ($tts_save_path) {
    echo "上传成功,<a href='/list.php'>前往查看</a>";
}

// 存数据库
$db = new DB($config['db_config']);
$var_dump($db->connection);
exit;

function ocrRequest($pic_path)
{
    global $config;
    $x_param = base64_encode(json_encode(array(
        "language" => "cn|en",
        "location" => "false",
    )));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $config['ocr_api']);
    $cur_time = time();
    $headers = [
        'X-Appid:' . $config['appid'],
        'X-CurTime:' . $cur_time,
        'X-Param:' . $x_param,
        'X-CheckSum:' . md5($config['ocr_key'] . $cur_time . $x_param),
        'Content-Type:application/x-www-form-urlencoded; charset=utf-8'
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'image' => base64EncodeImage($pic_path)
    ]));

    $data = json_decode(curl_exec($ch), TRUE);
    $content = '';
    foreach ($data['data']['block'][0]['line'] as $line) {
        foreach ($line['word'] as $word) {
            $content .= $word['content'];
        }
    }
    return $content;
}

function base64EncodeImage($image_file)
{
    $image_data = file_get_contents($image_file);
    $base64_image = base64_encode($image_data);
    return $base64_image;
}

function ttsRequest($content)
{
    global $config;
    $x_param = 'eyJhdWYiOiAiYXVkaW8vTDE2O3JhdGU9MTYwMDAiLCJhdWUiOiAicmF3Iiwidm9pY2VfbmFtZSI6ICJ4aWFveWFuIiwic3BlZWQiOiAiNTAiLCJ2b2x1bWUiOiAiNTAiLCJwaXRjaCI6ICI1MCIsImVuZ2luZV90eXBlIjogImludHA2NSIsInRleHRfdHlwZSI6ICJ0ZXh0In0=';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $config['tts_api']);
    $cur_time = time();
    $headers = [
        'X-Appid:' . $config['appid'],
        'X-CurTime:' . $cur_time,
        'X-Param:' . $x_param,
        'X-CheckSum:' . md5($config['tts_key'] . $cur_time . $x_param),
        'Content-Type:application/x-www-form-urlencoded; charset=utf-8'
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'text' => $content
    ]));

    $response = curl_exec($ch);
    $header = curl_getinfo($ch);
    $tts_save_path = '';

    if ($header['content_type'] == 'audio/mpeg') {
        $tts_save_path .= './upload/' . $cur_time . '.mp3';
        $res = file_put_contents($tts_save_path, $response);
    } else {
        $tts_save_path .= './upload/' . $cur_time . '.wav';
        $res = file_put_contents($tts_save_path, $response);
    }
    if ($res > 1) {
        return $tts_save_path;
    } else {
        return false;
    }
}
