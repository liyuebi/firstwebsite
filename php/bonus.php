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
	$staticUserCnt = $row["StaUserCount"];
	$dynamicUserCnt = $row["DyaUserCount"];
// 	$bonusTotalEver = $row["BonusTotal"];
	
	$res1 = mysql_query("select * from ShortStatis where IndexId=1");
	if (!$res1 || mysql_num_rows($res1) < 1) {
		writeLog($file, "\n！！！ 查找Short记录出错!\n\n");
		return false;
	}
	
	$row1 = mysql_fetch_assoc($res1);
	$gross = $row1["OrderGross"];
	
 	$lastBonusTotal = $row1["BonusTotal"];
	$lastBonusLeft = $row1["BonusLeft"];
	
	writeLog($file, "积分池余额：" . $pool . "\n");
	writeLog($file, "前期的分红总额：" . $lastBonusTotal . "\n");
	writeLog($file, "前期的分余额：" . $lastBonusLeft . "\n");
	// 剩余分红额小于0，说明超领了
	if ($lastBonusLeft < 0) {
		writeLog($file, "\n！！！ 积分剩余小于0，出错！\n\n");
	}
	
	include "constant.php";
	$bonusTotal = floor($gross * $rewardRate);
	$staticBonusTotal = floor($bonusTotal * $rewardStaticRate);
	$dynamicBonusTotal = floor($bonusTotal * $rewardDynamicRate);
	$staticBonusPer = 0;
	$dynamicBonusPer = 0;
	if ($staticUserCnt > 0) {
		$staticBonusPer = floor($staticBonusTotal / $staticUserCnt);
	}
	if ($dynamicUserCnt > 0) {
		$dynamicBonusPer = floor($dynamicBonusTotal / $dynamicUserCnt);
	}
	
	writeLog($file, "本期的订单总额：" . $gross . "\n");
	writeLog($file, "本期的订单比例：" . $rewardRate . "\n");
	writeLog($file, "本期的分红额：" . $bonusTotal . "\n");
	
	$pool += $lastBonusLeft;	// 未领取的分红返回基金池 
// 	$bonusTotalEver += ($lastBonusTotal - $lastBonusLeft); // 记录总分红值
	// 如果积分池小于分红额度，有问题，记log
	if ($pool < $bonusTotal) {
		writeLog($file, "\n!!! 积分池不够，分红池高于积分池！\n");
	}
	
	$pool -= $bonusTotal;		// 从积分池中播出此次分红额度
	
	$now = time();
	// 更新总统计表
	$res3 = mysql_query("update TotalStatis set CreditsPool='$pool' where IndexId=1");
	if (!$res3) {
		echo 1;
		echo mysql_error();
	}
	
	// 更新短期统计表	
	$res4 = mysql_query("update ShortStatis set Recharge=0, Withdraw=0, Transfer=0, OrderGross=0, WithdrawFee=0,
							TransferFee=0, BonusTotal='$bonusTotal', BonusLeft='$bonusTotal', StaUserCount='$staticUserCnt', 
							DynUserCount='$dynamicUserCnt', BonusPerSta='$staticBonusPer', BonusPerDya='$dynamicBonusPer', 
							StaUserObtained=0, DyaUserObtained=0, LastCalcTime='$now' where IndexId=1");
	if (!$res4) {
		echo 2;
		echo mysql_error();
	}
	
	$cnt = 0;
	writeLog($file, "\n------------------------------------------------------------------\n");
	$res5 = mysql_query("select * from User");
	if (!$res5) {
		writeLog($file, "\n!!! 查询用户表失败！\n");
	}
	else {
		while($row5 = mysql_fetch_array($res5)) {
			
			$cnt += 1;
			$userid = $row5["UserId"];
			$isDynamic = $row5["RecoCnt"] > 0;
			$res6 = mysql_query("select * from Credit where UserId='$userid'");
			if (!$res6 || mysql_num_rows($res6) < 1) {
				writeLog($file, "\n### 查询用户" . $userid . "的积分表失败！\n");
			}
			else {
				$amount = $staticBonusPer;
				if ($isDynamic) {
					$amount = $dynamicBonusPer;
				}
				$res7 = mysql_query("update Credit set CurrBonus='$amount' where UserId='$userid'");
				if (!$res7) {
					writeLog($file, "\n!!! " . $userid . "分红失败！\n");
				}
				else {
					writeLog($file, $userid . "分红得" . $amount . ".\n");
				}
			}
		}
	}
	writeLog($file, "\n------------------------------------------------------------------\n");
	writeLog($file, "--- 共分红给了 " . $cnt . " 用户！\n");
}

function acceptBonus($userId)
{	
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
		$dynFeng = $row1["DVault"];
		$dayObtained = $row1["DayObtained"];
		$lastObtainedtime = $row1["LastObtainedTime"];
		
		if ($currBonus <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'今天没有分红或您已经领取！'));
			return;
		}
		
		// 如果分不够，检查是否是动态用户，且是否还有动态分
		if ($currBonus > $feng) {
			
			if ($dynFeng > 0) {
				$res2 = mysql_query("select * from User where UserId='$userId'");
				if ($res2 && mysql_num_rows($res2) > 0) {
					$row2 = mysql_fetch_assoc($res2);
					if ($row2["RecoCnt"] > 0) {
						$feng += $dynFeng;
						$dynFeng = 0;
					}
				}
			}
		}
		
		if ($feng <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'您已经没有蜂值了，暂时不能领取！'));
			return;
		}
		
		$bNotEnoughFeng = false;
		// 仍然大于分红值，按剩余蜂值给用户分红
		if ($currBonus > $feng) {
			$currBonus = $feng;
			$bNotEnoughFeng = true;
		}
					
		$now = time();
		$bonusTotal += $currBonus;
		if (isInTheSameDay($now, $lastObtainedtime)) {
			$dayObtained += $currBonus;
		}
		else {
			$dayObtained = $currBonus;
		}
		
		$credit = $row1["Credits"];
		$res2 = mysql_query("update Credit set Credits='$credit', TotalBonus='$bonusTotal', CurrBonus=0, LastCBTime='$now', DayObtained='$dayObtained', LastObtainedTime='$now', Vault='$feng', DVault='$dynFeng' where UserId='$userId'");
		if (!$res2) {
 			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'您已经没有蜂值了，暂时不能领取！'));
			return;
		}
		
		if ($bNotEnoughFeng) {
			$msg = "您的蜂值余额不足，实际获得" . $currBonus . "蜜券！";
			echo json_encode(array('error'=>'false','not_enough'=>'true','error_msg'=>$msg));
		}
		else {
			echo json_encode(array('error'=>'false','not_enough'=>'false'));
		}
		
		// 添加积分记录
		include "constant.php";
		mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
						VALUES('$userId', '$currBonus', '$credit', '$now', '$now', '$codeDivident')");
		
		// 统计分红信息
		include_once "func.php";
		insertBonusStatistics($currBonus);
	}
}

?>