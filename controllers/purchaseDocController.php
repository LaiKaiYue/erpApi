<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/28
 * Time: 下午1:34
 */

require_once "../common/url.php";
require_once "../common/tools.php";
require_once "../model/db.php";
require_once "../vendor/autoload.php";
require_once "../common/stockInfo.php";

/**
 * 取所有進貨單
 */
function getAllPurchase() {
    global $db;
    $result = $db->execute("select order_number, vendor_code, vendor_name, create_date, payment_type, invoice_type,
                                  excluded_tax_total , tax, included_tax_total, status, remark 
                                  from purchase_header ORDER BY order_number ASC");
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 取廠商未結進貨單
 */
function getVendorNoPaymentPurchase() {
    global $db, $postDT;
    $vendor_code = $postDT["vendor_code"];
    $result = $db->execute("SELECT a.*, b.closing_date FROM purchase_header AS a 
                                INNER JOIN vendorsinfo AS b ON b.`code` = a.vendor_code 
                                where a.status = '0' and a.vendor_code = '$vendor_code'");
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 取單一進貨單
 */
function getOnePurchase() {
    global $db, $postDT;
    $order_number = $postDT["order_number"];
    $result = $db->query("purchase_header", "order_number = '$order_number'");
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 取進貨單商品資料
 */
function getOnePurchase_body() {
    global $db, $postDT;
    $order_number = $postDT["order_number"];
    $result = $db->query("purchase_body", "order_number='$order_number'");
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 產生進貨單號
 */
function getPurchaseLastOrderNumber() {
    global $db;
    $code = 0;
    $todayStr = date("Ymd");
    $result = $db->query("purchase_header", "1", "SN DESC", "order_number", "limit 1");
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
 * 新增進貨單
 */
function InsertPurchase() {
    global $db, $postDT;
    $stockInfo = new stockInfo();
    $product = json_decode(json_encode($postDT["product"]), true);

    // purchase header
    $order_number = $postDT["order_number"];
    $create_date = $postDT["create_date"];
    $vendor_code = $postDT["vendor_code"];
    $vendor_name = $postDT["vendor_name"];
    $payment_type = $postDT["payment_type"];
    $invoice_type = $postDT["invoice_type"];
    $excluded_tax_total = $postDT["excluded_tax_total"];
    $tax = $postDT["tax"];
    $included_tax_total = $postDT["included_tax_total"];
    $remark = $postDT["remark"];

    $execSQL = array();
    if ($payment_type == 0) {
        $execSQL[] = "insert into purchase_header(order_number, create_date, vendor_code, vendor_name, payment_type, invoice_type, excluded_tax_total, tax, included_tax_total, remark, status) VALUES
        ('$order_number', '$create_date', '$vendor_code', '$vendor_name', '$payment_type', '$invoice_type', '$excluded_tax_total', '$tax', '$included_tax_total', '$remark', '1')";
    }
    else {
        $execSQL[] = "insert into purchase_header(order_number, create_date, vendor_code, vendor_name, payment_type, invoice_type, excluded_tax_total, tax, included_tax_total, remark) VALUES
        ('$order_number', '$create_date', '$vendor_code', '$vendor_name', '$payment_type', '$invoice_type', '$excluded_tax_total', '$tax', '$included_tax_total', '$remark')";
    }

    // purchase body
    $productLength = count($product);
    for ($i = 0; $i < $productLength; $i++) {
        $product_order = $product[$i]["product_order"];
        $product_code = $product[$i]["product_code"];
        $product_name = $product[$i]["product_name"];
        $unit = $product[$i]["product_unit"];
        $product_num = $product[$i]["product_num"];
        $unit_cost = $product[$i]["unit_cost"];
        $excluded_sub_total = $product[$i]["excluded_tax_total"];
        $sub_tax = $product[$i]["tax"];
        $included_sub_total = $product[$i]["included_tax_total"];

        if ($payment_type == 0) {
            $execSQL[] = "insert into purchase_body (order_number, product_order, product_code, product_name, product_unit, product_num, unit_cost, excluded_tax_total, tax, included_tax_total, status) VALUES
            ('$order_number','$product_order', '$product_code', '$product_name', '$unit', '$product_num', '$unit_cost', '$excluded_sub_total', '$sub_tax', '$included_sub_total', '1')";
        }
        else {
            $execSQL[] = "insert into purchase_body (order_number, product_order, product_code, product_name, product_unit, product_num, unit_cost, excluded_tax_total, tax, included_tax_total) VALUES
            ('$order_number','$product_order', '$product_code', '$product_name', '$unit', '$product_num', '$unit_cost', '$excluded_sub_total', '$sub_tax', '$included_sub_total')";
        }

        //紀錄商品進貨次數 製作進貨排名用
        increase_leaderboard_count($product_code, $vendor_code, $product_name, $product_num, $execSQL);

        //增加產品庫存
        $stockInfo->increase_stock_num($product_code, $product_num, $execSQL);
    }
    $result = $db->transaction($execSQL);

    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 增加商品排行數量
 * @param $product_code 商品代號
 * @param $vendor_code 廠商代號
 */
function increase_leaderboard_count($product_code, $vendor_code, $product_name, $product_num, &$execSQL) {
    global $db;
    $result = $db->query("product_leaderboard", "product_code='$product_code' and vendor_code='$vendor_code'");
    if (count($result) > 0) {
        $count = $result[0]["count"];
        $count += (int)$product_num;
        $execSQL[] = "update product_leaderboard set count='$count' where product_code='$product_code' and vendor_code='$vendor_code'";
    }
    else {
        $execSQL[] = "insert into product_leaderboard (vendor_code, product_code, product_name) VALUES ('$vendor_code', '$product_code', '$product_name')";
    }
}

/**
 * 退整張單
 */
function RemovePurchase_header() {
    global $db, $postDT;
    $stockInfo = new stockInfo();
    $order_number = $postDT["order_number"];
    $type = $postDT["type"]; //0: 刪除，1: 退貨

    $result = $db->query("purchase_header", "order_number='$order_number'", "1", "vendor_code");
    $vendor_code = $result[0]["vendor_code"];

    //退貨後 減商品庫存
    $execSQL = array();
    $r1 = $db->query("purchase_body", "order_number = '$order_number'");
    foreach ($r1 as $item) {
        $product_code = $item["product_code"];
        $product_num = $item["product_num"];
        $status = $item["status"];

        //不是退貨扣庫存
        if ($status != 2) {
            //扣商品排名數量
            reduce_leaderboard_count($product_code, $vendor_code, $product_num, $execSQL);
            //扣庫存
            $stockInfo->reduce_stock_num($product_code, $product_num, $execSQL);
        }
    }

    if ($type == 0) {
        //Delete func
        $execSQL[] = "delete from purchase_header where order_number='$order_number'";
        $execSQL[] = "delete from purchase_body where order_number = '$order_number'";
    }
    else {
        //update func
        //將表頭改狀態為2(退貨)
        $execSQL[] = "update purchase_header set status='2' where order_number='$order_number'";
        //將所有商品狀態改為2(退貨)
        $execSQL[] = "update purchase_body set status='2' where order_number='$order_number'";
    }
    $result = $db->transaction($execSQL);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 退商品
 */
function RemovePurchase_body() {
    global $db, $postDT;
    $stockInfo = new stockInfo();
    $order_number = $postDT["order_number"];
    $pro_code = $postDT["product_code"];
    $pro_type = $postDT["type"]; //0: 刪除, 1: 結帳, 2: 退貨

    $dt = $db->query("purchase_header", "order_number='$order_number'", "1", "vendor_code");
    $vendor_code = $dt[0]["vendor_code"];

    $row = $db->query("purchase_body", "order_number='$order_number' and product_code='$pro_code'", "1", "product_num, status");
    $product_num = $row[0]["product_num"];
    $status = $row[0]["status"];
    $execSQL = array();
    //不是退貨扣庫存
    if ($status != 2) {
        //扣除商品進貨次數，製作進貨排名用
        reduce_leaderboard_count($pro_code, $vendor_code, $product_num, $execSQL);
        //扣庫存
        $stockInfo->reduce_stock_num($pro_code, $product_num, $execSQL);
    }

    if ($pro_type == 0) {
        //Delete func
        $execSQL[] = "delete from purchase_body where order_number='$order_number' and product_code='$pro_code'";
    }
    else {
        //update func
        $execSQL[] = "update purchase_body set status='2' where order_number='$order_number' and product_code='$pro_code'";
    }

    //    $productLength = count($RmProductAry);
    //    for ($i = 0; $i < $productLength; $i++) {
    //        $pro_code = $RmProductAry[$i]["product_code"];
    //        $pro_type = $RmProductAry[$i]["type"];
    //    }
    $result = $db->transaction($execSQL);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 扣商品排名數量
 * @param $pro_code 商品代號
 * @param $vendor_code 廠商代號
 */
function reduce_leaderboard_count($pro_code, $vendor_code, $product_num, &$execSQL) {
    global $db;
    $result = $db->query("product_leaderboard", "product_code='$pro_code' and vendor_code='$vendor_code'");
    if (count($result) > 0) {
        $count = $result[0]["count"];
        if ($count > 0) {
            $count -= (int)$product_num;
            $count = ($count < 0) ? 0 : $count;
            $execSQL[] = "update product_leaderboard set count='$count' where product_code='$pro_code' and vendor_code='$vendor_code'";
        }
    }
}

/**
 * 更新進貨單金額
 */
function update_purchase_header_price() {
    global $db, $postDT;
    $order_number = $postDT["order_number"];
    $excluded_tax_total = $postDT["excluded_tax_total"];
    $tax = $postDT["tax"];
    $included_tax_total = $postDT["included_tax_total"];

    $data_array = array(
        "excluded_tax_total" => $excluded_tax_total,
        "tax" => $tax,
        "included_tax_total" => $included_tax_total
    );
    $cond["order_number"] = $order_number;
    $result = $db->update("purchase_header", $data_array, $cond);

    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 取得所有未沖款進貨單
 */
function getAllPay() {
    global $db;
    $result = $db->execute("select a.*, b.closing_date from purchase_header a, vendorsInfo b where a.vendor_code = b.code and a.status = '0' ORDER BY a.order_number ASC");
    return $result === false ? $db->getErrorMessage() : $result;
}

// 進貨單結帳
function purchase_payment() {
    global $db, $postDT;
    $data["status"] = "1";
    $cond["order_number"] = $postDT["order_number"];
    $result = $db->update("purchase_header", $data, $cond);
    return $result === false ? $db->getErrorMessage() : $result;
}