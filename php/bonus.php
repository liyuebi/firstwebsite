<?php

include_once "database.php";

function calcBonus()
{
	$con = connectToDB();
	if (!$con) {
		return false;
	}
	
	mysql_select_db("my_db", $con);
	$res = mysql_query("select * from TotalStatis where IndexId=1");
	if (!$res || mysql_num_rows($res) < 1) {
		return false;
	}
	$row = mysql_fetch_assoc($res);
	$pool = $row["CreditsPool"];
	$staticUserCnt = $row["StaUserCount"];
	$dynamicUserCnt = $row["DyaUserCount"];
// 	$bonusTotalEver = $row["BonusTotal"];
	
	$res1 = mysql_query("select * from ShortStatis where IndexId=1");
	if (!$res1 || mysql_num_rows($res1) < 1) {
		return false;
	}
	
	$row1 = mysql_fetch_assoc($res1);
	$gross = $row1["OrderGross"];
	
 	$lastBonusTotal = $row1["BonusTotal"];
	$lastBonusLeft = $row1["BonusLeft"];
	// 剩余分红额小于0，说明超领了
	if ($lastBonusLeft < 0) {
		
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
	
	$pool += $lastBonusLeft;	// 未领取的分红返回基金池 
// 	$bonusTotalEver += ($lastBonusTotal - $lastBonusLeft); // 记录总分红值
	// 如果积分池小于分红额度，有问题，记log
	if ($pool < $bonusTotal) {
		
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
}

?>