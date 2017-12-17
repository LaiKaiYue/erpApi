<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2017/4/16
 * Time: 下午 10:00
 */

header("Content-Type:text/html; charset=utf-8");
require_once "config.php";

switch ($func) {
    case "getUnitList":
        $result = getUnitList();
        echo json_encode($result);
        break;
    case "addUnit":
        $result = addUnit();
        echo json_encode($result);
        break;
    case "updateUnit":
        $result = updateUnit();
        echo json_encode($result);
        break;
    case "delUnit":
        $result = delUnit();
        echo json_encode($result);
        break;
    case "getCategoryList":
        $result = getCategoryList();
        echo json_encode($result);
        break;
    case "addCategory":
        $result = addCategory();
        echo json_encode($result);
        break;
    case "updateCategory":
        $result = updateCategory();
        echo json_encode($result);
        break;
    case "delCategory":
        $result = delCategory();
        echo json_encode($result);
        break;
    default:
        $value = "An error has occurred";
        exit(json_encode($value));
        break;
}

// 取單位清單
function getUnitList(){
    global $link, $postDT;
    $sql = "select SN, Name from product_unit where enable = 'true'";

    $result = $link->query($sql);
    while($row = $result->fetch_array(MYSQLI_ASSOC)){
        $data[] = array(
            "SN" => $row["SN"],
            "Name" => $row["Name"]
        );
    }
    $link->close();
    return $data;
}

// 新增單位
function addUnit(){
    global $link, $postDT;
    $Name = $postDT["Name"];

    $sql = "insert into product_unit (Name, enable) values
            ('$Name', 'true')";

    $result = $link->query($sql);
    $link->close();
    return $result;
}

// 修改單位
function updateUnit(){
    global $link, $postDT;
    $Name = $postDT["Name"];
    $SN = $postDT["SN"];
    $sql = "update product_unit set Name = '$Name' where SN = '$SN'";

    $result = $link->query($sql);
    $link->close();
    return $result;
}

// 刪除單位
function delUnit(){
    global $link, $postDT;
    $SN = $postDT["SN"];
    $sql = "update product_unit set enable = 'false' where SN = '$SN'";

    $result = $link->query($sql);
    $link->close();
    return $result;
}

// 取種類清單
function getCategoryList(){
    global $link, $postDT;
    $sql = "select SN, Name from product_category where enable = 'true'";

    $result = $link->query($sql);
    while($row = $result->fetch_array(MYSQLI_ASSOC)){
        $data[] = array(
            "SN" => $row["SN"],
            "Name" => $row["Name"]
        );
    }
    $link->close();
    return $data;
}

// 新增單位
function addCategory(){
    global $link, $postDT;
    $Name = $postDT["Name"];

    $sql = "insert into product_category (Name, enable) values
            ('$Name', 'true')";

    $result = $link->query($sql);
    $link->close();
    return $result;
}

// 修改單位
function updateCategory(){
    global $link, $postDT;
    $Name = $postDT["Name"];
    $SN = $postDT["SN"];
    $sql = "update product_category set Name = '$Name' where SN = '$SN'";

    $result = $link->query($sql);
    $link->close();
    return $result;
}

// 刪除單位
function delCategory(){
    global $link, $postDT;
    $SN = $postDT["SN"];
    $sql = "update product_category set enable = 'false' where SN = '$SN'";

    $result = $link->query($sql);
    $link->close();
    return $result;
}

?>