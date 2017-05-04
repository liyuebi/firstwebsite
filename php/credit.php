<?php

include 'database.php';

if (!isset($_POST['func'])) {
	exit('非法访问！');
}

if ("recharge" == $_POST['func']) {
	applyRecharge();
}
else if ("allowRecharge" == $_POST['func']) {
	allowRecharge();
}
else if ("withdraw" == $_POST['func']) {
	applyWithdraw();
}
else if ("allowWithdraw" == $_POST['func']) {
	allowWithdraw();
}
else if ("transfer" == $_POST['func']) {
	transfer();
}
else if ("acceptBonus" == $_POST['func']) {
	include "bonus.php";
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	$userid = $_SESSION['userId'];
	acceptBonus($userid);
}

function applyRecharge()
{	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$amount = trim(htmlspecialchars($_POST["amount"]));
	
	include 'regtest.php';
	if (!isValidMoneyAmount($amount)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'输入的金额无效，请重新输入！'));
		return;		
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		$userid = $_SESSION['userId'];
		
		include "func.php";
		$left = getCreditsPoolLeft($con);
		if ($amount > $left) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'暂时无空闲蜜券发放，请稍后重试！'));	
			return;
		}
		
		$time = time();
		$result = createRechgeTable();
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查表失败，请稍后重试！'));	
			return;
		}
		$result = mysql_query("insert into RechargeApplication (UserId, Amount, ApplyTime)
						VALUES('$userid', '$amount', '$time')");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'申请充值失败，请稍后重试！'));
			return;
		}
		echo json_encode(array('error'=>'false'));
	}
	return;
}

function allowRecharge()
{
	// 权限检查 	！！！！！！！
	include 'constant.php';
	$index = trim(htmlspecialchars($_POST["index"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！','index'=>$index));
		return;
	}
	else 
	{
		$result = mysql_query("select * from RechargeApplication where IndexId='$index'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'找不到对应的充值申请，操作中断！','index'=>$index));	
			return;			
		}
		$row = mysql_fetch_assoc($result);
		$userid = $row["UserId"];
		$amount = $row["Amount"];
		$applyTime = $row["ApplyTime"];
		
		include "func.php";
		$left = getCreditsPoolLeft($con);
		if ($amount > $left) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'暂时无空闲蜜券发放，通过充值失败！','index'=>$index));	
			return;
		}

		// 添加积分纪录
		$result = createCreditRecordTable();
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'建交易记录表失败，请稍后重试！','index'=>$index));	
			return;
		}
		
		$result = mysql_query("select * from CreditRecord where UserId='$userid' and Type='$codeRecharge' and ApplyIndexId='$index'");
		if ($result && mysql_num_rows($result) > 0) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'该交易已经通过','index'=>$index));	
			mysql_query("delete from RechargeApplication where IndexId='$index'");
			return; 
		}
		else {
			$result = mysql_query("select * from Credit where UserId='$userid'");
			if (!$result) {
				echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'找不到对应的用户数据！','index'=>$index));	
				return;
			}
			$row = mysql_fetch_assoc($result);
			$credit = $row["Credits"];
			$total = $credit + $amount;
			$now = time();
			
			$result = mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, ApplyIndexId, Type, AcceptTime)
							VALUES('$userid', '$amount', '$total', '$applyTime', '$index', '$codeRecharge', '$now')");
			if (!$result) {
				echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'交易记录插入失败，请稍后重试','index'=>$index));	
				return; 				
			}
			
			// 更新用户数据
			$lastModified = $row["LastRechargeTime"];
			$dayRecharge = 0;
			$monRecharge = 0;
			$totalRecharge = $row["TotalRecharge"] + $amount;
			if (isInTheSameDay($now, $lastModified)) {
				$dayRecharge = $row["DayRecharge"] + $amount;
			}
			else  {
				$dayRecharge = $amount;
			}
			if (isInTheSameMonth($now, $lastModified)) {
				$monRecharge = $row["MonthRecharge"] + $amount;
			}
			else {
				$monRecharge = $amount;
			}
			if (isInTheSameYear($now, $lastModified)) {
// 				$yearRecharge = $row[] + $amount;
			}
			else {
// 				$yearRecharge = $amount;
			}

			$result = mysql_query("update Credit set Credits='$total', LastRechargeTime='$now', DayRecharge='$dayRecharge', MonthRecharge='$monRecharge', TotalRecharge='$totalRecharge' where UserId='$userid'");
			if (!$result) {
				echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'更新用户积分失败，请稍后重试','index'=>$index));	
				return; 				
			}
			
			mysql_query("delete from RechargeApplication where IndexId='$index'");
			
			insertRechargeStatistics($amount);
		}
	}
	
	echo json_encode(array('error'=>'false','index'=>$index));
	return;
} 

function applyWithdraw()
{
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$amount = trim(htmlspecialchars($_POST["amount"]));
	$paypwd = trim(htmlspecialchars($_POST["paypwd"]));
	
	include 'regtest.php';
	if (!isValidMoneyAmount($amount)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'输入的金额无效，请重新输入！'));
		return;		
	}
		
	include "constant.php";
	if ($amount < $withdrawFloorAmount) {
		echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'输入的金额小于最低取现额度，请重新输入！'));
		return;				
	}
		
	if ($paypwd != $_SESSION["buypwd"]) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'支付密码输入错误，请重新填写！'));
		return;		
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		$userid = $_SESSION['userId'];
		$time = time();
		
		$result = mysql_query("select * from Credit where UserId='$userid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'找不到对应的用户数据！'));	
			return;
		}
		$row = mysql_fetch_assoc($result);
		$credit = $row["Credits"];
		
		if ($credit < $amount) {
			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'输入的金额大于您的余额，请重新输入！'));	
			return;			
		}
		
		$dayWithdraw = 0;
		$dayWd = $row["DayWithdraw"];
		$lastWd = $row["LastWithdrawTime"];
		if (isInTheSameDay($time, $dayWd)) {
			$dayWithdraw = $dayWd;
		}
		$applyCount = 0;
		$mostCredit = $withdrawCeilAmountOneDay;
		$res1 = mysql_query("select * from WithdrawApplication where UserId='$userid'");
		if ($res1) {
			while ($row1 = mysql_fetch_array($res1)) {
				if (isInTheSameDay($time, $row1["ApplyTime"])) {
					$applyCount += $row1["ApplyAmount"];
				}
			}
		}
		$mostCredit = max(0, $mostCredit - $dayWithdraw - $applyCount);
		if ($amount > $mostCredit) {
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'输入的金额大于今天剩余可提取的额度，请重新输入！'));	
			return;		
		}
		
		$result = createWithdrawTable();
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查表失败，请稍后重试！'));	
			return;
		}
		
		$fee = calcHandleFee($amount, $withdrawHandleRate);
		$actual = $amount - $fee;
		
		$result = mysql_query("insert into WithdrawApplication (UserId, ApplyAmount, ActualAmount, ApplyTime)
						VALUES('$userid', '$amount', '$actual', '$time')");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'提现申请失败，请稍后重试！'));
			return;
		}
	
		echo json_encode(array('error'=>'false'));
	}
	return;
}

function allowWithdraw()
{
	// 权限检查 	！！！！！！！
	include 'constant.php';
	$index = trim(htmlspecialchars($_POST["index"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！','index'=>$index));
		return;
	}
	else 
	{
		$result = mysql_query("select * from WithdrawApplication where IndexId='$index'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'找不到对应的提现申请，操作中断！','index'=>$index));	
			return;			
		}
		$row = mysql_fetch_assoc($result);
		$userid = $row["UserId"];
		$amount = $row["ApplyAmount"];
		$applyTime = $row["ApplyTime"];
		
		$result = createCreditRecordTable();
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'建交易记录表失败，请稍后重试！','index'=>$index));	
			return;
		}
		
		$result = mysql_query("select * from CreditRecord where UserId='$userid' and Type='$codeWithdraw' and ApplyIndexId='$index'");
		if ($result && mysql_num_rows($result) > 0) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'该请求已经通过','index'=>$index));	
			mysql_query("delete from WithdrawApplication where IndexId='$index'");
			return; 
		}
		else {
			$result = mysql_query("select * from Credit where UserId='$userid'");
			if (!$result) {
				echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'找不到对应的用户数据！','index'=>$index));	
				return;
			}
			$row = mysql_fetch_assoc($result);
			$credit = $row["Credits"];
			if ($credit < $amount) {
				echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'剩余积分不够要提现的数额，请求失败！','index'=>$index));	
				return; 				
			}
			$total = $credit - $amount;
			$now = time();
			$fee = calcHandleFee($amount, $withdrawHandleRate);
			
			$result = mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, ApplyIndexId, Type, AcceptTime, HandleFee)
							VALUES('$userid', '$amount', '$total', '$applyTime', '$index', '$codeWithdraw', '$now', $fee)");
			if (!$result) {
				echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'交易记录插入失败，请稍后重试','index'=>$index, 'mysql_error'=>mysql_error()));
				return; 				
			}
			
			$lastModified = $row["LastWithdrawTime"];
			$preFee = $row["TotalFee"];
			$postFee = $preFee + $fee;
			$preWithdraw = $row["TotalWithdraw"];
			$postWithdraw = $preWithdraw + $amount;
			$dayWithdraw = 0;
			$monWithdraw = 0;
			if (isInTheSameDay($now, $lastModified)) {
				$dayWithdraw = $row["DayWithdraw"] + $amount;
			}
			else  {
				$dayWithdraw = $amount;
			}
			if (isInTheSameMonth($now, $lastModified)) {
				$monWithdraw = $row["MonthWithdraw"] + $amount;
			}
			else {
				$monWithdraw = $amount;
			}
			if (isInTheSameYear($now, $lastModified)) {
// 				$yearRecharge = $row[] + $amount;
			}
			else {
// 				$yearRecharge = $amount;
			}
			$result = mysql_query("update Credit set Credits='$total', TotalFee='$postFee', TotalWithdraw='$postWithdraw', DayWithdraw='$dayWithdraw', MonthWithdraw='$monWithdraw', LastWithdrawTime='$now' where UserId='$userid'");
			if (!$result) {
				echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'更新用户积分失败，请稍后重试','index'=>$index));	
				return; 				
			}
			
			mysql_query("delete from WithdrawApplication where IndexId='$index'");
			
			// 更新统计数据
			include "func.php";
			insertWithdrawStatistics($amount, $fee);
		}
	}
	
	echo json_encode(array('error'=>'false','index'=>$index));
	return;
}

function transfer()
{	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}

	$receiver = trim(htmlspecialchars($_POST["toUser"]));
	$amount = trim(htmlspecialchars($_POST["amount"]));
	$paypwd = trim(htmlspecialchars($_POST["pwd"]));
	
	if ($paypwd != $_SESSION["buypwd"]) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'支付密码输入错误，请重新输入！'));
		return;
	}

	include 'regtest.php';
	if (!isValidMoneyAmount($amount)) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'输入的金额无效，请重新输入！'));
		return;		
	}

	$userid = $_SESSION["userId"];
	if ($userid == $receiver) {
		echo json_encode(array('error'=>'true','error_code'=>'10','error_msg'=>'不能给自己转账！'));
		return;
	}

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！','index'=>$index));
		return;
	}
	
	// 检查收款方账号
	$res1 = mysql_query("select * from User where UserId='$receiver'");
	if (!$res1 || mysql_num_rows($res1) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'选择的收款账号无效，请重新选择！'));
		return;		
	}
	$res1 = mysql_query("select * from Credit where UserId='$receiver'");
	if (!$res1 || mysql_num_rows($res1) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'选择的收款账号无效，请重新选择！'));
		return;		
	}
	$row1 = mysql_fetch_assoc($res1);
	
	// 确定付款方余额
	$res2 = mysql_query("select * from Credit where UserId='$userid'");
	if (!$res2 || mysql_num_rows($res2) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'暂时无法查询您的账户，请稍后重试！'));
		return;		
	}
	$row2 = mysql_fetch_assoc($res2);
	
	// 检查余额
	$credit = $row2["Credits"];
	if ($credit < $amount) {
		echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'您的余额已不足！'));
		return;		
	}
	
	include "constant.php";
	// 扣款
	$left = $credit - $amount;
	$fee = calcHandleFee($amount, $transferHandleRate);
	$actualamount = $amount - $fee;
	$totalFee = $row2["TotalFee"] + $fee;
	$res3 = mysql_query("update Credit set Credits='$left', TotalFee='$totalFee' where UserId='$userid'");
	if (!$res3) {
		echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'您的账户扣蜜券失败，请重试！'));
		return;
	}
	
	$now = time();
	// add credit record, ignored if failed
	$res3 = mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, HandleFee, ApplyTime, AcceptTime, WithUserId, Type)
							VALUES('$userid', '$amount', '$left', '$fee', '$now', '$now', '$receiver', '$codeTransferTo')");
							
	// 收款人加款
	$post = $row1["Credits"] + $actualamount;
	$res3 = mysql_query("update Credit set Credits='$post' where UserId='$receiver'");
	if (!$res3) {
		echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'收款人增加蜜券失败！'));
		return;		
	}
	
	// add credit record, ignored if failed
	$res3 = mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, HandleFee, ApplyTime, AcceptTime, WithUserId, Type)
							VALUES('$receiver', '$amount', '$post', '$fee', '$now', '$now', '$userid', '$codeTransferFrom')");


	echo json_encode(array('error'=>'false'));
	
	// 增加统计数据，如出错忽略
	include "func.php";
	insertTransferStatistics($amount, $fee);
}
	
?>