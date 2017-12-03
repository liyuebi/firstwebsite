<?php
	
include "database.php";
include "regtest.php";
include "constant.php";
include_once "func.php";

$phonenum = trim(htmlspecialchars($_POST['phonenum']));
$paypwd = trim(htmlspecialchars($_POST['paypwd']));
$quantity = trim(htmlspecialchars($_POST['quantity']));
$isOlShop = trim(htmlspecialchars($_POST['olShop']));
// $username = htmlspecialchars($_POST['username']);

if ("1" == $isOlShop) {
	$isOlShop = true;
}
else {
	$isOlShop = false; 
}

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

if ($quantity < $regiCreditLeast) {
	$str = '投入额度不能小于' . $regiCreditLeast . '，请重新输入！';
	echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>$str));
	return;
}
else if ($quantity > $regiCreditMost) {
	$str = '投入额度不能大于' . $regiCreditMost . '，请重新输入！';
	echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>$str));
	return;		
}

if ($quantity % 100 != 0) {
	echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'投入额度必须是100的整数倍，请重新输入！'));
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
	$row = false;
	if (!$res1 || mysql_num_rows($res1) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'10','error_msg'=>'查询云量信息出错！','sql_error'=>mysql_error())); 
		return;		
	}
	else {
		$row = mysql_fetch_assoc($res1);
	}
	
	if ($row["Credits"] < $quantity) {
		echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'您的线上云量不足，不能推荐用户！'));
		return;		
	}
	if ($isOlShop && 
		$row["Credits"] < $quantity + $offlineShopRegisterFee) {
		
		echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'您的线上云量不足，不能推荐线下商家用户！'));
		return;		
	}
	
	$now = time();
	$sql = "select * from ClientTable where PhoneNum='$phonenum'";
	$result = mysql_query($sql, $con);
	$newuserid = 0;
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'账号查询出错，请稍后重试！','sql_error'=>mysql_error()));
		return;
	}
	else {
		$num = mysql_num_rows($result);
		if ($num == 0) {

			$error_code = '';
			$error_msg = '';
			$sql_error = '';
			
			$ret = insertNewUserNode($userid, $phonenum, '', '', $newuserid, $error_code, $error_msg, $sql_error);	
			if (!$ret) {
				echo json_encode(array('error'=>'true','error_code'=>$error_code,'error_msg'=>$error_msg,'sql_error'=>$sql_error));
				return;
			}
		}
		else {
			echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'该手机号已经注册过了！','sql_error'=>mysql_error()));
			return;
		}
	}
	
	$pnts = 0;
	$charity = 0;
	$newUserAsset = 0;
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
				$newUserAsset = $quantity * 3;
				$charity = floor($newUserAsset * $charityRate * 100) / 100;
				$pnts = floor($newUserAsset * $pntsRate * 100) / 100;
				$pntsReturnDirect = floor($pnts * $pntsReturnDirRate * 100) / 100;
				$pntsInBank = $pnts - $pntsReturnDirect;
				$diviCnt = floor($quantity * $dayBonusRate * 100) / 100;
				$vault1 = $newUserAsset - $charity - $pnts;
				$result = mysql_query("insert into Credit (UserId, Vault, Pnts, Charity)
					VALUES('$newuserid', '$vault1', '$pntsReturnDirect', '$charity')");
				if (!$result) {
					// !!! log error
// 					echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'新用户积分表插入失败，请稍后重试！','sql_error'=>mysql_error()));
// 					return;
				}
				else {
// 					echo "Register success<br />";

					// insert credit bank 
					$newSaveId = 0;
					$res4 = mysql_query("insert into CreditBank (UserId, Quantity, Invest, Balance, DiviCnt, SaveTime, Type)
										values('$newuserid', '$vault1', '$quantity', '$vault1', '$diviCnt', '$now', '1')");
					if (!$res4) {
						// !!! log error
					}
					else {
						$newSaveId = mysql_insert_id();
					}
					
					if ($pntsReturnDirect > 0) {
						// insert pnts record 
						$res4 = mysql_query("insert into PntsRecord (UserId, Amount, CurrAmount, ApplyTime, ApplyIndexId, WithUserId, Type)
											values('$newuserid', '$pntsReturnDirect', '$pntsReturnDirect', '$now', '$newSaveId', '$userid', '$code2Save')");
						if (!$res4) {
							// !!! log error
						}
					}

					// insert pnts saving into credit bank for return to user
					if ($pntsInBank > 0) {
						$pntsDiviCnt = floor($pntsInBank * $dayPntsBonusRate * 100) / 100;
						$res5 = mysql_query("insert into CreditBank (UserId, Quantity, Invest, Balance, DiviCnt, SaveTime, Type)
											values('$newuserid', '$pntsInBank', '$quantity', '$pntsInBank', '$pntsDiviCnt', '$now', '2')");
						if (!$res5) {
							// !!! log error
						}
					}
				}
			}					
			else {
				// !!! log error
			}
		}
	}
	
	echo json_encode(array('error'=>'false', 'new_user_id'=>$newuserid));
	
	// 添加初始订单
	$result = createTransactionTable();
	if ($result) {
		
		$res2 = mysql_query("insert into Transaction (UserId, ProductId, Type, Price, Count, OrderTime, Status)
						VALUES('$newuserid', '0', '1', '$quantity', '1', '$now', '$OrderStatusDefault') ");
		if (!$res2) {
			// !!! log error
		}
	}
	
	// 重新获取积分记录，因为在添加新用户时credit信息可能被改
	$res3 = mysql_query("select * from Credit where UserId='$userid'");
	$vault = 0;
	$credit = 0;
	if (!$res3) {
	}
	else {
		$row3 = mysql_fetch_assoc($res3);
		$vault = $row3["Vault"];
		$credit = $row3["Credits"];	
	}
	
	// 更新推荐人credit，添加消耗记录,若失败不影响返回结果
	$leftCredit = $credit - $quantity;
	if ($isOlShop) {
		$leftCredit -= $offlineShopRegisterFee;
	}
	
	$res4 = mysql_query("update Credit set Credits='$leftCredit' where UserId='$userid'");
	if (!$res4) {
		// !!! log error
	}
	else {
		// 修改用户的积分纪录，扣去推荐积分，若失败不影响返回结果
		$usedCredit = $quantity;
		if ($isOlShop) {
			$usedCredit += $offlineShopRegisterFee;
		}
		$result = mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
									VALUES($userid, $usedCredit, $leftCredit, $now, $now, $newuserid, $codeReferer)");
		if (!$result) {
			// !!! log error
		}
	}
								
	$addedCredit = $quantity * $referBonusRate;

	addCreditFromVault($userid, $vault, $leftCredit, $addedCredit, $newuserid, $codeReferBonus);
								
	// 分发碰撞奖励
	attributeCollisionBonus($userid, $newuserid, $quantity, $colliBonusRateRefer, $codeColliBonusNew);
								
	// 更新推荐人的推荐人数，若失败不影响返回结果
	$result = mysql_query("select * from ClientTable where UserId='$userid'");
	if (!$result) {
	}
	else {
		$row = mysql_fetch_assoc($result);
		$count = $row["RecoCnt"] + 1;
		mysql_query("update ClientTable set RecoCnt='$count' where UserId='$userid'");
	}
	
	// 更新统计数据,在订单统计里返还积分到积分池，而在推荐统计里不做不回积分池，只增加推荐消耗积分总额及用户人数
// 	insertOrderStatistics($refererConsumePoint, 1);
	insertRecommendStatistics($quantity, $newUserAsset, $charity);
	
	// open offline shop for new user
	if ($isOlShop) {
		
		include_once "offlineTrade.php";
		$error_msg = '';
		if (!openOfflineShop($newuserid, $userid, $error_msg)) {
			// !!! log error
		}
	}

	mysql_close($con);
	return;
}

?>