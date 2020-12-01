<?php 
include "vendor/autoload.php";

$config = [
    'appid' => '5ca1e52b',
    'ocr_api' => 'http://webapi.xfyun.cn/v1/service/v1/ocr/recognize_document', 
    'ocr_api_key' => 'd3698336c7f4803689e27dc3197f9cb9', 
    'tts_api' => 'wss://tts-api.xfyun.cn/v2/tts', 
    'tts_api_key' => '9a33948763acfcde53110d5283e99b02',
    'tts_api_secret' => '4e41a9b9384681551db5db956b133b80'
];

// 主流程
$content = ocrRequest("./images/test.jpg");
ttsRequest($content);

function ocrRequest($pic_path) {
    global $config;

    $appid = $config['appid'];
    $ocr_api = $config['ocr_api'];
    $ocr_api_key = $config['ocr_api_key'];
    $cur_time = time();

    $x_param = base64_encode(json_encode([
        'engine_type' => 'recognize_document'
    ]));
    $x_check_sum = md5($ocr_api_key . $cur_time . $x_param);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $config['ocr_api']);

    $headers = [
        'X-Appid:' . $appid,
        'X-CurTime:' . $cur_time,
        'X-Param:' . $x_param,
        'X-CheckSum:' . $x_check_sum
    ];
    $request_body = base64_encode(file_get_contents($pic_path));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'image' => $request_body
    ]));
    $result = json_decode(curl_exec($ch), true);
    $content = '';
    foreach ($result['data']['document']['blocks'] as $block) {
        $content .= $block['lines'][0]['text'];
    }
    return $content;
}

class WSSender extends Thread {

    public function __construct($ws_client, $message, $config) {
        $this->message = $message;
        $this->ws_client = $ws_client;
        $this->config = $config;
    }

    public function run() {
        $appid = ($this->config)['appid'];
        $data = json_encode([
            'common' => [
                'app_id' => $appid
            ],
            'business' => [
                'aue' => 'lame',
                'sfl' => 1,
                'vcn' => 'xiaoyan',
                'tte' => "UTF8"
            ],
            'data' => [
                'text' => base64_encode($this->message),
                'status' => 2
            ]
        ]);
        $result = $this->ws_client->send($data);
        while (true) {
            printf("接收数据ing...\n");
            $message = json_decode($this->ws_client->receive());
            switch ($message->data->status) {
                case 1:
                    $result .= base64_decode($message->data->audio);
                    echo "目前正在合成，合成长度为：" . strlen($result) . "\n";
                break;
                case 2:
                    $result .= base64_decode($message->data->audio);
                    echo "合成已结束，总合成长度为：" . strlen($result) . "\n";
                break 2;
            }
        }
        file_put_contents("./" . time() . ".mp3", $result);
    }
}

class WSReceiver extends Thread {
    private $ws_client;

    public function __construct($client) {
        $this->ws_client = $client;
    }

    public function run() {
        $result = '';
        while (true) {
            sleep(1);
            printf("接收数据ing...\n");
            $message = json_decode($this->ws_client->receive());
            switch ($message->data->status) {
                case 1:
                    $result .= base64_decode($message->data->audio);
                    echo "目前正在合成，合成长度为：" . strlen($result) . "\n";
                break;
                case 2:
                    $result .= base64_decode($message->data->audio);
                    echo "合成已结束，总合成长度为：" . strlen($result) . "\n";
                break 2;
            }
        }
        file_put_contents("./" . time() . ".mp3", $result);
    }
}

function ttsRequest($content) {
    global $config;

    $appid = $config['appid'];
    $tts_api_key = $config['tts_api_key'];
    $tts_api_secret = $config['tts_api_secret'];
    $host = 'tts-api.xfyun.cn';
    $request_line = 'GET /v2/tts HTTP/1.1';
    $date = gmstrftime("%a, %d %b %Y %T %Z", time());

    $signature_origin = "host: $host\ndate: $date\n$request_line";
    $signature_sha = hash_hmac('sha256', $signature_origin, $tts_api_secret, true);
    $signature = base64_encode($signature_sha);

    $authrization = base64_encode("api_key=\"$tts_api_key\",algorithm=\"hmac-sha256\",headers=\"host date request-line\",signature=\"$signature\"");
    $url = 'wss://tts-api.xfyun.cn/v2/tts?' . http_build_query([
        'host' => $host,
        'date' => $date,
        'authorization' => $authrization
    ]);

    $client = new WebSocket\Client($url);
    
    $sender = new WSSender($client, $content, $config);
    $receiver = new WSReceiver($client);

    $sender->start();
    // $receiver->start();
    // $sender->join();
    // $receiver->join();
    
}

function ttsRequest2($content) {
    global $config;

    $appid = $config['appid'];
    $tts_api_key = $config['tts_api_key'];
    $tts_api_secret = $config['tts_api_secret'];
    $host = 'tts-api.xfyun.cn';
    $request_line = 'GET /v2/tts HTTP/1.1';
    $date = gmstrftime("%a, %d %b %Y %T %Z", time());

    $signature_origin = "host: $host\ndate: $date\n$request_line";
    $signature_sha = hash_hmac('sha256', $signature_origin, $tts_api_secret, true);
    $signature = base64_encode($signature_sha);

    $authrization = base64_encode("api_key=\"$tts_api_key\",algorithm=\"hmac-sha256\",headers=\"host date request-line\",signature=\"$signature\"");
    $url = 'wss://tts-api.xfyun.cn/v2/tts?' . http_build_query([
        'host' => $host,
        'date' => $date,
        'authorization' => $authrization
    ]);

    $client = new WebSocket\Client($url);
    
    $data = json_encode([
        'common' => [
            'app_id' => $appid
        ],
        'business' => [
            'aue' => 'lame',
            'sfl' => 1,
            'vcn' => 'xiaoyan',
            'tte' => "UTF8"
        ],
        'data' => [
            'text' => base64_encode($content),
            'status' => 2
        ]
    ]);
    $result = $client->send($data);
    while (true) {
        printf("接收数据ing...\n");
        $message = json_decode($client->receive());
        switch ($message->data->status) {
            case 1:
                $result .= base64_decode($message->data->audio);
                echo "目前正在合成，合成长度为：" . strlen($result) . "\n";
            break;
            case 2:
                $result .= base64_decode($message->data->audio);
                echo "合成已结束，总合成长度为：" . strlen($result) . "\n";
            break 2;
        }
    }
    file_put_contents("./" . time() . ".mp3", $result);
    
}