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
    case "getAllCategory":
        $getResult = getAllCategory();
        echo json_encode($getResult);
        break;
    case "getOneCategory":
        $getResult = getOneCategory();
        echo json_encode($getResult);
        break;
    case "InsertCategory":
        $InsertResult = InsertCategory();
        echo json_encode($InsertResult);
        break;
    case "UpdateCategory":
        $UpdateResult = UpdateCategory();
        echo json_encode($UpdateResult);
        break;
    case "DeleteCategory":
        $DelResult = DeleteCategory();
        echo json_encode($DelResult);
        break;
    case "DeleteAllCategory":
        $DelResult = DeleteAllCategory();
        echo json_encode($DelResult);
        break;
    default:
        $value = "An error has occurred";
        exit(json_encode($value));
        break;
}
/**
 * 取得所有類別
 * @return array
 */
function getAllCategory(){
    global $link;
    $sql = "select * from product_category where enable='true' order by SN ASC";
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
 * 取單筆類別
 * @return array
 */
function getOneCategory(){
    global $link, $postDT;
    $SN = $postDT["SN"];
    $sql = "select * from product_category where enable='true' and SN='$SN' limit 1";
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
 * 新增類別
 * @return bool|mysqli_result
 */
function InsertCategory()
{
    global $postDT, $link;
    $Name = $postDT["Name"];
    $enable = "true";

    $sql = "insert into product_category (Name, enable) VALUES ('$Name' , '$enable')";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 修改類別
 * @return bool|mysqli_result
 */
function UpdateCategory(){
    global $postDT, $link;
    $SN = $postDT["SN"];
    $Name = $postDT["Name"];

    $sql = "update product_category set Name='$Name' where SN='$SN'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 刪除類別
 * @return bool|mysqli_result
 */
function DeleteCategory(){
    global $postDT, $link;
    $SN = $postDT["SN"];
    $enable = "false";
    $sql = "update product_category set enable='$enable' where SN='$SN'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 刪除所有類別
 * @return bool|mysqli_result
 */
function DeleteAllCategory(){
    global $link;
    $enable = "false";
    $sql = "update product_category set enable='$enable'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

?>