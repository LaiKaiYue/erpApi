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
    case "getProductAnalysis":
        $result = getProductAnalysis();
        echo json_encode($result);
        break;
    case "getCustomAnalysis":
        $result = getCustomAnalysis();
        echo json_encode($result);
        break;
    case "getSalesReportInfo":
        $result = getSalesReportInfo();
        echo json_encode($result);
        break;
            case "getSalesTotal":
        $result = getSalesTotal();
        echo json_encode($result);
        break;
    default:
        $value = "An error has occurred";
        exit(json_encode($value));
        break;
}

// 取商品分析
function getProductAnalysis(){
    global $link, $postDT;
    $product_code = $postDT["product_code"];
    $sql = "select a.custom_code, a.custom_name, sum(b.product_num) as count, a.create_date from sales_header a, sales_body b  
    where a.order_number = b.order_number 
    and b.product_code = '$product_code'
    GROUP BY a.custom_code
    order by count desc";

    $result = $link->query($sql);
    while($row = $result->fetch_array(MYSQLI_ASSOC)){
        $data[] = array(
            "custom_code" => $row["custom_code"],
            "custom_name" => $row["custom_name"],
            "count" => $row["count"],
            "create_date" => $row["create_date"]
        );
    }
    $link->close();
    return $data;
}

// 取客戶分析
function getCustomAnalysis(){
    global $link, $postDT;
    $custom_code = $postDT["custom_code"];
    $sql = "select b.product_code, b.product_name, sum(b.product_num) as count, a.create_date from sales_header a, sales_body b  
    where a.order_number = b.order_number 
    and a.custom_code = '$custom_code'
    GROUP BY b.product_code
    order by count desc";

    $result = $link->query($sql);
    while($row = $result->fetch_array(MYSQLI_ASSOC)){
        $data[] = array(
            "product_code" => $row["product_code"],
            "product_name" => $row["product_name"],
            "count" => $row["count"],
            "create_date" => $row["create_date"]
        );
    }
    $link->close();
    return $data;
}

// 取報表資訊
function getSalesReportInfo(){
    global $link, $postDT;
    $order_number = $postDT["order_number"];
    $sql = "select mn.create_date, mn.order_number, category.`Name` as category, dt.product_code, dt.product_name, dt.product_num, dt.selling_price, dt.tax, dt.included_tax_total, mn.ship_nos
    from sales_header mn, sales_body dt, stockinfo product, product_category category 
    where mn.order_number = dt.order_number 
    and dt.product_code = product.`code` 
    and product.category = category.SN 
    and mn.order_number in($order_number)";

    $result = $link->query($sql);
    while($row = $result->fetch_array(MYSQLI_ASSOC)){
        $data[] = array(
            "create_date" => $row["create_date"],
            "order_number" => $row["order_number"],
            "category" => $row["category"],
            "product_code" => $row["product_code"],
            "product_name" => $row["product_name"],
            "product_num" => $row["product_num"],
            "selling_price" => $row["selling_price"],
            "tax" => $row["tax"],
            "included_tax_total" => $row["included_tax_total"],
            "ship_nos" => $row["ship_nos"],
        );
    }
    $link->close();
    return $data;
}

// 取得應收帳款
function getSalesTotal(){
    global $link, $postDT;
    $order_number = $postDT["order_number"];
    $sql = "select sum(included_tax_total) as total from sales_header 
    where order_number in($order_number)";

    $result = $link->query($sql);
    $row = mysqli_fetch_array($result);
    $total = $row["total"];

    return $total;
}

?>