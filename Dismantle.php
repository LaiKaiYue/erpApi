<?php
/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2017/12/21
 * Time: 下午11:14
 */

header("Content-Type:text/html; charset=utf-8");
require_once "config.php";

switch ($func) {
    case "qryAllDismantle":
        $result = qryAllDismantle();
        echo json_encode($result);
        break;
    case "qryDismantleByStockCode":
        $result = qryDismantleByStockCode();
        echo json_encode($result);
        break;
    case "qryDismGroupNameByStockCode":
        $result = qryDismGroupNameByStockCode();
        echo json_encode($result);
        break;
    case "saveDismantle":
        $result = saveDismantle();
        echo json_encode($result);
        break;
    case "delDismantleByStockCode":
        $result = delDismantleByStockCode();
        echo json_encode($result);
        break;
    case "delDismantleByDismCode":
        $result = delDismantleByDismCode();
        echo json_encode($result);
        break;
}

function qryAllDismantle() {
    global $link;
    $sql = "select b.name as unit_name, a.name, a.code, c.group_name, c.group_num
            from stockinfo a
            inner join product_unit b on b.SN = a.unit
            inner join dismantleinfo c on c.stock_code = a.code
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

function qryDismantleByStockCode() {
    global $link, $postDT;
    $stock_code = $postDT["stock_code"];
    $group_num = $postDT["group_num"];

    //找組合名稱、單位
    $sql = "select b.name, a.stock_code, a.dism_code, a.count, c.Name as unit_name, a.group_name, a.group_num
            from dismantleinfo a
            inner join stockinfo b ON b.code = a.dism_code
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
            "dism_name" => $row["name"],
            "dism_code" => $row["dism_code"],
            "dism_unit_name" => $row["unit_name"],
            "count" => $row["count"],
            "group_name" => $row["group_name"],
            "group_num" => $row["group_num"]
        );
    }
    $link->close();
    return $data;
}

function qryDismGroupNameByStockCode() {
    global $link, $postDT;
    $stock_code = $postDT["stock_code"];
    $sql = "select group_name, group_num from dismantleinfo WHERE stock_code = '$stock_code'
                    GROUP BY group_num";
    $result = $link->query($sql);
    while($row = $result->fetch_assoc()){
        $data[] = array(
            "group_name" => $row["group_name"],
            "group_num" => $row["group_num"]
        );
    }

    $link->close();
    return $data;
}

function saveDismantle() {
    global $link, $postDT;
    $saveData = json_decode(json_encode($postDT["saveData"]), true);

    $s1 = "select COALESCE(MAX(group_num),0) group_num from dismantleinfo";
    $r1 = $link->query($s1);
    $dt = $r1->fetch_array(MYSQLI_ASSOC);
    $max_group_num = $dt["group_num"];
    $max_group_num++;

    for ($i = 0; $i < count($saveData); $i++) {

        $stock_code = $saveData[$i]["stock_code"];
        $dism_code = $saveData[$i]["dism_code"];
        $count = $saveData[$i]["count"];
        $group_num = $saveData[$i]["group_num"];
        $group_name = $saveData[$i]["group_name"];

        //修改
        if ($group_num != "-1") {
            if ($i == 0) {
                $s = "delete from dismantleinfo where stock_code='$stock_code' and group_num='$group_num'";
                $r = $link->query($s);
            }
        }
        //新增
        else {
            $group_num = $max_group_num;
        }

        $sql = "insert into dismantleinfo (stock_code, dism_code, `count`, group_name, group_num)
                VALUES ('$stock_code', '$dism_code', '$count', '$group_name', '$group_num')";
        $result = $link->query($sql);
    }
    $link->close();
    return $result;
}

function delDismantleByStockCode() {
    global $link, $postDT;
    $stock_code = $postDT["stock_code"];
    $group_num = $postDT["group_num"];
    $sql = "delete from dismantleinfo where stock_code='$stock_code' and group_num='$group_num'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

function delDismantleByDismCode() {
    global $link, $postDT;
    $stock_code = $postDT["stock_code"];
    $group_num = $postDT["group_num"];
    $dism_code = $postDT["dism_code"];
    $sql = "delete from dismantleinfo 
            where stock_code='$stock_code' and group_num='$group_num' and dism_code='$dism_code'";
    $result = $link->query($sql);
    $link->close();
    return $result;
}

