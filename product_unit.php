<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 2016/12/27
 * Time: 下午 10:28
 */

header("Content-Type:text/html; charset=utf-8");
require_once "config.php";

switch($func){
    case "getAllUnit":
        $getResult = getAllUnit();
        echo json_encode($getResult);
        break;
    case "getOneUnit":
        $getResult = getOneUnit();
        echo json_encode($getResult);
        break;
    case "InsertUnit":
        $InsertResult = InsertUnit();
        echo json_encode($InsertResult);
        break;
    case "UpdateUnit":
        $UpdateResult = UpdateUnit();
        echo json_encode($UpdateResult);
        break;
    case "DeleteUnit":
        $DelResult = DeleteUnit();
        echo json_encode($DelResult);
        break;
    case "DeleteAllUnit":
        $DelResult = DeleteAllUnit();
        echo json_encode($DelResult);
        break;
    default:
        $value = "An error has occurred";
        exit(json_encode($value));
        break;
}
/**
 * 取得所有單位
 * @return array
 */
function getAllUnit(){
    global $link;
    $sql = "select * from product_unit where enable='true' order by SN ASC";
    $result = $link->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = array(
                "SN" => $row["SN"],
                "Name" => $row["Name"],
                "enable" => $row["enable"]
            );
        }
    }
    $link->close();
    return $data;
}

/**
 * 取單筆單位
 * @return array
 */
function getOneUnit(){
    global $link, $postDT;
    $SN = $postDT["SN"];
    $sql = "select * from product_unit where enable='true' and SN='$SN' limit 1";
    $result = $link->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = array(
                "SN" => $row["SN"],
                "Name" => $row["Name"],
                "enable" => $row["enable"]
            );
        }
    }
    $link->close();
    return $data;
}

/**
 * 新增單位
 * @return bool|mysqli_result
 */
function InsertUnit()
{
    global $postDT, $link;
    $Name = $postDT["Name"];
    $enable = "true";

    $sql = "insert into product_unit (Name, enable) VALUES ('$Name' , '$enable')";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 修改單位
 * @return bool|mysqli_result
 */
function UpdateUnit(){
    global $postDT, $link;
    $SN = $postDT["SN"];
    $Name = $postDT["Name"];

    $sql = "update product_unit set Name='$Name' where SN='$SN'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 刪除單位
 * @return bool|mysqli_result
 */
function DeleteUnit(){
    global $postDT, $link;
    $SN = $postDT["SN"];
    $enable = "false";
    $sql = "update product_unit set enable='$enable' where SN='$SN'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 刪除所有單位
 * @return bool|mysqli_result
 */
function DeleteAllUnit(){
    global $link;
    $enable = "false";
    $sql = "update product_unit set enable='$enable'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

?>