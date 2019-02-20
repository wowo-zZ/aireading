<?php
/**
 *
 * User: wowo
 * Date: 2019/2/20 上午9:45
 */
class DB {
    private $host;
    private $port;
    private $user;
    private $password;
    private $database;
    public $connection;

    public function __construct($config)
    {
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->user = $config['user'];
        $this->password = $config['password'];
        $this->database = $config['database'];
        $this->connection = mysqli_connect(
            $this->host,
            $this->user,
            $this->password,
            $this->database,
            $this->port
        );
    }

    public function select($sql)
    {
        $query = mysqli_query($this->connection, $sql);
        $result = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $result[] = $row;
        }
        return $result;
    }

    public function insert($save_path, $save_time, $oct_time, $tts_time, $tts_path, $content, $comment)
    {
        $sql = "insert into picture (`save_path`, `save_time`, `ocr_time`, `tts_time`, `tts_path`, `content`, `comment`) VALUE (
                    '$save_path', 
                    '$save_time', 
                    '$oct_time',
                    '$tts_time',
                    '$tts_path',
                    '$content',
                    '$comment'
                )";
        mysqli_query($this->connection, $sql);
    }
}
