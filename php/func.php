<?php

function setUserCookie($name, $userid) 
{
	$time = time() + 60 * 30;
	setcookie("userN", $name, $time, '/');
	setcookie("useI", $userid, $time, '/');
	setcookie("isLogin", "true", $time, '/');
}

function deleteUserCookie()
{
	$time = time() - 1000;
	setcookie("userN", $name, $time, '/');
	setcookie("useI", $userid, $time, '/');
	setcookie("isLogin", "true", $time, '/');
}

//  给上游玩家分享注册资金
function distributeReferBonus($con, $userid, $count)
{
	$ret = 0;
	// 没有有效的数据库连接，返回
	if (!$con) {
		return $ret;
	}
	
	{
		$res1 = mysql_query("select * from User where UserId='$userid'");
		if ($res1 && mysql_num_rows($res1) > 0) {
			$row1 = mysql_fetch_assoc($res1);
			$referId = $row1["ReferreeId"];	// 推荐人
			$refeeId = $userid; // 被推荐人
			$idx = 1;
			
			$id2 = $referId;
			while($idx <= 13) {
				
				// 没有上游用户了,则退出
				if ($id2 == "0") {
					break;
				}
		
				$recommendCount = 1;
				$id3 = 0;
				// 得到推荐人的推荐人数以及他的推荐人
				$res3 = mysql_query("select * from User where UserId='$id2'");
				if ($res3 && mysql_num_rows($res3) > 0) {
					$row3 = mysql_fetch_assoc($res3);
					
					$id3 = $row3["ReferreeId"];
					$recommendCount = $row3["RecommendingCount"];
				}
				
				$res2 = mysql_query("select * from Credit where UserId='$id2'");
				if ($res2 && mysql_num_rows($res2) > 0) {
					$row2 = mysql_fetch_assoc($res2);
					$val1 = $row2["Credits"];
					$val2 = $row2["DayObtained"];
					$val3 = $row2["LastObtainedTime"];
					
					$add = 0;
					if ($idx >= 1 && $idx <= 3) {	// 1 到 3 层
						// 推荐了人即可拿到
						$add = 4;
					}
					else if ($idx >= 5 && $idx <= 8) { // 5 到 8 层
						// 推荐人数大于5即可拿到
						if ($recommendCount > 5) {
							$add = 3;
						}
					}
					else if ($idx >= 9 && $idx <= 13) { // 9 到 13 层
						// 推荐人数大于9即可拿到
						if ($recommendCount > 9) {
							$add = 2;
						}
					}
					$add *= $count;	// 分成是根据盒数确定
					
					if ($add > 0) {
						// must be a dynamic user
						$val4 = $row2["Vault"] + $row2["DynamicVault"];
					
						if ($val4 < $add) {
							$add = $val4;
							$val4 = 0;
						}
						else {
							$val4 -= $add;
						}
						
						if ($add > 0) {
							$time = time();
							if (isInTheSameDay($val3, $time)) {
								$val2 += $add;
							}
							else {
								$val2 = $add;
							}
							$val1 += $add;
		
							mysql_query("update Credit set Credits='$val1', Vault='$val4', DynamicVault='0', DayObtained='$val2', LastObtainedTime='$time'  where UserId='$id2'");

							include "constant.php";
							mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, ApplyIndexId, Type, AcceptTime, WithUserId)
											VALUES('$id2', '$add', '$val1', '$time', '0', '$codeBonus', '$time', '$refeeId')");
							$ret += $add;											
						}
					}
				}
				
				if ($id3 == 0) {
					break;
				}
				else {
					$id2 = $id3;
				}
				
				++$idx;
			}
		}
	}
	return $ret;
}

function isValidAddress($receiver, $phone, $address, &$error_str)
{
	if ($receiver == '') {
		$error_str = '输入的姓名无效！';
		return false;
	}
	
	if ($address == '') {
		$error_str = '输入的地址无效！';
		return false;
	}
	
	include_once "regtest.php";
	if (!isValidCellPhoneNum($phone)) {
		$error_str = '输入的电话号码有误，请重新输入！';
		return false;
	}
	
	return true;
}

function addOneAddress($con, $userid, $receiver, $phone, $address, $isDefault, &$error_str)
{	
	$result = createAddressTable();
	if (!$result) {
		$error_str = "创建地址表失败，请稍后重试！";
		return false;
	}
	else {
		$result = mysql_query("insert into Address (UserId, Receiver, PhoneNum, Address)
		 	VALUES('$userid', '$receiver', '$phone', '$address')");
		if (!$result) {
			$error_str = "插入地址失败，请稍后重试！";
			return false;
		}
		else {
			// 更新默认地址,出错了不做处理
			if ($isDefault) {
				$addId = mysql_insert_id();
				mysql_query("update User set DefaultAddressId='$addId' where UserId='$userid'");
			}
		}
	}
	return true;
}

function getCreditsPoolLeft($con)
{
	$ret = 0;
	// 没有有效的数据库连接，返回
	if (!$con) {
		return $ret;
	}	
	
	$result = mysql_query("select * from TotalStatis where IndexId=1");
	if (!$result) {
		return $ret;
	}
	
	if (mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		$ret = $row["CreditsPool"];
	}
	
	return $ret;
}

function insertRechargeStatistics($amount)
{
	$now = time();
	
	// 更新每日统计数据
	$result = createStatisticsTable();
	if ($result) {
		date_default_timezone_set('PRC');
		$year = date("Y", $now);
		$month = date("m", $now);
		$day = date("d", $now);
		
		$result = mysql_query("select * from Statistics where Ye='$year' and Mon='$month' and Day='$day'");
		if ($result && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$rechargeTotal = $row["RechargeTotal"] + $amount;
			mysql_query("update Statistics set RechargeTotal='$rechargeTotal' where Ye='$year' and Mon='$month' and Day='$day'");
		}
		else {
			mysql_query("insert into Statistics (Ye, Mon, Day, RechargeTotal)
					VALUES('$year', '$month', '$day', '$amount')");
		}
	}
	
	// 更新总统计数据
	$res1 = mysql_query("select * from TotalStatis where IndexId=1");
	if ($res1 && mysql_num_rows($res1) > 0) {
		
		$row1 = mysql_fetch_assoc($res1);
		$credits = $row1["CreditsPool"];
		$rechargeTotal = $row1["RechargeTotal"];
		$rechargeTimes = $row1["RechargeTimes"];
		
		$credits -= $amount;
		$rechargeTotal += $amount;
		$rechargeTimes += 1;
		
		mysql_query("update TotalStatis set CreditsPool='$credits', RechargeTotal='$rechargeTotal', RechargeTimes='$rechargeTimes' where IndexId=1");
	}
	
	// 更新短期统计数据
	$res2 = mysql_query("select * from ShortStatis where IndexId=1");
	if ($res2 && mysql_num_rows($res2) > 0) {
		$row2 = mysql_fetch_assoc($res2);
		$recharge = $row2["Recharge"] + $amount;
		mysql_query("update ShortStatis set Recharge='$recharge' where IndexId=1");
	}
}

function insertWithdrawStatistics($amount, $fee)
{
	$now = time();
	
	// 更新每日统计数据
	$result = createStatisticsTable();
	if ($result) {
		date_default_timezone_set('PRC');
		$year = date("Y", $now);
		$month = date("m", $now);
		$day = date("d", $now);
		
		$result = mysql_query("select * from Statistics where Ye='$year' and Mon='$month' and Day='$day'");
		if ($result && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$withdrawTotal = $row["WithdrawTotal"] + $amount;
			$feeTotal = $row["WithdrawFee"] + $fee;
			mysql_query("update Statistics set WithdrawTotal='$withdrawTotal', WithdrawFee='$feeTotal' where Ye='$year' and Mon='$month' and Day='$day'");
		}
		else {
			mysql_query("insert into Statistics (Ye, Mon, Day, WithdrawTotal, WithdrawFee)
					VALUES('$year', '$month', '$day', '$amount', '$fee')");
		}
	}
	
	// 更新总统计数据
	$res1 = mysql_query("select * from TotalStatis where IndexId=1");
	if ($res1 && mysql_num_rows($res1) > 0) {
		
		$row1 = mysql_fetch_assoc($res1);
		
		$credits = $row1["CreditsPool"];
		$withdrawTotal = $row1["WithdrawTotal"];
		$withdrawTimes = $row1["WithdrawTimes"];
		$withdrawFee = $row1["WithdrawFee"];
		
		$credits += $fee;			// 取现手续费收入积分池
		$withdrawTotal += $amount;
		$withdrawTimes += 1;
		$withdrawFee += $fee;
		mysql_query("update TotalStatis set CreditsPool='$credits', WithdrawTotal='$withdrawTotal', WithdrawTimes='$withdrawTimes', WithdrawFee='$withdrawFee' where IndexId=1");
	}
	
	// 更新短期统计数据
	$res2 = mysql_query("select * from ShortStatis where IndexId=1");
	if ($res2 && mysql_num_rows($res2) > 0) {
		$row2 = mysql_fetch_assoc($res2);
		
		$withdrawTotal = $row2["Withdraw"] + $amount;
		$withdrawFee = $row2["WithdrawFee"] + $fee;
		mysql_query("update ShortStatis set Withdraw='$withdrawTotal', WithdrawFee='$withdrawFee' where IndexId=1");
	}
}

function insertTransferStatistics($amount, $fee)
{
	$now = time();
	
	// 更新每日统计数据
	$result = createStatisticsTable();
	if ($result) {
		date_default_timezone_set('PRC');
		$year = date("Y", $now);
		$month = date("m", $now);
		$day = date("d", $now);
		
		$result = mysql_query("select * from Statistics where Ye='$year' and Mon='$month' and Day='$day'");
		if ($result && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$tfTotal = $row["TfTotal"] + $amount;
			$feeTotal = $row["TfFee"] + $fee;
			$times = $row["TfTimes"] + 1;
			mysql_query("update Statistics set TfTotal='$tfTotal', TfFee='$feeTotal', TfTimes='$times' where Ye='$year' and Mon='$month' and Day='$day'");
		}
		else {
			mysql_query("insert into Statistics (Ye, Mon, Day, TfTotal, TfFee, TfTimes)
					VALUES('$year', '$month', '$day', '$amount', '$fee', '1')");
		}
	}

	// 更新总统计数据
	$res1 = mysql_query("select * from TotalStatis where IndexId=1");
	if ($res1 && mysql_num_rows($res1) > 0) {
		
		$row1 = mysql_fetch_assoc($res1);
		$credits = $row1["CreditsPool"];
		$transferTotal = $row1["TransferTotal"];
		$transferTimes = $row1["TransferTimes"];
		$transferFee = $row1["TransferFee"];
		
		$credits += $fee;			// 转账手续费收入积分池
		$transferTotal += $amount;
		$transferTimes += 1;
		$transferFee += $fee;
		
		mysql_query("update TotalStatis set CreditsPool='$credits', TransferTotal='$transferTotal', TransferTimes='$transferTimes', TransferFee='$transferFee' where IndexId=1");
	}
	
	// 更新短期统计数据
	$res2 = mysql_query("select * from ShortStatis where IndexId=1");
	if ($res2 && mysql_num_rows($res2) > 0) {
		$row2 = mysql_fetch_assoc($res2);
		$transfer = $row2["Transfer"] + $amount;
		$transFee = $row2["TransferFee"] + $fee;
		mysql_query("update ShortStatis set Transfer='$transfer', TransferFee='$transFee' where IndexId=1");
	}
}
	
function insertOrderStatistics($totalPrice, $count, $referBonus)
{
	$now = time();
	
	// 更新每日统计数据
	$result = createStatisticsTable();
	if ($result) {
		date_default_timezone_set('PRC');
		$year = date("Y", $now);
		$month = date("m", $now);
		$day = date("d", $now);
		
		$result = mysql_query("select * from Statistics where Ye='$year' and Mon='$month' and Day='$day'");
		if ($result && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$gross = $row["OrderGross"] + $totalPrice;
			$orderNum = $row["OrderNum"] + 1;
			$refer = $row["RRTotal"] + $referBonus;
			$spnum = $row["SPNum"] + $count;
			mysql_query("update Statistics set OrderGross='$gross', OrderNum='$orderNum', RRTotal='$refer', SPNum='$spnum' where Ye='$year' and Mon='$month' and Day='$day'");
		}
		else {
			mysql_query("insert into Statistics (Ye, Mon, Day, OrderGross, OrderNum, RRTotal, SPNum)
					VALUES('$year', '$month', '$day', '$totalPrice', '1', '$referBonus', '$count')");
		}
	}

	// 更新总统计数据
	$res1 = mysql_query("select * from TotalStatis where IndexId=1");
	if ($res1 && mysql_num_rows($res1) > 0) {
		
		$row1 = mysql_fetch_assoc($res1);
		$credits = $row1["CreditsPool"];
		$gross = $row1["OrderGross"] + $totalPrice;
		$orderNum = $row1["OrderNum"] + 1;
		$spnum = $row1["SPNum"] + $count;
		$refer = $row1["RRTotal"] + $referBonus;
		
		$credits = $credits + $totalPrice - $referBonus;	// 购买使用的积分归入积分池，再取出奖励积分分给上游用户
		mysql_query("update TotalStatis set CreditsPool='$credits', OrderGross='$gross', OrderNum='$orderNum', RRTotal='$refer', SPNum='$spnum' where IndexId=1");
	}
	
	// 更新短期统计数据
	$res2 = mysql_query("select * from ShortStatis where IndexId=1");
	if ($res2 && mysql_num_rows($res2) > 0) {
		
		$row2 = mysql_fetch_assoc($res2);
		$gross = $row2["OrderGross"] + $totalPrice;
		mysql_query("update ShortStatis set OrderGross='$gross' where IndexId='1'");
	}
}

function insertRecommendStatistics($referFee, $bStaticBefore)
{
	$now = time();
	
	// 更新每日统计数据
	$result = createStatisticsTable();
	if ($result) {
		date_default_timezone_set('PRC');
		$year = date("Y", $now);
		$month = date("m", $now);
		$day = date("d", $now);
		
		$result = mysql_query("select * from Statistics where Ye='$year' and Mon='$month' and Day='$day'");
		if ($result && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$newUserCount = $row["NSCount"] + 1;
			$fee = $row["RecommendFee"] + $referFee;

			mysql_query("update Statistics set NSCount='$newUserCount', RecommendFee='$fee' where Ye='$year' and Mon='$month' and Day='$day'");
		}
		else {
			mysql_query("insert into Statistics (Ye, Mon, Day, NSCount, RecommendFee)
					VALUES('$year', '$month', '$day', '1', '$referFee')");
		}
	}
	
	// 更新总统计数据
	$res1 = mysql_query("select * from TotalStatis where IndexId=1");
	if ($res1 && mysql_num_rows($res1) > 0) {
		
		$row1 = mysql_fetch_assoc($res1);
		$userCnt = $row1["UserCount"] + 1;
		$staUserCnt = $row1["StaUserCount"];
		$dynUserCnt = $row1["DyaUserCount"];
		if ($bStaticBefore) {
			$dynUserCnt += 1;
		}
		else {
			$staUserCnt += 1;
		}
		$recomTotal = $row1["RecommendTotal"] + $referFee;
		mysql_query("update TotalStatis set UserCount='$userCnt', StaUserCount='$staUserCnt', DyaUserCount='$dynUserCnt', RecommendTotal='$recomTotal' where IndexId=1");
	}
	
	// 更新短期统计数据
	// 无短期统计数据需要更新
}

function insertBonusStatistics($bonus)
{
	$now = time();
	
	// 更新每日统计数据
	$result = createStatisticsTable();
	if ($result) {
		date_default_timezone_set('PRC');
		$year = date("Y", $now);
		$month = date("m", $now);
		$day = date("d", $now);
		
		$result = mysql_query("select * from Statistics where Ye='$year' and Mon='$month' and Day='$day'");
		if ($result && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			
			$total = $row["BonusTotal"] + $bonus;
			mysql_query("update Statistics set BonusTotal='$total' where Ye='$year' and Mon='$month' and Day='$day'");
		}
		else {
			mysql_query("insert into Statistics (Ye, Mon, Day, BonusTotal)
					VALUES('$year', '$month', '$day', '$total')");
		}
	}

	// 更新总统计数据
	$res1 = mysql_query("select * from TotalStatis where IndexId=1");
	if ($res1 && mysql_num_rows($res1) > 0) {
		
		$row1 = mysql_fetch_assoc($res1);
		$total = $row1["BonusTotal"] + $bonus;
		
		mysql_query("update TotalStatis set BonusTotal='$total' where IndexId=1");
	}
	
	// 更新短期统计数据
	$res2 = mysql_query("select * from ShortStatis where IndexId=1");
	if ($res2 && mysql_num_rows($res2) > 0) {
		
		$row2 = mysql_fetch_assoc($res2);
		$left = $row2["BonusLeft"];
		if ($left < $bonus) {
			// record error
		}
		
		$left -= $bonus;
		$obtained = $row2["StaUserObtained"] + 1;
		mysql_query("update ShortStatis set BonusLeft='$left', StaUserObtained='$obtained' where IndexId=1");
	}

}

?>