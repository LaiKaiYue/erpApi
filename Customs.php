<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 2016/12/24
 * Time: 下午 01:51
 */

header("Content-Type:text/html; charset=utf-8");
require_once "config.php";

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
    default:
        $value = "An error has occurred";
        exit(json_encode($value));
        break;
}

/**
 * 取特定客戶資料
 * @param $code 廠商代號
 * @return string
 */
function getOneCustoms()
{
    global $link, $postDT;
    $code = $postDT["code"];
    $sql = "select * from customerInfo where code='$code'";
    $result = $link->query($sql);
    $row = $result->fetch_assoc();
    $data[] = array(
        "code" => $row["code"],
        "name" => $row["name"],
        "tell_nos" => $row["tell_nos"],
        "fax_nos" => $row["fax_nos"],
        "address" => $row["address"],
        "invoice_type" => $row["invoice_type"],
        "payment_type" => $row["payment_type"],
        "uni_code" => $row["uni_code"],
        "ceo_name" => $row["ceo_name"],
        "ceo_tell_nos" => $row["ceo_tell_nos"],
        "ceo_mail" => $row["ceo_mail"],
        "contact_name" => $row["contact_name"],
        "contact_tell_nos" => $row["contact_tell_nos"],
        "contact_mail" => $row["contact_mail"],
        "update_date" => $row["update_date"],
        "closing_date" => $row["closing_date"],
        "remark" => $row["remark"],
        "enable" => $row["enable"]
    );
    $link->close();
    return $data;
}

/**
 * 取所有客戶資料
 * @return array
 */
function getAllCustoms()
{
    global $link;
    $sql = "select * from customerInfo where enable='true' order by code ASC";
    $result = $link->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = array(
                "code" => $row["code"],
                "name" => $row["name"],
                "tell_nos" => $row["tell_nos"],
                "fax_nos" => $row["fax_nos"],
                "address" => $row["address"],
                "invoice_type" => $row["invoice_type"],
                "payment_type" => $row["payment_type"],
                "uni_code" => $row["uni_code"],
                "ceo_name" => $row["ceo_name"],
                "ceo_tell_nos" => $row["ceo_tell_nos"],
                "ceo_mail" => $row["ceo_mail"],
                "contact_name" => $row["contact_name"],
                "contact_tell_nos" => $row["contact_tell_nos"],
                "contact_mail" => $row["contact_mail"],
                "discount" => $row["discount"],
                "update_date" => $row["update_date"],
                "closing_date" => $row["closing_date"],
                "remark" => $row["remark"],
                "enable" => $row["enable"]
            );
        }
    }
    $link->close();
    return $data;
}

/**
 * 取最新客戶SN
 * @return string SN
 */
function getCustomsLastSN(){
    global $link;
    $sql = "select SN from customerInfo order by SN DESC limit 1";
    $result = $link->query($sql);
    $row = $result->fetch_assoc();
    $link->close();
    $SN = $row["SN"]+1;
    if($SN < 10) $SN = "C000".$SN;
    else if($SN < 100) $SN = "C00".$SN;
    else if($SN < 1000) $SN = "C0".$SN;

    return $SN;
}

/**
 * 新增客戶
 * @return bool|mysqli_result 回傳新增結果
 */
function InsertCustom(){
    global $postDT, $link;
    $code = $postDT["code"];
    $name = $postDT["name"];
    $tell_nos = $postDT["tell_nos"];
    $fax_nos = $postDT["fax_nos"];
    $address = $postDT["address"];
    $invoice_type = $postDT["invoice_type"];
    $payment_type = $postDT["payment_type"];
    $uni_code = $postDT["uni_code"];
    $ceo_name = $postDT["ceo_name"];
    $ceo_tell_nos = $postDT["ceo_tell_nos"];
    $ceo_mail = $postDT["ceo_mail"];
    $contact_name = $postDT["contact_name"];
    $contact_tell_nos = $postDT["contact_tell_nos"];
    $contact_mail = $postDT["contact_mail"];
    $discount = $postDT["discount"];
    $update_date = date("Y-m-d H:i:s");
    $closing_date = $postDT["closing_date"];
    $remark = $postDT["remark"];
    $enable = "true";

    if($closing_date != ""){
        $sql = "insert into customerInfo (code, name, tell_nos, fax_nos, address, invoice_type, payment_type, uni_code, ceo_name, ceo_tell_nos,
            ceo_mail, contact_name, contact_tell_nos, contact_mail, discount, update_date, closing_date, remark, enable ) values
            ('$code', '$name', '$tell_nos', '$fax_nos', '$address', '$invoice_type', '$payment_type', '$uni_code', '$ceo_name', '$ceo_tell_nos',
            '$ceo_mail', '$contact_name', '$contact_tell_nos', '$contact_mail', '$discount', '$update_date', '$closing_date', '$remark', '$enable')";
    }
    else{
        $sql = "insert into customerInfo (code, name, tell_nos, fax_nos, address, invoice_type, payment_type, uni_code, ceo_name, ceo_tell_nos,
            ceo_mail, contact_name, contact_tell_nos, contact_mail, discount, update_date, remark, enable ) values
            ('$code', '$name', '$tell_nos', '$fax_nos', '$address', '$invoice_type', '$payment_type', '$uni_code', '$ceo_name', '$ceo_tell_nos',
            '$ceo_mail', '$contact_name', '$contact_tell_nos', '$contact_mail', '$discount', '$update_date', '$remark', '$enable')";
    }


    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 修改客戶資料
 */
function UpdateCustom(){
    global $postDT, $link;
    $code = $postDT["code"];
    $name = $postDT["name"];
    $tell_nos = $postDT["tell_nos"];
    $fax_nos = $postDT["fax_nos"];
    $address = $postDT["address"];
    $invoice_type = $postDT["invoice_type"];
    $payment_type = $postDT["payment_type"];
    $uni_code = $postDT["uni_code"];
    $ceo_name = $postDT["ceo_name"];
    $ceo_tell_nos = $postDT["ceo_tell_nos"];
    $ceo_mail = $postDT["ceo_mail"];
    $contact_name = $postDT["contact_name"];
    $contact_tell_nos = $postDT["contact_tell_nos"];
    $contact_mail = $postDT["contact_mail"];
    $discount = $postDT["discount"];
    $update_date = date("Y-m-d H:i:s");
    $closing_date = $postDT["closing_date"];
    $remark = $postDT["remark"];
    $enable = "true";

    if($closing_date != ""){
        $sql = "update customerInfo set name='$name', tell_nos='$tell_nos', fax_nos='$fax_nos', address='$address', invoice_type='$invoice_type', payment_type='$payment_type',
            uni_code='$uni_code', ceo_name='$ceo_name', ceo_tell_nos='$ceo_tell_nos', ceo_mail='$ceo_mail', contact_name='$contact_name', contact_tell_nos='$contact_tell_nos', contact_mail='$contact_mail',
            discount='$discount', update_date='$update_date', closing_date='$closing_date', remark='$remark', enable='$enable' where code='$code'";
    }
    else{
        $sql = "update customerInfo set name='$name', tell_nos='$tell_nos', fax_nos='$fax_nos', address='$address', invoice_type='$invoice_type', payment_type='$payment_type',
            uni_code='$uni_code', ceo_name='$ceo_name', ceo_tell_nos='$ceo_tell_nos', ceo_mail='$ceo_mail', contact_name='$contact_name', contact_tell_nos='$contact_tell_nos', contact_mail='$contact_mail',
            discount='$discount', update_date='$update_date', remark='$remark', enable='$enable' where code='$code'";
    }

    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 刪除客戶資料
 * @return bool|mysqli_result
 */
function DeleteCustom(){
    global $postDT, $link;
    $code = $postDT["code"];
    $enable = "false";
    $sql = "update customerInfo set enable='$enable' where code='$code'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 刪除所有客戶資料
 * @return bool|mysqli_result
 */
function DeleteAllCustom(){
    global $link;
    $enable = "false";
    $sql = "update customerInfo set enable='$enable'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

?>