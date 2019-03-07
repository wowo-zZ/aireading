<?php

/**
 *
 * User: wowo
 * Date: 2019/3/7 上午10:54
 */
class DB_Sqlite
{

    private $sqlite;

    public function __construct($file_path)
    {
        $this->sqlite = new SQLite3($file_path);
    }

    public function select($sql)
    {
        $results = $this->sqlite->query($sql);
        $return = [];
        while ($row = $results->fetchArray()) {
            $return[] = $row;
        }
        return $return;
    }

    public function insert($save_path, $save_time, $oct_time, $tts_time, $tts_path, $content, $comment)
    {
        $sql = "insert into picture (save_path, save_time, ocr_time, tts_time, tts_path, content, comment) VALUES (
                    '$save_path', 
                    '$save_time', 
                    '$oct_time',
                    '$tts_time',
                    '$tts_path',
                    '$content',
                    '$comment'
                )";
        $this->sqlite->exec($sql);
    }
}