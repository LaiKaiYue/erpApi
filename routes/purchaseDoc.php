<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/28
 * Time: 下午1:33
 * 進貨單
 */

require_once "../controllers/purchaseDocController.php";
require_once "../common/url.php";
switch ($func) {
    case "getAllPurchase":
        $result = getAllPurchase();
        echo json_encode($result);
        break;
    case "getVendorNoPaymentPurchase":
        $result = getVendorNoPaymentPurchase();
        echo json_encode($result);
        break;
    case "getOnePurchase":
        $result = getOnePurchase();
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
//    case "UpdatePurchase":
//        $result = UpdatePurchase();
//        echo json_encode($result);
//        break;
//    case "DeletePurchase":
//        $result = DeletePurchase();
//        echo json_encode($result);
//        break;
//    case "DeleteAllPurchase":
//        $result = DeleteAllPurchase();
//        echo json_encode($result);
//        break;
    case "RemovePurchase_header":
        $result = RemovePurchase_header();
        echo json_encode($result);
        break;
    case "RemovePurchase_body":
        $result = RemovePurchase_body();
        echo json_encode($result);
        break;
    case "update_purchase_header_price":
        $result = update_purchase_header_price();
        echo json_encode($result);
        break;
    case "getAllPay":
        $result = getAllPay();
        echo json_encode($result);
        break;
    case "purchase_payment":
        $result = purchase_payment();
        echo json_encode($result);
        break;
}