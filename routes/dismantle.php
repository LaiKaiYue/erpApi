<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2017/12/21
 * Time: 下午11:14
 */

require_once "../controllers/dismantleController.php";
require_once "../common/url.php";

switch ($func) {
    case "qryAllDismantle":
        $result = qryAllDismantle();
        echo json_encode($result);
        break;
    case "qryDismantleByStockCode":
        $result = qryDismantleByStockCode();
        echo json_encode($result);
        break;
    case "qryDismGroupNameByStockCode":
        $result = qryDismGroupNameByStockCode();
        echo json_encode($result);
        break;
    case "saveDismantle":
        $result = saveDismantle();
        echo json_encode($result);
        break;
    case "delDismantleByStockCode":
        $result = delDismantleByStockCode();
        echo json_encode($result);
        break;
    case "delDismantleByDismCode":
        $result = delDismantleByDismCode();
        echo json_encode($result);
        break;
}