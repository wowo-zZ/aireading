<?php
include "config.php";
include "db.php";
include "db_sqlite.php";
include 'vendor/autoload.php';

if (!isset($config)) {
    die('配置错误。');
}

// 检查上传文件
if (empty($_FILES['input_image']['size'])) {
    header("Location:/list.php");
    exit();
}

// 校验文件大小
if ($_FILES['input_image']['size'] > 3145728) {
    die('图片文件过大,请压缩后重试!');
}

// 上传文件
$uploaded_path = $_FILES['input_image']['tmp_name'];
$save_path = $config['upload_path'] . '/' . time() . '.jpeg';
$save_time = time();
move_uploaded_file($uploaded_path, $save_path);

// ocr
$content = ocrRequest($save_path);
$oct_time = time();

// tts
if (mb_strlen($content) >= 300) {
	$content = mb_substr($content, 0, 300);
}
$tts_path = ttsRequest($content);
$tts_time = time();
if ($tts_path) {
    header("Location:/list.php");
}

// 存数据库
$db_sqlite = new DB_Sqlite('./sqlite.db');
$db_sqlite->insert($save_path, $save_time, $oct_time, $tts_time, $tts_path, $content, 'comment');

function ocrRequest($pic_path)
{
    global $config;
    $x_param = base64_encode(json_encode(array(
		"engine_type" => "recognize_document",
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
    foreach ($data['data']['document']['blocks'] as $block) {
		$content .= $block['lines'][0]['text'];
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
    // 鉴权参数
    $host = 'tts-api.xfyun.cn';
    $date = gmstrftime("%a, %d %b %Y %T %Z", time());
    $request_line = 'GET /v2/tts HTTP/1.1';
    $appid = $config['appid'];
    $api_key = $config['ws-tts-key'];
    $api_secret = $config['ws-tts-secret'];
    $sign_data = "host: $host\ndate: $date\n$request_line";
    $signature_sha = hash_hmac('sha256', $sign_data, $api_secret, true);
    $signature = base64_encode($signature_sha);
    $authorization_origin = "api_key=\"$api_key\",algorithm=\"hmac-sha256\",headers=\"host date request-line\",signature=\"$signature\"";
    $authorization = base64_encode($authorization_origin);
    $params = [
        'authorization' => $authorization,
        'date' => $date,
        'host' => $host
    ];
    $url = "wss://tts-api.xfyun.cn/v2/tts?" . http_build_query($params);
    $client = new WebSocket\Client($url);
    $data_origin = [
        "common" => [
            "app_id" => $appid
        ],
        "business" => [
            "vcn" => "xiaoyan",
            "aue" => "lame",
            "sfl" => 1,
            "speed" => 50,
            "tte" => "UTF8"
        ],
        "data" => [
            "status" => 2,
            "text" => base64_encode($content)
        ]
    ];
    $data = json_encode($data_origin);
    $client->send($data);

    $result = "";
    while (true) {
        try {
            $message = json_decode($client->receive());
            switch ($message->data->status) {
                case 0:
                    // echo "开始合成\n";
                    break;
                case 1:
                    // echo "合成中，拼接结果...\n";
                    $result .= base64_decode($message->data->audio);
                    // echo "目前结果长度：" . strlen($result) . "\n";
                    break;
                case 2:
                    $result .= base64_decode($message->data->audio);
                    // echo "目前结果长度：" . strlen($result) . "\n";
                    // echo "合成结束，退出接收数据";
                    break 2;
            }
        } catch (\WebSocket\ConnectionException $e) {
            break;
        }
    }
    // echo "最终结果数据长度：" . strlen($result) . "\n";
    $tts_save_path = 'upload/' . time() . '.mp3';
    file_put_contents($tts_save_path, $result);
    $client->close();
    return $tts_save_path;
}
