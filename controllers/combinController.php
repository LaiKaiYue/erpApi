<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/8
 * Time: 下午10:03
 */
require_once "../model/db.php";

function qryAllCombination() {
    global $db;
    $sql = "select b.name as unit_name, a.name, a.code, c.group_name, c.group_num
            from stockinfo a
            inner join product_unit b on b.SN = a.unit
            inner join combineinfo c on c.stock_code = a.code
            group by a.code, c.group_num";
    $result = $db->execute($sql);
    return $result === false ? $db->getErrorMessage() : $result;
}

function qryCombinationByStockCode() {
    global $db, $postDT;
    $stock_code = $postDT["stock_code"];
    $group_num = $postDT["group_num"];

    //找組合名稱、單位
    $sql = "select b.name, a.stock_code, a.comb_code, a.count, c.Name as unit_name, a.group_name, a.group_num
            from combineinfo a
            inner join stockinfo b on b.code = a.comb_code
            inner join product_unit c on c.SN = b.unit
            where a.stock_code = '$stock_code' and a.group_num = '$group_num'";
    $result = $db->execute($sql);

    //找產品名稱、單位
    $s = "select a.name stock_name, b.Name stock_unit_name from stockinfo a
          inner join product_unit b on a.unit = b.SN 
          where code='$stock_code'";
    $dt = $db->execute($s);

    $data = __::map($result, function ($item) use ($dt) {
        return __::extend($item, $dt[0]);
    });
    return $data;
}

function qryCombGroupNameByStockCode() {
    global $db, $postDT;
    $stock_code = $postDT["stock_code"];
    $result = $db->execute("select group_name, group_num from combineinfo WHERE stock_code = '$stock_code'
                    GROUP BY group_num");
    return $result === false ? $db->getErrorMessage() : $result;
}

function saveCombination() {
    global $db, $postDT;
    $saveData = json_decode(json_encode($postDT["saveData"]), true);
    $dt = $db->execute("select COALESCE(MAX(group_num),0) group_num from combineinfo");
    $max_group_num = $dt[0]["group_num"];
    $max_group_num++;
    $execSQL = array();

    for ($i = 0; $i < count($saveData); $i++) {
        $stock_code = $saveData[$i]["stock_code"];
        $comb_code = $saveData[$i]["comb_code"];
        $count = $saveData[$i]["count"];
        $group_num = $saveData[$i]["group_num"];
        $group_name = $saveData[$i]["group_name"];

        //修改
        if ($group_num != "-1") {
            if ($i == 0) {
                $execSQL[] = "delete from combineinfo where stock_code='$stock_code' and group_num='$group_num'";
            }
        }
        //新增
        else {
            $group_num = $max_group_num;
        }

        $execSQL[] = "insert into combineinfo (stock_code, comb_code, `count`, group_name, group_num) 
                VALUES ('$stock_code', '$comb_code', '$count', '$group_name', '$group_num')";
    }
    $result = $db->transaction($execSQL);
    return $result === false ? $db->getErrorMessage() : $result;
}

function delCombinationByStockCode() {
    global $db, $postDT;
    $cond["stock_code"] = $postDT["stock_code"];
    $cond["group_num"] = $postDT["group_num"];
    $result = $db->delete("combineinfo", $cond);
    return $result === false ? $db->getErrorMessage() : $result;
}

function delCombinationByCombCode() {
    global $db, $postDT;
    $cond["stock_code"] = $postDT["stock_code"];
    $cond["group_num"] = $postDT["group_num"];
    $cond["comb_code"] = $postDT["comb_code"];
    $result = $db->delete("combineinfo", $cond);

    return $result === false ? $db->getErrorMessage() : $result;
}