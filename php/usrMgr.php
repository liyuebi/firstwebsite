<?php
	
include "database.php";
include "regtest.php";
include "constant.php";

// admin login
// 判断是否登录
/*
session_start();
if (!$_SESSION["isLogin"]) {
	echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
	return;
}
*/

$phonenum = trim(htmlspecialchars($_POST['phonenum']));
$username = htmlspecialchars($_POST['name']);

// 验证电话号码
if (!isValidCellPhoneNum($phonenum)) {
	echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'电话号码格式不对，请重新填写！'));
	return;
}

$con = connectToDB();
if (!$con)
{
	echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'数据库连接失败，请稍后重试！','sql_error'=>mysql_error()));
	return;
}
else 
{		  
	$result = createUserTable();
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'用户表创建失败，请稍后重试！','sql_error'=>mysql_error())); 
		return;
	}
	$result = createCreditTable();
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'积分表创建失败，请稍后重试！','sql_error'=>mysql_error())); 
		return;
	}
	
	$userid = 0;
	
	$sql = "select * from User where PhoneNum='$phonenum'";
	$result = mysql_query($sql, $con);
	$newuserid = 0;
	$now = time();
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'账号查询出错，请稍后重试！','sql_error'=>mysql_error()));
		return;
	}
	else {
		$num = mysql_num_rows($result);
		if ($num == 0) {
			$pwd = md5('000000');
			$pwd = password_hash($pwd, PASSWORD_DEFAULT);
			$result = mysql_query("insert into User (PhoneNum, Name, Password, RegisterTime, ReferreeId)
				VALUES('$phonenum', '$username', '$pwd', '$now', '$userid')");
			if (!$result) {
				echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'创建账号失败，请稍后重试！','sql_error'=>mysql_error()));
				return;
			}
			else {
				$newuserid = mysql_insert_id();
			}
		}
		else {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'该手机号已经注册过！','sql_error'=>mysql_error()));
			return;			
		}
	}
		
	if (0 != $newuserid) {
		$result = mysql_query("select * from Credit where UserId='$newuserid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'积分查询出错，请稍后重试！','sql_error'=>mysql_error()));
			return;
		}
		else {
			$num = mysql_num_rows($result);
			if ($num == 0) {
				$result = mysql_query("insert into Credit (UserId)
					VALUES('$newuserid')");
				if (!$result) {
// 					echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'创建积分失败，请稍后重试！','sql_error'=>mysql_error()));
// 					return;
				}
				else {
// 					echo "Register success<br />";
				}
			}					
			else {
			}
		}
	}
	
	echo json_encode(array('error'=>'false'));
	
	// 更新统计数据
	include "../php/func.php";
	insertRecommendStatistics(0);
	
	mysql_close($con);
/*
	$home_url = '../html/home.php';
	header('Location: ' . $home_url);
*/

	return;
}

?>