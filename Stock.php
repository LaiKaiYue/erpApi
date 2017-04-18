<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 2016/12/27
 * Time: 下午 08:37
 */

header("Content-Type:text/html; charset=utf-8");
require_once "config.php";

switch ($func) {
    case "getAllStock":
        $allStock = getAllStock();
        echo json_encode($allStock);
        break;
    case "getOneStock":
        $stock = getOneStock();
        echo json_encode($stock);
        break;
    case "getVendorProduct":
        $result = getVendorProduct();
        echo json_encode($result);
        break;
    case "getProductLeaderBoard":
        $result = getProductLeaderBoard();
        echo json_encode($result);
        break;
    case "getEnableStock":
        break;
    case "getDisableStock":
        break;
    case "getStockLastSN":
        $SN = getStockLastSN();
        echo json_encode($SN);
        break;
    case "InsertStock":
        $result = InsertStock();
        echo json_encode($result);
        break;
    case "UpdateStock":
        $result = UpdateStock();
        echo json_encode($result);
        break;
    case "DeleteStock":
        $result = DeleteStock();
        echo json_encode($result);
        break;
    case "DeleteAllStock":
        $result = DeleteAllStock();
        echo json_encode($result);
        break;
    case "getUnderSafeStock":
        $result = getUnderSafeStock();
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
 * 取庫存最新流水號
 * @return string
 */
function getStockLastSN()
{
    global $link;
    $sql = "select SN from stockInfo order by SN DESC limit 1";
    $result = $link->query($sql);
    $row = $result->fetch_assoc();
    $link->close();
    $SN = $row["SN"] + 1;
    if ($SN < 10) $SN = "S000" . $SN;
    else if ($SN < 100) $SN = "S00" . $SN;
    else if ($SN < 1000) $SN = "S0" . $SN;

    return $SN;
}

/**
 * 取得所有庫存
 * @return array
 */
function getAllStock()
{
    global $link;
    $result = getStockSQL();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
//            if ($row["stock_num"] > $row["safe_stock"]) {
            $code = "";
            $name = "";
            $contact_name = "";
            $contact_tell_nos = "";
            $vendor = explode(',', $row["vendors_code"]);
            $length = count($vendor);
            if ($length > 1) {
                for ($index = 0; $index < $length; $index++) {
                    $s = "select code, name, contact_name, contact_tell_nos from vendorsInfo where code='$vendor[$index]' and enable='true'";
                    $r = $link->query($s);
                    $dt = mysqli_fetch_array($r);
                    if (($index + 1) == $length) {
                        $code .= $dt["code"];
                        $name .= $dt["name"];
                        $contact_name .= $dt["contact_name"];
                        $contact_tell_nos .= $dt["contact_tell_nos"];
                    } else {
                        $code .= $dt["code"] . ",";
                        $name .= $dt["name"] . ",";
                        $contact_name .= $dt["contact_name"] . ",";
                        $contact_tell_nos .= $dt["contact_tell_nos"] . ",";
                    }
                }
            } else {
                $s = "select code, name, contact_name, contact_tell_nos from vendorsInfo where code='$vendor[0]' and enable='true'";
                $r = $link->query($s);
                $dt = mysqli_fetch_array($r);
                $code .= $dt["code"];
                $name .= $dt["name"];
                $contact_name .= $dt["contact_name"];
                $contact_tell_nos .= $dt["contact_tell_nos"];
            }

            $data[] = array(
                "code" => $row["code"],
                "vendor_code" => $code,
                "vendor_name" => $name,
                "contact_name" => $contact_name,
                "contact_tell_nos" => $contact_tell_nos,
                "category" => $row["category"],
                "name" => $row["name"],
                "unit" => $row["unit"],
                "selling_price" => $row["selling_price"],
                "safe_stock" => $row["safe_stock"],
                "stock_num" => $row["stock_num"],
                "unit_cost" => $row["unit_cost"],
                "excluded_tax_total" => $row["excluded_tax_total"],
                "tax" => $row["tax"],
                "remark" => $row["remark"],
                "description" => $row["description"],
                "update_date" => $row["update_date"],
                "enable" => $row["enable"]
            );
        }
//        }
    }
    $link->close();
    return $data;
}

/**
 * 取單筆庫存
 * @param $code {String} 庫存code
 * @return array 庫存資料
 */
function getOneStock()
{
    global $link, $postDT;
    $code = $postDT["code"];
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
            stockInfo.excluded_tax_total AS excluded_tax_total,
            stockInfo.tax AS tax,
            stockInfo.remark AS remark,
            stockInfo.description AS description,
            stockInfo.update_date AS update_date,
            stockInfo.`enable` AS `enable`
            FROM
            stockInfo
            INNER JOIN product_unit ON product_unit.SN = stockInfo.unit
            INNER JOIN product_category ON product_category.SN = stockInfo.category
            WHERE
            stockInfo.`enable` = 'true' and stockInfo.`code` = '$code' limit 1";

    $result = $link->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
//            if ($row["stock_num"] > $row["safe_stock"]) {
            $code = "";
            $name = "";
            $contact_name = "";
            $contact_tell_nos = "";
            $vendor = explode(',', $row["vendors_code"]);
            $length = count($vendor);
            if ($length > 1) {
                for ($index = 0; $index < $length; $index++) {
                    $s = "select code, name, contact_name, contact_tell_nos from vendorsInfo where code='$vendor[$index]' and enable='true'";
                    $r = $link->query($s);
                    $dt = mysqli_fetch_array($r);
                    if (($index + 1) == $length) {
                        $code .= $dt["code"];
                        $name .= $dt["name"];
                        $contact_name .= $dt["contact_name"];
                        $contact_tell_nos .= $dt["contact_tell_nos"];
                    } else {
                        $code .= $dt["code"] . ",";
                        $name .= $dt["name"] . ",";
                        $contact_name .= $dt["contact_name"] . ",";
                        $contact_tell_nos .= $dt["contact_tell_nos"] . ",";
                    }
                }
            } else {
                $s = "select code, name, contact_name, contact_tell_nos from vendorsInfo where code='$vendor[0]' and enable='true'";
                $r = $link->query($s);
                $dt = mysqli_fetch_array($r);
                $code .= $dt["code"];
                $name .= $dt["name"];
                $contact_name .= $dt["contact_name"];
                $contact_tell_nos .= $dt["contact_tell_nos"];
            }

            $data[] = array(
                "code" => $row["code"],
                "vendor_code" => $code,
                "vendor_name" => $name,
                "contact_name" => $contact_name,
                "contact_tell_nos" => $contact_tell_nos,
                "category" => $row["category"],
                "name" => $row["name"],
                "unit" => $row["unit"],
                "selling_price" => $row["selling_price"],
                "safe_stock" => $row["safe_stock"],
                "stock_num" => $row["stock_num"],
                "unit_cost" => $row["unit_cost"],
                "excluded_tax_total" => $row["excluded_tax_total"],
                "tax" => $row["tax"],
                "remark" => $row["remark"],
                "description" => $row["description"],
                "update_date" => $row["update_date"],
                "enable" => $row["enable"]
            );
        }
//        }
    }
    $link->close();
    return $data;
}

/**
 * 取廠商商品資料
 */
function getVendorProduct()
{
    global $postDT, $link;
    $code = $postDT["code"];

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
            stockInfo.excluded_tax_total AS excluded_tax_total,
            stockInfo.tax AS tax,
            stockInfo.remark AS remark,
            stockInfo.description AS description,
            stockInfo.update_date AS update_date,
            stockInfo.`enable` AS `enable`
            FROM
            stockInfo
            INNER JOIN product_unit ON product_unit.SN = stockInfo.unit
            INNER JOIN product_category ON product_category.SN = stockInfo.category
            WHERE
            stockInfo.`enable` = 'true' and stockInfo.vendors_code LIKE '%$code%'
            ORDER BY
            stockInfo.code ASC";
    $result = $link->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = array(
                "code" => $row["code"],
                "category" => $row["category"],
                "name" => $row["name"],
                "unit" => $row["unit"],
                "selling_price" => $row["selling_price"],
                "safe_stock" => $row["safe_stock"],
                "stock_num" => $row["stock_num"],
                "unit_cost" => $row["unit_cost"],
                "excluded_tax_total" => $row["excluded_tax_total"],
                "tax" => $row["tax"],
                "remark" => $row["remark"],
                "description" => $row["description"]
            );
        }
    }
    $link->close();
    return $data;
}

/**
 * 新增庫存
 * @return bool|mysqli_result
 */
function InsertStock()
{
    global $postDT, $link;
    $code = $postDT["code"];
    $vendors_code = $postDT["vendors_code"];
    $category = $postDT["category"];
    $name = $postDT["name"];
    $unit = $postDT["unit"];
    $selling_price = $postDT["selling_price"];
    $safe_stock = $postDT["safe_stock"];
    $stock_num = $postDT["stock_num"];
    $unit_cost = $postDT["unit_cost"];
    $excluded_tax_total = $postDT["excluded_tax_total"];
    $tax = $postDT["tax"];
    $remark = $postDT["remark"];
    $description = $postDT["description"];
    $update_date = date("Y-m-d H:i:s");
    $enable = "true";

    $sql = "insert into stockInfo (code, vendors_code, category, name, unit, selling_price, safe_stock, stock_num, unit_cost, excluded_tax_total, tax, remark, description, update_date, enable ) values
            ('$code', '$vendors_code', '$category', '$name', '$unit', '$selling_price', '$safe_stock', '$stock_num', '$unit_cost', '$excluded_tax_total', '$tax', '$remark',
            '$description', '$update_date', '$enable')";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 修改庫存
 * @return bool|mysqli_result
 */
function UpdateStock()
{
    global $postDT, $link;
    $code = $postDT["code"];
    $vendors_code = $postDT["vendors_code"];
    $category = $postDT["category"];
    $name = $postDT["name"];
    $unit = $postDT["unit"];
    $selling_price = $postDT["selling_price"];
    $safe_stock = $postDT["safe_stock"];
    $stock_num = $postDT["stock_num"];
    $unit_cost = $postDT["unit_cost"];
    $excluded_tax_total = $postDT["excluded_tax_total"];
    $tax = $postDT["tax"];
    $remark = $postDT["remark"];
    $description = $postDT["description"];
    $update_date = date("Y-m-d H:i:s");
    $enable = "true";

    $sql = "update stockInfo set vendors_code='$vendors_code', category='$category', name='$name', unit='$unit', selling_price='$selling_price',
            safe_stock='$safe_stock', stock_num='$stock_num', unit_cost='$unit_cost', excluded_tax_total='$excluded_tax_total', tax='$tax', remark='$remark', description='$description', update_date='$update_date',
            enable='$enable' where code='$code'";

    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 刪除庫存
 * @return bool|mysqli_result
 */
function DeleteStock()
{
    global $postDT, $link;
    $code = $postDT["code"];
    $enable = "false";
    $sql = "update stockInfo set enable='$enable' where code='$code'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 刪除所有庫存
 * @return bool|mysqli_result
 */
function DeleteAllStock()
{
    global $link;
    $enable = "false";
    $sql = "update stockInfo set enable='$enable'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

/**
 * 取得低於安全庫存
 * @return array
 */
function getUnderSafeStock()
{
    global $link;
    $result = getStockSQL();

    while ($row = mysqli_fetch_array($result)) {
        if ($row["stock_num"] <= $row["safe_stock"]) {
            $code = "";
            $name = "";
            $contact_name = "";
            $contact_tell_nos = "";
            $vendor = explode(',', $row["vendors_code"]);
            $length = count($vendor);
            if ($length > 1) {
                for ($index = 0; $index < $length; $index++) {
                    $s = "select code, name, contact_name, contact_tell_nos from vendorsInfo where code='$vendor[$index]' and enable='true'";
                    $r = $link->query($s);
                    $dt = mysqli_fetch_array($r);
                    if (($index + 1) == $length) {
                        $code .= $dt["code"];
                        $name .= $dt["name"];
                        $contact_name .= $dt["contact_name"];
                        $contact_tell_nos .= $dt["contact_tell_nos"];
                    } else {
                        $code .= $dt["code"] . ",";
                        $name .= $dt["name"] . ",";
                        $contact_name .= $dt["contact_name"] . ",";
                        $contact_tell_nos .= $dt["contact_tell_nos"] . ",";
                    }
                }
            } else {
                $s = "select code, name, contact_name, contact_tell_nos from vendorsInfo where code='$vendor[0]' and enable='true'";
                $r = $link->query($s);
                $dt = mysqli_fetch_array($r);
                $code .= $dt["code"];
                $name .= $dt["name"];
                $contact_name .= $dt["contact_name"];
                $contact_tell_nos .= $dt["contact_tell_nos"];
            }


            $data[] = array(
                "code" => $row["code"],
                "vendor_code" => $code,
                "vendor_name" => $name,
                "contact_name" => $contact_name,
                "contact_tell_nos" => $contact_tell_nos,
                "category" => $row["category"],
                "name" => $row["name"],
                "unit" => $row["unit"],
                "selling_price" => $row["selling_price"],
                "safe_stock" => $row["safe_stock"],
                "stock_num" => $row["stock_num"],
                "unit_cost" => $row["unit_cost"],
                "remark" => $row["remark"],
                "description" => $row["description"],
                "update_date" => $row["update_date"],
                "enable" => $row["enable"]
            );
        }
    }
    $link->close();
    return $data;
}

/**
 * 取商品排行
 */
function getProductLeaderBoard()
{
    global $link, $postDT;
    $vendor_code = $postDT["vendor_code"];
    $sql = "select * from product_leaderboard where vendor_code='$vendor_code' ORDER BY count DESC";
    $result = $link->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $data[] = array(
            "product_code" => $row["product_code"],
            "product_name" => $row["product_name"]
        );
    }
    return $data;
}

?>