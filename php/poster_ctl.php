<?php

include_once "database.php";

if (!isset($_POST['func'])) {
	exit('非法访问！');
}

if ("addNew" == $_POST['func']) {
	addNewPoster();
}
else if ("edit" == $_POST['func']) {
	editPoster();
}
else if ("post" == $_POST['func']) {
	postPoster();
}
else if ("unpost" == $_POST['func']) {
	unpostPoster();
}

function addNewPoster()
{
	$title = trim(htmlspecialchars($_POST["tle"]));
	$content = trim(htmlspecialchars($_POST["cont"]));
	
	if (strlen($title) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'标题不能为空！'));
		return;
	}
	
	if (strlen($content) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'内容不能为空！'));
		return;
	}
	
	$now = time();
	
	date_default_timezone_set('PRC');
	$filename = date("YmdHis", $now) . ".txt";
	
	$fileNameStr = dirname(__FILE__) . "/../poster/" . $filename;
	if (file_exists($fileNameStr)) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'文件名重复，请稍后重试！'));
		return;		
	}
	
	$file = fopen($fileNameStr, "w");
	if (!$file) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'打开文件失败！'));
		return;
	}
	
	fwrite($file, $content);
	fclose($file);
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	createPostTable();

	$res = mysql_query("insert into PostTable (Title, TextFile, AddTime) values('$title', '$filename', '$now')");
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'添加公告失败！'));
		return;		
	}
	
	echo json_encode(array('error'=>'false'));
}

function editPoster()
{
	$idx = trim(htmlspecialchars($_POST["idx"]));
	$title = trim(htmlspecialchars($_POST["tle"]));
	$content = trim(htmlspecialchars($_POST["cont"]));
	
	if (strlen($title) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'标题不能为空！'));
		return;
	}
	
	if (strlen($content) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'内容不能为空！'));
		return;
	}
	
	$now = time();
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$res = mysql_query("select * from PostTable where IndexId='$idx'");
	if (!$res || mysql_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到对应的公告！'));
		return;		
	}
	$row = mysql_fetch_assoc($res);
	$filename = $row["TextFile"];
	$fileNameStr = dirname(__FILE__) . "/../poster/" . $filename;
	// if cannot find previous file, create a new file -- but this should not happen
	if (!file_exists($fileNameStr)) {
		date_default_timezone_set('PRC');
		$filename = date("YmdHis", $now) . ".txt";	
		$fileNameStr = dirname(__FILE__) . "/../poster/" . $filename;	
	}
	
	$file = fopen($fileNameStr, "w");
	if (!$file) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'打开文件失败！'));
		return;
	}
	
	fwrite($file, $content);
	fclose($file);
	
	$res2 = mysql_query("update PostTable set Title='$title', TextFile='$filename', LMT='$now' where IndexId='$idx'");
	if (!$res2) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'更新数据库失败，请稍后重试！'));
		return;		
	}
	
	echo json_encode(array('error'=>'false'));

}

function postPoster()
{
	$idx = trim(htmlspecialchars($_POST["idx"]));	
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$res = mysql_query("select * from PostTable where IndexId='$idx'");
	if (!$res || mysql_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到对应的公告！'));
		return;		
	}
	
	include "constant.php";
	$now = time();
	$res2 = mysql_query("update PostTable set Status='$postStatusOnline', OnlineTime='$now' where IndexId='$idx'");
	if (!$res2) {
		echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'修改公告状态失败！'));
		return;				
	}
	
	echo json_encode(array('error'=>'false', 'idx'=>$idx));
}

function unpostPoster()
{
	$idx = trim(htmlspecialchars($_POST["idx"]));	
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$res = mysql_query("select * from PostTable where IndexId='$idx'");
	if (!$res || mysql_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到对应的公告！'));
		return;		
	}
	
	include "constant.php";
	$now = time();
	$res2 = mysql_query("update PostTable set Status='$postStatusDown', ReT='$now' where IndexId='$idx'");
	if (!$res2) {
		echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'修改公告状态失败！'));
		return;				
	}
	
	echo json_encode(array('error'=>'false', 'idx'=>$idx));
}

?>