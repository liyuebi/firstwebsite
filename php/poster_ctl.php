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
else if ("delete" == $_POST['func']) {
	deletePoster();
}

function addNewPoster()
{
	$title = trim(htmlspecialchars($_POST["title"]));
	$content = trim(htmlspecialchars($_POST["content"]));
	$imgfile = $_FILES['file'];

	if (strlen($title) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'标题不能为空！'));
		return;
	}
	
	// make sure the poster has proper content
	if (strlen($content) <= 0 && $imgfile == '') {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'公告正文和图片不能为空！'));
		return;
	}
		
	$now = time();
	$textFileName = '';
	$imgFileName = '';
	
	if (strlen($content) > 0) {
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
		
		$textFileName = $filename;
	}
	
	if ($imgfile != '') {
		
		$imgType = $imgfile['type'];
		$imgSize = $imgfile['size'];
		$error = $imgfile['error'];
		
		if ($error != 0) {
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'上传图片出错，出错码是 ' . $error));
			return;
		}
		
		if ($imgSize > 1024 * 1024) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'图片大小不能超过1MB！'));
			return;
		}
		
		if ($imgType != 'image/jpeg' && $imgType != 'image/png') {
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'图片格式不对，请使用jpg或png格式的图片，使用图片的格式是' . $imgType));
			return;
		}
		
		$name = $imgfile['name'];
		$pos = strpos($name, '.');
		$prename = substr($name, 0, $pos);
		$postname = substr($name, $pos);
		
		$prename .= '_' . $now;
		$imgFileName = $prename . $postname;
		
		$new_name = dirname(__FILE__) . '/../poster/' . $imgFileName;
		move_uploaded_file($imgfile['tmp_name'], $new_name);
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	createPostTable($con);

	$res = mysqli_query($con, "insert into PostTable (Title, TextFile, Pic, AddTime) values('$title', '$textFileName', '$imgFileName', '$now')");
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'添加公告失败！','sql_error'=>mysqli_error($con)));
		return;		
	}
	
	echo json_encode(array('error'=>'false'));
}

function editPoster()
{
	$idx = trim(htmlspecialchars($_POST["idx"]));
	$title = trim(htmlspecialchars($_POST["title"]));
	$content = trim(htmlspecialchars($_POST["content"]));
	$imgfile = $_FILES['file'];
	
	if (strlen($title) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'标题不能为空！'));
		return;
	}
	
	$now = time();
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$res = mysqli_query($con, "select * from PostTable where IndexId='$idx'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到对应的公告！'));
		return;		
	}
	$row = mysqli_fetch_assoc($res);
	
	$textFileName = $row["TextFile"];
	$imgFileName = $row['Pic'];
	
	// 更新文本
	$filename = $row["TextFile"];
	if ($filename != '' || strlen($content) > 0) {
		$fileNameStr = dirname(__FILE__) . "/../poster/" . $filename;
		
		// if cannot find previous file, create a new file -- but this should not happen
		if ($filename == '' || !file_exists($fileNameStr)) {
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
		
		$textFileName = $filename;
	}
	
	// 如果有新选择的图片，保存新图片
	if ($imgfile != '') {
		
		$imgType = $imgfile['type'];
		$imgSize = $imgfile['size'];
		$error = $imgfile['error'];
		
		if ($error != 0) {
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'上传图片出错，出错码是 ' . $error));
			return;
		}
		
		if ($imgSize > 1024 * 1024) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'图片大小不能超过1MB！'));
			return;
		}
		
		if ($imgType != 'image/jpeg' && $imgType != 'image/png') {
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'图片格式不对，请使用jpg或png格式的图片，使用图片的格式是' . $imgType));
			return;
		}
		
		$name = $imgfile['name'];
		$pos = strpos($name, '.');
		$prename = substr($name, 0, $pos);
		$postname = substr($name, $pos);
		
		$prename .= '_' . $now;
		$imgFileName = $prename . $postname;
		
		$new_name = dirname(__FILE__) . '/../poster/' . $imgFileName;
		move_uploaded_file($imgfile['tmp_name'], $new_name);
	}
	else {
		// 如果没有新图片，检查是否删除了原图
		if ($row['Pic'] != '') {
			$rmOri = trim(htmlspecialchars($_POST['rmOriImg']));
			if ($rmOri == '1') {
				$imgFileName = '';
			}
		}
	}

	
	$res2 = mysqli_query($con, "update PostTable set Title='$title', TextFile='$filename', Pic='$imgFileName', LMT='$now' where IndexId='$idx'");
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
	
	$res = mysqli_query($con, "select * from PostTable where IndexId='$idx'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到对应的公告！'));
		return;		
	}
	
	include "constant.php";
	$now = time();
	$res2 = mysqli_query($con, "update PostTable set Status='$postStatusOnline', OnlineTime='$now' where IndexId='$idx'");
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
	
	$res = mysqli_query($con, "select * from PostTable where IndexId='$idx'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到对应的公告！'));
		return;		
	}
	
	include "constant.php";
	$now = time();
	$res2 = mysqli_query($con, "update PostTable set Status='$postStatusDown', ReT='$now' where IndexId='$idx'");
	if (!$res2) {
		echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'修改公告状态失败！'));
		return;				
	}
	
	echo json_encode(array('error'=>'false', 'idx'=>$idx));
}

function deletePoster()
{
	$idx = trim(htmlspecialchars($_POST["idx"]));		
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$res = mysqli_query($con, "select * from PostTable where IndexId='$idx'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到对应的公告！'));
		return;		
	}
	
	$res2 = mysqli_query($con, "delete from PostTable where IndexId='$idx'");
	if (!$res2) {
		echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'删除出错','sql_error'=>mysqli_error($con)));
		return;				
	}
	
	echo json_encode(array('error'=>'false', 'idx'=>$idx));
}

?>