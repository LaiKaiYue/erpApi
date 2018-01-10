<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/8
 * Time: 下午10:23
 */
header("Content-Type:text/html; charset=utf-8");
$postDT = json_decode(file_get_contents("php://input"), true);
$func = ($postDT["func"] == "") ? $_GET["func"] : $postDT["func"];