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
	mysql_select_db("my_db", $con);
		  
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
	$userNotRegistered = true;
	$now = time();
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'账号查询出错，请稍后重试！','sql_error'=>mysql_error()));
		return;
	}
	else {
		$num = mysql_num_rows($result);
		if ($num == 0) {
			$result = mysql_query("insert into User (PhoneNum, Name, Password, RegisterTime, ReferreeId)
				VALUES('$phonenum', '$username', '000000', '$now', '$userid')");
			if (!$result) {
				echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'创建账号失败，请稍后重试！','sql_error'=>mysql_error()));
				return;
			}
			else {
				$newuserid = mysql_insert_id();
			}
		}
		else {
			$userNotRegistered = false;
			$row = mysql_fetch_assoc($result);
			if ($row) {
				$newuserid = $row["UserId"];
			}	
		}
	}
		
	$creditNotRegistered = true;
	if (0 != $newuserid) {
		$result = mysql_query("select * from Credit where UserId='$newuserid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'积分查询出错，请稍后重试！','sql_error'=>mysql_error()));
			return;
		}
		else {
			$num = mysql_num_rows($result);
			if ($num == 0) {
				$result = mysql_query("insert into Credit (UserId)
					VALUES('$newuserid')");
				if (!$result) {
					echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'创建积分失败，请稍后重试！','sql_error'=>mysql_error()));
					return;
				}
				else {
// 					echo "Register success<br />";
				}
			}					
			else {
				$creditNotRegistered = false;
			}
		}
	}
	
	if (!$userNotRegistered && !$creditNotRegistered) {
		echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'该手机号已注册，请不要反复注册！'));
		return;		
	}
	
	echo json_encode(array('error'=>'false'));
	
	// 更新统计数据
	$result = createStatisticsTable();
	if ($result) {
		date_default_timezone_set('PRC');
		$year = date("Y", $now);
		$month = date("m", $now);
		$day = date("d", $now);
		
		$result = mysql_query("select * from Statistics where Ye='$year' and Mon='$month' and Day='$day'");
		if ($result && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$newUserCount = $row["NewUserCount"] + 1;
			mysql_query("update Statistics set NewUserCount='$newUserCount' where Ye='$year' and Mon='$month' and Day='$day'");
		}
		else {
			mysql_query("insert into Statistics (Ye, Mon, Day, NewUserCount)
					VALUES('$year', '$month', '$day', '1')");
		}
	}

	
	mysql_close($con);
/*
	$home_url = '../html/home.php';
	header('Location: ' . $home_url);
*/

	return;
}

?>