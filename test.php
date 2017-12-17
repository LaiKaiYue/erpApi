<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 2016/12/27
 * Time: 下午 08:37
 */

header("Content-Type:text/html; charset=utf-8");
require_once "config.php";

//$product = json_decode($postDT["product"]);
//$WorkingArray = json_decode(json_encode($postDT["product"]),true);

echo phpinfo();

//$key[] = array(
//    "key" => "1"
//);
//$data[] = array(
//    "code" => "test",
//    "product" => $key
//);
//
//echo json_encode($data);
//    $username = $_POST["uname"];
//    $apellido = $_POST["apellido"];
//    $cedula = $_POST["cedula"];
//    $email = $_POST["email"];
//    $score = $_POST["score"];
//    print_r($_POST);
//    if (!($link=mysqli_connect(databasemanager,username,password)))
//    {
//        echo "Error conectando a la base de datos.";
//        exit();
//    }
//       if (!mysqli_select_db(database,$link))
//       {
//           echo "Error seleccionando la base de datos.";
//           exit();
//       }
//       try
//       {
//           mysqli_query("insert into scores(name,lastName,email,document,score) values('$username','$apellido','$email','$cedula','$score')",$link);
//           print "done=true";
//       }
//       catch(Exception $e)
//       {
//           print "done=$e->getMessage()";
//       }
//    echo "test=1&done=123123".$username;

?>