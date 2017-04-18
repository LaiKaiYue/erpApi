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
    case "getOnePurchase_body":
        $result = getOnePurchase_body();
        echo json_encode($result);
        break;
    case "getPurchaseLastOrderNumber":
        $result = getPurchaseLastOrderNumber();
        echo json_encode($result);
        break;
    case "InsertPurchase":
        $result = InsertPurchase();
        echo json_encode($result);
        break;
    case "UpdatePurchase":
        $result = UpdatePurchase();
        echo json_encode($result);
        break;
    case "DeletePurchase":
        $result = DeletePurchase();
        echo json_encode($result);
        break;
    case "DeleteAllPurchase":
        $result = DeleteAllPurchase();
        echo json_encode($result);
        break;
    case "RemovePurchase_header":
        $result = RemovePurchase_header();
        echo json_encode($result);
        break;
    case "RemovePurchase_body":
        $result = RemovePurchase_body();
        echo json_encode($result);
        break;
    case "update_header_price":
        $result = update_header_price();
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
function getOnePurchase()
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
?>