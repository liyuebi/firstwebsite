<?php
	
include "database.php";
include "regtest.php";
include "constant.php";
include_once "func.php";

$phonenum = trim(htmlspecialchars($_POST['phonenum']));
$paypwd = trim(htmlspecialchars($_POST['paypwd']));
$quantity = trim(htmlspecialchars($_POST['quantity']));
// $username = htmlspecialchars($_POST['username']);

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

if (!password_verify($paypwd, $_SESSION["buypwd"])) {
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
	
	$userid = $_SESSION["userId"];
	$res1 = mysql_query("select * from Credit where UserId='$userid'");
	if (!$res1) {
	}
	else {
		$row = mysql_fetch_assoc($res1);
	}
	
	if ($quantity < 300) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'投入额度不能小于300，请重新输入！'));
		return;
	}
	else if ($quantity > 9000) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'投入额度不能大于9000，请重新输入！'));
		return;		
	}
	
	if ($quantity % 100 != 0) {
		echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'投入额度必须是100的整数倍，请重新输入！'));
		return;
	}
	
	if ($row["Credits"] < $quantity) {
		echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'您的蜜券不足，不能推荐用户！'));
		return;		
	}
	
	$now = time();
	$sql = "select * from ClientTable where PhoneNum='$phonenum'";
	$result = mysql_query($sql, $con);
	$newuserid = 0;
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'账号查询出错，请稍后重试！','sql_error'=>mysql_error()));
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
			echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'该手机号已经注册过了！','sql_error'=>mysql_error()));
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
			$diviCnt = 0;
			$num = mysql_num_rows($result);
			if ($num == 0) {
				$vault1 = $quantity * 3;
				$charity = floor($vault1 * 0.05 * 100) / 100;
				$pnts = floor($vault1 * 0.15 * 100) / 100;
				$diviCnt = floor($vault1 * 0.005 * 100) / 100;
				$vault1 = $vault1 - $charity - $pnts;
				$result = mysql_query("insert into Credit (UserId, Vault, Pnts, Charity)
					VALUES('$newuserid', '$vault1', '$pnts', '$charity')");
				if (!$result) {
					// !!! log error
// 					echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'新用户积分表插入失败，请稍后重试！','sql_error'=>mysql_error()));
// 					return;
				}
				else {
// 					echo "Register success<br />";
				}
			}					
			else {
				// !!! log error
			}
			
			// insert credit bank 
			$res4 = mysql_query("insert into CreditBank (UserId, Quantity, Invest, Balance, DiviCnt, SaveTime)
								values('$newuserid', '$vault1', '$quantity', '$vault1', '$diviCnt', '$now')");
			if (!$res4) {
				// !!! log error
			}
		}
	}
	
	echo json_encode(array('error'=>'false', 'new_user_id'=>$newuserid));
	
	// 添加初始订单
/*
	$res2 = mysql_query("insert into Transaction (UserId, ProductId, Price, Count, OrderTime, Status)
					VALUES('$newuserid', '1', '$refererConsumePoint', '1', '$now', '$OrderStatusDefault') ");
	$bInsertOrder = $res2 != false;
*/
	
	// 重新获取积分记录，因为在添加新用户时credit信息可能被改
	$res3 = mysql_query("select * from Credit where UserId='$userid'");
	$vault = 0;
	$credit = 0;
	$pnts = 0;
	if (!$res3) {
	}
	else {
		$row3 = mysql_fetch_assoc($res3);
		$vault = $row3["Vault"];
		$credit = $row3["Credits"];	
		$pnts = $row3["Pnts"];
	}
	
	// 更新推荐人credit，添加消耗记录,若失败不影响返回结果
	$leftCredit = $credit - $quantity;
	
	// 修改用户的积分纪录，扣去推荐积分，若失败不影响返回结果
	$result = mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
								VALUES($userid, $quantity, $leftCredit, $now, $now, $newuserid, $codeReferer)");
								
	$addedCredit = $quantity * 0.1;
	if ($addedCredit > $vault) {
		$addedCredit = $vault;
	}
	$vault -= $addedCredit;
	$leftCredit = $leftCredit + $addedCredit;
	
	// 修改用户的积分纪录，增加直推奖励，若失败不影响返回结果
	$result = mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
								VALUES($userid, $addedCredit, $leftCredit, $now, $now, $newuserid, $codeReferBonus)");
								
	$result = mysql_query("update Credit set Credits='$leftCredit', Vault='$vault' where UserId='$userid'");
	if (!$result) {
		// !!! log error
	}
	else {
		// 分发推荐奖励
		attributeCollisionBonus($userid, $newuserid, $quantity);
	}
								
	// 更新推荐人的推荐人数，若失败不影响返回结果
	$result = mysql_query("select * from ClientTable where UserId='$userid'");
	if (!$result) {
	}
	else {
		$row = mysql_fetch_assoc($result);
		$count = $row["RecoCnt"];
		mysql_query("update ClientTable set RecoCnt='$count' where UserId='$userid'");
	}
	
	// 更新统计数据,在订单统计里返还积分到积分池，而在推荐统计里不做不回积分池，只增加推荐消耗积分总额及用户人数
// 	insertOrderStatistics($refererConsumePoint, 1);
// 	insertRecommendStatistics($refererConsumePoint, true);

	mysql_close($con);
	return;
}

?>