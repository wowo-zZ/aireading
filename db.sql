CREATE TABLE `picture` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `save_path` varchar(64) DEFAULT NULL COMMENT '图片存储地址',
  `save_time` int(11) DEFAULT NULL COMMENT '图片存储时间',
  `ocr_time` int(11) DEFAULT NULL COMMENT 'ocr识别时间',
  `tts_time` int(11) DEFAULT NULL COMMENT '语音合成时间',
  `content` text COMMENT 'ocr识别内容',
  `tts_path` varchar(64) DEFAULT NULL COMMENT '语音合成结果存储地址',
  `comment` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;