<?php
include "config-45minClass.php";
include "vendor/autoload.php";

$content = ocrRequest("./upload/test.jpg");

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

    $image_data = file_get_contents($pic_path);
    $base64_image = base64_encode($image_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'image' => $base64_image
    ]));

    $data = json_decode(curl_exec($ch), TRUE);
    $content = '';
    foreach ($data['data']['document']['blocks'] as $block) {
		$content .= $block['lines'][0]['text'];
    }
    return $content;
}

ttsRequest($content);

function ttsRequest($content) {
    global $config;

    $host = 'tts-api.xfyun.cn';
    $date = gmstrftime("%a, %d %b %Y %T %Z",time());
    $request_line = 'GET /v2/tts HTTP/1.1';

    $appid = $config['appid'];
    $api_key = $config['ws-tts-key'];
    $api_secret = $config['ws-tts-secret'];

    $signature_origin = "host: $host\ndate: $date\n$request_line";
    $signature_sha = hash_hmac('sha256', $signature_origin, $api_secret, true);
    $signature = base64_encode($signature_sha);

    $authorization_origin = "api_key=\"$api_key\",algorithm=\"hmac-sha256\",headers=\"host date request-line\",signature=\"$signature\"";
    $authorization = base64_encode($authorization_origin);
    $params = [
        'authorization' => $authorization,
        'host' => $host,
        'date' => $date
    ];
    $url = 'wss://tts-api.xfyun.cn/v2/tts?' . http_build_query($params);

    $client = new WebSocket\Client($url);
    $data = [
        'common' => [
            'app_id' => $appid
        ],
        'business' => [
            'aue' => 'lame',
            'sfl' => 1,
            'vcn' => 'xiaoyan',
            'tte' => 'UTF8'
        ],
        'data' => [
            'text' => base64_encode($content),
            'status' => 2
        ]
    ];
    $client->send(json_encode($data));

    $result = "";
    while (true) {
        try {
            // 接收数据
            $message = json_decode($client->receive());
            switch ($message->data->status) {
                case 0:
                    echo "开始合成\n";
                    break;
                case 1:
                    echo "合成中，拼接结果...\n";
                    $result .= base64_decode($message->data->audio);
                    echo "目前结果长度：" . strlen($result) . "\n";
                    break;
                case 2:
                    $result .= base64_decode($message->data->audio);
                    echo "目前结果长度：" . strlen($result) . "\n";
                    echo "合成结束，退出接收数据\n";
                    break 2;
            }

        } catch (\WebSocket\ConnectionException $e) {
            // 异常处理
        }
    }
    echo "最终结果数据长度：" . strlen($result) . "\n";
    $tts_save_path = 'upload/' . time() . '.mp3';
    file_put_contents($tts_save_path, $result);
    $client->close();
    return $tts_save_path;
}