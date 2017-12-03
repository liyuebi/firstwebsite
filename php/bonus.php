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
function getDayBonus($userId)
{
	$ret = 0;
	
	$res = mysql_query("select * from CreditBank where UserId='$userId' and Type='1' and Balance>0 order by SaveTime desc");
	if ($res) {
		
		$now = time();
		while ($row = mysql_fetch_array($res)) {
			
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
	
	$res = mysql_query("select * from TotalStatis where IndexId=1");
	if (!$res || mysql_num_rows($res) < 1) {
		writeLog($file, "!!! 查找TotalStatis记录出错!\n");
		return false;
	}
	$row = mysql_fetch_assoc($res);
	$pool = $row["CreditsPool"];
	
	$res1 = mysql_query("select * from ShortStatis where IndexId=1");
	if (!$res1 || mysql_num_rows($res1) < 1) {
		writeLog($file, "\n!!! 查找Short记录出错!\n\n");
		return false;
	}
	
	$row1 = mysql_fetch_assoc($res1);
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
	$res5 = mysql_query("select * from ClientTable");
	if (!$res5) {
		writeLog($file, "\n!!! 查询用户表失败！\n");
	}
	else {
		while($row5 = mysql_fetch_array($res5)) {
			
			$userid = $row5["UserId"];
			$lvl = $row5["Lvl"];
			$res6 = mysql_query("select * from Credit where UserId='$userid'");
			if (!$res6 || mysql_num_rows($res6) < 1) {
				writeLog($file, "\n!!! 查询用户" . $userid . "的积分表失败！\n");
			}
			else {
				$row6 = mysql_fetch_assoc($res6);
				
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
				$res7 = mysql_query("update Credit set CurrBonus='$bonus', Vault='$vault' where UserId='$userid'");
				if (!$res7) {
					writeLog($file, "\n!!! 更新用户 " . $userid . " 的固定分红失败: " . mysql_error() . "\n\n");
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
	$res3 = mysql_query("update TotalStatis set CreditsPool='$pool' where IndexId=1");
	if (!$res3) {
		echo "\n!!! 更新总统计表失败： " . mysql_error() . "\n";
	}
	
	// 更新短期统计表	
	$res4 = mysql_query("update ShortStatis set Recharge=0, Withdraw=0, Transfer=0, OrderGross=0, WithdrawFee=0, TransferFee=0, 
						BonusTotal='$totalBonus', BonusLeft='$totalBonus', LastCalcTime='$now' where IndexId=1");
/*
	$res4 = false;
	if ($bCalcBonus) {
		$res4 = mysql_query("update ShortStatis set Recharge=0, Withdraw=0, Transfer=0, OrderGross=0, WithdrawFee=0, TransferFee=0, 
								BonusTotal='$totalBonus', BonusLeft='$totalBonus', LastCalcTime='$now',
								DBonusTotal='$dBonusTotal', DBonusLeft='$dBonusTotal', LastDCalcTime='$now' where IndexId=1");
	}
	else {
		$res4 = mysql_query("update ShortStatis set Recharge=0, Withdraw=0, Transfer=0, OrderGross=0, WithdrawFee=0, TransferFee=0, 
								DBonusTotal='$dBonusTotal', DBonusLeft='$dBonusTotal', LastDCalcTime='$now' where IndexId=1");		
	}
*/
	if (!$res4) {
		echo "\n!!! 更新短期统计表失败： " . mysql_error() . "\n";
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
	
	$res1 = mysql_query("select * from Credit where UserId='$userId'");
	if (!$res1 || mysql_num_rows($res1) < 1) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'数据获取失败，请稍后重试！'));
		return;
	}
	else {
		
		$now = time();
		
		$row1 = mysql_fetch_assoc($res1);
		
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
		$res = mysql_query("select * from CreditBank where UserId='$userId' and Type='1' and Balance>0 order by SaveTime");
		if ($res) {
			
			$now = time();
			while ($row = mysql_fetch_array($res)) {
				
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
				$res2 = mysql_query("update CreditBank set Balance='$balance', Divident='$divident', LastDiviT='$now', LastChangeT='$now', EmptyTime='$emptyTime' where IdxId=$idx");
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
		
		$res2 = mysql_query("update Credit set Credits='$credit', LastCBTime='$now', Vault='$vault' where UserId='$userId'");
		if (!$res2) {
 			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'领取失败，请稍后重试'));
			return;
		}
		
		echo json_encode(array('error'=>'false','credit'=>$credit,'vault'=>$vault));
		
		// 添加积分记录
		if ($bonus) {
			mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
							VALUES('$userId', '$bonus', '$credit', '$now', '$now', '$codeDivident')");
		}
				
 		// 统计分红信息
 		insertBonusStatistics($bonus);
	}
}

function acceptDBonus($userid)
{
	include_once "func.php";
	include "constant.php";
	
	$con = connectToDB();
	if (!$con) {
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$res1 = mysql_query("select * from Credit where UserId='$userid'");
	if (!$res1 || mysql_num_rows($res1) < 1) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'数据获取失败，请稍后重试！'));
		return;
	}
	else {
		$row1 = mysql_fetch_assoc($res1);
		
		$dayObtained = $row1["DayObtained"];
		$lastObtainedtime = $row1["LastObtainedTime"];
		$lastObtainedPntTime = $row1["LastObtainedPntTime"];
		
		$now = time();
					
		$toPnts = 0;
		$toCredit = 0;
		
/*
		$dBonusTotal += $toCredit;
		if (isInTheSameDay($now, $lastObtainedtime)) {
			$dayObtained += $toCredit;
		}
		else {
			$dayObtained = $toCredit;
		}
*/
		if (!isInTheSameDay($now, $lastObtainedtime)) {
			$dayObtained = 0;
		}
		
		$totalPnt = $row1["TotalObtainedPnts"];
		$yearPnt = $row1["YearObtainedPnts"];
		$monPnt = $row1["MonObtainedPnts"];
		$dayPnt = $row1["DayObtainedPnts"];
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

		
		$credit = $row1["Credits"] + $toCredit;
		$pnts = $row1["Pnts"] + $toPnts;
		$res2 = mysql_query("update Credit set Credits='$credit', Pnts='$pnts', DayObtained='$dayObtained', DayObtainedPnts='$dayPnt', MonObtainedPnts='$monPnt', YearObtainedPnts='$yearPnt', TotalObtainedPnts='$totalPnt', LastObtainedPntTime='$now' where UserId='$userid'");
		if (!$res2) {
 			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'领取失败，请稍后重试','sql_error'=>mysql_error()));
			return;
		}
		
		echo json_encode(array('error'=>'false','not_enough'=>'false','credit'=>$credit,'pnts'=>$pnts,'DayObtained'=>$dayObtained));
		
		// 添加积分记录
// 		mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
// 						VALUES('$userid', '$toCredit', '$credit', '$now', '$now', '$codeDynDivident')");
						
		// 添加线下云量记录
		mysql_query("insert into PntsRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
				VALUES('$userid', '$toPnts', '$pnts', '$now', '$now', '$code2DynDivident')");
		
		// 统计分红信息
// 		insertDynBonusStatistics($toCredit, $toPnts);
	}
}

?>