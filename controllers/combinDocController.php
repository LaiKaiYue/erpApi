<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/6
 * Time: 下午6:42
 */

require_once "../common/url.php";
require_once "../common/tools.php";
require_once "../model/db.php";
require_once "../vendor/autoload.php";

/**
 * 取可組合商品
 * @return array|bool|string
 */
function qryCombProduct() {
    global $db;
    $result = $db->execute("select stock.name stock_name, stock.code stock_code from combineinfo comb
                            INNER JOIN stockinfo stock ON stock.code = comb.stock_code GROUP BY comb.stock_code");
    return $result === false ? $db->getErrorMessage() : $result;
}

function qryAllCombDocMn() {
    global $db;
    $dt = $db->execute("select mn.order_num, mn.date, stock.name, dt.group_name, mn.number  from combdoc_mn mn
          INNER JOIN stockinfo stock on stock.code = mn.stock_code
          INNER JOIN product_unit unit on unit.SN = stock.unit
          INNER JOIN combdoc_dt dt on mn.order_num = dt.order_num
          group by mn.order_num");

    return $dt === false ? $db->getErrorMessage() : $dt;
}

function qryOneCombDocMnByOrderNum() {
    global $db, $postDT;
    $order_num = $postDT["order_num"];

    $dt = $db->execute("select mn.date, mn.stock_code, stock.name as stock_name, unit.`Name` as unit_name, mn.number from combdoc_mn mn
          INNER JOIN stockinfo stock ON stock.code = mn.stock_code
          INNER JOIN product_unit unit ON unit.SN = stock.unit
          WHERE mn.order_num = '$order_num'");

    return $dt === false ? $db->getErrorMessage() : $dt;
}

function qryCombDocDtByOrderNum() {
    global $db, $postDT;
    $order_num = $postDT["order_num"];

    $dt = $db->execute("select dt.group_name, dt.comb_code, stock.`name` as comb_name, dt.count, unit.name as unit_name from combdoc_dt dt
          INNER JOIN stockinfo stock ON stock.code = dt.comb_code
          INNER JOIN product_unit unit ON unit.SN = stock.unit
          WHERE dt.order_num = '$order_num'");

    return $dt === false ? $db->getErrorMessage() : $dt;
}

function insCombDoc() {
    global $db, $postDT, $scale;
    $tools = new tools();
    $order_num = $tools->genOrderNumber();
    $order_name = $postDT["order_name"];
    $date = $postDT["date"];
    $stock_code = $postDT["stock_code"];
    $number = $postDT["number"];
    $comb_group_num = $postDT["group_num"];
    $ins_dat = $tools->genInsOrUpdDateTime();
    $upd_dat = $tools->genInsOrUpdDateTime();
    $execSQL = array();

    $allStock = $db->query("stockinfo");
    $mnStockIndex = array_search($stock_code, array_column($allStock, "code"));
    if ($mnStockIndex === false) return "this order mn product not find";
    $mnStockInfo = $allStock[$mnStockIndex];

    //新增後庫存數量 : 目前庫存 ＋ 新增數量
    $mnUpdStockNumber = bcadd((float)$mnStockInfo["stock_num"], (float)$number, $scale);
    $mnUpdStockNumber = $tools->trimRightZero($mnUpdStockNumber);
    //更新庫存
    $execSQL[] = "update stockinfo set stock_num='$mnUpdStockNumber' WHERE code='$stock_code'";
    //新增組合單Mn
    $execSQL[] = "insert into combdoc_mn (order_num, order_name, `date`, stock_code, `number`, ins_dat, upd_dat)
                  VALUES ('$order_num', '$order_name', '$date', '$stock_code', '$number', '$ins_dat', '$upd_dat')";

    //計算明細庫存
    //增加主檔產品要扣明細原料數量（明細數量跟主檔數量相反）: 0 - 新增數量
    $number = bcsub(0.00, $number, $scale);
    $la_dt = $db->query("combineinfo", "group_num = '$comb_group_num' and stock_code='$stock_code'");
    foreach ($la_dt as $lo_dt) {
        $comb_code = $lo_dt["comb_code"];
        $count = $lo_dt["count"];
        $group_name = $lo_dt["group_name"];

        $dtStockIndex = array_search($comb_code, array_column($allStock, "code"));
        if ($dtStockIndex === false) return "this order mn product not find";
        $dtStockInfo = $allStock[$dtStockIndex];

        //新增後庫存數量 : 目前庫存 ＋ 新增數量 * 組合數量
        $dtUpdStockNumber = bcadd($dtStockInfo["stock_num"], bcmul($number, $count, $scale), $scale);
        $dtUpdStockNumber = $tools->trimRightZero($dtUpdStockNumber);
        //更新庫存
        $execSQL[] = "update stockinfo set stock_num='$dtUpdStockNumber' WHERE code='$comb_code'";
        //新增組合單Dt
        $execSQL[] = "insert into combdoc_dt (order_num, comb_code, `count`, group_name, group_num, ins_dat)
              VALUES ('$order_num', '$comb_code', '$count', '$group_name', '$comb_group_num', '$ins_dat')";
    }
    $result = $db->transaction($execSQL);
    return $result === false ? $db->getErrorMessage() : $result;
}

function updCombDocMnByOrderNum() {
    global $db, $postDT, $scale;
    $tools = new tools();
    $order_num = $postDT["order_num"];
    $date = $postDT["date"];
    $new_combin_number = $postDT["number"];
    $upd_dat = $tools->genInsOrUpdDateTime();
    $execSQL = array();

    $allStock = $db->query("stockinfo");

    $oldCombDocInfo = $db->execute("select * from combdoc_mn mn 
                  INNER JOIN combdoc_dt dt ON mn.order_num = dt.order_num
                  WHERE mn.order_num = '$order_num'");
    $old_combin_number = $oldCombDocInfo[0]["number"];
    $old_date = $oldCombDocInfo[0]["date"];
    $stock_code = $oldCombDocInfo[0]["stock_code"];

    //數量一樣，不計算庫存
    if ($new_combin_number == $old_combin_number) {
        //日期不一樣，僅修改日期
        if ($old_date != $date) {
            $date = date_create($date);
            $updData["date"] = date_format($date, 'Y-m-d');
            $cond["order_num"] = $order_num;
            $result = $db->update("combdoc_mn", $updData, $cond);
            return $result === false ? $db->getErrorMessage() : $result;
        }
        else {
            return true;
        }
    }

    $mnStockIndex = array_search($stock_code, array_column($allStock, "code"));
    if ($mnStockIndex === false) return "this order mn product not find";
    //主檔產品資訊
    $mnStockInfo = $allStock[$mnStockIndex];
    //主檔產品庫存差異 : 新數量 - 舊數量
    $mnStockDiff = bcsub((float)$new_combin_number, (float)$old_combin_number, $scale);

    //修改後主檔產品庫存 : 目前庫存 + 差異
    $mnUpdStockNumber = bcadd((float)$mnStockInfo["stock_num"], (float)$mnStockDiff, $scale);
    $mnUpdStockNumber = $tools->trimRightZero($mnUpdStockNumber);
    //更新主檔庫存
    $execSQL[] = "update stockinfo set stock_num='$mnUpdStockNumber', update_date='$upd_dat' where code='$stock_code'";
    $execSQL[] = "update combdoc_mn set `date`='$date', `number`='$new_combin_number', upd_dat='$upd_dat' where order_num='$order_num'";

    //計算明細庫存
    //增加主檔產品要扣明細原料數量（明細數量跟主檔數量相反）: 0 - 主檔產品數量差異
    $mnStockDiff = bcsub(0.00, (float)$mnStockDiff, $scale);
    foreach ($oldCombDocInfo as $eachCombDt) {
        $comb_code = $eachCombDt["comb_code"];
        $combStockIndex = array_search($comb_code, array_column($allStock, "code"));
        //明細原料資訊
        $combStockInfo = $allStock[$combStockIndex];
        //明細原料庫存差異 : 主檔差異 * 組合原料的數量
        $combStockDiff = bcmul((float)$mnStockDiff, (float)$eachCombDt["count"], $scale);
        //修改後明細原料庫存 : 目前庫存 + 明細差異
        $combUpdStockNumber = bcadd((float)$combStockInfo["stock_num"], (float)$combStockDiff, $scale);
        $combUpdStockNumber = $tools->trimRightZero($combUpdStockNumber);
        //更新明細庫存
        $execSQL[] = "update stockinfo set stock_num='$combUpdStockNumber', update_date='$upd_dat' where code = '$comb_code'";
    }
    $result = $db->transaction($execSQL);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 刪除組合單，刪除加庫存
 * @param {string} order_num: 訂單編號
 * @return bool|string
 */
function delCombDocByOrderNum() {
    global $db, $postDT, $scale;
    $order_num = $postDT["order_num"];
    $tools = new tools();
    $execSQL = array();

    //查主檔資訊
    $qryResult = $db->query("combdoc_mn", "order_num='$order_num'");
    $number = $qryResult[0]["number"];
    $stock_code = $qryResult[0]["stock_code"];
    if (count($qryResult) == 0) {
        return "Order number is not found";
    }

    //查產品資訊
    $stockInfo = $db->query("stockinfo", "code='$stock_code'")[0];
    $stock_num = $stockInfo["stock_num"];
    //更新產品庫存
    $upd_stock_num = $tools->trimRightZero(bcsub($stock_num, $number, $scale));
    $execSQL[] = "update stockinfo set stock_num='$upd_stock_num' WHERE code='$stock_code'";

    //查明細資訊
    $upd_comb_num = 0;
    $qryDtResult = $db->query("combdoc_dt", "order_num='$order_num'");
    __::each($qryDtResult, function ($dt) use (&$execSQL, $db, $number, $upd_comb_num, $scale, $tools) {
        $comb_code = $dt["comb_code"];
        $stock_num = $db->query("stockinfo", "code='$comb_code'")[0]['stock_num'];
        $upd_stock_num = bcadd(bcmul($number, $dt["count"], $scale), $stock_num, $scale);
        $upd_stock_num = $tools->trimRightZero($upd_stock_num);
        $execSQL[] = "update stockinfo set stock_num='$upd_stock_num' where code='$comb_code'";
    });

    $execSQL[] = "delete from combdoc_mn where order_num = '$order_num'";
    $execSQL[] = "delete from combdoc_dt WHERE order_num = '$order_num'";
    $result = $db->transaction($execSQL);
    return $result === false ? $db->getErrorMessage() : $result;
}