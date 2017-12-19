<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2017/12/17
 * Time: 下午4:56
 */

header("Content-Type:text/html; charset=utf-8");
require_once "config.php";

switch ($func) {
    case "qryAllCombination":
        $result = qryAllCombination();
        echo json_encode($result);
        break;
    case "qryCombinationByStockCode":
        $result = qryCombinationByStockCode();
        echo json_encode($result);
        break;
    case "insCombination":
        $result = insCombination();
        echo json_encode($result);
        break;
    case "saveCombination":
        $result = saveCombination();
        echo json_encode($result);
        break;
}

function qryAllCombination() {
    global $link;
    $sql = "select b.name, a.stock_code, a.comb_code, a.count, c.Name as unit_name from combineinfo a
            inner join stockinfo b on b.code = a.stock_code or b.code = a.comb_code
            inner join product_unit c on c.SN = b.unit
            GROUP BY a.stock_code";
    $result = $link->query($sql);
    $counter = 0;
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $data[] = array(
            "stock_code" => $row["stock_code"],
            "stock_name" => $row["name"],
            "stock_unit_name" => $row["unit_name"]
        );
    }
    $link->close();
    return $data;
}

function qryCombinationByStockCode() {
    global $link, $postDT;
    $stock_code = $postDT["stock_code"];
    $sql = "select b.name, a.stock_code, a.comb_code, a.count, c.Name as unit_name from combineinfo a
            inner join stockinfo b on b.code = a.stock_code or b.code = a.comb_code
            inner join product_unit c on c.SN = b.unit
            where a.stock_code = '$stock_code'";
    $result = $link->query($sql);
    $counter = 0;
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $counter++;

        if ($counter % 2 == 1) {
            $stock_name = $row["name"];
            $stock_unit_name = $row["unit_name"];
        }
        else {
            $data[] = array(
                "stock_code" => $row["stock_code"],
                "stock_name" => $stock_name,
                "stock_unit_name" => $stock_unit_name,
                "comb_name" => $row["name"],
                "comb_code" => $row["comb_code"],
                "comb_unit_name" => $row["unit_name"],
                "count" => $row["count"]
            );
        }
    }
    $link->close();
    return $data;
}

function insCombination() {
    global $link, $postDT;
    $insData = json_decode(json_encode($postDT["insData"]), true);

    for ($i = 0; $i < count($insData); $i++) {
        $stock_code = $insData[$i]["stock_code"];
        $comb_code = $insData[$i]["comb_code"];
        $count = $insData[$i]["count"];

        $sql = "insert into combineinfo (stock_code, comb_code, `count`) values ('$stock_code', '$comb_code', '$count')";
        $result = $link->query($sql);
    }
    $link->close();
    return $result;
}

function saveCombination() {
    global $link, $postDT;
    $saveData = json_decode(json_encode($postDT["saveData"]), true);


    for ($i = 0; $i < count($saveData); $i++) {

        $stock_code = $saveData[$i]["stock_code"];
        $comb_code = $saveData[$i]["comb_code"];
        $count = $saveData[$i]["count"];

        if ($i == 0) {
            $s = "delete from combineinfo where stock_code='$stock_code'";
            $r = $link->query($s);
        }

        $sql = "insert into combineinfo (stock_code, comb_code, `count`) VALUES ('$stock_code', '$comb_code', '$count')";
        $result = $link->query($sql);
    }
    $link->close();
    return $result;
}

