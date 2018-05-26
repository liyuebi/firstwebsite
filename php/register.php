<?php
	
include "database.php";
include "regtest.php";
include "constant.php";
include_once "func.php";

$phonenum = trim(htmlspecialchars($_POST['phonenum']));
$paypwd = trim(htmlspecialchars($_POST['paypwd']));
$quantity = trim(htmlspecialchars($_POST['quantity']));
$isOlShop = trim(htmlspecialchars($_POST['olShop']));
$buyPack = trim(htmlspecialchars($_POST['pack']));
$packId = trim(htmlspecialchars($_POST['pId']));
// $username = htmlspecialchars($_POST['username']);

if ("1" == $isOlShop) {
	$isOlShop = true;
}
else {
	$isOlShop = false; 
}

if ("1" == $buyPack) {
	$buyPack = true;
}
else {
	$buyPack = false;
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

if (!$buyPack) {
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
}

$con = connectToDB();
if (!$con)
{
	echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'数据库连接失败，请稍后重试！','sql_error'=>mysqli_error($con)));
	return;
}
else 
{		  
	$result = createClientTable($con);
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'用户表创建失败，请稍后重试！','sql_error'=>mysqli_error($con))); 
		return;
	}
	$result = createCreditTable($con);
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'积分表创建失败，请稍后重试！','sql_error'=>mysqli_error($con))); 
		return;
	}

	$packRes = false;
	$packSaveRate = 0;
	$saveCnt = $quantity;
	// calc quantity and save cnt if is through buying pack method
	if ($buyPack) {
		$packRes = mysqli_query($con, "select * from ProductPack where PackId='$packId' and Status=1");
		if (!$packRes || mysqli_num_rows($packRes) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'11','error_msg'=>'查询产品包出错，请刷新后重试','sql_error'=>mysqli_error($con))); 
			return;		
		}

		$packRow = mysqli_fetch_assoc($packRes);
		$quantity = $packRow["Price"];
		$packSaveRate =  $packRow["SaveRate"];
		$saveCnt = floor($quantity * $packSaveRate);
		$packCnt = $packRow["StockCnt"];

		// if equals -1, means cnt unlimited, if larger than 0, can be bought
		if (0 == $packCnt) {
			echo json_encode(array('error'=>'true','error_code'=>'12','error_msg'=>'该产品包已售磬，请刷新页面重试！','sql_error'=>mysqli_error($con))); 
			return;		
		}
	}
	$newUserAsset = $saveCnt * 3;

	// check whether is credits pool left enough
 	$poolLeft = getCreditsPoolLeft($con);
 	if ($poolLeft < $newUserAsset - $quantity) {
		echo json_encode(array('error'=>'true','error_code'=>'15','error_msg'=>'发行云量已全部进入流通，余额不足，暂时不能推荐新用户！'));
		return;	
 	}

	$userid = $_SESSION["userId"];
	$res1 = mysqli_query($con, "select * from Credit where UserId='$userid'");
	$row = false;
	if (!$res1 || mysqli_num_rows($res1) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'10','error_msg'=>'查询云量信息出错！','sql_error'=>mysqli_error($con))); 
		return;		
	}
	else {
		$row = mysqli_fetch_assoc($res1);
	}

	if ($row["ShareCredit"] < $quantity) {
		echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'您的分享云量不足，不能推荐用户！'));
		return;		
	}
	if ($isOlShop && 
		$row["ShareCredit"] < $quantity + $offlineShopRegisterFee) {
		
		echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'您的分享云量不足，不能推荐线下商家用户！'));
		return;		
	}
	
	$now = time();
	$sql = "select * from ClientTable where PhoneNum='$phonenum'";
	$result = mysqli_query($con, $sql);
	$newuserid = 0;
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'账号查询出错，请稍后重试！','sql_error'=>mysqli_error($con)));
		return;
	}
	else {
		$num = mysqli_num_rows($result);
		if ($num == 0) {

			$error_code = '';
			$error_msg = '';
			$sql_error = '';
			
			$ret = insertNewUserNode($con, $userid, $phonenum, '', '', $newuserid, $error_code, $error_msg, $sql_error);	
			if (!$ret) {
				echo json_encode(array('error'=>'true','error_code'=>$error_code,'error_msg'=>$error_msg,'sql_error'=>$sql_error));
				return;
			}
		}
		else {
			echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'该手机号已经注册过了！','sql_error'=>mysqli_error($con)));
			return;
		}
	}
	
	$pnts = 0;
	$charity = 0;
	if (0 != $newuserid) {
		$result = mysqli_query($con, "select * from Credit where UserId='$newuserid'");
		if (!$result) {
			// !!! log
// 			echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'积分查询出错，请稍后重试！','sql_error'=>mysqli_error($con)));
// 			return;
		}
		else {
			$diviCnt = 0;
			$num = mysqli_num_rows($result);
			if ($num == 0) {
				$charity = floor($newUserAsset * $charityRate * 100) / 100;
				$pnts = floor($newUserAsset * $pntsRate * 100) / 100;
				$pntsReturnDirect = floor($pnts * $pntsReturnDirRate * 100) / 100;
				$pntsInBank = $pnts - $pntsReturnDirect;
				$diviCnt = floor($saveCnt * $dayBonusRate * 100) / 100;
				$vault1 = $newUserAsset - $charity - $pnts;
				$result = mysqli_query($con, "insert into Credit (UserId, Vault, Pnts, Charity)
					VALUES('$newuserid', '$vault1', '$pntsReturnDirect', '$charity')");
				if (!$result) {
					// !!! log error
// 					echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'新用户积分表插入失败，请稍后重试！','sql_error'=>mysqli_error($con)));
// 					return;
				}
				else {
// 					echo "Register success<br />";

					// insert credit bank 
					$newSaveId = 0;
					$res4 = mysqli_query($con, "insert into CreditBank (UserId, Quantity, Invest, Balance, DiviCnt, SaveTime, Type)
										values('$newuserid', '$vault1', '$saveCnt', '$vault1', '$diviCnt', '$now', '1')");
					if (!$res4) {
						// !!! log error
					}
					else {
						$newSaveId = mysqli_insert_id($con);
					}
					
					if ($pntsReturnDirect > 0) {
						// insert pnts record 
						$res4 = mysqli_query($con, "insert into PntsRecord (UserId, Amount, CurrAmount, ApplyTime, ApplyIndexId, WithUserId, Type)
											values('$newuserid', '$pntsReturnDirect', '$pntsReturnDirect', '$now', '$newSaveId', '$userid', '$code2Save')");
						if (!$res4) {
							// !!! log error
						}
					}

					// insert pnts saving into credit bank for return to user
					if ($pntsInBank > 0) {
						$pntsDiviCnt = floor($pntsInBank * $dayPntsBonusRate * 100) / 100;
						$res5 = mysqli_query($con, "insert into CreditBank (UserId, Quantity, Invest, Balance, DiviCnt, SaveTime, Type)
											values('$newuserid', '$pntsInBank', '$saveCnt', '$pntsInBank', '$pntsDiviCnt', '$now', '2')");
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
	
	// // 添加初始订单
	// $result = createTransactionTable($con);
	// if ($result) {
		
	// 	$res2 = mysqli_query($con, "insert into Transaction (UserId, ProductId, Type, Price, Count, OrderTime, Status)
	// 					VALUES('$newuserid', '0', '1', '$quantity', '1', '$now', '$OrderStatusDefault') ");
	// 	if (!$res2) {
	// 		// !!! log error
	// 	}
	// }

	// 添加产品包订单
	if ($buyPack) {

		$result = createTransactionTable($con);
		if ($result) {
			
			$res2 = mysqli_query($con, "insert into Transaction (UserId, ProductId, Type, Price, Count, OrderTime, Status)
							VALUES('$newuserid', '$packId', '8', '$quantity', '1', '$now', '$OrderStatusDefault') ");
			if (!$res2) {
				// !!! log error
			}

			// 更新产品包数量
			if (-1 != $packCnt) {

				if ($packCnt == 0) {
					// !!! log error
				}
				else {
					$packCnt -= 1;
					$status = 1;
					if (0 == $packCnt) {
						$status = 0;
					}
					$res = mysqli_query($con, "update ProductPack set StockCnt='$packCnt', Status='$status' where PackId='$packId'");
					if (!$res) {
						// !!! log error
					}
				}
			}
		}
	}
	
	// 重新获取积分记录，因为在添加新用户时credit信息可能被改
	$res3 = mysqli_query($con, "select * from Credit where UserId='$userid'");
	$vault = 0;
	$shareCredit = 0;
	$credit = 0;
	if (!$res3) {
	}
	else {
		$row3 = mysqli_fetch_assoc($res3);
		$vault = $row3["Vault"];
		$shareCredit = $row3["ShareCredit"];	
		$credit = $row3["Credits"];
	}
	
	// 更新推荐人share credit，添加消耗记录,若失败不影响返回结果
	$leftCredit = $shareCredit - $quantity;
	if ($isOlShop) {
		$leftCredit -= $offlineShopRegisterFee;
	}
	
	$res4 = mysqli_query($con, "update Credit set ShareCredit='$leftCredit' where UserId='$userid'");
	if (!$res4) {
		// !!! log error
	}
	else {
		// 修改用户的积分纪录，扣去推荐积分，若失败不影响返回结果
		$usedCredit = $quantity;
		if ($isOlShop) {
			$usedCredit += $offlineShopRegisterFee;
		}
		$result = mysqli_query($con, "insert into ShareCreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
									VALUES($userid, $usedCredit, $leftCredit, $now, $now, $newuserid, $code4Referer)");
		if (!$result) {
			// !!! log error
		}
	}

	$bonusAmt = $quantity;
	if ($buyPack) {
		$bonusAmt = floor($quantity * $packSaveRate);
	}				
	$addedCredit = $bonusAmt * $referBonusRate;
	addCreditFromVault($con, $userid, $vault, $credit, $addedCredit, $newuserid, $codeReferBonus);
								
	// 分发碰撞奖励
	attributeCollisionBonus($con, $userid, $newuserid, $bonusAmt, $colliBonusRateRefer, $codeColliBonusNew);
								
	// 更新推荐人的推荐人数，若失败不影响返回结果
	$result = mysqli_query($con, "select * from ClientTable where UserId='$userid'");
	if (!$result) {
	}
	else {
		$row = mysqli_fetch_assoc($result);
		$count = $row["RecoCnt"] + 1;
		mysqli_query($con, "update ClientTable set RecoCnt='$count' where UserId='$userid'");
	}
	
	// 更新统计数据,在订单统计里返还积分到积分池，而在推荐统计里不做不回积分池，只增加推荐消耗积分总额及用户人数
// 	insertOrderStatistics($con, $refererConsumePoint, 1);
	insertRecommendStatistics($con, $quantity, $newUserAsset, $charity);
	
	// open offline shop for new user
	if ($isOlShop) {
		
		include_once "offlineTrade.php";
		$error_msg = '';
		if (!openOfflineShop($con, $newuserid, $userid, $error_msg)) {
			// !!! log error
		}
	}

	mysqli_close($con);
	return;
}

?>