<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/19
 * Time: 上午1:01
 */

require_once "../controllers/dismantleDocController.php";
require_once "../common/url.php";

switch ($func) {
    case "qryDismantleProduct":
        $result = qryDismantleProduct();
        echo json_encode($result);
        break;
    case "qryAllDismantleDocMn":
        $result = qryAllDismantleDocMn();
        echo json_encode($result);
        break;
    case "qryOneDismantleDocMnByOrderNum":
        $result = qryOneDismantleDocMnByOrderNum();
        echo json_encode($result);
        break;
    case "qryDismantleDocDtByOrderNum":
        $result = qryDismantleDocDtByOrderNum();
        echo json_encode($result);
        break;
    case "insDismantleDoc":
        $result = insDismantleDoc();
        echo json_encode($result);
        break;
    case "updDismantleDocMnByOrderNum":
        $result = updDismantleDocMnByOrderNum();
        echo json_encode($result);
        break;
    case "delDismantleDocByOrderNum":
        $result = delDismantleDocByOrderNum();
        echo json_encode($result);
        break;
}