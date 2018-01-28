<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/28
 * Time: 下午6:09
 * 銷貨單
 */

require_once "../controllers/salesDocController.php";
require_once "../common/url.php";
switch ($func) {
    case "getAllSales":
        $result = getAllSales();
        echo json_encode($result);
        break;
    case "getCustomNoPaymentSales":
        $result = getCustomNoPaymentSales();
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
//    case "UpdateSales":
//        $result = UpdateSales();
//        echo json_encode($result);
//        break;
//    case "DeleteSales":
//        $result = DeleteSales();
//        echo json_encode($result);
//        break;
//    case "DeleteAllSales":
//        $result = DeleteAllSales();
//        echo json_encode($result);
//        break;
    case "RemoveSales_header":
        $result = RemoveSales_header();
        echo json_encode($result);
        break;
    case "RemoveSales_body":
        $result = RemoveSales_body();
        echo json_encode($result);
        break;
    case "update_sales_header_price":
        $result = update_sales_header_price();
        echo json_encode($result);
        break;
    case "getAllBill":
        $result = getAllBill();
        echo json_encode($result);
        break;
    case "sales_payment":
        $result = sales_payment();
        echo json_encode($result);
        break;
    case "qryLastSaleStockInfoByCustomCode":
        $result = qryLastSaleStockInfoByCustomCode();
        echo json_encode($result);
        break;
}