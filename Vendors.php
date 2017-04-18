<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 2016/12/15
 * Time: 上午 12:32
 */
header("Content-Type:text/html; charset=utf-8");
require_once "config.php";

switch ($func) {
    case "getAllVendors":
        $allVendors = getAllVendors();
        echo json_encode($allVendors);
        break;
    case "getOneVendors":
        $vendors = getOneVendors();
        echo json_encode($vendors);
        break;
    case "getEnableVendors":
        break;
    case "getDisableVendors":
        break;
    case "getVendorsLastSN":
        $SN = getVendorsLastSN();
        echo json_encode($SN);
        break;
    case "InsertVendors":
        $result = InsertVendors();
        echo json_encode($result);
        break;
    case "UpdateVendors":
        $result = UpdateVendors();
        echo json_encode($result);
        break;
    case "DeleteVendors":
        $result = DeleteVendors();
        echo json_encode($result);
        break;
    case "DeleteAllVendors":
        $result = DeleteAllVendors();
        echo json_encode($result);
        break;
    default:
        $value = "An error has occurred";
        exit(json_encode($value));
        break;
}


function getStockSQL()
{
    global $link;
    $sql = "SELECT
            stockInfo.`code` AS `code`,
            stockInfo.vendors_code AS vendors_code,
            product_category.`Name` AS category,
            stockInfo.`name` AS `name`,
            product_unit.`Name` AS unit,
            stockInfo.selling_price AS selling_price,
            stockInfo.safe_stock AS safe_stock,
            stockInfo.stock_num AS stock_num,
            stockInfo.unit_cost AS unit_cost,
            stockInfo.remark AS remark,
            stockInfo.description AS description,
            stockInfo.update_date AS update_date,
            stockInfo.`enable` AS `enable`
            FROM
            stockInfo
            INNER JOIN product_unit ON product_unit.SN = stockInfo.unit
            INNER JOIN product_category ON product_category.SN = stockInfo.category
            WHERE
            stockInfo.`enable` = 'true'
            ORDER BY
            stockInfo.code ASC";
    $result = $link->query($sql);

    return $result;
}

/**
 * 取特定廠商資料
 * @param $code 廠商代號
 * @return string
 */
function getOneVendors()
{
    global $link, $postDT;
    $code = $postDT["code"];

    $sql = "select * from vendorsInfo where code='$code'";
    $result = $link->query($sql);
    $row = $result->fetch_assoc();
    if($row["enable"] == "false"){
        $data = "此廠商已刪除";
    }
    else{
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
    }

    $link->close();
    return $data;
}

/**
 * 取所有廠商資料
 * @return array
 */
function getAllVendors()
{
    global $link;
    $sql = "select * from vendorsInfo where enable='true' order by code ASC";
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
 * 取最新廠商SN
 * @return string SN
 */
function getVendorsLastSN(){
    global $link;
    $sql = "select SN from vendorsInfo order by SN DESC limit 1";
    $result = $link->query($sql);
    $row = $result->fetch_assoc();
    $link->close();
    $SN = $row["SN"]+1;
    if($SN < 10) $SN = "V000".$SN;
    else if($SN < 100) $SN = "V00".$SN;
    else if($SN < 1000) $SN = "V0".$SN;

    return $SN;
}

/**
 * 新增廠商
 * @return bool|mysqli_result 回傳新增結果
 */
function InsertVendors(){
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
    $update_date = date("Y-m-d H:i:s");
    $closing_date = $postDT["closing_date"];
    $remark = $postDT["remark"];
    $enable = "true";

    if($closing_date != ""){
        $sql = "insert into vendorsInfo (code, name, tell_nos, fax_nos, address, invoice_type, payment_type, uni_code, ceo_name, ceo_tell_nos,
            ceo_mail, contact_name, contact_tell_nos, contact_mail, update_date, closing_date, remark, enable ) values
            ('$code', '$name', '$tell_nos', '$fax_nos', '$address', '$invoice_type', '$payment_type', '$uni_code', '$ceo_name', '$ceo_tell_nos',
            '$ceo_mail', '$contact_name', '$contact_tell_nos', '$contact_mail','$update_date', '$closing_date', '$remark', '$enable')";
    }
    else{
        $sql = "insert into vendorsInfo (code, name, tell_nos, fax_nos, address, invoice_type, payment_type, uni_code, ceo_name, ceo_tell_nos,
            ceo_mail, contact_name, contact_tell_nos, contact_mail, update_date, remark, enable ) values
            ('$code', '$name', '$tell_nos', '$fax_nos', '$address', '$invoice_type', '$payment_type', '$uni_code', '$ceo_name', '$ceo_tell_nos',
            '$ceo_mail', '$contact_name', '$contact_tell_nos', '$contact_mail','$update_date', '$remark', '$enable')";
    }

    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 修改廠商資料
 */
function UpdateVendors(){
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
    $update_date = date("Y-m-d H:i:s");
    $closing_date = $postDT["closing_date"];
    $remark = $postDT["remark"];
    $enable = "true";

    if($closing_date != ""){
        $sql = "update vendorsInfo set name='$name', tell_nos='$tell_nos', fax_nos='$fax_nos', address='$address', invoice_type='$invoice_type', payment_type='$payment_type',
            uni_code='$uni_code', ceo_name='$ceo_name', ceo_tell_nos='$ceo_tell_nos', ceo_mail='$ceo_mail', contact_name='$contact_name', contact_tell_nos='$contact_tell_nos', contact_mail='$contact_mail',
            update_date='$update_date', closing_date='$closing_date', remark='$remark', enable='$enable' where code='$code'";
    }
    else{
        $sql = "update vendorsInfo set name='$name', tell_nos='$tell_nos', fax_nos='$fax_nos', address='$address', invoice_type='$invoice_type', payment_type='$payment_type',
            uni_code='$uni_code', ceo_name='$ceo_name', ceo_tell_nos='$ceo_tell_nos', ceo_mail='$ceo_mail', contact_name='$contact_name', contact_tell_nos='$contact_tell_nos', contact_mail='$contact_mail',
            update_date='$update_date', remark='$remark', enable='$enable' where code='$code'";
    }

    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 刪除廠商資料
 * @return bool|mysqli_result
 */
function DeleteVendors(){
    global $postDT, $link;
    $code = $postDT["code"];
    $enable = "false";
    $sql = "update vendorsInfo set enable='$enable' where code='$code'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 刪除所有廠商資料
 * @return bool|mysqli_result
 */
function DeleteAllVendors(){
    global $link;
    $enable = "false";
    $sql = "update vendorsInfo set enable='$enable'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

?>