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
    case "saveCombination":
        $result = saveCombination();
        echo json_encode($result);
        break;
    case "delCombinationByStockCode":
        $result = delCombinationByStockCode();
        echo json_encode($result);
        break;
    case "delCombinationByCombCode":
        $result = delCombinationByCombCode();
        echo json_encode($result);
        break;
}

function qryAllCombination() {
    global $link;
    $sql = "select b.name as unit_name, a.name, a.code, c.group_name, c.group_num
            from stockinfo a
            inner join product_unit b on b.SN = a.unit
            inner join combineinfo c on c.stock_code = a.code
            group by a.code, c.group_num";
    $result = $link->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $data[] = array(
            "stock_code" => $row["code"],
            "stock_name" => $row["name"],
            "stock_unit_name" => $row["unit_name"],
            "group_name" => $row["group_name"],
            "group_num" => $row["group_num"]
        );
    }
    $link->close();
    return $data;
}

function qryCombinationByStockCode() {
    global $link, $postDT;
    $stock_code = $postDT["stock_code"];
    $group_num = $postDT["group_num"];

    //找組合名稱、單位
    $sql = "select b.name, a.stock_code, a.comb_code, a.count, c.Name as unit_name, a.group_name, a.group_num
            from combineinfo a
            inner join stockinfo b on b.code = a.comb_code
            inner join product_unit c on c.SN = b.unit
            where a.stock_code = '$stock_code' and a.group_num = '$group_num'";
    $result = $link->query($sql);

    //找產品名稱、單位
    $s = "select a.name, b.Name from stockinfo a
          inner join product_unit b on a.unit = b.SN 
          where code='$stock_code'";
    $r = $link->query($s);
    $dt = $r->fetch_array();
    $stock_name = $dt["name"];
    $stock_unit_name = $dt["Name"];

    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $data[] = array(
            "stock_code" => $row["stock_code"],
            "stock_name" => $stock_name,
            "stock_unit_name" => $stock_unit_name,
            "comb_name" => $row["name"],
            "comb_code" => $row["comb_code"],
            "comb_unit_name" => $row["unit_name"],
            "count" => $row["count"],
            "group_name" => $row["group_name"],
            "group_num" => $row["group_num"]
        );
    }
    $link->close();
    return $data;
}

function saveCombination() {
    global $link, $postDT;
    $saveData = json_decode(json_encode($postDT["saveData"]), true);

    $s1 = "select COALESCE(MAX(group_num),0) group_num from combineinfo";
    $r1 = $link->query($s1);
    $dt = $r1->fetch_array(MYSQLI_ASSOC);
    $max_group_num = $dt["group_num"];
    $max_group_num++;


    for ($i = 0; $i < count($saveData); $i++) {

        $stock_code = $saveData[$i]["stock_code"];
        $comb_code = $saveData[$i]["comb_code"];
        $count = $saveData[$i]["count"];
        $group_num = $saveData[$i]["group_num"];
        $group_name = $saveData[$i]["group_name"];

        //修改
        if ($group_num != "-1") {
            if ($i == 0) {
                $s = "delete from combineinfo where stock_code='$stock_code' and group_num='$group_num'";
                $r = $link->query($s);
            }
        }
        //新增
        else {
            $group_num = $max_group_num;
        }

        $sql = "insert into combineinfo (stock_code, comb_code, `count`, group_name, group_num) 
                VALUES ('$stock_code', '$comb_code', '$count', '$group_name', '$group_num')";
        $result = $link->query($sql);
    }
    $link->close();
    return $result;
}

function delCombinationByStockCode() {
    global $link, $postDT;
    $stock_code = $postDT["stock_code"];
    $group_num = $postDT["group_num"];
    $sql = "delete from combineinfo where stock_code='$stock_code' and group_num='$group_num'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

function delCombinationByCombCode() {
    global $link, $postDT;
    $stock_code = $postDT["stock_code"];
    $group_num = $postDT["group_num"];
    $comb_code = $postDT["comb_code"];
    $sql = "delete from combineinfo 
            where stock_code='$stock_code' and group_num='$group_num' and comb_code='$comb_code'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

