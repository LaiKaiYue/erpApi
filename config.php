<?php

/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 2016/12/15
 * Time: 下午 10:08
 */
$config = parse_ini_file('./config.ini', true); // Assuming your php is in the root directory of your web server, so placing the file where it can't be seen by prying eyes!
$dbhost = $config['database']['host'];
$dbuser = $config['database']['username'];
$dbpass = $config['database']['password'];
$dbname = $config['database']['dbname'];

$backup_dbhost = $config['backupDB']['host'];
$backup_dbuser = $config['backupDB']['username'];
$backup_dbpass = $config['backupDB']['password'];
$backup_dbname = $config['backupDB']['dbname'];


//設定時區
date_default_timezone_set('Asia/Taipei');

$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
mysqli_set_charset($link,'utf8');

if (!$link) {
    $content = "Error: Unable to connect to MySQL." . PHP_EOL;
    $content .= "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    $content .= "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    echo json_encode(array("success"=>false, "msg"=> $content));
    exit;
}

$postDT = json_decode(file_get_contents("php://input"), true);
$func = ($postDT["func"] == "") ? $_GET["func"] : $postDT["func"];

?>