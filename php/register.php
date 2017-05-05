<?php
	
include "database.php";
include "regtest.php";
include "constant.php";
include_once "func.php";

$phonenum = trim(htmlspecialchars($_POST['phonenum']));
$paypwd = htmlspecialchars($_POST['paypwd']);
// $username = htmlspecialchars($_POST['username']);
// $points = 500;	// default points value
$points = 0;

// 验证电话号码
if (!isValidCellPhoneNum($phonenum)) {
	echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'电话号码格式不对，请重新填写！'));
	return;
}

// 判断是否登录
session_start();
if (!$_SESSION["isLogin"]) {
	echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
	return;
}

if ($paypwd != $_SESSION["buypwd"]) {
	echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'支付密码出错，请重试！'));
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
	
	$userid = $_SESSION["userId"];
	$res1 = mysql_query("select * from Credit where UserId='$userid'");
	$credit = 0;
	if (!$res1) {
	}
	else {
		$row = mysql_fetch_assoc($res1);
		$credit = $row["Credits"];
	}
	
	if ($credit < $refererConsumePoint) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'您的蜜券不足，不能推荐用户！'));
		return;		
	}
	
	$now = time();
	$sql = "select * from User where PhoneNum='$phonenum'";
	$result = mysql_query($sql, $con);
	$newuserid = 0;
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'账号查询出错，请稍后重试！','sql_error'=>mysql_error()));
		return;
	}
	else {
		$num = mysql_num_rows($result);
		if ($num == 0) {

			$error_code = '';
			$error_msg = '';
			$sql_error = '';
			
			$ret = insertNewUserNode($userid, $phonenum, '', '', 0, $newuserid, $error_code, $error_msg, $sql_error);	
			if (!$ret) {
				echo json_encode(array('error'=>'true','error_code'=>$error_code,'error_msg'=>$error_msg,'sql_error'=>$sql_error));
				return;
			}
		}
		else {
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'账号已经注册过了！','sql_error'=>mysql_error()));
			return;
		}
	}
		
	if (0 != $newuserid) {
		$result = mysql_query("select * from Credit where UserId='$newuserid'");
		if (!$result) {
			// !!! log
// 			echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'积分查询出错，请稍后重试！','sql_error'=>mysql_error()));
// 			return;
		}
		else {
			$num = mysql_num_rows($result);
			if ($num == 0) {
				$vault1 = 0;
				$dynVault = $dyNewUserVault;
				$result = mysql_query("insert into Credit (UserId, Vault, DVault)
					VALUES('$newuserid', '$vault1', '$dynVault')");
				if (!$result) {
					// !!! log
// 					echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'新用户积分表插入失败，请稍后重试！','sql_error'=>mysql_error()));
// 					return;
				}
				else {
// 					echo "Register success<br />";
				}
			}					
			else {
				// !!! log
			}
		}
	}
	
	echo json_encode(array('error'=>'false', 'new_user_id'=>$newuserid));
	
	// 添加初始订单
	$res2 = mysql_query("insert into Transaction (UserId, ProductId, Price, Count, OrderTime, Status)
					VALUES('$newuserid', '1', '$refererConsumePoint', '3', '$now', '$OrderStatusDefault') ");
	$bInsertOrder = $res2 != false;
	
	// 更新推荐人的推荐人数，若失败不影响返回结果
	$result = mysql_query("select * from User where UserId='$userid'");
	if (!$result) {
	}
	else {
		$row = mysql_fetch_assoc($result);
		$count = $row["RecoCnt"];
		$lvl = $row["Lvl"];
		$count += 1;
		if ($lvl <= 1) {
			if ($count >= 3) {
				$lvl = 2;
				$vault += $levelBonus[0];
			}
		}
		mysql_query("update User set RecoCnt='$count', Lvl='$lvl' where UserId='$userid'");
	}
	
	// 更新推荐人credit，添加消耗记录,若失败不影响返回结果
	$res3 = mysql_query("select * from Credit where UserId='$userid'");
	$vault = 0;
	if (!$res3) {
	}
	else {
		$row3 = mysql_fetch_assoc($res3);
		$credit = $row3["Credits"];
		$vault = $row3["Vault"];
	}
	$leftCredit = $credit - $refererConsumePoint;
	mysql_query("update Credit set Credits='$leftCredit', Vault='$vault' where UserId='$userid'");
	// 修改用户的积分纪录，若失败不影响返回结果
	$result = mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
								VALUES($userid, $refererConsumePoint, $leftCredit, $now, $now, $newuserid, $codeRecommend)");
	
	// 更新统计数据,在订单统计里返还积分到积分池，而在推荐统计里不做不回积分池，只增加推荐消耗积分总额及用户人数
	$bStaticBefore = false;
	insertOrderStatistics($refererConsumePoint, 1, 0);
	insertRecommendStatistics($refererConsumePoint, $bStaticBefore);

	mysql_close($con);
	return;
}

?>