<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/3/7
 * Time: 下午11:48
 */

require_once "../controllers/reportController.php";
require_once "../common/url.php";

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
}