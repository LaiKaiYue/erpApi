<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/3/6
 * Time: 下午9:11
 */

require_once "../controllers/stockController.php";
require_once "../common/url.php";

switch ($func) {
    case "getAllStock":
        $allStock = getAllStock();
        echo json_encode($allStock);
        break;
    case "getStockLastSN":
        $result = getStockLastSN();
        echo json_encode($result);
        break;
    case "getOneStock":
        $stock = getOneStock();
        echo json_encode($stock);
        break;
    case "getVendorProduct":
        $result = getVendorProduct();
        echo json_encode($result);
        break;
    case "getProductLeaderBoard":
        $result = getProductLeaderBoard();
        echo json_encode($result);
        break;
    case "getSalesProductLeaderBoard":
        $result = getSalesProductLeaderBoard();
        echo json_encode($result);
        break;
    case "getEnableStock":
        break;
    case "getDisableStock":
        break;
    case "getStockLastSN":
        $SN = getStockLastSN();
        echo json_encode($SN);
        break;
    case "InsertStock":
        $result = InsertStock();
        echo json_encode($result);
        break;
    case "UpdateStock":
        $result = UpdateStock();
        echo json_encode($result);
        break;
    case "DeleteStock":
        $result = DeleteStock();
        echo json_encode($result);
        break;
    case "DeleteAllStock":
        $result = DeleteAllStock();
        echo json_encode($result);
        break;
    case "getUnderSafeStock":
        $result = getUnderSafeStock();
        echo json_encode($result);
        break;
}