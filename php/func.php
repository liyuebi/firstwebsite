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
	setcookie("userN", '', $time, '/');
	setcookie("useI", '', $time, '/');
	setcookie("isLogin", 'false', $time, '/');
}

function setSession($row)
{
	$_SESSION["userId"] = $row['UserId'];
	$_SESSION['phonenum'] = $row['PhoneNum'];
	$_SESSION['nickname'] = $row['NickName'];
	$_SESSION['password'] = $row['Password'];
	$_SESSION['buypwd'] = $row["PayPwd"];
	$_SESSION['isLogin'] = true;
	$_SESSION['pwdModiT'] = $row["LastPwdModiTime"];
	$_SESSION['ppwdModiT'] = $row["LastPPwdModiTime"];
	
	setUserCookie($row['NickName'], $row['UserId']);
}

/*
 * 分发直推奖励，先从蜂值里扣，若蜂值不够，则从采蜜券里扣，若依然不够，仍给足用户推荐奖励
 * $collisionVal: 新用户支线的碰撞值
 */
function attributeCollisionBonus($userid, $newuserid, $collisionVal)
{
	include "constant.php";
	include_once "database.php";
	
	$collChild1 = $newuserid;
	$collVal1 = $collisionVal;
	
	$now = time();

	while ($userid != 0) {	
		
		// 对当前父节点进行碰撞
		$res = mysql_query("select * from Credit where UserId='$userid'");
		if (!$res) {
			// !!! log error
			break;
		}
		
		$row = mysql_fetch_assoc($res);
		$credit = $row["Credits"];
		$vault = $row["Vault"];
		$collChild2 = $row["CollChild"];
		$collVal2 = $row["CollVal"];
		
		$newCollChild = 0;
		$newCollVal = 0;
		$currCollVal = 0;
		$addedCredit = 0;
		
		if ($collChild1 == $collChild2) {
			$newCollChild = $collChild2;
			$newCollVal = $collVal1 + $collVal2;	
		}
		else {
			if ($collVal2 >= $collVal1) {
				$newCollChild = $collChild2;
				$newCollVal = $collVal2 - $collVal1;
				$currCollVal = $collVal1;
			}
			else {
				$newCollChild = $collChild1;
				$newCollVal = $collVal1 - $collVal2;
				$currCollVal = $collVal2;
			}
			
			$addedCredit = $currCollVal * 0.1;
			if ($addedCredit > $vault) {
				$addedCredit = $vault;
			}
			$vault -= $addedCredit;
			$credit += $addedCredit;
		}
		
		$res1 = mysql_query("update Credit set Credits='$credit', Vault='$vault', CollChild='$newCollChild', CollVal='$newCollVal' where UserId='$userid'");
		if (!$res1) {
			// !!! log error
		}
		else {
			if ($addedCredit != 0) {
				mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
									VALUES($userid, $addedCredit, $credit, $now, $now, $newuserid, $codeColliBonus)");
			}
		}
		
		// 取得下一个进行碰撞的父节点
		$res2 = mysql_query("select * from ClientTable where UserId='$userid'");
		if (!$res2) {
			// !!! log error
			break;
		}
		if (mysql_num_rows($res2) <= 0) {
			// !!! log error
			break;
		}
			
		$row2 = mysql_fetch_assoc($res2);
		$collChild1 = $userid;
		$userid = $row2["ParentId"];
	}
}

/*
 * 分发升级奖励，先从蜂值里扣，若蜂值不够，则从采蜜券里扣，若依然不够，按前两者总数拨款
 * $lvl: 升到第几级
 * $credit: 目前的蜜券数量
 * $pnts: 目前的采蜜券数量
 * $vault: 目前的固定蜂值，需要先分发新级别的固定蜂值
 * $lastObtainedT: 上次获得蜜券的时刻
 * $dayObtained: 当日获得蜜券数量
 */
function attributeLevelupBonus($userid, $lvl, &$credit, &$pnts, &$vault, &$lastObtainedT, &$dayObtained)
{
	include "constant.php";
	include_once "database.php";
	if ($lvl <= count($levelBonus)) {
		$vault = $levelBonus[$lvl - 1];
		$addedCredit = 0;
		$pntsToCredit = 0;
		$now = time();
		
		if ($vault >= $levelUpBonus[$lvl - 1]) {
			
			$addedCredit += $levelUpBonus[$lvl - 1];
			$vault -= $levelUpBonus[$lvl - 1];	
		}
		else {
			$vault = 0;
			$addedCredit = $vault;
			
			$v1 = $levelUpBonus[$lvl - 1] - $vault;
			if ($pnts > 0) {
				if ($pnts >= $v1) {
					$pnts -= $v1;
					$pntsToCredit = $v1;
				}
				else {
					$v1 = $pnts;
					$pnts = 0;
					$pntsToCredit = $v1;
				}
				$addedCredit += $v1;
			}			
		}
		
		$credit += $addedCredit;
		if (!isInTheSameDay($now, $lastObtainedT)) {
			$dayObtained = 0;
		}
		$dayObtained += $addedCredit;
		$lastObtainedT = $now;
		$res = mysql_query("update Credit set Credits='$credit', Vault='$vault', Pnts='$pnts', LastObtainedTime='$lastObtainedT', DayObtained='$dayObtained' where UserId='$userid'");
		if (!$res) {
			// 出错
		}
		
		mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
							values('$userid', '$addedCredit', '$credit', '$now', '$now', '$codeLevelupBonus')");		
							
		if ($pntsToCredit > 0) {
			mysql_query("insert into PntsRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
					values('$userid', '$pntsToCredit', '$pnts', '$now', '$now', '$cdoe2TransferToCredit')");
		}
	}
	else {
		// do nothing
	}
}

// 插入一个新用户账号
function insertNewUserNode($userid, $phonenum, $name, $idNum, $groupId, &$newUserId, &$error_code, &$error_msg, &$sql_error)
{
	include "constant.php";
	
	$parentId = $userid;
	
	if ($parentId == 0) {
		$error_code = '51';
		$error_msg = '查找可插入的父节点失败，请稍后重试';
		$sql_error = mysql_error();
		return false;
	}
	
	$now = time();
	$pwd = md5('000000');
	$pwd = password_hash($pwd, PASSWORD_DEFAULT);
	$res4 = mysql_query("insert into ClientTable (PhoneNum, Name, IDNum, Password, ReferreeId, ParentId, RegisterTime)
							values('$phonenum', '$name', '$idNum', '$pwd', '$userid', '$parentId', '$now')");
	if (!$res4) {
		$error_code = '52';
		$error_msg = '插入用户失败，请稍后重试';
		$sql_error = mysql_error();
		return false;
	}
	$newUserId = mysql_insert_id();	
	return true;
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
		$res1 = mysql_query("select * from ClientTable where UserId='$userid'");
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
				$res3 = mysql_query("select * from ClientTable where UserId='$id2'");
				if ($res3 && mysql_num_rows($res3) > 0) {
					$row3 = mysql_fetch_assoc($res3);
					
					$id3 = $row3["ReferreeId"];
					$recommendCount = $row3["RecoCnt"];
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
						$val4 = $row2["Vault"];
					
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
		
							mysql_query("update Credit set Credits='$val1', Vault='$val4', DayObtained='$val2', LastObtainedTime='$time'  where UserId='$id2'");

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
				mysql_query("update ClientTable set DefaultAddressId='$addId' where UserId='$userid'");
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

function findCurrTreeLvl($idx)
{
	if ($idx < 0)
		return 0;
	if ($idx == 0)
		return 1;

	$cnt = 1;
	$lvl = 1;
	$val = $idx + 1;
	while ($cnt < $val) {
		
		$cnt += pow(3, $lvl); 
		++$lvl;
	}  
	return $lvl;
}

function findNextAvailablePos($con, $idx)
{
	$ret = 0;
	$nextIdx = 0;
	if ($idx < 0) {
		$nextIdx = 0;
	}
	
// 	$result = mysql_query("select * from User where ")
}

/////////////////////////// insert statistics function begin ///////////////////////////
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
		
		$credits += $amount;			// 取现积分数收入积分池，积分数包含手续费
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
	
function insertOrderStatistics($totalPrice, $count)
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
			$spnum = $row["SPNum"] + $count;
			mysql_query("update Statistics set OrderGross='$gross', OrderNum='$orderNum', SPNum='$spnum' where Ye='$year' and Mon='$month' and Day='$day'");
		}
		else {
			mysql_query("insert into Statistics (Ye, Mon, Day, OrderGross, OrderNum, SPNum)
					VALUES('$year', '$month', '$day', '$totalPrice', '1', '$count')");
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
		
		$credits = $credits + $totalPrice;	// 购买使用的积分归入积分池，再取出奖励积分分给上游用户
		mysql_query("update TotalStatis set CreditsPool='$credits', OrderGross='$gross', OrderNum='$orderNum', SPNum='$spnum' where IndexId=1");
	}
	
	// 更新短期统计数据
	$res2 = mysql_query("select * from ShortStatis where IndexId=1");
	if ($res2 && mysql_num_rows($res2) > 0) {
		
		$row2 = mysql_fetch_assoc($res2);
		$gross = $row2["OrderGross"] + $totalPrice;
		mysql_query("update ShortStatis set OrderGross='$gross' where IndexId='1'");
	}
}

function insertRecommendStatistics($referFee, $isNewUser)
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
			$newUserCount = $row["NSCount"];
			if ($isNewUser) {
				$newUserCount += 1;
			}
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
		$userCnt = $row1["UserCount"];
		if ($isNewUser) {
			$userCnt += 1;
		}
		$accCnt = $row1["AccountCount"] + 1;
		$recomTotal = $row1["RecommendTotal"] + $referFee;
		mysql_query("update TotalStatis set UserCount='$userCnt', AccountCount='$accCnt', RecommendTotal='$recomTotal' where IndexId=1");
	}
	
	// 更新短期统计数据
	// 无短期统计数据需要更新
}

// 静态分红统计
function insertBonusStatistics($bonus, $bonusPnts)
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
			$total1 = $row["BonusPntTotal"] + $bonusPnts;
			mysql_query("update Statistics set BonusTotal='$total', BonusPntTotal='$total1' where Ye='$year' and Mon='$month' and Day='$day'");
		}
		else {
			mysql_query("insert into Statistics (Ye, Mon, Day, BonusTotal, BonusPntTotal)
					VALUES('$year', '$month', '$day', '$bonus', '$bonusPnts')");
		}
	}

	// 更新总统计数据
	$res1 = mysql_query("select * from TotalStatis where IndexId=1");
	if ($res1 && mysql_num_rows($res1) > 0) {
		
		$row1 = mysql_fetch_assoc($res1);
		$total = $row1["BonusTotal"] + $bonus;
		$total1 = $row["BonusPntTotal"] + $bonusPnts;
		
		mysql_query("update TotalStatis set BonusTotal='$total', BonusPntTotal='$total1' where IndexId=1");
	}
	
	// 更新短期统计数据
	$res2 = mysql_query("select * from ShortStatis where IndexId=1");
	if ($res2 && mysql_num_rows($res2) > 0) {
		
		$row2 = mysql_fetch_assoc($res2);
		$left = $row2["BonusLeft"];
		if ($left < $bonus) {
			// record error
		}
		
		$left -= ($bonus + $bonusPnts);
		mysql_query("update ShortStatis set BonusLeft='$left' where IndexId=1");
	}

}

// 动态分红统计
function insertDynBonusStatistics($dBonus, $dBonusPnts)
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
			
			$total = $row["DBonusTotal"] + $dBonus;
			$total1 = $row["DBonusPntTotal"] + $dBonusPnts;
			mysql_query("update Statistics set DBonusTotal='$total', DBonusPntTotal='$total1' where Ye='$year' and Mon='$month' and Day='$day'");
		}
		else {
			mysql_query("insert into Statistics (Ye, Mon, Day, DBonusTotal, DBonusPntTotal)
					VALUES('$year', '$month', '$day', '$dBonus', '$dBonusPnts')");
		}
	}

	// 更新总统计数据
	$res1 = mysql_query("select * from TotalStatis where IndexId=1");
	if ($res1 && mysql_num_rows($res1) > 0) {
		
		$row1 = mysql_fetch_assoc($res1);
		$total = $row1["DBonusTotal"] + $dBonus;
		$total1 = $row1["DBonusPntTotal"] + $dBonusPnts;
		
		mysql_query("update TotalStatis set DBonusTotal='$total', DBonusPntTotal='$total1' where IndexId=1");
	}
	
	// 更新短期统计数据
	$res2 = mysql_query("select * from ShortStatis where IndexId=1");
	if ($res2 && mysql_num_rows($res2) > 0) {
		
		$row2 = mysql_fetch_assoc($res2);
		$left = $row2["DBonusLeft"];
		if ($left < $dBonus) {
			// record error
		}
		
		$left -= ($dBonus + $dBonusPnts);
		mysql_query("update ShortStatis set DBonusLeft='$left' where IndexId=1");
	}

}

/////////////////////////// insert statistics function begin ///////////////////////////

?>
