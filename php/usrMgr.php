<?php
	
include "database.php";
include "regtest.php";
include "constant.php";


if (!isset($_POST['func'])) {
	exit('非法访问！');
}

if ("addUser" == $_POST['func']) {
	addUser();
}
else if ("queryUser" == $_POST['func']) {
	queryUser();	
}
else if ("qubd" == $_POST['func']) {
	queryUserByCondition();
} 
else if ("getDFeng" == $_POST['func']) {
	getAllDFeng();
}
else if ("rlp" == $_POST['func']) {
	resetLoginPwd();
}
else if ("rpp" == $_POST['func']) {
	resetPayPwd();
}

// admin login
// 判断是否登录
/*
session_start();
if (!$_SESSION["isLogin"]) {
	echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
	return;
}
*/

function addUser()
{
	$phonenum = trim(htmlspecialchars($_POST['phone']));
	$username = trim(htmlspecialchars($_POST['name']));
	
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
		$result = createClientTable();
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
		
		$sql = "select * from ClientTable where PhoneNum='$phonenum'";
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
				$result = mysql_query("insert into ClientTable (PhoneNum, Name, Password, RegisterTime, ReferreeId)
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
}

function queryUser()
{
	$userid = trim(htmlspecialchars($_POST['uid']));	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'数据库连接失败，请稍后重试！','sql_error'=>mysql_error()));
		return;
	}


	$res = mysql_query("select * from ClientTable where UserId='$userid'");
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'查找用户失败，请稍后重试！','sql_error'=>mysql_error()));
		return;
	}
	
	if (mysql_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'查找不到您输入的用户，请重新输入！','sql_error'=>mysql_error()));
		return;		
	}
	
	$row = mysql_fetch_assoc($res);
	
	$credit = 0;
	$pnts = 0;
	$vault = 0;
	$res1 = mysql_query("select * from Credit where UserId='$userid'");
	if ($res1 && mysql_num_rows($res1) > 0) {
		$row1 = mysql_fetch_assoc($res1);
		$credit = $row1["Credits"];
		$pnts = $row1["Pnts"];
		$vault = $row1["Vault"];
	}
	
	echo json_encode(array('error'=>'false','nickname'=>$row["NickName"],'id'=>$row["UserId"],
				'phone'=>$row["PhoneNum"],'RecoCnt'=>$row['RecoCnt'],'ChildCnt'=>$row['ChildCnt'],
				'credit'=>$credit,'pnt'=>$pnts,'vault'=>$vault));
}

function queryUserByCondition()
{
// 	$lvl = trim(htmlspecialchars($_POST["lvl"]));
	$recoLow = trim(htmlspecialchars($_POST["rlow"]));
	$recoHigh = trim(htmlspecialchars($_POST["rhigh"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'数据库连接失败，请稍后重试！','sql_error'=>mysql_error()));
		return;
	}
	
	$sql = "select * from ClientTable where ";
	$cond = 0;
/*
	if ($lvl != "") {
		$sql = $sql . "Lvl = " . $lvl;
		++$cond;
	}
*/
	if ($recoLow != "") {
		if ($cond > 0) {
			$sql = $sql . " and ";
		}
		$sql = $sql . "RecoCnt >= " . $recoLow;
		++$cond;
	}
	if ($recoHigh != "") {
		if ($cond > 0) {
			$sql = $sql . " and ";
		}
		$sql = $sql . "RecoCnt <= " . $recoHigh;
		++$cond;
	}
	
	if ($cond <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'请至少选择一个有效条件！','sql_error'=>mysql_error()));
		return;	
	}
	$res = mysql_query($sql);
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'查询出错！','sql_error'=>mysql_error()));
		return;		
	}
	
	$arr = array();
	while ($row = mysql_fetch_array($res)) {
		
		$arr1 = array();
		
		$arr1["nickname"] = $row["NickName"];
		$arr1["phone"] = $row["PhoneNum"];
		$arr1["name"] = $row["Name"];
		$arr1["RecoCnt"] = $row["RecoCnt"];
		$arr1["ChildCnt"] = $row["ChildCnt"];
		
		$userid = $row['UserId'];
		$res1 = mysql_query("select * from Credit where UserId='$userid'");
		if ($res1 && mysql_num_rows($res1)) {
			$row1 = mysql_fetch_assoc($res1);
			$arr1["credit"] = $row1["Credits"];
			$arr1["pnt"] = $row1["Pnts"];
			$arr1["vault"] = $row1["Vault"];
		}
		
		$arr[$userid] = $arr1;
	}
	echo json_encode(array('error'=>'false','cnt'=>mysql_num_rows($res),'list'=>$arr));
}

function getAllDFeng()
{
	include "constant.php";
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'数据库连接失败，请稍后重试！','sql_error'=>mysql_error()));
		return;
	}
	
	$res = mysql_query("select * from Credit");
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'查找积分库失败，请稍后重试！','sql_error'=>mysql_error()));
		return;
	}
	
	$feng = 0;
	while($row = mysql_fetch_array($res)) {
	}
	echo json_encode(array('error'=>'false','dfeng'=>$feng));	
}

function resetLoginPwd()
{
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'数据库连接失败，请稍后重试！','sql_error'=>mysql_error()));
		return;
	}
	
	$userid = trim(htmlspecialchars($_POST['uid']));
	$res = mysql_query("select * from ClientTable where UserId='$userid'");
	if (!$res || mysql_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'查找用户失败！','sql_error'=>mysql_error()));
		return;		
	}
	
	$pwd = md5('000000');
	$pwd = password_hash($pwd, PASSWORD_DEFAULT);
	$res2 = mysql_query("update ClientTable set Password='$pwd' where UserId='$userid'");
	if (!$res2) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'修改数据库失败，请稍后重试！','sql_error'=>mysql_error()));
		return;				
	}
	
	echo json_encode(array('error'=>'false'));
}

function resetPayPwd()
{
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'数据库连接失败，请稍后重试！','sql_error'=>mysql_error()));
		return;
	}
	
	$userid = trim(htmlspecialchars($_POST['uid']));
	$res = mysql_query("select * from ClientTable where UserId='$userid'");
	if (!$res || mysql_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'查找用户失败！','sql_error'=>mysql_error()));
		return;		
	}
	
	$res2 = mysql_query("update ClientTable set PayPwd='' where UserId='$userid'");
	if (!$res2) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'修改数据库失败，请稍后重试！','sql_error'=>mysql_error()));
		return;				
	}
	
	echo json_encode(array('error'=>'false'));
}

?>