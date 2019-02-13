<?php
// 设置文件保存路径
$upload_path = '/usr/local/var/www/aireading/upload';

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
$save_path = $upload_path . '/' . time() . '.jpeg';
move_uploaded_file($uploaded_path, $save_path);

// 存数据库
