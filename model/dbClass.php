<?php

/**
 * Created by PhpStorm.
 * User: lai.kaiyue
 * Date: 2018/1/6
 * Time: 下午4:38
 */
class DBClass {
    private $mysql_address = "";
    private $mysql_username = "";
    private $mysql_password = "";
    private $mysql_database = "";
    private $link;
    private $last_sql = "";
    private $last_id = 0;
    private $last_num_rows = 0;
    private $error_message = "";

    /**
     * 這段是『建構式』會在物件被 new 時自動執行，裡面主要是建立跟資料庫的連接，並設定語系是萬國語言以支援中文
     */
    public function __construct($mysql_address, $mysql_username, $mysql_password, $mysql_database) {
        $this->mysql_address = $mysql_address;
        $this->mysql_username = $mysql_username;
        $this->mysql_password = $mysql_password;
        $this->mysql_database = $mysql_database;

        $this->link = ($GLOBALS["___mysqli_ston"] = mysqli_connect($this->mysql_address, $this->mysql_username, $this->mysql_password));

        if (mysqli_connect_errno()) {
            $this->error_message = "Failed to connect to MySQL: ".mysqli_connect_error();
            echo $this->error_message;
            return false;
        }
        mysqli_query($GLOBALS["___mysqli_ston"], "SET NAMES utf8");
        mysqli_query($this->link, "SET NAMES utf8");
        mysqli_query($this->link, "SET CHARACTER_SET_database= utf8");
        mysqli_query($this->link, "SET CHARACTER_SET_CLIENT= utf8");
        mysqli_query($this->link, "SET CHARACTER_SET_RESULTS= utf8");

        if (!(bool)mysqli_query($this->link, "USE ".$this->mysql_database)) {
            $this->error_message = 'Database '.$this->mysql_database.' does not exist!';
            return false;
        }
        return $this;
    }

    /**
     * 這段是『解構式』會在物件被 unset 時自動執行，裡面那行指令是切斷跟資料庫的連接
     */
    public function __destruct() {
        mysqli_close($this->link);
    }

    /**
     * 執行同transcation下的交易
     * @param array $la_sql {array} 要執行的sql
     * @return bool|string 執行結果
     */
    public function transaction($la_sql = array()) {
        if ($la_sql === []) {
            $this->error_message = "sql command is Empty";
            return false;
        }
        mysqli_report(MYSQLI_REPORT_STRICT);
        $this->link->autocommit(false);

        try {
            foreach ($la_sql as $lo_sql) {
                if (!$this->link->query($lo_sql)) {
                    throw new Exception(mysqli_error($this->link));
                }
                else{
                    $ins_dat = date("Y-m-d H:i:s");
                    $apiLogSql = "insert into api_logs (execSQL, response, ins_dat) values ('".addslashes($lo_sql)."', 'exec sql scuess', '$ins_dat')";
                    $this->link->query($apiLogSql);
                }
            }
            $this->link->commit();
            return true;
        }
        catch (Exception $e) {
            $this->error_message = $e->getMessage();
            $this->link->rollback();
            $ins_dat = date("Y-m-d H:i:s");
            $execSQL = addslashes(json_encode($la_sql));
            $apiLogSql = "insert into api_logs (execSQL, response, ins_dat) values ('$execSQL', '".addslashes($this->error_message)."', '$ins_dat')";
            $this->link->query($apiLogSql);
            $this->link->commit();
            return false;
        }
    }

    /**
     * 這段用來執行 MYSQL 資料庫的語法，可以靈活使用
     */
    public function execute($sql = null) {
        if ($sql === null or trim($sql) === "") {
            $this->error_message = "sql command is empty";
            return false;
        }
        $this->last_sql = str_ireplace("DROP", "", $sql);
        $result_set = array();

        $result = mysqli_query($this->link, $this->last_sql);

        if (((is_object($this->link)) ? mysqli_error($this->link) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))) {
            $this->error_message = "MySQL ERROR: ".((is_object($this->link)) ? mysqli_error($this->link) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
            $this->insSqlLog();
            return false;
        }
        else {
            $this->last_num_rows = @mysqli_num_rows($result);
            for ($xx = 0; $xx < @mysqli_num_rows($result); $xx++) {
                $result_set[$xx] = mysqli_fetch_assoc($result);
            }
            if (isset($result_set)) {
                $this->insSqlLog();
                if (count($result_set) == 0) {
                    return true;
                }
                else {
                    return $result_set;
                }
            }
            else {
                $this->error_message = "result: zero";
                $this->insSqlLog();
                return false;
            }
        }
    }

    /**
     * 這段用來讀取資料庫中的資料，回傳的是陣列資料
     */
    public function query($table = null, $condition = "1", $order_by = "1", $fields = "*", $limit = "") {
        $sql = "SELECT {$fields} FROM {$table} WHERE {$condition} ORDER BY {$order_by} {$limit}";
        return $this->execute($sql);
    }

    /**
     * 這段可以新增資料庫中的資料，並把最後一筆的 ID 存到變數中，可以用 getLastId() 取出
     */
    public function insert($table = null, $data_array = array()) {
        if ($table === null) return false;
        if (count($data_array) == 0) return false;

        $tmp_col = array();
        $tmp_dat = array();

        foreach ($data_array as $key => $value) {
            $value = mysqli_real_escape_string($this->link, $value);
            $tmp_col[] = $key;
            $tmp_dat[] = "'$value'";
        }
        $columns = join(",", $tmp_col);
        $data = join(",", $tmp_dat);

        $this->last_sql = "INSERT INTO $table (".$columns.") VALUES (".$data.")";
        $result = mysqli_query($this->link, $this->last_sql);

        if (((is_object($this->link)) ? mysqli_error($this->link) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))) {
            $this->error_message = "MySQL Insert Error: ".((is_object($this->link)) ? mysqli_error($this->link) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
            $this->insSqlLog();
            return false;
        }
        else {
            if ($result == false) $this->error_message = "insert error";
            $this->last_id = mysqli_insert_id($this->link);
            $this->insSqlLog();
            return $result;
        }
    }

    /**
     * 這段可以更新資料庫中的資料
     */
    public function update($table = null, $data_array = null, $condition = null) {
        if ($table == null) {
            echo "table is null";
            return false;
        }
        if ($condition == null) return false;
        if (count($data_array) == 0) return false;

        $setting_list = "";
        $cond_list = "";

        $index = 0;
        foreach ($data_array as $key => $value) {
            $value = mysqli_real_escape_string($this->link, $value);
            $setting_list .= $key."="."\"".$value."\"";
            if ($index != count($data_array) - 1) {
                $setting_list .= ", ";
                $index++;
            }
        }

        $index = 0;
        foreach ($condition as $key => $value) {
            $value = mysqli_real_escape_string($this->link, $value);
            $cond_list .= $key."="."\"".$value."\"";
            if ($index != count($condition) - 1) {
                $cond_list .= " and ";
                $index++;
            }
        }

        $this->last_sql = "UPDATE ".$table." SET ".$setting_list." WHERE ".$cond_list;
        $result = mysqli_query($this->link, $this->last_sql);

        if (((is_object($this->link)) ? mysqli_error($this->link) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))) {
            $this->error_message = "MySQL Update Error: ".((is_object($this->link)) ? mysqli_error($this->link) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
            $this->insSqlLog();
            return false;
        }
        else {
            if ($result == false) $this->error_message = "update error";
            $this->insSqlLog();
            return $result;
        }
    }

    /**
     * 這段可以刪除資料庫中的資料
     */
    public function delete($table = null, $condition = null) {
        if ($table === null) return false;
        if ($condition === null) return false;

        $index = 0;
        $cond_list = "";
        foreach ($condition as $key => $value) {
            $value = mysqli_real_escape_string($this->link, $value);
            $cond_list .= $key."="."\"".$value."\"";
            if ($index != count($condition) - 1) {
                $cond_list .= " and ";
                $index++;
            }
        }

        $this->last_sql = "DELETE FROM $table WHERE $cond_list";
        $result = mysqli_query($this->link, $this->last_sql);

        if (((is_object($this->link)) ? mysqli_error($this->link) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))) {
            $this->error_message = "MySQL Delete Error: ".((is_object($this->link)) ? mysqli_error($this->link) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
            $this->insSqlLog();
            return false;
        }
        else {
            if ($result == false) $this->error_message = "delete error";
            $this->insSqlLog();
            return $result;
        }
    }

    /**
     * 紀錄新刪修Log
     */
    private function insSqlLog() {
        $response = $this->error_message == "" ? "exec sql scuessfully" : addslashes($this->error_message);
        $ins_dat = date("Y-m-d H:i:s");
        //查詢暫時不紀錄
        if (stristr($this->last_sql, "select") === false) {
            $sql = "insert into api_logs (execSQL, response, ins_dat) values ('".addslashes($this->last_sql)."', '$response', '$ins_dat')";
            $result = mysqli_query($this->link, $sql);
        }
    }

    /**
     * @return string
     * 這段會把最後執行的語法回傳給你
     */
    public function getLastSql() {
        return $this->last_sql;
    }

    /**
     * @param string $last_sql
     * 這段是把執行的語法存到變數裡，設定成 private 只有內部可以使用，外部無法呼叫
     */
    private function setLastSql($last_sql) {
        $this->last_sql = $last_sql;
    }

    /**
     * @return int
     * 主要功能是把新增的 ID 傳到物件外面
     */
    public function getLastId() {
        return $this->last_id;
    }

    /**
     * @param int $last_id
     * 把這個 $last_id 存到物件內的變數
     */
    private function setLastId($last_id) {
        $this->last_id = $last_id;
    }

    /**
     * @return int
     */
    public function getLastNumRows() {
        return $this->last_num_rows;
    }

    /**
     * @param int $last_num_rows
     */
    private function setLastNumRows($last_num_rows) {
        $this->last_num_rows = $last_num_rows;
    }

    /**
     * @return string
     * 取出物件內的錯誤訊息
     */
    public function getErrorMessage() {
        return $this->error_message;
    }

    /**
     * @param string $error_message
     * 記下錯誤訊息到物件變數內
     */
    private function setErrorMessage($error_message) {
        $this->error_message = $error_message;
    }
}
