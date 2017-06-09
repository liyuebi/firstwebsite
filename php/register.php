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
	$regiToken = 0;
	if (!$res1) {
	}
	else {
		$row = mysql_fetch_assoc($res1);
		$regiToken = $row["RegiToken"];
	}
	
	if ($regiToken < $refererConsumePoint) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'您的蜜券不足，不能推荐用户！'));
		return;		
	}
	
	$now = time();
	$sql = "select * from ClientTable where PhoneNum='$phonenum'";
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
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'该手机号已经注册过了！','sql_error'=>mysql_error()));
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
				$vault1 = $levelBonus[0];
				$dynVault = 0;
				$result = mysql_query("insert into Credit (UserId, Vault, DVault, BPCnt, LastRwdBPCnt)
					VALUES('$newuserid', '$vault1', '$dynVault', '1', '1')");
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
					VALUES('$newuserid', '1', '$refererConsumePoint', '1', '$now', '$OrderStatusDefault') ");
	$bInsertOrder = $res2 != false;
	
	// 重新获取积分记录，因为在添加新用户时credit信息可能被改
	$res3 = mysql_query("select * from Credit where UserId='$userid'");
	$vault = 0;
	$regiToken = 0;
	$credit = 0;
	$pnts = 0;
	$lastObtainedT = 0;
	$dayObtainedCredit = 0;
	if (!$res3) {
	}
	else {
		$row3 = mysql_fetch_assoc($res3);
		$regiToken = $row3["RegiToken"];
		$vault = $row3["Vault"];
		$credit = $row3["Credits"];	
		$pnts = $row3["Pnts"];
		$lastObtainedT = $row3["LastObtainedTime"];
		$dayObtainedCredit = $row3["DayObtained"];
	}
	
	// 更新推荐人credit，添加消耗记录,若失败不影响返回结果
	$leftCredit = $regiToken - $refererConsumePoint;
	mysql_query("update Credit set RegiToken='$leftCredit' where UserId='$userid'");
	
	// 修改用户的积分纪录，若失败不影响返回结果
	$result = mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
								VALUES($userid, $refererConsumePoint, $leftCredit, $now, $now, $newuserid, $codeRecoRegiToken)");
								
	// 分发推荐奖励
	attributeRecoBonus($userid, $lvl, $newuserid, $credit, $pnts, $vault, $lastObtainedT, $dayObtainedCredit);
								
	// 更新推荐人的推荐人数，若失败不影响返回结果
	$result = mysql_query("select * from ClientTable where UserId='$userid'");
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
				
				// 第一级剩余的蜂值转到采蜜券, 优先从固定蜂值中播出升级奖励，不够则从采蜜券中播出
				$addedPnts = $vault;
				$vault = 0;
				$pnts += $addedPnts;	
				mysql_query("insert into PntsRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
								values('$userid', '$addedPnts', '$pnts', '$now', '$now', '$code2TransferFromVault')");
								
				attributeLevelupBonus($userid, 2, $credit, $pnts, $vault, $lastObtainedT, $dayObtainedCredit);
				
				$group1Cnt = $row['Group1Cnt'];
				$group2Cnt = $row['Group2Cnt'];
				$group3Cnt = $row['Group3Cnt'];
				$idx = 2;
				$length = count($team1Cnt);
				while ($idx < $length) {
					
					if ($group1Cnt >= $team1Cnt[$idx] && $group2Cnt >= $team2Cnt[$idx] && $group3Cnt >= $team3Cnt[$idx]) {
						++$lvl;
						attributeLevelupBonus($userid, $lvl, $credit, $pnts, $vault, $lastObtainedT, $dayObtainedCredit);
					}
					else {
						break;
					}
					
					++$idx;
				}
			}
		}
		mysql_query("update ClientTable set RecoCnt='$count', Lvl='$lvl' where UserId='$userid'");
		$_SESSION['lvl'] = $lvl;
	}
	
	// 更新统计数据,在订单统计里返还积分到积分池，而在推荐统计里不做不回积分池，只增加推荐消耗积分总额及用户人数
	insertOrderStatistics($refererConsumePoint, 1);
	insertRecommendStatistics($refererConsumePoint, true);

	mysql_close($con);
	return;
}

?>