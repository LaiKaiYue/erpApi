<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/8
 * Time: 下午10:07
 */

$files = glob('*.{php}', GLOB_BRACE);
foreach ($files as $file) {
    if ($file != "service.php") {
        require_once $file;
    }
}