<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/28
 * Time: 下午7:37
 */
//require_once "../model/db.php";

class stockInfo {
    /**
     * 扣商品庫存
     * @param $product_code 商品代號
     * @param $product_num 商品數量
     * @param $execSQL SQL語法
     */
    public function reduce_stock_num($product_code, $product_num, &$execSQL) {
        global $db;
        $result = $db->query("stockinfo", "code='$product_code'", "1", "stock_num");
        $stock_num = $result[0]["stock_num"];
        if ($stock_num > 0) {
            $stock_num -= (int)$product_num;
            $stock_num = ($stock_num < 0) ? 0 : $stock_num;
            $execSQL[] = "update stockInfo set stock_num='$stock_num' where code='$product_code'";
        }
    }

    /**
     * 增加商品庫存
     * @param $product_code 商品代號
     * @param $product_num 商品數量
     * @param $execSQL SQL語法
     */
    public function increase_stock_num($product_code, $product_num, &$execSQL) {
        global $db;
        $result = $db->query("stockinfo", "code='$product_code'", "1", "stock_num");
        $stock_num = $result[0]["stock_num"];
        $stock_num += (float)$product_num;
        $execSQL[] = "update stockInfo set stock_num='$stock_num' where code='$product_code'";
    }
}