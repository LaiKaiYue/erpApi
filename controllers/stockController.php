<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/3/6
 * Time: 下午9:13
 */

require_once "../common/url.php";
require_once "../common/tools.php";
require_once "../common/stockInfo.php";
require_once "../model/db.php";
require_once "../vendor/autoload.php";

function getStockSQL() {
    global $db;
    $result = $db->execute("SELECT
            stockinfo.`code` AS `code`,
            stockinfo.vendors_code AS vendors_code,
            product_category.`Name` AS category,
            stockinfo.`name` AS `name`,
            product_unit.`Name` AS unit,
            stockinfo.selling_price AS selling_price,
            stockinfo.safe_stock AS safe_stock,
            stockinfo.stock_num AS stock_num,
            stockinfo.unit_cost AS unit_cost,
            stockinfo.remark AS remark,
            stockinfo.description AS description,
            stockinfo.update_date AS update_date,
            stockinfo.`enable` AS `enable`
            FROM stockinfo
            INNER JOIN product_unit ON product_unit.SN = stockinfo.unit
            INNER JOIN product_category ON product_category.SN = stockinfo.category
            WHERE stockinfo.`enable` = 'true'
            ORDER BY stockinfo.code ASC");

    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 取庫存最新流水號
 * @return string
 */
function getStockLastSN() {
    global $db;
    $row = $db->query("stockinfo", "1", "SN DESC", "SN", "1");
    $SN = $row[0]["SN"] + 1;
    if ($SN < 10) $SN = "S000".$SN;
    else if ($SN < 100) $SN = "S00".$SN;
    else if ($SN < 1000) $SN = "S0".$SN;

    return $SN;
}

/**
 * 取得所有庫存
 * @return array
 */
function getAllStock() {
    global $db;
    $result = getStockSQL();
    if (count($result) > 0) {
        foreach ($result as $row) {
            $code = "";
            $name = "";
            $contact_name = "";
            $contact_tell_nos = "";
            $la_vendor = explode(',', $row["vendors_code"]);
            $ln_length = count($la_vendor);

            if ($ln_length > 1) {
                $ln_dtIdx = 0;
                foreach ($la_vendor as $ls_vendor) {
                    $dt = $db->execute("select code, name, contact_name, contact_tell_nos from vendorsinfo where code='$ls_vendor' and enable='true'");
                    if (($ln_dtIdx + 1) == $ln_length) {
                        $code .= $dt[0]["code"];
                        $name .= $dt[0]["name"];
                        $contact_name .= $dt[0]["contact_name"];
                        $contact_tell_nos .= $dt[0]["contact_tell_nos"];
                    }
                    else {
                        $code .= $dt[0]["code"].",";
                        $name .= $dt[0]["name"].",";
                        $contact_name .= $dt[0]["contact_name"].",";
                        $contact_tell_nos .= $dt[0]["contact_tell_nos"].",";
                    }
                    $ln_dtIdx++;
                }
            }
            else {
                $dt = $db->execute("select code, `name`, contact_name, contact_tell_nos from vendorsinfo where code='$la_vendor[0]' and enable='true'");
                $code .= $dt[0]["code"];
                $name .= $dt[0]["name"];
                $contact_name .= $dt[0]["contact_name"];
                $contact_tell_nos .= $dt[0]["contact_tell_nos"];
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
    }
    else {
        $data[] = array();
    }
    return $data;
}

/**
 * 取單筆庫存
 * @param $code {String} 庫存code
 * @return array 庫存資料
 */
function getOneStock() {
    global $db, $postDT;
    $code = $postDT["code"];
    $result = $db->execute("SELECT
            stockinfo.`code` AS `code`,
            stockinfo.vendors_code AS vendors_code,
            product_category.`Name` AS category,
            stockinfo.`name` AS `name`,
            product_unit.`Name` AS unit,
            stockinfo.selling_price AS selling_price,
            stockinfo.safe_stock AS safe_stock,
            stockinfo.stock_num AS stock_num,
            stockinfo.unit_cost AS unit_cost,
            stockinfo.excluded_tax_total AS excluded_tax_total,
            stockinfo.tax AS tax,
            stockinfo.remark AS remark,
            stockinfo.description AS description,
            stockinfo.update_date AS update_date,
            stockinfo.`enable` AS `enable`
            FROM stockinfo
            INNER JOIN product_unit ON product_unit.SN = stockinfo.unit
            INNER JOIN product_category ON product_category.SN = stockinfo.category
            WHERE stockinfo.`enable` = 'true' and stockinfo.`code` = '$code' limit 1");

    if (count($result) > 0) {
        foreach ($result as $row) {
            $code = "";
            $name = "";
            $contact_name = "";
            $contact_tell_nos = "";
            $la_vendor = explode(',', $row["vendors_code"]);
            $ln_length = count($la_vendor);
            if ($ln_length > 1) {
                $ln_dtIdx = 0;
                foreach ($la_vendor as $ls_vendor) {
                    $dt = $db->execute("select code, `name`, contact_name, contact_tell_nos from vendorsInfo where code='$ls_vendor' and enable='true'");
                    if (($ln_dtIdx + 1) == $ln_length) {
                        $code .= $dt[0]["code"];
                        $name .= $dt[0]["name"];
                        $contact_name .= $dt[0]["contact_name"];
                        $contact_tell_nos .= $dt[0]["contact_tell_nos"];
                    }
                    else {
                        $code .= $dt[0]["code"].",";
                        $name .= $dt[0]["name"].",";
                        $contact_name .= $dt[0]["contact_name"].",";
                        $contact_tell_nos .= $dt[0]["contact_tell_nos"].",";
                    }
                    $ln_dtIdx++;
                }
            }
            else {
                $dt = $db->execute("select code, `name`, contact_name, contact_tell_nos from vendorsInfo where code='$la_vendor[0]' and enable='true'");
                $code .= $dt[0]["code"];
                $name .= $dt[0]["name"];
                $contact_name .= $dt[0]["contact_name"];
                $contact_tell_nos .= $dt[0]["contact_tell_nos"];
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
    }
    else {
        $data[] = array();
    }
    return $data;
}

/**
 * 取廠商商品資料
 */
function getVendorProduct() {
    global $postDT, $db;
    $code = $postDT["code"];
    $result = $db->execute("SELECT
            stockinfo.`code` AS `code`,
            stockinfo.vendors_code AS vendors_code,
            product_category.`Name` AS category,
            stockinfo.`name` AS `name`,
            product_unit.`Name` AS unit,
            stockinfo.selling_price AS selling_price,
            stockinfo.safe_stock AS safe_stock,
            stockinfo.stock_num AS stock_num,
            stockinfo.unit_cost AS unit_cost,
            stockinfo.excluded_tax_total AS excluded_tax_total,
            stockinfo.tax AS tax,
            stockinfo.remark AS remark,
            stockinfo.description AS description,
            stockinfo.update_date AS update_date,
            stockinfo.`enable` AS `enable`
            FROM stockinfo
            INNER JOIN product_unit ON product_unit.SN = stockinfo.unit
            INNER JOIN product_category ON product_category.SN = stockinfo.category
            WHERE stockinfo.`enable` = 'true' and stockinfo.vendors_code LIKE '%$code%'
            ORDER BY stockinfo.code ASC");
    if (count($result) > 0) {
        foreach ($result as $row) {
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
    return $data;
}

/**
 * 新增產品
 * @return bool|mysqli_result
 */
function InsertStock() {
    global $postDT, $db;

    $data = array(
        "code" => $postDT["code"],
        "vendors_code" => $postDT["vendors_code"],
        "category" => $postDT["category"],
        "name" => $postDT["name"],
        "unit" => $postDT["unit"],
        "selling_price" => $postDT["selling_price"],
        "safe_stock" => $postDT["safe_stock"],
        "stock_num" => $postDT["stock_num"],
        "unit_cost" => $postDT["unit_cost"],
        "excluded_tax_total" => $postDT["excluded_tax_total"],
        "tax" => $postDT["tax"],
        "remark" => $postDT["remark"],
        "description" => $postDT["description"],
        "update_date" => date("Y-m-d H:i:s"),
        "enable" => "true"
    );
    $result = $db->insert("stockinfo", $data);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 修改庫存
 * @return bool|mysqli_result
 */
function UpdateStock() {
    global $postDT, $db;

    $data = array(
        "vendors_code" => $postDT["vendors_code"],
        "category" => $postDT["category"],
        "name" => $postDT["name"],
        "unit" => $postDT["unit"],
        "selling_price" => $postDT["selling_price"],
        "safe_stock" => $postDT["safe_stock"],
        "stock_num" => $postDT["stock_num"],
        "unit_cost" => $postDT["unit_cost"],
        "excluded_tax_total" => $postDT["excluded_tax_total"],
        "tax" => $postDT["tax"],
        "remark" => $postDT["remark"],
        "description" => $postDT["description"],
        "update_date" => date("Y-m-d H:i:s"),
        "enable" => "true"
    );
    $cond["code"] = $postDT["code"];

    $result = $db->update("stockinfo", $data, $cond);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 刪除庫存
 * @return bool|mysqli_result
 */
function DeleteStock() {
    global $postDT, $db;
    $cond["code"] = $postDT["code"];
    $data["enable"] = "false";
    $result = $db->update("stockinfo", $data, $cond);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 刪除所有庫存
 * @return bool|mysqli_result
 */
function DeleteAllStock() {
    global $db;
    $result = $db->execute("update stockinfo set enable='false'");
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 取得低於安全庫存
 * @return array
 */
function getUnderSafeStock() {
    global $db;
    $result = getStockSQL();
    foreach ($result as $row) {
        if ($row["stock_num"] <= $row["safe_stock"]) {
            $code = "";
            $name = "";
            $contact_name = "";
            $contact_tell_nos = "";
            $la_vendor = explode(',', $row["vendors_code"]);
            $length = count($la_vendor);
            if ($length > 1) {
                $ln_dtIdx = 0;
                foreach ($la_vendor as $ls_vendor) {
                    $dt = $db->execute("select code, name, contact_name, contact_tell_nos from vendorsInfo where code='$ls_vendor' and enable='true'");
                    if (($ln_dtIdx + 1) == $length) {
                        $code .= $dt[0]["code"];
                        $name .= $dt[0]["name"];
                        $contact_name .= $dt[0]["contact_name"];
                        $contact_tell_nos .= $dt[0]["contact_tell_nos"];
                    }
                    else {
                        $code .= $dt[0]["code"].",";
                        $name .= $dt[0]["name"].",";
                        $contact_name .= $dt[0]["contact_name"].",";
                        $contact_tell_nos .= $dt[0]["contact_tell_nos"].",";
                    }
                    $ln_dtIdx++;
                }
            }
            else {
                $dt = $db->execute("select code, name, contact_name, contact_tell_nos from vendorsInfo where code='$la_vendor[0]' and enable='true'");
                $code .= $dt[0]["code"];
                $name .= $dt[0]["name"];
                $contact_name .= $dt[0]["contact_name"];
                $contact_tell_nos .= $dt[0]["contact_tell_nos"];
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
    return $data;
}

/**
 * 取商品排行
 */
function getProductLeaderBoard() {
    global $db, $postDT;
    $vendor_code = $postDT["vendor_code"];
    $result = $db->execute("select * from product_leaderboard where vendor_code='$vendor_code' ORDER BY `count` DESC");
    foreach ($result as $row) {
        $data[] = array(
            "product_code" => $row["product_code"],
            "product_name" => $row["product_name"]
        );
    }
    return $data;
}

function getSalesProductLeaderBoard() {
    global $db, $postDT;
    $custom_code = $postDT["custom_code"];
    $result = $db->execute("select * from sales_leaderboard where custom_code='$custom_code' order BY `count` DESC");
    foreach ($result as $row) {
        $data[] = array(
            "product_code" => $row["product_code"],
            "product_name" => $row["product_name"]
        );
    }
    return $data;
}