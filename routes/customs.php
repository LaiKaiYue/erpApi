<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 2016/12/24
 * Time: 下午 01:51
 */

require_once "../controllers/customsController.php";
require_once "../common/url.php";

switch ($func) {
    case "getAllCustoms":
        $allCustoms = getAllCustoms();
        echo json_encode($allCustoms);
        break;
    case "getOneCustoms":
        $customs = getOneCustoms();
        echo json_encode($customs);
        break;
    case "getEnableCustoms":
        break;
    case "getDisableCustoms":
        break;
    case "getCustomsLastSN":
        $SN = getCustomsLastSN();
        echo json_encode($SN);
        break;
    case "InsertCustom":
        $result = InsertCustom();
        echo json_encode($result);
        break;
    case "UpdateCustom":
        $result = UpdateCustom();
        echo json_encode($result);
        break;
    case "DeleteCustom":
        $result = DeleteCustom();
        echo json_encode($result);
        break;
    case "DeleteAllCustom":
        $result = DeleteAllCustom();
        echo json_encode($result);
        break;
}