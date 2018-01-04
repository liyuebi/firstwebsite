<?php

include_once "database.php";

function writeLog($file, $msg) 
{
	if ($file) {
		fwrite($file, $msg);
	}
}

/*
 * 计算用户当日应该获得的云量利息。
 * 
 */
function getDayBonus($con, $userId)
{
	$ret = 0;
	
	$res = mysqli_query($con, "select * from CreditBank where UserId='$userId' and Type='1' and Balance>0 order by SaveTime desc");
	if ($res) {
		
		$now = time();
		while ($row = mysqli_fetch_assoc($res)) {
			
			// 当日存的云量不进行返还
			$savetime = $row["SaveTime"];
			
			if (isInTheSameDay($savetime, $now)) {
				continue;
			}
			
			$balance = $row["Balance"];
			$diviCnt = $row["DiviCnt"];
			if ($diviCnt > $balance) {
				$diviCnt = $balance;
			}
			
			$ret += $diviCnt;
		}
	}
	
	return $ret;
}

function calcBonus($file)
{
	include "constant.php";
	
	$con = connectToDB();
	if (!$con) {
		return false;
	}
	
	$res = mysqli_query($con, "select * from TotalStatis where IndexId=1");
	if (!$res || mysqli_num_rows($res) < 1) {
		writeLog($file, "!!! 查找TotalStatis记录出错!\n");
		return false;
	}
	$row = mysqli_fetch_assoc($res);
	$pool = $row["CreditsPool"];
	
	$res1 = mysqli_query($con, "select * from ShortStatis where IndexId=1");
	if (!$res1 || mysqli_num_rows($res1) < 1) {
		writeLog($file, "\n!!! 查找Short记录出错!\n\n");
		return false;
	}
	
	$row1 = mysqli_fetch_assoc($res1);
	$gross = $row1["OrderGross"];
	
	$now = time();
	
	$lastCalcTime = $row1["LastCalcTime"];
	$lastDCalcTime = $row1["LastDCalcTime"];
 	$lastBonusTotal = $row1["BonusTotal"];
	$lastBonusLeft = $row1["BonusLeft"];
// 	$lastDBonusTotal = $row1["DBonusTotal"];
// 	$lastDBonusLeft = $row1["DBonusLeft"];
	
	$bCalcBonus = true;
	if (isInTheSameDay($now, $lastCalcTime)) {
		$bCalcBonus = false;
	}
	
	writeLog($file, "积分池余额：" . $pool . "\n");
	writeLog($file, "前期的固定分红总额：" . $lastBonusTotal . "\n");
	writeLog($file, "前期的固定分红余额：" . $lastBonusLeft . "\n");
// 	writeLog($file, "前期的动态分红总额：" . $lastDBonusTotal . "\n");
// 	writeLog($file, "前期的动态分红余额：" . $lastDBonusLeft . "\n");
	if ($bCalcBonus) {
		writeLog($file, "\n### 本次需重新计算固定分红\n\n");
	}
	else {
		writeLog($file, "\n### 本次不重新计算固定分红\n\n");
		return;
	}
	
	// 计算所有层级
	$allLevel = count($levelBonus);
	writeLog($file, "\n### 目前共有 " . $allLevel . " 个层级\n\n");
	
	// 剩余分红额小于0，说明超领了
	if ($lastBonusLeft < 0) {
		writeLog($file, "\n!!! 固定分红剩余小于0，出错！\n\n");
	}
// 	if ($lastDBonusLeft < 0) {
// 		writeLog($file, "\n!!! 动态分红剩余小于0，出错！\n\n");
// 	}
	
// 	$dBonusTotal = floor($gross * $rewardRate * 100) / 100;
	
	writeLog($file, "本期的订单总额：" . $gross . "\n");
// 	writeLog($file, "本期的订单分红比例：" . $rewardRate . "\n");
// 	writeLog($file, "本期的动态分红额：" . $dBonusTotal . "\n");
// 	if ($rewardVal > 0) {
// 		writeLog($file, "\n 本期设置了动态分红值，每蜂值分红：" . $rewardVal . "\n\n");
// 	}
	
	// 未领取的分红返回基金池 
// 	$pool += $lastDBonusLeft;	
	if ($bCalcBonus) {
		$pool += $lastBonusLeft;
	}
		
	// 计算并分发固定分红
	$dCnt = 0;
	$totalDFeng = 0;
	$totalBonus = 0;
	writeLog($file, "\n------------------------------------------------------------------\n");
	$res5 = mysqli_query($con, "select * from ClientTable");
	if (!$res5) {
		writeLog($file, "\n!!! 查询用户表失败！\n");
	}
	else {
		while($row5 = mysqli_fetch_assoc($res5)) {
			
			$userid = $row5["UserId"];
			$lvl = $row5["Lvl"];
			$res6 = mysqli_query($con, "select * from Credit where UserId='$userid'");
			if (!$res6 || mysqli_num_rows($res6) < 1) {
				writeLog($file, "\n!!! 查询用户" . $userid . "的积分表失败！\n");
			}
			else {
				$row6 = mysqli_fetch_assoc($res6);
				
				$vault = $row6["Vault"];
				$bonus = 0;

				$bonus = $levelDayBonus[$lvl - 1];
/*
					// 如果已经是最高级且没有固定蜂值剩余，则不予以拨分红，以节省分红
					// 否则即使用户没有峰值，也先播出固态分红，如果用户升级即可继续领取
					if ($lvl == $allLevel && $vault <= 0) {
						$bonus = 0;
					}
*/
					// 如果分红大于固定分红剩余，按剩余额进行分红；并即时减去固定蜂值
				if ($bonus > $vault) {
					$bonus = $vault;
				}
				$vault -= $bonus;
					
				$totalBonus += $bonus;
				
				if ($bonus > 0) {
					++$dCnt;
				}
				$res7 = mysqli_query($con, "update Credit set CurrBonus='$bonus', Vault='$vault' where UserId='$userid'");
				if (!$res7) {
					writeLog($file, "\n!!! 更新用户 " . $userid . " 的固定分红失败: " . mysqli_error($con) . "\n\n");
				}
				else {
					writeLog($file, $userid . " 固定分红：" . $bonus . "，固定蜂值： " . $vault . "\n");
				}
			}
		}
	}
	writeLog($file, "\n------------------------------------------------------------------\n");
	if ($bCalcBonus) {
		writeLog($file, "--- 共分红给了 " . $dCnt . " 用户，固定分红总值是 " . $totalBonus . "\n");
	}
	else {
		writeLog($file, "--- 不进行固定分红！\n");
	}


	// 如果积分池小于分红额度，有问题，记log
	if ($pool < $totalBonus /* + $dBonusTotal */) {
		writeLog($file, "\n!!! 积分池不够，分红池高于积分池！\n");
	}
	
// 	$pool -= $dBonusTotal;		// 从积分池中播出此次分红额度
	if ($bCalcBonus) {
		$pool -= $totalBonus;
	}
	
	writeLog($file, "\n分红后积分池余额：" . $pool . "\n");
	
	// 更新总统计表
	$res3 = mysqli_query($con, "update TotalStatis set CreditsPool='$pool' where IndexId=1");
	if (!$res3) {
		echo "\n!!! 更新总统计表失败： " . mysqli_error($con) . "\n";
	}
	
	// 更新短期统计表	
	$res4 = mysqli_query($con, "update ShortStatis set Recharge=0, Withdraw=0, Transfer=0, OrderGross=0, WithdrawFee=0, TransferFee=0, 
						BonusTotal='$totalBonus', BonusLeft='$totalBonus', LastCalcTime='$now' where IndexId=1");
/*
	$res4 = false;
	if ($bCalcBonus) {
		$res4 = mysqli_query($con, "update ShortStatis set Recharge=0, Withdraw=0, Transfer=0, OrderGross=0, WithdrawFee=0, TransferFee=0, 
								BonusTotal='$totalBonus', BonusLeft='$totalBonus', LastCalcTime='$now',
								DBonusTotal='$dBonusTotal', DBonusLeft='$dBonusTotal', LastDCalcTime='$now' where IndexId=1");
	}
	else {
		$res4 = mysqli_query($con, "update ShortStatis set Recharge=0, Withdraw=0, Transfer=0, OrderGross=0, WithdrawFee=0, TransferFee=0, 
								DBonusTotal='$dBonusTotal', DBonusLeft='$dBonusTotal', LastDCalcTime='$now' where IndexId=1");		
	}
*/
	if (!$res4) {
		echo "\n!!! 更新短期统计表失败： " . mysqli_error($con) . "\n";
	}
}

function acceptBonus($userId)
{	
	include_once "func.php";
	include "constant.php";
	
	$con = connectToDB();
	if (!$con) {
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$res1 = mysqli_query($con, "select * from Credit where UserId='$userId'");
	if (!$res1 || mysqli_num_rows($res1) < 1) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'数据获取失败，请稍后重试！'));
		return;
	}
	else {
		
		$now = time();
		
		$row1 = mysqli_fetch_assoc($res1);
		
		$bonusTotal = $row1["TotalBonus"];
		$vault = $row1["Vault"];
//		$dayObtained = $row1["DayObtained"];
//		$lastObtainedtime = $row1["LastObtainedTime"];
//		$lastObtainedPntTime = $row1["LastObtainedPntTime"];
		$lastCBTime = $row1["LastCBTime"];

		if (isInTheSameDay($now, $lastCBTime)) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'您已经领取了！'));
			return;
		}
			
		$bonus = 0;
		$res = mysqli_query($con, "select * from CreditBank where UserId='$userId' and Type='1' and Balance>0 order by SaveTime");
		if ($res) {
			
			$now = time();
			while ($row = mysqli_fetch_assoc($res)) {
				
				// 当日存的云量不进行返还
				$savetime = $row["SaveTime"];
				
				if (isInTheSameDay($savetime, $now)) {
					continue;
				}
				
				$balance = $row["Balance"];
				$diviCnt = $row["DiviCnt"];
				$emptyTime = 0;
				if ($diviCnt > $balance) {
					$diviCnt = $balance;
					$emptyTime = $now;
				}
				$balance -= $diviCnt;
				$divident = $row["Divident"] + $diviCnt;
				
				$bonus += $diviCnt;
				$idx = $row["IdxId"];
				$res2 = mysqli_query($con, "update CreditBank set Balance='$balance', Divident='$divident', LastDiviT='$now', LastChangeT='$now', EmptyTime='$emptyTime' where IdxId=$idx");
				if (!$res2) {
					// !!! log error
				}
			}
		}
							
/*
		$bonusTotal += $toCredit;
		if (isInTheSameDay($now, $lastObtainedtime)) {
			$dayObtained += $toCredit;
		}
		else {
			$dayObtained = $toCredit;
		}
		
		$totalPnt = $row1["TotalObtainedPnts"];
		$yearPnt = $row1["YearObtainedPnts"];
		$monPnt = $row1["MonObtainedPnts"];
		$dayPnt = $row1["DayObtainedPnts"];
		if ($toPnts > 0) {
			if (isInTheSameDay($now, $lastObtainedPntTime)) {
				$dayPnt += $toPnts;
			}
			else {
				$dayPnt = $toPnts;
			}
			if (isInTheSameMonth($now, $lastObtainedPntTime)) {
				$monPnt += $toPnts;
			}
			else {
				$monPnt = $toPnts; 
			}
			if (isInTheSameYear($now, $lastObtainedPntTime)) {
				$yearPnt += $toPnts;
			}
			else {
				$yearPnt = $toPnts;
			}
			$totalPnt += $toPnts;
			$lastObtainedPntTime = $now;
		}
*/
		
		if ($bonus <= 0) {
			echo json_encode(array('error'=>'false','credit'=>$row1["Credits"],'vault'=>$vault));
			return;
		}
		
		$credit = $row1["Credits"] + $bonus;
		$vault -= $bonus;
		if ($vault < 0) {
			$vault = 0;
			// !!! log error
		}
		
		$res2 = mysqli_query($con, "update Credit set Credits='$credit', LastCBTime='$now', Vault='$vault' where UserId='$userId'");
		if (!$res2) {
 			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'领取失败，请稍后重试'));
			return;
		}
		
		echo json_encode(array('error'=>'false','credit'=>$credit,'vault'=>$vault));
		
		// 添加积分记录
		mysqli_query($con, "insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
						VALUES('$userId', '$bonus', '$credit', '$now', '$now', '$codeDivident')");
				
 		// 统计分红信息
 		insertBonusStatistics($con, $bonus);
	}
}

/*
 * Send user pnts bonus every day.
 * Check when user login to home page, so need to check more than today to send user pnts.
 */
function acceptPntsBonus($con, $userId, &$pnts)
{
	include_once "func.php";
	include "constant.php";
	
	$res1 = mysqli_query($con, "select * from Credit where UserId='$userId'");
	if (!$res1 || mysqli_num_rows($res1) < 1) {
		// echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'数据获取失败，请稍后重试！'));
		return false;
	}
	else {
		
		$now = time();
		
		$row1 = mysqli_fetch_assoc($res1);
		$credit = $row1["Pnts"];

		$lastCBPntsTime = $row1["LastCBPTime"];

		// 以前领过每日分红，判断今日是否已经领过
		if ($lastCBPntsTime > 0) {
			if (isInTheSameDay($now, $lastCBPntsTime)) {
				// already got for today
				return false;
			}
		}
		// 从未领过每日分红，获取注册时间，从注册时间开始领取
		else {
			$res3 = mysqli_query($con, "select * from ClientTable where UserId='$userId'");
			if (!$res3 || mysqli_num_rows($res3) < 1) {
				return false;
			}
			$row3 = mysqli_fetch_assoc($res3);
			$lastCBPntsTime = $row3["RegisterTime"];

			if ($lastCBPntsTime >= $now 
				|| isInTheSameDay($now, $lastCBPntsTime)) {
				return false;
			}
		}
			
		$bonus = 0;
		if ($lastCBPntsTime < $now) {

			$i = 0; // 计数，一次只累积五天，安全处理，以免无限循环
			while ($i < 5) {

				$i += 1;

				$oneTime = $lastCBPntsTime + 24 * 60 * 60;
				$lastCBPntsTime = $oneTime; 
				if ($oneTime >= $now
					|| isInTheSameDay($oneTime, $now)) {

					$oneTime = $now;
					$i = 5;	// break from while after run this time
				}

				$dayBonus = 0;
				$res = mysqli_query($con, "select * from CreditBank where UserId='$userId' and Type='2' and Balance>0 order by SaveTime");
				if ($res) {
					
					while ($row = mysqli_fetch_assoc($res)) {
						
						// 当日存的云量不进行返还
						$savetime = $row["SaveTime"];
						
						if ($savetime >= $oneTime 
							|| isInTheSameDay($savetime, $oneTime)) {
							continue;
						}
						
						$balance = $row["Balance"];
						$diviCnt = $row["DiviCnt"];
						$emptyTime = 0;
						if ($diviCnt > $balance) {
							$diviCnt = $balance;
							$emptyTime = $oneTime;
						}
						$balance -= $diviCnt;
						$divident = $row["Divident"] + $diviCnt;
						
						$dayBonus += $diviCnt;
						$idx = $row["IdxId"];
						$res2 = mysqli_query($con, "update CreditBank set Balance='$balance', Divident='$divident', LastDiviT='$oneTime', LastChangeT='$oneTime', EmptyTime='$emptyTime' where IdxId=$idx");
						if (!$res2) {
							// !!! log error
						}
					}

					if ($dayBonus > 0) {

						$bonus += $dayBonus;
						$credit += $dayBonus;

						// 添加线下云量记录
						$res4 = mysqli_query($con, "insert into PntsRecord (UserId, Amount, CurrAmount, ApplyTime, Type)
										VALUES('$userId', '$dayBonus', '$credit', '$oneTime', '$code2Divident')");
						if (!$res4) {
							// !!! log error
						}
					}
				}
			}
		}
							
		if ($bonus <= 0) {
			return false;
		}
		
		$res2 = mysqli_query($con, "update Credit set Pnts='$credit', LastCBPTime='$now' where UserId='$userId'");
		if (!$res2) {
			// !!! log error
			return false;
		}
		
		$pnts = $credit;
		// echo json_encode(array('error'=>'false','pnts'=>$credit));		
	}

	return true;
}

?>