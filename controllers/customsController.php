<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 2016/12/24
 * Time: 下午 01:51
 */

header("Content-Type:text/html; charset=utf-8");
require_once "../model/db.php";

/**
 * 取特定客戶資料
 * @return array
 */
function getOneCustoms() {
    global $db, $postDT;
    $code = $postDT["code"];

    $result = $db->query("customerInfo", "code='$code'");
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 取所有客戶資料
 * @return array
 */
function getAllCustoms() {
    global $db;
    $result = $db->query("customerinfo", "enable='true'", "code ASC");

    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 取最新客戶SN
 * @return string SN
 */
function getCustomsLastSN() {
    global $db;
    $result = $db->query("customerinfo", "1", "SN DESC", "SN", "limit 1");
    $SN = $result[0]["SN"] + 1;
    if ($SN < 10) $SN = "C000".$SN;
    else if ($SN < 100) $SN = "C00".$SN;
    else if ($SN < 1000) $SN = "C0".$SN;

    return $SN;
}

/**
 * 新增客戶
 * @return bool|mysqli_result 回傳新增結果
 */
function InsertCustom() {
    global $postDT, $db;

    $data_array = array(
        "code" => $postDT["code"],
        "name" => $postDT["name"],
        "tell_nos" => $postDT["tell_nos"],
        "fax_nos" => $postDT["fax_nos"],
        "address" => $postDT["address"],
        "invoice_type" => $postDT["invoice_type"],
        "payment_type" => $postDT["payment_type"],
        "uni_code" => $postDT["uni_code"],
        "ceo_name" => $postDT["ceo_name"],
        "ceo_tell_nos" => $postDT["ceo_tell_nos"],
        "ceo_mail" => $postDT["ceo_mail"],
        "contact_name" => $postDT["contact_name"],
        "contact_tell_nos" => $postDT["contact_tell_nos"],
        "contact_mail" => $postDT["contact_mail"],
        "discount" => $postDT["discount"],
        "update_date" => date("Y-m-d H:i:s"),
        "closing_date" => $postDT["closing_date"] == "" ? 25 : $postDT["closing_date"],
        "remark" => $postDT["remark"],
        "enable" => "true"
    );
    $result = $db->insert("customerinfo", $data_array);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 修改客戶資料
 * @return bool|mysqli_result
 */
function UpdateCustom() {
    global $postDT, $db;
    $data_array = array(
        "code" => $postDT["code"],
        "name" => $postDT["name"],
        "tell_nos" => $postDT["tell_nos"],
        "fax_nos" => $postDT["fax_nos"],
        "address" => $postDT["address"],
        "invoice_type" => $postDT["invoice_type"],
        "payment_type" => $postDT["payment_type"],
        "uni_code" => $postDT["uni_code"],
        "ceo_name" => $postDT["ceo_name"],
        "ceo_tell_nos" => $postDT["ceo_tell_nos"],
        "ceo_mail" => $postDT["ceo_mail"],
        "contact_name" => $postDT["contact_name"],
        "contact_tell_nos" => $postDT["contact_tell_nos"],
        "contact_mail" => $postDT["contact_mail"],
        "discount" => $postDT["discount"],
        "update_date" => date("Y-m-d H:i:s"),
        "closing_date" => $postDT["closing_date"] == "" ? 25 : $postDT["closing_date"],
        "remark" => $postDT["remark"],
        "enable" => "true"
    );
    $cond["code"] = $data_array["code"];
    $result = $db->update("customerinfo", $data_array, $cond);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 刪除客戶資料
 * @return bool|mysqli_result
 */
function DeleteCustom() {
    global $postDT, $db;
    $code = $postDT["code"];
    $enable = "false";
    $data_array["enable"] = $enable;
    $cond["code"] = $code;
    $result = $db->update("customerinfo", $data_array, $cond);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 刪除所有客戶資料
 * @return bool|mysqli_result
 */
function DeleteAllCustom() {
    global $db;
    $result = $db->execute("update customerInfo set enable='false'");
    return $result === false ? $db->getErrorMessage() : $result;
}