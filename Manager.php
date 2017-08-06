<?php
/**
 * Created by PhpStorm.
 * Date: 2017/8/6
 * Time: 下午 03:05
 */

header("Content-Type:text/html; charset=utf-8");
require_once "config.php";

$func = "syncSql";

switch ($func) {
    case "syncSql":
        $result = syncSql();
        echo json_encode("success");
        break;
}


function syncSql()
{
    $fileName = backUpSql();
    restoreSql($fileName);
    return "success";
}

function backUpSql()
{
    global $link;

    $mysql = "set charset utf8;\r\n";#for mysql<=5.0
    $q1 = $link->query("show tables");
    while ($t = mysqli_fetch_array($q1)) {
        $table = $t[0];
        $q2 = $link->query("show create table `$table`");
        $sql = mysqli_fetch_array($q2);
        $mysql .= $sql['Create Table'] . ";\r\n\r\n";#DDL

        $q3 = $link->query("select * from `$table`");
        while ($data = mysqli_fetch_assoc($q3)) {
            $keys = array_keys($data);
            $keys = array_map('addslashes', $keys);
            $keys = join('`,`', $keys);
            $keys = "`" . $keys . "`";
            $vals = array_values($data);
            $vals = array_map('addslashes', $vals);
            $vals = join("','", $vals);
            $vals = "'" . $vals . "'";

            $mysql .= "insert into `$table`($keys) values($vals);\r\n";
        }
        $mysql .= "\r\n";
    }

    $filename = "./backup/".date('Ymj') . ".sql"; //文件名為當天的日期
    $fp = fopen($filename, 'w');
    fputs($fp, $mysql);
    fclose($fp);
    echo "<br><center>數據備份成功,生成備份文件" . $filename . "</center>";
    $link->close();
    return $filename;
}

function restoreSql($fname)
{
    global $backup_dbhost, $backup_dbuser, $backup_dbpass, $backup_dbname;
    $link = mysqli_connect($backup_dbhost, $backup_dbuser, $backup_dbpass, $backup_dbname);
    mysqli_set_charset($link, 'utf8');

    $mysql_file = "./backup/".$fname; //指定要恢復的MySQL備份文件路徑,請自已修改此路徑

    if (file_exists($fname)) {
        $sql_value = "";
        $cg = 0;
        $sb = 0;
        $sqls = file($fname);
        foreach ($sqls as $sql) {
            $sql_value .= $sql;
        }
        $a = explode(";\r\n", $sql_value); //根據";\r\n"條件對數據庫中分條執行
        $total = count($a) - 1;
        $link->query("set names 'utf8'");
        for ($i = 0; $i < $total; $i++) {
            $link->query("set names 'utf8'");
            //執行命令
            if ($link->query($a[$i])) {
                $cg += 1;
            } else {
                $sb += 1;
                $sb_command[$sb] = $a[$i];
            }
        }
        echo "<br><center>操作完畢，共處理 $total 條命令，成功 $cg 條，失敗 $sb 條</center>";
    }
}
?>