<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/19
 * Time: 上午1:02
 */
require_once "../common/url.php";
require_once "../common/tools.php";
require_once "../model/db.php";
require_once "../vendor/autoload.php";

/**
 * 取可拆解商品
 * @return array|bool|string
 */
function qryDismantleProduct() {
    global $db;
    $result = $db->execute("select stock.name stock_name, stock.code stock_code from dismantleinfo dism
                            INNER JOIN stockinfo stock ON stock.code = dism.stock_code GROUP BY dism.stock_code");
    return $result === false ? $db->getErrorMessage() : $result;
}

function qryAllDismantleDocMn() {
    global $db;
    $dt = $db->execute("select mn.order_num, mn.date, stock.name, dt.group_name, mn.number  from dismdoc_mn mn
          INNER JOIN stockinfo stock on stock.code = mn.stock_code
          INNER JOIN product_unit unit on unit.SN = stock.unit
          INNER JOIN dismdoc_dt dt on mn.order_num = dt.order_num
          group by mn.order_num");

    return $dt === false ? $db->getErrorMessage() : $dt;
}

function qryOneDismantleDocMnByOrderNum() {
    global $db, $postDT;
    $order_num = $postDT["order_num"];

    $dt = $db->execute("select mn.date, mn.stock_code, stock.name as stock_name, unit.`Name` as unit_name, mn.number 
          from dismdoc_mn mn
          INNER JOIN stockinfo stock ON stock.code = mn.stock_code
          INNER JOIN product_unit unit ON unit.SN = stock.unit
          WHERE mn.order_num = '$order_num'");

    return $dt === false ? $db->getErrorMessage() : $dt;
}

function qryDismantleDocDtByOrderNum() {
    global $db, $postDT;
    $order_num = $postDT["order_num"];

    $dt = $db->execute("select dt.group_name, dt.dism_code, stock.`name` as dism_name, dt.count, unit.name as unit_name from dismdoc_dt dt
          INNER JOIN stockinfo stock ON stock.code = dt.dism_code
          INNER JOIN product_unit unit ON unit.SN = stock.unit
          WHERE dt.order_num = '$order_num'");

    return $dt === false ? $db->getErrorMessage() : $dt;
}

function insDismantleDoc() {
    global $db, $postDT, $scale;
    $tools = new tools();
    $order_num = $tools->genOrderNumber();
    $order_name = $postDT["order_name"];
    $date = $postDT["date"];
    $stock_code = $postDT["stock_code"];
    $number = $postDT["number"];
    $dism_group_num = $postDT["group_num"];
    $ins_dat = $tools->genInsOrUpdDateTime();
    $upd_dat = $tools->genInsOrUpdDateTime();
    $execSQL = array();

    $allStock = $db->query("stockinfo");
    $mnStockIndex = array_search($stock_code, array_column($allStock, "code"));
    if ($mnStockIndex === false) return "this order mn product not find";
    $mnStockInfo = $allStock[$mnStockIndex];

    //新增後庫存數量 : 目前庫存 - 拆解數量
    $mnUpdStockNumber = bcsub((float)$mnStockInfo["stock_num"], (float)$number, $scale);
    $mnUpdStockNumber = $tools->trimRightZero($mnUpdStockNumber);
    //更新庫存
    $execSQL[] = "update stockinfo set stock_num='$mnUpdStockNumber' WHERE code='$stock_code'";
    //新增拆解單Mn
    $execSQL[] = "insert into dismdoc_mn (order_num, order_name, `date`, stock_code, `number`, ins_dat, upd_dat)
                  VALUES ('$order_num', '$order_name', '$date', '$stock_code', '$number', '$ins_dat', '$upd_dat')";

    //計算明細庫存
    //拆解主檔產品要加明細原料數量
    $la_dt = $db->query("dismantleinfo", "group_num = '$dism_group_num' and stock_code='$stock_code'");
    foreach ($la_dt as $lo_dt) {
        $dism_code = $lo_dt["dism_code"];
        $count = $lo_dt["count"];
        $group_name = $lo_dt["group_name"];

        $dtStockIndex = array_search($dism_code, array_column($allStock, "code"));
        if ($dtStockIndex === false) return "this order mn product not find";
        $dtStockInfo = $allStock[$dtStockIndex];

        //新增後庫存數量 : 目前庫存 ＋ 拆解數量 * 拆解關聯數量
        $dtUpdStockNumber = bcadd($dtStockInfo["stock_num"], bcmul($number, $count, $scale), $scale);
        $dtUpdStockNumber = $tools->trimRightZero($dtUpdStockNumber);
        //更新庫存
        $execSQL[] = "update stockinfo set stock_num='$dtUpdStockNumber' WHERE code='$dism_code'";
        //新增拆解單Dt
        $execSQL[] = "insert into dismdoc_dt (order_num, dism_code, `count`, group_name, group_num, ins_dat)
              VALUES ('$order_num', '$dism_code', '$count', '$group_name', '$dism_group_num', '$ins_dat')";
    }
    $result = $db->transaction($execSQL);
    return $result === false ? $db->getErrorMessage() : $result;
}

function updDismantleDocMnByOrderNum() {
    global $db, $postDT, $scale;
    $tools = new tools();
    $order_num = $postDT["order_num"];
    $date = $postDT["date"];
    $new_dism_number = $postDT["number"];
    $upd_dat = $tools->genInsOrUpdDateTime();
    $execSQL = array();

    $allStock = $db->query("stockinfo");

    $oldDismDocInfo = $db->execute("select * from dismdoc_mn mn 
                  INNER JOIN dismdoc_dt dt ON mn.order_num = dt.order_num
                  WHERE mn.order_num = '$order_num'");
    $old_dism_number = $oldDismDocInfo[0]["number"];
    $old_date = $oldDismDocInfo[0]["date"];
    $stock_code = $oldDismDocInfo[0]["stock_code"];

    //數量一樣，不計算庫存
    if ($new_dism_number == $old_dism_number) {
        //日期不一樣，僅修改日期
        if ($old_date != $date) {
            $date = date_create($date);
            $updData["date"] = date_format($date, 'Y-m-d');
            $cond["order_num"] = $order_num;
            $result = $db->update("dismdoc_mn", $updData, $cond);
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
    $mnStockDiff = bcsub((float)$new_dism_number, (float)$old_dism_number, $scale);

    //修改後主檔產品庫存 : 目前庫存 - 差異
    $mnUpdStockNumber = bcsub((float)$mnStockInfo["stock_num"], (float)$mnStockDiff, $scale);
    $mnUpdStockNumber = $tools->trimRightZero($mnUpdStockNumber);
    //更新主檔庫存
    $execSQL[] = "update stockinfo set stock_num='$mnUpdStockNumber', update_date='$upd_dat' where code='$stock_code'";
    $execSQL[] = "update dismdoc_mn set `date`='$date', `number`='$new_dism_number', upd_dat='$upd_dat' where order_num='$order_num'";

    //計算明細庫存
    //增加主檔產品要扣明細原料數量: 主檔產品數量差異
//    $mnStockDiff = bcsub("0", $mnStockDiff, $scale);
    foreach ($oldDismDocInfo as $eachDismDt) {
        $dism_code = $eachDismDt["dism_code"];
        $dismStockIndex = array_search($dism_code, array_column($allStock, "code"));
        //明細原料資訊
        $dismStockInfo = $allStock[$dismStockIndex];
        //明細原料庫存差異 : 主檔差異 * 拆解原料的數量
        $dismStockDiff = bcmul((float)$mnStockDiff, (float)$eachDismDt["count"], $scale);
        //修改後明細原料庫存 : 目前庫存 + 明細差異
        $dismUpdStockNumber = bcadd((float)$dismStockInfo["stock_num"], (float)$dismStockDiff, $scale);
        $dismUpdStockNumber = $tools->trimRightZero($dismUpdStockNumber);
        //更新明細庫存
        $execSQL[] = "update stockinfo set stock_num='$dismUpdStockNumber', update_date='$upd_dat' where code = '$dism_code'";
    }
    $result = $db->transaction($execSQL);
    return $result === false ? $db->getErrorMessage() : $result;
}

/**
 * 刪除拆解單，刪除加庫存
 * @param {string} order_num: 訂單編號
 * @return bool|string
 */
function delDismantleDocByOrderNum() {
    global $db, $postDT, $scale;
    $order_num = $postDT["order_num"];
    $tools = new tools();
    $execSQL = array();

    //查主檔資訊
    $qryResult = $db->query("dismdoc_mn", "order_num='$order_num'");
    $number = $qryResult[0]["number"];
    $stock_code = $qryResult[0]["stock_code"];
    if (count($qryResult) == 0) {
        return "Order number is not found";
    }

    //查產品資訊
    $stockInfo = $db->query("stockinfo", "code='$stock_code'")[0];
    $stock_num = $stockInfo["stock_num"];
    //更新產品庫存
    $upd_stock_num = $tools->trimRightZero(bcadd($stock_num, $number, $scale));
    $execSQL[] = "update stockinfo set stock_num='$upd_stock_num' WHERE code='$stock_code'";

    //查明細資訊
    $upd_dism_num = 0;
    $qryDtResult = $db->query("dismdoc_dt", "order_num='$order_num'");
    __::each($qryDtResult, function ($dt) use (&$execSQL, $db, $number, $upd_dism_num, $scale, $tools) {
        $dism_code = $dt["dism_code"];
        $stock_num = $db->query("stockinfo", "code='$dism_code'")[0]['stock_num'];
        $upd_stock_num = bcsub(bcmul($number, $dt["count"], $scale), $stock_num, $scale);
        $upd_stock_num = $tools->trimRightZero($upd_stock_num);
        $execSQL[] = "update stockinfo set stock_num='$upd_stock_num' where code='$dism_code'";
    });

    $execSQL[] = "delete from dismdoc_mn where order_num = '$order_num'";
    $execSQL[] = "delete from dismdoc_dt WHERE order_num = '$order_num'";
    $result = $db->transaction($execSQL);
    return $result === false ? $db->getErrorMessage() : $result;
}