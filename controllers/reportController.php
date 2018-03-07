<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/3/7
 * Time: 下午11:51
 */
require_once "../common/url.php";
require_once "../common/tools.php";
require_once "../model/db.php";

// 取商品分析
function getProductAnalysis() {
    global $db, $postDT;
    $product_code = $postDT["product_code"];

    $result = $db->execute("select a.custom_code, a.custom_name, sum(b.product_num) as count, a.create_date from sales_header a, sales_body b  
    where a.order_number = b.order_number 
    and b.product_code = '$product_code'
    GROUP BY a.custom_code
    order by `count` desc");
    return $result === false ? $db->getErrorMessage() : $result;
}

// 取客戶分析
function getCustomAnalysis() {
    global $db, $postDT;
    $custom_code = $postDT["custom_code"];
    $result = $db->execute("select b.product_code, b.product_name, sum(b.product_num) as count, a.create_date from sales_header a, sales_body b  
    where a.order_number = b.order_number 
    and a.custom_code = '$custom_code'
    GROUP BY b.product_code
    order by `count` desc");

    return $result === false ? $db->getErrorMessage() : $result;
}

// 取報表資訊
function getSalesReportInfo() {
    global $db, $postDT;
    $order_number = $postDT["order_number"];
    $result = $db->execute("select mn.create_date, mn.order_number, category.`Name` as category, dt.product_code, dt.product_name, 
    dt.product_num, dt.discount_type, dt.selling_price, dt.tax, dt.excluded_tax_total, dt.included_tax_total, mn.ship_nos
    from sales_header mn, sales_body dt, stockinfo product, product_category category 
    where mn.order_number = dt.order_number 
    and dt.product_code = product.`code` 
    and product.category = category.SN 
    and mn.order_number in ($order_number)");

    return $result === false ? $db->getErrorMessage(): $result;
}

// 取得應收帳款
function getSalesTotal() {
    global $db, $postDT;
    $order_number = $postDT["order_number"];
    $result = $db->execute("select sum(included_tax_total) as total from sales_header 
    where order_number in($order_number)");
    $total = $result[0]["total"];

    return $total;
}