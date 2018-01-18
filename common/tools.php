<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/6
 * Time: 下午3:55
 */

class tools {

    //產生單號
    public function genOrderNumber() {
        return date("YmdHis");
    }

    //產生時間
    public function genInsOrUpdDateTime() {
        return date("Y-m-d H:i:s");
    }

    public function trimRightZero($str){
        return rtrim(rtrim($str, '0'), '.');
    }
}

?>