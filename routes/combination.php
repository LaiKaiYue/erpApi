<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2017/12/17
 * Time: 下午4:56
 */

require_once "../controllers/combinController.php";
require_once "../common/url.php";

switch ($func) {
    case "qryAllCombination":
        $result = qryAllCombination();
        echo json_encode($result);
        break;
    case "qryCombinationByStockCode":
        $result = qryCombinationByStockCode();
        echo json_encode($result);
        break;
    case "qryCombGroupNameByStockCode":
        $result = qryCombGroupNameByStockCode();
        echo json_encode($result);
        break;
    case "saveCombination":
        $result = saveCombination();
        echo json_encode($result);
        break;
    case "delCombinationByStockCode":
        $result = delCombinationByStockCode();
        echo json_encode($result);
        break;
    case "delCombinationByCombCode":
        $result = delCombinationByCombCode();
        echo json_encode($result);
        break;
}



