<?php

header('Content-type:text/html;charset=utf-8');
session_start();
$time = 1 * $_POST['time'];
$log=$_SESSION['log'];
$arr = json_decode($log, true);
$nowlog=array_shift($arr);
$_SESSION['log']=  json_encode($arr);
sleep($time);
echo json_encode($nowlog);
