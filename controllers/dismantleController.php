<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/26
 * Time: 上午12:18
 */
require_once "../common/url.php";
require_once "../common/tools.php";
require_once "../model/db.php";
require_once "../vendor/autoload.php";

/**
 * 查詢所有拆解組合
 * @return array 所有拆解組合
 */
function qryAllDismantle() {
    global $db;
    $result = $db->execute("select b.name as unit_name, a.name, a.code, c.group_name, c.group_num
            from stockinfo a
            inner join product_unit b on b.SN = a.unit
            inner join dismantleinfo c on c.stock_code = a.code
            group by a.code, c.group_num");
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 依產品拆解組別查詢所有原料
 * @param string stock_code 產品代號
 * @param int group_num 拆解關聯組別代碼
 * @return array 所有拆解原料
 */
function qryDismantleByStockCode() {
    global $db, $postDT;
    $stock_code = $postDT["stock_code"];
    $group_num = $postDT["group_num"];

    //找組合名稱、單位
    $result = $db->execute("select b.name, a.stock_code, a.dism_code, a.count, c.Name as unit_name, a.group_name, a.group_num
            from dismantleinfo a
            inner join stockinfo b ON b.code = a.dism_code
            inner join product_unit c on c.SN = b.unit
            where a.stock_code = '$stock_code' and a.group_num = '$group_num'");
    if ($result === false) return $db->getErrorMessage();

    //找產品名稱、單位
    $dt = $db->execute("select a.name as stock_name, b.Name as stock_unit_name from stockinfo a
          inner join product_unit b on a.unit = b.SN 
          where code='$stock_code'");
    if ($dt === false) return $db->getErrorMessage();

    $data = __::map($result, function ($item) use ($dt) {
        return __::extend($item, $dt[0]);
    });
    return $data;
}

/**
 * 依產品代號查詢拆解關聯組別名稱
 * @param string stock_code 產品代號
 * @return array|bool|string
 */
function qryDismGroupNameByStockCode() {
    global $db, $postDT;
    $stock_code = $postDT["stock_code"];
    $result = $db->execute("select group_name, group_num from dismantleinfo WHERE stock_code = '$stock_code'
                    GROUP BY group_num");
    return $result === false ? $db->getErrorMessage() : $result;
}


function saveDismantle() {
    global $db, $postDT;
    $saveData = json_decode(json_encode($postDT["saveData"]), true);
    $dt = $db->execute("select COALESCE(MAX(group_num),0) group_num from dismantleinfo");
    $max_group_num = $dt[0]["group_num"];
    $max_group_num++;
    $execSQL = array();

    for ($i = 0; $i < count($saveData); $i++) {
        $stock_code = $saveData[$i]["stock_code"];
        $dism_code = $saveData[$i]["dism_code"];
        $count = $saveData[$i]["count"];
        $group_num = $saveData[$i]["group_num"];
        $group_name = $saveData[$i]["group_name"];

        //修改
        if ($group_num != "-1") {
            if ($i == 0) {
                $execSQL[] =
                $s = "delete from dismantleinfo where stock_code='$stock_code' and group_num='$group_num'";
                $cond = array(
                    "stock_code" => $stock_code,
                    "group_num" => $group_num
                );
                $result = $db->delete("dismantleinfo", $cond);
                if ($result === false) return $db->getErrorMessage();
            }
        }
        //新增
        else {
            $group_num = $max_group_num;
        }

        $data_array[] = array(
            "stock_code" => $stock_code,
            "dism_code" => $dism_code,
            "count" => $count,
            "group_name" => $group_name,
            "group_num" => $group_num
        );
        $result = $db->insert("dismantleinfo", $data_array);
    }
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 依產品代號組別代號刪除拆解關聯
 * @param string stock_code 產品代號
 * @param int group_num 組別代號
 * @return bool
 */
function delDismantleByStockCode() {
    global $db, $postDT;
    $stock_code = $postDT["stock_code"];
    $group_num = $postDT["group_num"];
    $cond = array(
        "stock_code" => $stock_code,
        "group_num" => $group_num
    );
    $result = $db->delete("dismantleinfo", $cond);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 依產品組別原料代號刪除拆解原料
 * @param string stock_code 產品代號
 * @param int group_num 組別代號
 * @param string dism_code 原料代號
 * @return bool
 */
function delDismantleByDismCode() {
    global $db, $postDT;
    $stock_code = $postDT["stock_code"];
    $group_num = $postDT["group_num"];
    $dism_code = $postDT["dism_code"];

    $cond = array(
        "stock_code" => $stock_code,
        "group_num" => $group_num,
        "dism_code" => $dism_code
    );
    $result = $db->delete("dismantleinfo", $cond);
    return $result === false ? $db->getErrorMessage() : $result;
}