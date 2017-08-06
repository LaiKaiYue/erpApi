<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2017/4/16
 * Time: 下午 10:00
 */

header("Content-Type:text/html; charset=utf-8");
require_once "config.php";

switch ($func) {
    case "getAllSales":
        $result = getAllSales();
        echo json_encode($result);
        break;
    case "getOneSales":
        $result = getOneSales();
        echo json_encode($result);
        break;
    case "getOneSales_body":
        $result = getOneSales_body();
        echo json_encode($result);
        break;
    case "getSalesLastOrderNumber":
        $result = getSalesLastOrderNumber();
        echo json_encode($result);
        break;
    case "InsertSales":
        $result = InsertSales();
        echo json_encode($result);
        break;
    case "UpdateSales":
        $result = UpdateSales();
        echo json_encode($result);
        break;
    case "DeleteSales":
        $result = DeleteSales();
        echo json_encode($result);
        break;
    case "DeleteAllSales":
        $result = DeleteAllSales();
        echo json_encode($result);
        break;
    case "RemoveSales_header":
        $result = RemoveSales_header();
        echo json_encode($result);
        break;
    case "RemoveSales_body":
        $result = RemoveSales_body();
        echo json_encode($result);
        break;
    case "update_header_price":
        $result = update_header_price();
        echo json_encode($result);
        break;
    case "payment":
        $result = payment();
        echo json_encode($result);
        break;
    default:
        $value = "An error has occurred";
        exit(json_encode($value));
        break;
}

/**
 * 取所有銷貨單
 */
function getAllSales()
{
    global $link;

    $s1 = "select * from sales_header ORDER BY order_number ASC";
    $r1 = $link->query($s1);
    while ($row = $r1->fetch_array(MYSQLI_ASSOC)) {
        $data[] = array(
            "order_number" => $row["order_number"],
            "custom_code" => $row["custom_code"],
            "custom_name" => $row["custom_name"],
            "create_date" => $row["create_date"],
            "payment_type" => $row["payment_type"],
            "invoice_type" => $row["invoice_type"],
            "excluded_tax_total" => $row["excluded_tax_total"],
            "tax" => $row["tax"],
            "included_tax_total" => $row["included_tax_total"],
            "status" => $row["status"],
            "remark" => $row["remark"]
        );
    }
    $link->close();
    return $data;
}

/**
 * 取單一進銷貨單
 */
function getOneSales()
{
    global $link, $postDT;
    $order_number = $postDT["order_number"];
    $s1 = "select * from sales_header where order_number = '$order_number'";
    $r1 = $link->query($s1);
    while ($row = $r1->fetch_array(MYSQLI_ASSOC)) {
        $data[] = array(
            "order_number" => $row["order_number"],
            "custom_code" => $row["custom_code"],
            "custom_name" => $row["custom_name"],
            "create_date" => $row["create_date"],
            "payment_type" => $row["payment_type"],
            "invoice_type" => $row["invoice_type"],
            "excluded_tax_total" => $row["excluded_tax_total"],
            "tax" => $row["tax"],
            "included_tax_total" => $row["included_tax_total"],
            "status" => $row["status"],
            "remark" => $row["remark"]
        );
    }
    $link->close();
    return $data;
}

/**
 * 取銷貨單商品資料
 */
function getOneSales_body()
{
    global $link, $postDT;
    $order_number = $postDT["order_number"];
    $s = "SELECT * FROM sales_body WHERE order_number='$order_number'";
    $r = $link->query($s);
    while ($row = $r->fetch_array(MYSQLI_ASSOC)) {
        $data[] = array(
            "order_number" => $row["order_number"],
            "product_order" => $row["product_order"],
            "product_code" => $row["product_code"],
            "product_name" => $row["product_name"],
            "product_unit" => $row["product_unit"],
            "product_num" => $row["product_num"],
            "unit_cost" => $row["unit_cost"],
            "excluded_tax_total" => $row["excluded_tax_total"],
            "tax" => $row["tax"],
            "included_tax_total" => $row["included_tax_total"],
            "status" => $row["status"]
        );
    }
    $link->close();
    return $data;
}

/**
 * 產生銷貨單號
 */
function getSalesLastOrderNumber()
{
    global $link;

    $code = 0;

    $todayStr = date("Ymd");

    $sql = "select order_number from sales_header order by SN DESC limit 1";
    $result = $link->query($sql);
    $row = mysqli_fetch_array($result);
    $order_number = $row["order_number"];
    $code = substr($order_number, -3); //取末三碼
    $code = (int)$code;
    $code++;
    if ($code < 10) $code = "00" . $code;
    else if ($code < 100) $code = "0" . $code;

    $order_number = $todayStr . $code;

    return $order_number;
}

/**
 * 新增銷貨單
 */
function InsertSales()
{
    global $link, $postDT;
    $product = json_decode(json_encode($postDT["product"]), true);

//    Sales header
    $order_number = $postDT["order_number"];
    $create_date = $postDT["create_date"];
    $custom_code = $postDT["custom_code"];
    $custom_name = $postDT["custom_name"];
    $payment_type = $postDT["payment_type"];
    $invoice_type = $postDT["invoice_type"];
    $excluded_tax_total = $postDT["excluded_tax_total"];
    $tax = $postDT["tax"];
    $included_tax_total = $postDT["included_tax_total"];
    $remark = $postDT["remark"];

    $s1 = "insert into sales_header(order_number, create_date, custom_code, custom_name, payment_type, invoice_type, excluded_tax_total, tax, included_tax_total, remark) VALUES
('$order_number', '$create_date', '$custom_code', '$custom_name', '$payment_type', '$invoice_type', '$excluded_tax_total', '$tax', '$included_tax_total', '$remark')";
    $r1 = $link->query($s1);

    // Sales body
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

        $s2 = "insert into sales_body (order_number, product_order, product_code, product_name, product_unit, product_num, unit_cost, excluded_tax_total, tax, included_tax_total) VALUES
('$order_number','$product_order', '$product_code', '$product_name', '$unit', '$product_num', '$unit_cost', '$excluded_sub_total', '$sub_tax', '$included_sub_total')";
        $r2 = $link->query($s2);

        //紀錄商品進貨次數 製作進貨排名用
        increase_leaderboard_count($product_code, $custom_code, $product_name, $product_num);

        //增加產品庫存
        increase_stock_num($product_code, $product_num);
    }
    $link->close();

    return $r2;
}

/**
 * 增加商品排行數量
 * @param $product_code 商品代號
 * @param $custom_code 客戶代號
 */
function increase_leaderboard_count($product_code, $custom_code, $product_name, $product_num)
{
    global $link;
    $s3 = "select * from product_leaderboard where product_code='$product_code' and vendor_code='$custom_code'";
    $r3 = $link->query($s3);
    if ($r3->num_rows > 0) {
        $row = $r3->fetch_array(MYSQLI_ASSOC);
        $count = $row["count"];
        $count += (int)$product_num;
        $s4 = "update product_leaderboard set count='$count' where product_code='$product_code' and vendor_code='$custom_code'";
        $r4 = $link->query($s4);
    } else {
        $s4 = "insert into product_leaderboard (vendor_code, product_code, product_name) VALUES ('$custom_code', '$product_code', '$product_name')";
        $r4 = $link->query($s4);
    }
}

/**
 * 加庫存
 */
function increase_stock_num($product_code, $product_num)
{
    global $link;
    $s1 = "select stock_num from stockInfo where code='$product_code'";
    $r1 = $link->query($s1);
    $row = $r1->fetch_array(MYSQLI_ASSOC);
    $stock_num = $row["stock_num"];
    $stock_num += (int)$product_num;

    $s2 = "update stockInfo set stock_num='$stock_num' where code='$product_code'";
    $r2 = $link->query($s2);
}

/**
 * 退整張單
 */
function RemoveSales_header()
{
    global $link, $postDT;
    $order_number = $postDT["order_number"];
    $type = $postDT["type"]; //0: 刪除，1: 退貨

    $sql = "select custom_code from sales_header where order_number='$order_number'";
    $result = $link->query($sql);
    $dt = $result->fetch_array(MYSQLI_ASSOC);
    $custom_code = $dt["custom_code"];

    //退貨後 減商品庫存
    $s1 = "select * from sales_body where order_number = '$order_number'";
    $r1 = $link->query($s1);
    while ($row = $r1->fetch_array(MYSQLI_ASSOC)) {
        $product_code = $row["product_code"];
        $product_num = $row["product_num"];
        $status = $row["status"];

        //不是退貨扣庫存
        if($status != 2){
            //扣商品排名數量
            reduce_leaderboard_count($product_code, $custom_code, $product_num);
            //扣庫存
            reduce_stock_num($product_code, $product_num);
        }

    }

    if ($type == 0) {
        //Delete func
        $s = "delete from sales_header where order_number='$order_number'";
        $r = $link->query($s);

        $s2 = "delete from sales_body where order_number = '$order_number'";
        $r2 = $link->query($s2);
    } else {
        //update func
        //將表頭改狀態為2(退貨)
        $s = "update sales_header set status='2' where order_number='$order_number'";
        $r = $link->query($s);
        //將所有商品狀態改為2(退貨)
        $s2 = "update sales_body set status='2' where order_number='$order_number'";
        $r2 = $link->query($s2);
    }
    $link->close();
    return $r2;
}

/**
 * 退商品
 */
function RemoveSales_body()
{
    global $link, $postDT;
    $order_number = $postDT["order_number"];
    $pro_code = $postDT["product_code"];
    $pro_type = $postDT["type"]; //0: 刪除，1: 退貨
//    $RmProductAry = json_decode(json_encode($postDT["RmProductAry"]), true);

    $s = "select custom_code from sales_header where order_number='$order_number'";
    $r = $link->query($s);
    $dt = $r->fetch_array(MYSQLI_ASSOC);
    $custom_code = $dt["custom_code"];

    $s2 = "select product_num, status from sales_body where order_number='$order_number' and product_code='$pro_code'";
    $r2 = $link->query($s2);
    $row = $r2->fetch_array(MYSQLI_ASSOC);
    $product_num = $row["product_num"];
    $status = $row["status"];

    //不是退貨扣庫存
    if($status != 2){
        //扣除商品進貨次數，製作進貨排名用
        reduce_leaderboard_count($pro_code, $custom_code, $product_num);
        //扣庫存
        reduce_stock_num($pro_code, $product_num);
    }

    if ($pro_type == 0) {
        //Delete func
        $s1 = "delete from sales_body where order_number='$order_number' and product_code='$pro_code'";
        $r1 = $link->query($s1);
    } else {
        //update func
        $s1 = "update sales_body set status='2' where order_number='$order_number' and product_code='$pro_code'";
        $r1 = $link->query($s1);
    }

    //    $productLength = count($RmProductAry);
    //    for ($i = 0; $i < $productLength; $i++) {
    //        $pro_code = $RmProductAry[$i]["product_code"];
    //        $pro_type = $RmProductAry[$i]["type"];
    //    }

    $link->close();
    return $r1;
}

/**
 * 扣商品排名數量
 * @param $pro_code 商品代號
 * @param $custom_code 客戶代號
 */
function reduce_leaderboard_count($pro_code, $custom_code, $product_num)
{
    global $link;
    $s2 = "select * from product_leaderboard where product_code='$pro_code' and vendor_code='$custom_code'";
    $r2 = $link->query($s2);
    if ($r2->num_rows > 0) {
        $row = $r2->fetch_array(MYSQLI_ASSOC);
        $count = $row["count"];
        if ($count > 0) {
            $count -= (int)$product_num;
            $count = ($count < 0) ? 0 : $count;
            $s3 = "update product_leaderboard set count='$count' where product_code='$pro_code' and vendor_code='$custom_code'";
            $r3 = $link->query($s3);
        }
    }
}

/**
 * 扣商品庫存
 * @param $pro_code 商品代號
 */
function reduce_stock_num($pro_code, $product_num)
{
    global $link;
    $s2 = "select stock_num from stockInfo where code='$pro_code'";
    $r2 = $link->query($s2);
    $row = $r2->fetch_array(MYSQLI_ASSOC);
    $stock_num = $row["stock_num"];
    if ($stock_num > 0) {
        $stock_num -= (int)$product_num;
        $stock_num = ($stock_num < 0) ? 0 : $stock_num;
        $s3 = "update stockInfo set stock_num='$stock_num' where code='$pro_code'";
        $r3 = $link->query($s3);
    }
}

/**
 * 更新銷貨單金額
 */
function update_header_price()
{
    global $link, $postDT;
    $order_number = $postDT["order_number"];
    $excluded_tax_total = $postDT["excluded_tax_total"];
    $tax = $postDT["tax"];
    $included_tax_total = $postDT["included_tax_total"];

    $sql = "update sales_header set excluded_tax_total='$excluded_tax_total', tax='$tax', included_tax_total='$included_tax_total' where order_number='$order_number'";
    $result = $link->query($sql);

    $link->close();
    return $result;
}

// 結帳
function payment(){
    global $link, $postDT;
    $sql = "update sales_header set status='1' where order_number = '" . $postDT["order_number"] . "'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

?>