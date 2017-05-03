<?php

date_default_timezone_set('PRC');

$bCreateFolder = false;
// $bCreateLogFolder = false;
$bCreateFile = true;

$path = "../log/";
if (!file_exists($path)) {
	mkdir("$path", 0777, true);
	$bCreateFolder = true;
}

/*
$path = "~/Code/Log";
if (!file_exists($path)) {
	mkdir("$path", 0777, true);
	$bCreateLogFolder = true;
	echo 2;
}
echo 1.2;
*/
list($startM, $startTime) = explode(" ", microtime());
$startTimeStr = date("Y-m-d H:i:s", $startTime) . " " . round($startM * 1000);
// echo $startTimeStr;

$fileNameStr = "../log/bonus_" . date("YmdHis", $startTime);
if (file_exists($fileNameStr)) {
	$bCreateFile = false;
}

$file = fopen($fileNameStr, "w");
if (!$file) {
	echo "打开文件失败！";
	return;
}

if ($bCreateFolder) {
	fwrite($file, "创建mifenggongfang文件夹!\n");
}
/*
if ($bCreateLogFolder) {
	fwrite($file, "创建Log文件夹!\n");
}
*/
if (!$bCreateFile) {
	fwrite($file, $fileNameStr . "文件已存在!\n");
}

fwrite($file, "\n\n");
fwrite($file, "***********************" . $startTimeStr . "***********************\n");

include "bonus.php";
calcBonus($file);

/*
$endTime = time();
$endTimeStr = date("Y-m-d H:i:s" , $endTime);
$endTime1 = microtime();
echo $endTimeStr;
echo $endTime1;
*/

list($endM, $endTime) = explode(" ", microtime());
$endTimeStr = date("Y-m-d H:i:s", $endTime) . " " . round($endM * 1000);	
// echo $endTimeStr;

$spanS = $endTime - $startTime;
$spanM = ($endM - $startM) * 1000;
if ($spanM < 0) {
	$spanS -= 1;
	$spanM = 1000 + $spanM;
}
fwrite($file, "***********************" . $endTimeStr . "***********************\n");
// echo "耗时：" . $spanS . "秒" . $spanM . "毫秒！";
fwrite($file, "耗时：" . $spanS . "秒" . $spanM . "毫秒！\n");

fclose($file);

?>