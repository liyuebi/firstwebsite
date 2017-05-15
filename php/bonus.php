<?php

include_once "database.php";

function writeLog($file, $msg) 
{
	if ($file) {
		fwrite($file, $msg);
	}
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
		writeLog($file, "！！！ 查找TotalStatis记录出错!\n");
		return false;
	}
	$row = mysql_fetch_assoc($res);
	$pool = $row["CreditsPool"];
	
	$res1 = mysql_query("select * from ShortStatis where IndexId=1");
	if (!$res1 || mysql_num_rows($res1) < 1) {
		writeLog($file, "\n！！！ 查找Short记录出错!\n\n");
		return false;
	}
	
	$row1 = mysql_fetch_assoc($res1);
	$gross = $row1["OrderGross"];
	
	$now = time();
	
	$lastCalcTime = $row1["LastCalcTime"];
	$lastDCalcTime = $row1["LastDCalcTime"];
 	$lastBonusTotal = $row1["BonusTotal"];
	$lastBonusLeft = $row1["BonusLeft"];
	$lastDBonusTotal = $row1["DBonusTotal"];
	$lastDBonusLeft = $row1["DBonusLeft"];
	
	$bCalcBonus = true;
	if (isInTheSameDay($now, $lastCalcTime)) {
		$bCalcBonus = false;
	}
	
	writeLog($file, "积分池余额：" . $pool . "\n");
	writeLog($file, "前期的固定分红总额：" . $lastBonusTotal . "\n");
	writeLog($file, "前期的固定分红余额：" . $lastBonusLeft . "\n");
	writeLog($file, "前期的动态分红总额：" . $lastBonusTotal . "\n");
	writeLog($file, "前期的动态分红余额：" . $lastBonusLeft . "\n");
	if ($bCalcBonus) {
		writeLog($file, "\n### 本次需重新计算固定分红\n\n");
	}
	else {
		writeLog($file, "\n### 本次不重新计算固定分红\n\n");
	}
	
	// 计算所有层级
	$allLevel = count($levelBonus) + 1;
	writeLog($file, "\n### 目前共有 " . $allLevel . " 个层级\n\n");
	
	// 剩余分红额小于0，说明超领了
	if ($lastBonusLeft < 0) {
		writeLog($file, "\n！！！ 固定分红剩余小于0，出错！\n\n");
	}
	if ($lastDBonusLeft < 0) {
		writeLog($file, "\n！！！ 动态分红剩余小于0，出错！\n\n");
	}
	
	$dBonusTotal = floor($gross * $rewardRate);
	
	writeLog($file, "本期的订单总额：" . $gross . "\n");
	writeLog($file, "本期的订单分红比例：" . $rewardRate . "\n");
	writeLog($file, "本期的动态分红额：" . $dBonusTotal . "\n");
	
	// 未领取的分红返回基金池 
	$pool += $lastDBonusLeft;	
	if ($bCalcBonus) {
		$pool += $lastBonusLeft;
	}
		
	// 计算并分发固定分红
	$cnt = 0;
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
				
				// 累计计算动态峰值总数
				$dVault = $row6["DVault"];
				$dfeng = ceil($dVault / $fengzhiValue);
				$totalDFeng += $dfeng;
				
				if (!$bCalcBonus) {
					continue;
				}
				
				$vault = $row6["Vault"];
				$bonus = 0;

				if ($lvl > 1) {
					$bonus = $levelDayBonus[$lvl - 2];
					// 如果已经是最高级且没有固定蜂值剩余，则不予以拨分红，以节省分红
					// 否则即使用户没有峰值，也先播出固态分红，如果用户升级即可继续领取
					if ($lvl == $allLevel && $vault <= 0) {
						$bonus = 0;
					}
					$totalBonus += $bonus;
				}
				
				if ($bonus > 0) {
					++$dCnt;
				}
				$res7 = mysql_query("update Credit set CurrBonus='$bonus' where UserId='$userid'");
				if (!$res7) {
					writeLog($file, "\n!!! 更新用户 " . $userid . " 的固定分红失败: " . mysql_error() . "\n");
				}
				else {
					writeLog($file, $userid . " : " . $bonus . "\n");
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
		
	// 计算每个动态峰值分多少积分 
	$dBonusPerF = 0;
	if ($totalDFeng > 0) {
		$dBonusPerF = floor($dBonusTotal / $totalDFeng);	
	}
	
	if ($dBonusPerF > 0) {
				
		$cnt = 0;
		writeLog($file, "\n------------------------------------------------------------------\n");
		$res8 = mysql_query("select * from ClientTable");
		if (!$res8) {
			writeLog($file, "\n!!! 查询用户表失败！\n");
		}
		else {
			while($row8 = mysql_fetch_array($res8)) {
				
				$userid = $row8["UserId"];
				$res6 = mysql_query("select * from Credit where UserId='$userid'");
				if (!$res6 || mysql_num_rows($res6) < 1) {
					writeLog($file, "\n!!! 查询用户" . $userid . "的积分表失败！\n");
				}
				else {
					$row6 = mysql_fetch_assoc($res6);
					
					// 累计计算动态峰值总数
					$dVault = $row6["DVault"];
					if ($dVault > 0) {
						$dfeng = ceil($dVault / $fengzhiValue);
						$dBonus = $dfeng * $dBonusPerF;
					
						$res7 = mysql_query("update Credit set CurrDBonus='$dBonus' where UserId='$userid'");
						if (!$res7) {
							writeLog($file, "\n!!! 更新用户" . $userid . "的动态分红失败: " . mysql_error() . "\n");
						}
						else {
							writeLog($file, $userid . " : " . $dBonus . "\n");	
						}
						
						++$cnt;
					}
				}
			}
		}
		writeLog($file, "\n------------------------------------------------------------------\n");
		writeLog($file, "--- 共分红给了 " . $cnt . " 用户\n");
		writeLog($file, "--- 动态分红总值是 " . $dBonusTotal . "\n");
		writeLog($file, "--- 动态分红每蜂值分得 " . $dBonusPerF . "\n");
	}
	else {
		writeLog($file, "--- 动态分红每蜂值为0，不进行动态分红！\n");
	}

	// 如果积分池小于分红额度，有问题，记log
	if ($pool < $totalBonus + $dBonusTotal) {
		writeLog($file, "\n!!! 积分池不够，分红池高于积分池！\n");
	}
	
	$pool -= $dBonusTotal;		// 从积分池中播出此次分红额度
	if ($bCalcBonus) {
		$pool -= $totalBonus;
	}
	
	writeLog($file, "\n分红后积分池余额：" . $pool . "\n");
	
	// 更新总统计表
	$res3 = mysql_query("update TotalStatis set CreditsPool='$pool', DFengTotal='$totalDFeng' where IndexId=1");
	if (!$res3) {
		echo "\n!!! 更新总统计表失败： " . mysql_error() . "\n";
	}
	
	// 更新短期统计表	
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
	if (!$res4) {
		echo "\n!!! 更新短期统计表失败： " . mysql_error() . "\n";
	}
}

function acceptBonus($userId)
{	
	include_once "func.php";
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
		$row1 = mysql_fetch_assoc($res1);
		
		$bonusTotal = $row1["TotalBonus"];
		$currBonus = $row1["CurrBonus"];
		$feng = $row1["Vault"];
		$dayObtained = $row1["DayObtained"];
		$lastObtainedtime = $row1["LastObtainedTime"];
		$lastCBTime = $row1["LastCBTime"];
		
		$now = time();
		if (isInTheSameDay($now, $lastCBTime)) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'您已经领取了！'));
			return;
		}
		
		if ($currBonus <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'今天没有固定分红或您已经领取！'));
			return;
		}		
		
		if ($feng <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'您已经没有固定蜂值了，暂时不能领取！'));
			return;
		}
		
		$bNotEnoughFeng = false;
		// 分红值大于蜂值，按剩余蜂值给用户分红
		if ($currBonus > $feng) {
			$currBonus = $feng;
			$bNotEnoughFeng = true;
		}
		$feng -= $currBonus;
					
		$bonusTotal += $currBonus;
		if (isInTheSameDay($now, $lastObtainedtime)) {
			$dayObtained += $currBonus;
		}
		else {
			$dayObtained = $currBonus;
		}
		
		$credit = $row1["Credits"] + $currBonus;
		$res2 = mysql_query("update Credit set Credits='$credit', TotalBonus='$bonusTotal', CurrBonus=0, LastCBTime='$now', DayObtained='$dayObtained', LastObtainedTime='$now', Vault='$feng' where UserId='$userId'");
		if (!$res2) {
 			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'领取失败，请稍后重试'));
			return;
		}
		
		if ($bNotEnoughFeng) {
			$msg = "您的固定蜂值余额不足，实际获得" . $currBonus . "蜜券！";
			echo json_encode(array('error'=>'false','not_enough'=>'true','error_msg'=>$msg,'credit'=>$credit,'vault'=>$feng,'DayObtained'=>$dayObtained));
		}
		else {
			echo json_encode(array('error'=>'false','not_enough'=>'false','credit'=>$credit,'vault'=>$feng,'DayObtained'=>$dayObtained));
		}
		
		// 添加积分记录
		include "constant.php";
		mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
						VALUES('$userId', '$currBonus', '$credit', '$now', '$now', '$codeDivident')");
		
		// 统计分红信息
		insertBonusStatistics($currBonus);
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
		
		$dBonusTotal = $row1["TotalDBonus"];
		$currDBonus = $row1["CurrDBonus"];
		$dynFeng = $row1["DVault"];
		$dayObtained = $row1["DayObtained"];
		$lastObtainedtime = $row1["LastObtainedTime"];
// 		$lastCDBTime = $row1["LastCDBTime"];
		
		$now = time();
		
		if ($currDBonus <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'今天没有动态分红或您已经领取！'));
			return;
		}		
		
		if ($dynFeng <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'您已经没有动态蜂值了，暂时不能领取！'));
			return;
		}
		
		$bNotEnoughFeng = false;
		// 分红值大于蜂值，按剩余蜂值给用户分红
		if ($currDBonus > $dynFeng) {
			$currDBonus = $dynFeng;
			$bNotEnoughFeng = true;
		}
		$dynFeng -= $currDBonus;
					
		$dBonusTotal += $currDBonus;
		if (isInTheSameDay($now, $lastObtainedtime)) {
			$dayObtained += $currDBonus;
		}
		else {
			$dayObtained = $currDBonus;
		}
		
		$credit = $row1["Credits"] + $currDBonus;
		$res2 = mysql_query("update Credit set Credits='$credit', TotalDBonus='$dBonusTotal', CurrDBonus=0, LastCDBTime='$now', DayObtained='$dayObtained', LastObtainedTime='$now', DVault='$dynFeng' where UserId='$userid'");
		if (!$res2) {
 			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'领取失败，请稍后重试','sql_error'=>mysql_error()));
			return;
		}
		
		$dynFeng = ceil($dynFeng / $fengzhiValue);
		if ($bNotEnoughFeng) {
			$msg = "您的动态蜂值余额不足，实际获得" . $currDBonus . "蜜券！";
			echo json_encode(array('error'=>'false','not_enough'=>'true','error_msg'=>$msg,'credit'=>$credit,'dVault'=>$dynFeng,'DayObtained'=>$dayObtained));
		}
		else {
			echo json_encode(array('error'=>'false','not_enough'=>'false','credit'=>$credit,'dVault'=>$dynFeng,'DayObtained'=>$dayObtained));
		}
		
		// 添加积分记录
		mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
						VALUES('$userid', '$currDBonus', '$credit', '$now', '$now', '$codeDynDivident')");
		
		// 统计分红信息
		insertDynBonusStatistics($currDBonus);
	}
}

?>