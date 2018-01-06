<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/6
 * Time: 下午6:37
 */

header("Content-Type:text/html; charset=utf-8");
require_once "../controllers/combDocController.php";

switch ($func) {
    case "qryAllCombDocMn":
        $result = qryAllCombDocMn();
        echo json_encode($result);
        break;
    case "qryOneCombDocMn":
        $result = qryOneCombDocMn();
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
?>