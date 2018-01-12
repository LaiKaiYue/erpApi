<?php
/**
 * phpDocumentor 使用示範
 * 這個檔案是一個簡單的示範
 * 內容涵蓋了許多常用的註解方式。
 * 有任何的問題請和作者連絡
 * @package phpDocumentorExample
 * @author 多采多姿 <pkwbim.programming@gmail.com>
 * @version 0.1b
 */

/**
 * 這是 lib.inc.php 的標題
 * 這是 lib.inc.php 的描述
 */
include_once('lib.inc.php');

/**
 * 圓周率
 * 圓周和直徑的比值
 */
define('pi', 3.14159);

/**
 *  這是 funtion1 的註解區塊標題
 *  這是 funtion1 的描述
 *  @global int 這是函式內第一個全域變數的註解，就是 $global1 的註解
 *  @global string 這是函式內第二個全域變數的註解，就是 $global2 的註解
 *  @param bool $arg1 這是函式參數 $arg1 的註解
 *  @param int|string $arg2 這裡是函式參數 $arg2 的註解
 *  @return mixed 傳回值的註解
 */
function function1($arg1, $arg2) {
    global $global1, $global2;
    return array($arg1, $arg2);
}

/**
 * 這是MyClass的標題
 *
 * 建立簡寫型的清單
 * 這裡建立一份無序清單
 * - 項目一
 * - 項目二，
 *   每個項目可以是多行，
 *   就像這個項目，
 *   這行還在項目二中
 * - 項目三
 * 清單結束，因為沒縮排
 * 這裡建立一份有序清單
 * 1 有序的項目一，數字後一定要加一個空白。
 * 2 有序的項目二
 * 有序清單的另一種寫法
 * 1. ordered item 1
 * 2. ordered item 2
 * 清單在此結束
 *
 * @package phpDocumentorExample
 * @author 多采多姿
 * @since 1.0rc1
 * @version 0.2b
 *
 */
class MyClass {
    /**
     * 這裡是成員變數的註解
     *
     * @var string 成員變數的註解
     * @access private
     */
    private $_variable = "Hello";

    /**
     * 這是一個公開的成員函式
     *
     * @param bool $var1 參數1
     * @param string|array $var2 參數1
     * @return void
     * @access public
     */
    public function set_vars($var1, $var2) {
    }
}
?>