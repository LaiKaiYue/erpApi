<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/6
 * Time: 下午6:37
 */

require_once "../controllers/combinDocController.php";
require_once "../common/url.php";

switch ($func) {
    case "qryCombProduct":
        $result = qryCombProduct();
        echo json_encode($result);
        break;
    case "qryAllCombDocMn":
        $result = qryAllCombDocMn();
        echo json_encode($result);
        break;
    case "qryOneCombDocMnByOrderNum":
        $result = qryOneCombDocMnByOrderNum();
        echo json_encode($result);
        break;
    case "qryCombDocDtByOrderNum":
        $result = qryCombDocDtByOrderNum();
        echo json_encode($result);
        break;
    case "insCombDoc":
        $result = insCombDoc();
        echo json_encode($result);
        break;
    case "updCombDocMnByOrderNum":
        $result = updCombDocMnByOrderNum();
        echo json_encode($result);
        break;
    case "delCombDocByOrderNum":
        $result = delCombDocByOrderNum();
        echo json_encode($result);
        break;
}