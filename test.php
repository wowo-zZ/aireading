<?php
/**
 * Created by PhpStorm.
 * User: guizheng
 * Date: 2020/6/12
 * Time: 11:54
 */
require_once 'vendor/autoload.php';
require_once 'config.php';

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
    'authorizations' => $authorization,
    'dates' => $date,
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
        "text" => base64_encode("我爱北京天安门，天安门上太阳升")
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
                echo "开始合成\n";
                break;
            case 1:
                echo "合成中，拼接结果...{$message->data->ced}\n";
                $result .= base64_decode($message->data->audio);
                echo "目前结果长度：" . strlen($result) . "\n";
                break;
            case 2:
                echo "合成中，拼接结果...{$message->data->ced}\n";
                $result .= base64_decode($message->data->audio);
                echo "合成结束，退出接收数据" . "\n";
                break 2;
        }
    } catch (\WebSocket\ConnectionException $e) {
        echo $e->getMessage();exit();
        break;
    }
}
echo "最终结果数据长度：" . strlen($result) . "\n";
file_put_contents('upload/' . time() . '.mp3', $result);

$client->close();