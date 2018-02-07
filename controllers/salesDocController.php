<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/28
 * Time: 下午6:11
 */

require_once "../common/url.php";
require_once "../common/tools.php";
require_once "../common/stockInfo.php";
require_once "../model/db.php";
require_once "../vendor/autoload.php";

/**
 * 取所有銷貨單
 */
function getAllSales() {
    global $db;
    $result = $db->query("sales_header", "1", "order_number ASC");
    return $result === false ? $db->getErrorMessage() : $result;
}

// 取客戶未結進貨單
function getCustomNoPaymentSales() {
    global $db, $postDT;
    $custom_code = $postDT["custom_code"];
    $result = $db->execute("SELECT a.*, b.closing_date FROM sales_header AS a 
                                INNER JOIN customerinfo AS b ON b.`code` = a.custom_code 
                                where a.status = '0' and a.custom_code = '$custom_code'");
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 取單一進銷貨單
 */
function getOneSales() {
    global $db, $postDT;
    $order_number = $postDT["order_number"];
    $result = $db->query("sales_header", "order_number='$order_number'");
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 取銷貨單商品資料
 */
function getOneSales_body() {
    global $db, $postDT;
    $order_number = $postDT["order_number"];
    $result = $db->query("sales_body", "order_number='$order_number'");
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 產生銷貨單號
 */
function getSalesLastOrderNumber() {
    global $db;

    $code = 0;

    $todayStr = date("Ymd");

    $result = $db->query("sales_header", "1", "SN DESC", "order_number", "limit 1");
    $order_number = $result[0]["order_number"];
    $code = substr($order_number, -3); //取末三碼
    $code = (int)$code;
    $code++;
    if ($code < 10) $code = "00".$code;
    else if ($code < 100) $code = "0".$code;

    $order_number = $todayStr.$code;

    return $order_number;
}

/**
 * 新增銷貨單
 */
function InsertSales() {
    global $db, $postDT;
    $stockInfo = new stockInfo();
    $product = json_decode(json_encode($postDT["product"]), true);

//    Sales header
    $order_number = $postDT["order_number"];
    $ship_nos = $postDT["ship_nos"];
    $create_date = $postDT["create_date"];
    $custom_code = $postDT["custom_code"];
    $custom_name = $postDT["custom_name"];
    $payment_type = $postDT["payment_type"];
    $invoice_type = $postDT["invoice_type"];
    $excluded_tax_total = $postDT["excluded_tax_total"];
    $tax = $postDT["tax"];
    $included_tax_total = $postDT["included_tax_total"];
    $remark = $postDT["remark"];
    $execSQL = array();
    if ($payment_type == 0) {
        $execSQL[] = "insert into sales_header(order_number, ship_nos, create_date, custom_code, custom_name, payment_type, invoice_type, excluded_tax_total, tax, included_tax_total, remark, status) VALUES
        ('$order_number', '$ship_nos', '$create_date', '$custom_code', '$custom_name', '$payment_type', '$invoice_type', '$excluded_tax_total', '$tax', '$included_tax_total', '$remark', '1')";
    }
    else {
        $execSQL[] = "insert into sales_header(order_number, ship_nos, create_date, custom_code, custom_name, payment_type, invoice_type, excluded_tax_total, tax, included_tax_total, remark) VALUES
        ('$order_number', '$ship_nos', '$create_date', '$custom_code', '$custom_name', '$payment_type', '$invoice_type', '$excluded_tax_total', '$tax', '$included_tax_total', '$remark')";
    }

    // Sales body
    $productLength = count($product);
    for ($i = 0; $i < $productLength; $i++) {
        $product_order = $product[$i]["product_order"];
        $product_code = $product[$i]["product_code"];
        $product_name = $product[$i]["product_name"];
        $unit = $product[$i]["product_unit"];
        $product_num = $product[$i]["product_num"];
        $selling_price = $product[$i]["selling_price"];
        $excluded_sub_total = $product[$i]["excluded_tax_total"];
        $sub_tax = $product[$i]["tax"];
        $included_sub_total = $product[$i]["included_tax_total"];
        $discount_type = $product[$i]["discount_type"];

        if ($payment_type == 0) {
            $execSQL[] = "insert into sales_body (order_number, product_order, product_code, product_name, product_unit, product_num, selling_price, excluded_tax_total, tax, included_tax_total, discount_type, status) VALUES
            ('$order_number','$product_order', '$product_code', '$product_name', '$unit', '$product_num', '$selling_price', '$excluded_sub_total', '$sub_tax', '$included_sub_total', '$discount_type', '1')";
        }
        else {
            $execSQL[] = "insert into sales_body (order_number, product_order, product_code, product_name, product_unit, product_num, selling_price, excluded_tax_total, tax, included_tax_total, discount_type) VALUES
            ('$order_number','$product_order', '$product_code', '$product_name', '$unit', '$product_num', '$selling_price', '$excluded_sub_total', '$sub_tax', '$included_sub_total', '$discount_type')";
        }

        //紀錄商品進貨次數 製作進貨排名用
        increase_sales_leaderboard_count($product_code, $custom_code, $product_name, $product_num, $execSQL);
        //減少產品庫存
        $stockInfo->reduce_stock_num($product_code, $product_num, $execSQL);
    }
    $result = $db->transaction($execSQL);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 增加商品排行數量
 * @param $product_code 商品代號
 * @param $custom_code 客戶代號
 */
function increase_sales_leaderboard_count($product_code, $custom_code, $product_name, $product_num, &$execSQL) {
    global $db;
    $result = $db->query("sales_leaderboard", "product_code='$product_code' and custom_code='$custom_code'");

    if (count($result) > 0) {
        $count = $result[0]["count"];
        $count += (int)$product_num;
        $execSQL[] = "update sales_leaderboard set count='$count' where product_code='$product_code' and custom_code='$custom_code'";
    }
    else {
        $execSQL[] = "insert into sales_leaderboard (custom_code, product_code, product_name) VALUES ('$custom_code', '$product_code', '$product_name')";
    }
}

/**
 * 退整張單
 */
function RemoveSales_header() {
    global $db, $postDT;
    $stockInfo = new stockInfo();
    $order_number = $postDT["order_number"];
    $type = $postDT["type"]; //0: 刪除，1: 退貨

    $dt = $db->query("sales_header", "order_number='$order_number'", "1", "custom_code");
    $custom_code = $dt[0]["custom_code"];

    //退貨後 減商品庫存
    $execSQL = array();
    $result = $db->query("sales_body", "order_number = '$order_number'");
    foreach($result as $row){
        $product_code = $row["product_code"];
        $product_num = $row["product_num"];
        $status = $row["status"];

        //不是退貨扣庫存
        if ($status != 2) {
            //扣商品排名數量
            reduce_sales_leaderboard_count($product_code, $custom_code, $product_num, $execSQL);
            //加庫存
            $stockInfo->increase_stock_num($product_code, $product_num, $execSQL);
        }

    }

    if ($type == 0) {
        //Delete func
        $execSQL[] = "delete from sales_header where order_number='$order_number'";
        $execSQL[] = "delete from sales_body where order_number = '$order_number'";
    }
    else {
        //update func
        //將表頭改狀態為2(退貨)
        $execSQL[] = "update sales_header set status='2' where order_number='$order_number'";
        //將所有商品狀態改為2(退貨)
        $execSQL[] = "update sales_body set status='2' where order_number='$order_number'";
    }
    $result = $db->transaction($execSQL);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 退商品
 */
function RemoveSales_body() {
    global $db, $postDT;
    $stockInfo = new stockInfo();
    $order_number = $postDT["order_number"];
    $pro_code = $postDT["product_code"];
    $pro_type = $postDT["type"]; //0: 刪除，1: 退貨

    $dt = $db->query("sales_header", "order_number='$order_number'", "1", "custom_code");
    $custom_code = $dt[0]["custom_code"];

    $row = $db->query("sales_body", "order_number='$order_number' and product_code='$pro_code'", "1", "product_num, status");
    $product_num = $row[0]["product_num"];
    $status = $row[0]["status"];
    $execSQL = array();
    //不是退貨扣庫存
    if ($status != 2) {
        //扣除商品進貨次數，製作進貨排名用
        reduce_sales_leaderboard_count($pro_code, $custom_code, $product_num, $execSQL);
        //加庫存
        $stockInfo->increase_stock_num($pro_code, $product_num, $execSQL);
    }

    if ($pro_type == 0) {
        //Delete func
        $execSQL[] = "delete from sales_body where order_number='$order_number' and product_code='$pro_code'";
    }
    else {
        //update func
        $execSQL[] = "update sales_body set status='2' where order_number='$order_number' and product_code='$pro_code'";
    }

    $result = $db->transaction($execSQL);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 扣商品排名數量
 * @param $product_code 商品代號
 * @param $custom_code 客戶代號
 */
function reduce_sales_leaderboard_count($product_code, $custom_code, $product_num, &$execSQL) {
    global $db;
    $row = $db->query("sales_leaderboard", "product_code='$product_code' and custom_code='$custom_code'");

    if (count($row) > 0) {
        $count = $row[0]["count"];
        if ($count > 0) {
            $count -= (int)$product_num;
            $count = ($count < 0) ? 0 : $count;
            $execSQL[] = "update sales_leaderboard set count='$count' where product_code='$product_code' and custom_code='$custom_code'";
        }
    }
}

/**
 * 更新銷貨單金額
 */
function update_sales_header_price() {
    global $db, $postDT;
    $order_number = $postDT["order_number"];
    $excluded_tax_total = $postDT["excluded_tax_total"];
    $tax = $postDT["tax"];
    $included_tax_total = $postDT["included_tax_total"];

    $data = array(
        "excluded_tax_total" => $excluded_tax_total,
        "tax" => $tax,
        "included_tax_total" => $included_tax_total
    );
    $cond["order_number"] = $order_number;
    $result = $db->update("sales_header", $data, $cond);

    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 取得所有未沖款銷貨單
 */
function getAllBill() {
    global $db;
    $result = $db->execute("select a.*, b.closing_date from sales_header a, customerInfo b where a.custom_code = b.code and a.status = '0' ORDER BY a.order_number ASC");
    return $result === false ? $db->getErrorMessage() : $result;
}

// 銷貨單結帳
function sales_payment() {
    global $db, $postDT;
    $data["status"] = "1";
    $cond["order_number"] = $postDT["order_number"];
    $result = $db->update("sales_header", $data, $cond);

    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 查詢此客戶最新一次銷貨商品資訊
 * @param string custom_code 客戶代號
 * @param string stock_code 商品代號
 * @return array|bool|string
 */
function qryLastSaleStockInfoByCustomCode(){
    global $db, $postDT;
    $custom_code = $postDT["custom_code"];
    $stock_code = $postDT["stock_code"];

    $result = $db->execute("select dt.discount_type, dt.selling_price from sales_header mn INNER JOIN sales_body dt ON mn.order_number = dt.order_number
                              WHERE mn.custom_code = '$custom_code' and dt.product_code = '$stock_code' ORDER BY dt.SN DESC limit 1");
    return $result === false ? $db->getErrorMessage() : $result[0];

}