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
else if ("denyRecharge" == $_POST['func']) {
	denyRecharge();
}
else if ("wdPnt" == $_POST['func']) {
	applyWithdrawPnts();
}
else if ("allowWdPnt" == $_POST['func']) {
	allowWithdrawPnts();
}
else if ("denyWdPnt" == $_POST['func']) {
	denyWithdrawPnts();
}
else if ("withdraw" == $_POST['func']) {
	applyWithdraw();
}
else if ("allowWithdraw" == $_POST['func']) {
	allowWithdraw();
}
else if ("denyWithdraw" == $_POST['func']) {
	denyWithdraw();
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
else if ("getCredit" == $_POST['func']) {
	queryCredit();
}
else if ("getPnts" == $_POST['func']) {
	queryPnt();
}
else if ("getProfit" == $_POST['func']) {
	quertProfit();
}
else if ("pToC" == $_POST['func']) {
	profitToCredit();
}
else if ("pToP" == $_POST['func']) {
	profitToPnt();
}
else if ("pToS" == $_POST['func']) {
	profitToSharePoint();
}

function applyRecharge()
{	
	include 'regtest.php';
	include 'constant.php';
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$amount = trim(htmlspecialchars($_POST["amount"]));
	$method = trim(htmlspecialchars($_POST["method"]));
	
	if (!isValidMoneyAmount($amount)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'输入的金额无效，请重新输入！'));
		return;		
	}
	
	if ($amount % $refererConsumePoint != 0) {
		$msg = '选择的金额必须是' . $refererConsumePoint . '的倍数！';
		echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>$msg));
		return;		
	}
	
	$method = intval($method);
// 	if ($method != $paymentWechat && $method != $paymentAlipay && $method != $paymentBank) {
// 		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'请选择支付方式！'));
// 		return;		
// 	}
	
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
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'暂时无空闲线上云量发放，请稍后重试！'));	
			return;
		}
		
		$account = '';
		$bankUser = '';
		$bankName = '';
		$bankBranch = '';
		if ($method == 1) {
			$res = mysqli_query($con, "select * from WechatAccount where UserId='$userid'");
			if (!$res || mysqli_num_rows($res) <= 0) {
				echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'找不到您的微信账号！'));	
				return;
			}
			$row = mysqli_fetch_assoc($res);	
			$account = $row["WechatAcc"];
		}
		else if ($method == 2) {
			$res = mysqli_query($con, "select * from AlipayAccount where UserId='$userid'");
			if (!$res || mysqli_num_rows($res) <= 0) {
				echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'找不到您的支付宝账号！'));	
				return;
			}
			$row = mysqli_fetch_assoc($res);
			$account = $row["AlipayAcc"];
		}
		else if ($method == 3) {
			$res = mysqli_query($con, "select * from BankAccount where UserId='$userid'");
			if (!$res || mysqli_num_rows($res) <= 0) {
				echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'找不到您的银行账号！'));	
				return;
			}
			$row = mysqli_fetch_assoc($res);
			$account = $row["BankAcc"];
			$bankUser = $row["AccName"];
			$bankName = $row["BankName"];
			$bankBranch = $row["BankBranch"];
		}
				
		$time = time();
		$result = createRechargeTable($con);
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查表失败，请稍后重试！'));	
			return;
		}
		$nickname= $_SESSION['nickname'];
		$phone = $_SESSION['phonenum'];
		$result = mysqli_query($con, "insert into RechargeApplication (UserId, NickName, PhoneNum, Amount, ApplyTime, Method, Account, BankUser, BankName, BankBranch)
						VALUES('$userid', '$nickname', '$phone', '$amount', '$time', '$method', '$account', '$bankUser', '$bankName', '$bankBranch')");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'申请充值失败，请稍后重试！'));
			return;
		}
		echo json_encode(array('error'=>'false'));
		
		// check user credit info exists, if not insert
		$res1 = mysqli_query($con, "select * from Credit where UserId='$userid'");
		if (!$res1) {
			// !!! log error
		}
		else {
			if (mysqli_num_rows($res1) <= 0) {
				if (!mysqli_query($con, "insert into Credit (UserId) values('$userid')")) {
					// !!! log error
				}
			}
		}
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
		$result = mysqli_query($con, "select * from RechargeApplication where IndexId='$index'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'找不到对应的充值申请，操作中断！','index'=>$index));	
			return;			
		}
		$row = mysqli_fetch_assoc($result);
		$userid = $row["UserId"];
		$amount = $row["Amount"];
		$applyTime = $row["ApplyTime"];
		
		include "func.php";
		$left = getCreditsPoolLeft($con);
		if ($amount > $left) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'暂时无空闲线上云量发放，通过充值失败！','index'=>$index));	
			return;
		}

		// 添加积分纪录
		$result = createCreditRecordTable();
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'建交易记录表失败，请稍后重试！','index'=>$index));	
			return;
		}
		
		$result = mysqli_query($con, "select * from CreditRecord where UserId='$userid' and Type='$codeRecharge' and ApplyIndexId='$index'");
		if ($result && mysqli_num_rows($result) > 0) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'该交易已经通过','index'=>$index));	
			mysqli_query($con, "delete from RechargeApplication where IndexId='$index'");
			return; 
		}
		else {
			$result = mysqli_query($con, "select * from Credit where UserId='$userid'");
			if (!$result) {
				echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'找不到对应的用户数据！','index'=>$index));	
				return;
			}
			$row = mysqli_fetch_assoc($result);
			$total = 0;
			$now = time();
			
			$result = mysqli_query($con, "insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, ApplyIndexId, Type, AcceptTime)
							VALUES('$userid', '$amount', '$total', '$applyTime', '$index', '$codeChargeRegiToken', '$now')");
			if (!$result) {
				echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'交易记录插入失败，请稍后重试','index'=>$index));	
				return; 				
			}
			
			// 更新用户数据
			$lastModified = $row["LastRechargeTime"];
			$dayRecharge = 0;
			$monRecharge = 0;
			$yearRecharge = 0;
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
				$yearRecharge = $row["YearRecharge"] + $amount;
			}
			else {
				$yearRecharge = $amount;
			}

			$result = mysqli_query($con, "update Credit set LastRechargeTime='$now', DayRecharge='$dayRecharge', MonthRecharge='$monRecharge', YearRecharge='$yearRecharge', TotalRecharge='$totalRecharge' where UserId='$userid'");
			if (!$result) {
				echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'更新用户积分失败，请稍后重试','index'=>$index));	
				return; 				
			}
			
			mysqli_query($con, "delete from RechargeApplication where IndexId='$index'");
		}
	}
	
	echo json_encode(array('error'=>'false','index'=>$index,'pre'=>0,'post'=>$total));
	return;
}

function denyRecharge()
{
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
		$result = mysqli_query($con, "select * from RechargeApplication where IndexId='$index'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'找不到对应的充值申请，操作中断！','index'=>$index));	
			return;			
		}

		$result = mysqli_query($con, "delete from RechargeApplication where IndexId='$index'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'删除充值申请时出错，请稍后重试！','index'=>$index));	
			return;			
		}
	}
	
	echo json_encode(array('error'=>'false','index'=>$index));
	return;
}

function applyWithdrawPnts()
{
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$amount = trim(htmlspecialchars($_POST["amount"]));
	$paypwd = trim(htmlspecialchars($_POST["paypwd"]));
	$method = trim(htmlspecialchars($_POST["method"]));
	$shopId = trim(htmlspecialchars($_POST["idx"]));
	
	include 'regtest.php';
	if (!isValidMoneyAmount($amount)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'输入的金额无效，请重新输入！'));
		return;		
	}
		
	include "constant.php";
	if ($amount < $pntWithdrawFloorAmt) {
		$msg = '输入的金额小于最低取现额度' . $pntWithdrawFloorAmt . '，请重新输入！';
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>$msg));
		return;				
	}
	
	if ($amount % 100 != 0) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'输入的金额不是一百的倍数，请重新输入！'));
		return;
	}
		
	if (!password_verify($paypwd, $_SESSION["buypwd"])) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'支付密码输入错误，请重新填写！'));
		return;		
	}
	
	$method = intval($method);
	if ($method != $paymentWechat && $method != $paymentAlipay && $method != $paymentBank) {
		echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'请选择支付方式！'));
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
		
		$result = mysqli_query($con, "select * from OfflineShop where ShopId='$shopId'");
		if (!$result || mysqli_num_rows($result) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'用户数据有误，请稍后重试！'));	
			return;
		}
		$row = mysqli_fetch_assoc($result);
		if ($userid != $row["UserId"]) {
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'用户数据有误，请稍后重试！'));	
			return;
		}
		$handlefee = $pntWithdrawHandleRate; // $row["WdFeeRate"];
		
		$result = mysqli_query($con, "select * from Credit where UserId='$userid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'找不到对应的用户数据！'));	
			return;
		}
		$row = mysqli_fetch_assoc($result);
		$pnts = $row["Pnts"];
		
		if ($pnts < $amount) {
			echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'输入的金额大于您的余额，请重新输入！'));	
			return;			
		}

		// $mostCredit = 0; //$profitWithdrawCeilAmtOneDay;
		// $applyCount = 0;
		// $res1 = mysqli_query($con, "select * from PntsWdApplication where UserId='$userid' and Status!='$olShopWdDeclined' order by ApplyTime desc");
		// if ($res1) {
			
		// 	while ($row1 = mysqli_fetch_array($res1)) {
				
		// 		if (isInTheSameDay($time, $row1["ApplyTime"])) {
		// 			$applyCount += $row1["ApplyAmount"];
		// 		}
		// 		else {
		// 			break;
		// 		}
		// 	}
		// }

		// $mostCredit = max(0, $mostCredit - $applyCount);
		// if ($amount > $mostCredit) {
		// 	$msg = '输入的金额大于今天剩余可提取的额度' . $mostCredit . '，请重新输入！';
		// 	echo json_encode(array('error'=>'true','error_code'=>'10','error_msg'=>$msg));	
		// 	return;		
		// }
		
		$total = $pnts - $amount;
		// $fee = calcHandleFee($amount, $profitWithdrawHandleRate);
		$fee = calcHandleFee($amount, $handlefee);
		$actual = $amount - $fee;
		
		// 添加交易申请
		$result = createPntsWithdrawTable($con);
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'查表失败，请稍后重试！'));	
			return;
		}

		$accountId = 0;
		$account = '';
		$bankUser = '';
		$bankName = '';
		$bankBranch = '';
		if ($method == $paymentWechat) {
			$res = mysqli_query($con, "select * from WechatAccount where UserId='$userid'");
			if (!$res || mysqli_num_rows($res) <= 0) {
				echo json_encode(array('error'=>'true','error_code'=>'11','error_msg'=>'找不到您的微信账号！'));	
				return;
			}
			$row = mysqli_fetch_assoc($res);	
			$accountId = $row["IndexId"];
			$account = $row["WechatAcc"];
		}
		else if ($method == $paymentAlipay) {
			$res = mysqli_query($con, "select * from AlipayAccount where UserId='$userid'");
			if (!$res || mysqli_num_rows($res) <= 0) {
				echo json_encode(array('error'=>'true','error_code'=>'12','error_msg'=>'找不到您的支付宝账号！'));	
				return;
			}
			$row = mysqli_fetch_assoc($res);
			$accountId = $row["IndexId"];
			$account = $row["AlipayAcc"];
		}
		else if ($method == $paymentBank) {
			$res = mysqli_query($con, "select * from BankAccount where UserId='$userid'");
			if (!$res || mysqli_num_rows($res) <= 0) {
				echo json_encode(array('error'=>'true','error_code'=>'13','error_msg'=>'找不到您的银行账号！'));	
				return;
			}
			$row = mysqli_fetch_assoc($res);
			$accountId = $row["IndexId"];
			$account = $row["BankAcc"];
			$bankUser = $row["AccName"];
			$bankName = $row["BankName"];
			$bankBranch = $row["BankBranch"];
		}
		
		$nickname= $_SESSION['nickname'];
		$phone = $_SESSION['phonenum'];
		$result = mysqli_query($con, "insert into PntsWdApplication (UserId, ShopId, NickName, PhoneNum, ApplyAmount, ActualAmount, ApplyTime, Method, AccountId, Account, BankUser, BankName, BankBranch, Status)
						VALUES('$userid', '$shopId', '$nickname', '$phone', '$amount', '$actual', '$time', '$method', '$accountId', '$account', '$bankUser', '$bankName', '$bankBranch', '$olShopWdApplied')");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'提现申请失败，请稍后重试！'));
			return;
		}

		// 修改credit表
		$result = mysqli_query($con, "update Credit set Pnts='$total' where UserId='$userid'");
		if (!$result) {
			// !!! log error
		}
		else {
			// 添加交易记录
			$result = createPntsRecordTable($con);
			if (!$result) {
				// !!! log error
			}
			$result = mysqli_query($con, "insert into PntsRecord (UserId, Amount, CurrAmount, RelatedAmount, HandleFee, ApplyTime, ApplyIndexId, WithStoreId, Type)
							VALUES('$userid', '$actual', '$total', '$amount', '$fee', '$time', '0', '$shopId', '$code2OlShopWdApply')");
			if (!$result) {
				// !!! log error			
			}
		}
						
		echo json_encode(array('error'=>'false'));
	}
	return;
}

function allowWithdrawPnts()
{
	include_once 'admin_func.php';
	session_start();
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}

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
		$result = mysqli_query($con, "select * from PntsWdApplication where IndexId='$index'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'找不到对应的提现申请，操作中断！','index'=>$index));	
			return;			
		}
		$row = mysqli_fetch_assoc($result);
		$shopId = $row["ShopId"];
		$amount = $row["ApplyAmount"];
		$applyTime = $row["ApplyTime"];
		$fee = $amount - $row["ActualAmount"];

		if ($olShopWdApplied != $row["Status"]) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'状态已改变，操作中断！','index'=>$index));	
			return;			
		}
		
		$adminId = $_SESSION['adminUid'];
		
		$now = time();
		$result = mysqli_query($con, "update PntsWdApplication set Status='$olShopWdAccepted', AcceptTime='$now', AdminId='$adminId' where IndexId='$index'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'删除取现申请记录失败，请稍后重试！','index'=>$index));	
			return;			
		}
		
		$res1 = mysqli_query($con, "select * from OfflineShop where ShopId='$shopId'");
		if ($res1 && mysqli_num_rows($res1) > 0) {
			
			$row1 = mysqli_fetch_assoc($res1);
			$withdrawAmt = $row1["WithdrawAmount"] + $amount;
			$withdrawFee = $row1["WithdrawFee"] + $fee;
			
			$res2 = mysqli_query($con, "update OfflineShop set WithdrawAmount='$withdrawAmt', WithdrawFee='$withdrawFee' where ShopId='$shopId'");
			if (!$res2) {
				// !!! log error
			}
		}
			
		// 更新统计数据
		include "func.php";
		insertOfflineShopWithdrawStatistics($con, $amount, $fee);
	}
	
	echo json_encode(array('error'=>'false','index'=>$index));
	return;
}

function denyWithdrawPnts()
{
	include_once 'admin_func.php';
	session_start();
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}

	include 'constant.php';
	$index = trim(htmlspecialchars($_POST["index"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！','index'=>$index));
		return;
	}
	
	$result = mysqli_query($con, "select * from PntsWdApplication where IndexId='$index'");
	if (!$result || mysqli_num_rows($result) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'找不到对应的提现申请，操作中断！','index'=>$index));	
		return;			
	}
	
	$row = mysqli_fetch_assoc($result);
	$userid = $row["UserId"];
	$shopId = $row["ShopId"];
	$amount = $row["ApplyAmount"];

	if ($olShopWdApplied != $row["Status"]) {
		echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'状态已改变，操作中断！','index'=>$index));	
		return;			
	}
	
	$res = mysqli_query($con, "select * from Credit where UserId='$userid'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'找不到申请对应的用户，操作中断！','index'=>$index));	
		return;					
	}
	$row1 = mysqli_fetch_assoc($res);
	$pnt = $row1["Pnts"];
	$pntPost = $pnt + $amount;

	$now = time();
	$adminId = $_SESSION['adminUid'];

	// 拒绝取现申请
	$result = mysqli_query($con, "update PntsWdApplication set Status='$olShopWdDeclined', AcceptTime='$now', AdminId='$adminId' where IndexId='$index'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'拒绝取现申请失败，请稍后重试','index'=>$index));	
		return; 				
	}
	
	// 更新用户数据
	$result = mysqli_query($con, "update Credit set Pnts='$pntPost' where UserId='$userid'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'更新用户线下云量失败，请稍后重试','index'=>$index));	
		return; 				
	}

	// 添加交易记录
	$result = createPntsRecordTable($con);
	if (!$result) {
		// !!! log error
		// echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'建交易记录表失败，请稍后重试！','index'=>$index));	
		// return;
	}
	else {
		$result = mysqli_query($con, "insert into PntsRecord (UserId, Amount, CurrAmount, ApplyTime, ApplyIndexId, WithStoreId, Type)
						VALUES('$userid', '$amount', '$pntPost', '$now', '$index', '$shopId', '$code2OlSHopWdDecline')");
		if (!$result) {
			// !!! log error
			// echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'交易记录插入失败，请稍后重试','index'=>$index, 'mysql_error'=>mysqli_error($con)));
			// return; 				
		}
	}
	
	echo json_encode(array('error'=>'false','index'=>$index,'pre'=>$pnt,'post'=>$pntPost));
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
	$method = trim(htmlspecialchars($_POST["method"]));
	
	include 'regtest.php';
	if (!isValidMoneyAmount($amount)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'输入的金额无效，请重新输入！'));
		return;		
	}
		
	include "constant.php";
	if ($amount < $profitWithdrawFloorAmt) {
		echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'输入的金额小于最低取现额度，请重新输入！'));
		return;				
	}
	
	if ($amount % 100 != 0) {
		echo json_encode(array('error'=>'true','error_code'=>'11','error_msg'=>'输入的金额不是一百的倍数，请重新输入！'));
		return;
	}
		
	if (!password_verify($paypwd, $_SESSION["buypwd"])) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'支付密码输入错误，请重新填写！'));
		return;		
	}
	
	$method = intval($method);
	if ($method != $paymentWechat && $method != $paymentAlipay && $method != $paymentBank) {
		echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'请选择支付方式！'));
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
		
		$result = mysqli_query($con, "select * from Credit where UserId='$userid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'找不到对应的用户数据！'));	
			return;
		}
		$row = mysqli_fetch_assoc($result);
		$credit = $row["Credits"];
		
		if ($credit < $amount) {
			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'输入的金额大于您的余额，请重新输入！'));	
			return;			
		}
		
		$dayWithdraw = 0;
		$dayWd = $row["DayWithdraw"];
		$lastWd = $row["LastWithdrawTime"];
		if (isInTheSameDay($time, $lastWd)) {
			$dayWithdraw = $dayWd;
		}
		$mostCredit = $profitWithdrawCeilAmtOneDay;
		$applyCount = 0;

		$mostCredit = max(0, $mostCredit - $dayWithdraw /* - $applyCount */);
		if ($amount > $mostCredit) {
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'输入的金额大于今天剩余可提取的额度，请重新输入！'));	
			return;		
		}
		
		$total = $credit - $amount;
		$now = time();
		$fee = calcHandleFee($amount, $profitWithdrawHandleRate);
		
		// 添加交易记录
		$result = createCreditRecordTable();
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'建交易记录表失败，请稍后重试！','index'=>$index));	
			return;
		}
		$result = mysqli_query($con, "insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, ApplyIndexId, Type, AcceptTime, HandleFee)
						VALUES('$userid', '$amount', '$total', '$now', '0', '$codeWithdraw', '$now', $fee)");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'12','error_msg'=>'交易记录插入失败，请稍后重试','index'=>$index, 'mysql_error'=>mysqli_error($con)));
			return; 				
		}
		
		// 修改credit表
		$lastModified = $lastWd;
		$preFee = $row["TotalFee"];
		$postFee = $preFee + $fee;
		$preWithdraw = $row["TotalWithdraw"];
		$postWithdraw = $preWithdraw + $amount;
		$dayWithdraw = 0;
		$monWithdraw = 0;
		$yearWithdraw = 0;
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
			$yearWithdraw = $row["YearWithdraw"] + $amount;
		}
		else {
			$yearWithdraw = $amount;
		}
		$result = mysqli_query($con, "update Credit set Credits='$total', TotalFee='$postFee', TotalWithdraw='$postWithdraw', DayWithdraw='$dayWithdraw', MonthWithdraw='$monWithdraw', YearWithdraw='$yearWithdraw', LastWithdrawTime='$now' where UserId='$userid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'更新用户积分失败，请稍后重试','index'=>$index));	
			return; 				
		}
			
		// 添加交易申请
		$result = createWithdrawTable($con);
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'查表失败，请稍后重试！'));	
			return;
		}
		
		$account = '';
		$bankUser = '';
		$bankName = '';
		$bankBranch = '';
		if ($method == 1) {
			$res = mysqli_query($con, "select * from WechatAccount where UserId='$userid'");
			if (!$res || mysqli_num_rows($res) <= 0) {
				echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'找不到您的微信账号！'));	
				return;
			}
			$row = mysqli_fetch_assoc($res);	
			$account = $row["WechatAcc"];
		}
		else if ($method == 2) {
			$res = mysqli_query($con, "select * from AlipayAccount where UserId='$userid'");
			if (!$res || mysqli_num_rows($res) <= 0) {
				echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'找不到您的支付宝账号！'));	
				return;
			}
			$row = mysqli_fetch_assoc($res);
			$account = $row["AlipayAcc"];
		}
		else if ($method == 3) {
			$res = mysqli_query($con, "select * from BankAccount where UserId='$userid'");
			if (!$res || mysqli_num_rows($res) <= 0) {
				echo json_encode(array('error'=>'true','error_code'=>'10','error_msg'=>'找不到您的银行账号！'));	
				return;
			}
			$row = mysqli_fetch_assoc($res);
			$account = $row["BankAcc"];
			$bankUser = $row["AccName"];
			$bankName = $row["BankName"];
			$bankBranch = $row["BankBranch"];
		}
		
		$actual = $amount - $fee;
		
		$nickname= $_SESSION['nickname'];
		$phone = $_SESSION['phonenum'];
		$result = mysqli_query($con, "insert into WithdrawApplication (UserId, NickName, PhoneNum, ApplyAmount, ActualAmount, ApplyTime, Method, Account, BankUser, BankName, BankBranch)
						VALUES('$userid', '$nickname', '$phone', '$amount', '$actual', '$time', '$method', '$account', '$bankUser', '$bankName', '$bankBranch')");
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
		$result = mysqli_query($con, "select * from WithdrawApplication where IndexId='$index'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'找不到对应的提现申请，操作中断！','index'=>$index));	
			return;			
		}
		$row = mysqli_fetch_assoc($result);
		$userid = $row["UserId"];
		$amount = $row["ApplyAmount"];
		$applyTime = $row["ApplyTime"];
		$fee = $amount - $row["ActualAmount"];
		
		$result = mysqli_query($con, "delete from WithdrawApplication where IndexId='$index'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'删除取现申请记录失败，请稍后重试！','index'=>$index));	
			return;			
		}
			
		// 更新统计数据
		include "func.php";
// 		insertWithdrawStatistics($amount, $fee);
	}
	
	echo json_encode(array('error'=>'false','index'=>$index));
	return;
}

function denyWithdraw()
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
	
	$result = mysqli_query($con, "select * from WithdrawApplication where IndexId='$index'");
	if (!$result || mysqli_num_rows($result) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'找不到对应的提现申请，操作中断！','index'=>$index));	
		return;			
	}
	
	$row = mysqli_fetch_assoc($result);
	$userid = $row["UserId"];
	$amount = $row["ApplyAmount"];
	$fee = $amount - $row["ActualAmount"];
	
	$res = mysqli_query($con, "select * from Credit where UserId='$userid'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'找不到申请对应的用户，操作中断！','index'=>$index));	
		return;					
	}
	$row1 = mysqli_fetch_assoc($res);
	$credit = $row1["Credits"];
	$creditPost = $credit + $amount;
	$prefee = $row1["TotalFee"];
	$postfee = $prefee - $fee;
	$preWithdraw = $row1["TotalWithdraw"];
	$postWithdraw = $preWithdraw - $amount;
	$lastModified = $row1["LastWithdrawTime"];
	$dayWithdraw = 0;
	$monWithdraw = 0;
	$yearWithdraw = 0;
	$now = time();
	if (isInTheSameDay($now, $lastModified)) {
		$dayWithdraw = $row1["DayWithdraw"] - $amount;
	}
	else  {
		$dayWithdraw = $amount;
	}
	if (isInTheSameMonth($now, $lastModified)) {
		$monWithdraw = $row1["MonthWithdraw"] - $amount;
	}
	else {
		$monWithdraw = $amount;
	}
	if (isInTheSameYear($now, $lastModified)) {
		$yearWithdraw = $row1["YearWithdraw"] - $amount;
	}
	else {
		$yearWithdraw = $amount;
	}
	
	// 删除取现申请
	$result = mysqli_query($con, "delete from WithdrawApplication where IndexId='$index'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'删除取现申请失败，请稍后重试','index'=>$index));	
		return; 				
	}
	
	// 更新用户数据
	$result = mysqli_query($con, "update Credit set Credits='$creditPost', TotalFee='$postfee', TotalWithdraw='$postWithdraw', DayWithdraw='$dayWithdraw', MonthWithdraw='$monWithdraw', YearWithdraw='$yearWithdraw' where UserId='$userid'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'更新用户积分失败，请稍后重试','index'=>$index));	
		return; 				
	}

	// 添加交易记录
	$result = createCreditRecordTable($con);
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'建交易记录表失败，请稍后重试！','index'=>$index));	
		return;
	}
	$result = mysqli_query($con, "insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, ApplyIndexId, Type, AcceptTime, HandleFee)
					VALUES('$userid', '$amount', '$creditPost', '$now', '$index', '$codeWithdrawCancelled', '$now', '0')");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'交易记录插入失败，请稍后重试','index'=>$index, 'mysql_error'=>mysqli_error($con)));
		return; 				
	}
	
	echo json_encode(array('error'=>'false','index'=>$index,'pre'=>$credit,'post'=>$creditPost));
}

function transfer()
{	
	echo json_encode(array('error'=>'true','error_code'=>'50','error_msg'=>'转账功能已禁用！'));
	return;
		
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}

	$receiver = trim(htmlspecialchars($_POST["toUser"]));
	$amount = trim(htmlspecialchars($_POST["amount"]));
	$paypwd = trim(htmlspecialchars($_POST["pwd"]));
	
	if (!password_verify($paypwd, $_SESSION["buypwd"])) {
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
	$res1 = mysqli_query($con, "select * from ClientTable where UserId='$receiver'");
	if (!$res1 || mysqli_num_rows($res1) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'选择的收款账号无效，请重新选择！'));
		return;		
	}
	$res1 = mysqli_query($con, "select * from Credit where UserId='$receiver'");
	if (!$res1 || mysqli_num_rows($res1) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'选择的收款账号无效，请重新选择！'));
		return;		
	}
	$row1 = mysqli_fetch_assoc($res1);
	
	// 确定付款方余额
	$res2 = mysqli_query($con, "select * from Credit where UserId='$userid'");
	if (!$res2 || mysqli_num_rows($res2) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'暂时无法查询您的账户，请稍后重试！'));
		return;		
	}
	$row2 = mysqli_fetch_assoc($res2);
	
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
	if ($userid == "100016" || $userid == "100030") {
		$fee = 0;
	}
	$actualamount = $amount - $fee;
	$totalFee = $row2["TotalFee"] + $fee;
	$res3 = mysqli_query($con, "update Credit set Credits='$left', TotalFee='$totalFee' where UserId='$userid'");
	if (!$res3) {
		echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'您的账户扣线上云量失败，请重试！'));
		return;
	}
	
	$now = time();
	// add credit record, ignored if failed
	$res3 = mysqli_query($con, "insert into CreditRecord (UserId, Amount, CurrAmount, HandleFee, ApplyTime, AcceptTime, WithUserId, Type)
							VALUES('$userid', '$amount', '$left', '$fee', '$now', '$now', '$receiver', '$codeTransferTo')");
							
	// 收款人加款
	$post = $row1["Credits"] + $actualamount;
	$res3 = mysqli_query($con, "update Credit set Credits='$post' where UserId='$receiver'");
	if (!$res3) {
		echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'收款人增加线上云量失败！'));
		return;		
	}
	
	// add credit record, ignored if failed
	$res3 = mysqli_query($con, "insert into CreditRecord (UserId, Amount, CurrAmount, HandleFee, ApplyTime, AcceptTime, WithUserId, Type)
							VALUES('$receiver', '$amount', '$post', '$fee', '$now', '$now', '$userid', '$codeTransferFrom')");


	echo json_encode(array('error'=>'false'));
	
	// 增加统计数据，如出错忽略
	include "func.php";
	insertTransferStatistics($amount, $fee);
}

function queryCredit()
{
	$index = trim(htmlspecialchars($_POST["index"]));
	$userId = trim(htmlspecialchars($_POST["user"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！','index'=>$index));
		return;
	}
	
	$res = mysqli_query($con, "select * from Credit where UserId='$userId'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'查找不到对应用户！','index'=>$index));
		return;
	}
	$row = mysqli_fetch_assoc($res);
	$credit = $row["Credits"];
	echo json_encode(array('error'=>'false','index'=>$index,'credit'=>$credit));
}

function queryPnt()
{
	$index = trim(htmlspecialchars($_POST["index"]));
	$userId = trim(htmlspecialchars($_POST["user"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！','index'=>$index));
		return;
	}
	
	$res = mysqli_query($con, "select * from Credit where UserId='$userId'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'查找不到对应用户！','index'=>$index));
		return;
	}
	$row = mysqli_fetch_assoc($res);
	$pnt = $row["Pnts"];
	echo json_encode(array('error'=>'false','index'=>$index,'pnt'=>$pnt));
}

function quertProfit()
{
	$index = trim(htmlspecialchars($_POST["index"]));
	$userId = trim(htmlspecialchars($_POST["user"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！','index'=>$index));
		return;
	}
	
	$res = mysqli_query($con, "select * from Credit where UserId='$userId'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'查找不到对应用户！','index'=>$index));
		return;
	}
	$row = mysqli_fetch_assoc($res);
	$profit = $row["ProfitPnt"];
	echo json_encode(array('error'=>'false','index'=>$index,'profit'=>$profit));
}

function profitToCredit()
{
	include_once "regtest.php";

	$cnt = trim(htmlspecialchars($_POST["num"]));
	if (!isValidNum($cnt) || $cnt <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'无效的数额，请重新输入！'));
		return;
	}

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	session_start();
	$userid = $_SESSION["userId"];

	$res = mysqli_query($con, "select * from Credit where UserId='$userid'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到对应用户！'));
		return;
	}
	$row = mysqli_fetch_assoc($res);
	$credit = $row["Credits"];
	$profit = $row["ProfitPnt"];

	if ($profit < $cnt) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'您输入的额度超过您的消费云量，请重新输入！'));
		return;
	}

	$profit -= $cnt;
	$credit += $cnt;

	$res = mysqli_query($con, "update Credit set Credits='$credit', ProfitPnt='$profit' where UserId='$userid'");
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'转移操作失败，请稍后重试！'));
		return;
	}

	// 添加转移记录
	{
		include "constant.php";

		$now = time();

		$result = createProfitPntRecordTable($con);
		if (!$result) {
			// !!! log error
		}
		else {
			$result = mysqli_query($con, "insert into ProfitPntRecord (UserId, Amount, CurrAmount, ApplyTime, ApplyIndexId, Type)
							VALUES('$userid', '$cnt', '$profit', '$now', '0', '$code3ToCredit')");
			if (!$result) {
				// !!! log error	
			}
		}

		$result = createCreditRecordTable($con);
		if (!$result) {
			// !!! log error
		}
		else {
			$result = mysqli_query($con, "insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
							VALUES('$userid', '$cnt', '$credit', '$now', '$now', '$codeFromProfit')");
			if (!$result) {
				// !!! log error	
			}
		}
	}

	echo json_encode(array('error'=>'false'));	
}

function profitToPnt()
{
	include_once "regtest.php";

	$cnt = trim(htmlspecialchars($_POST["num"]));
	if (!isValidNum($cnt) || $cnt <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'无效的数额，请重新输入！'));
		return;
	}

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	session_start();
	$userid = $_SESSION["userId"];

	$res = mysqli_query($con, "select * from Credit where UserId='$userid'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到对应用户！'));
		return;
	}
	$row = mysqli_fetch_assoc($res);
	$pnt = $row["Pnts"];
	$profit = $row["ProfitPnt"];

	if ($profit < $cnt) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'您输入的额度超过您的消费云量，请重新输入！'));
		return;
	}

	$profit -= $cnt;
	$pnt += $cnt;

	$res = mysqli_query($con, "update Credit set Pnts='$pnt', ProfitPnt='$profit' where UserId='$userid'");
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'转移操作失败，请稍后重试！'));
		return;
	}

	// 添加转移记录
	{
		include "constant.php";

		$now = time();

		$result = createProfitPntRecordTable($con);
		if (!$result) {
			// !!! log error
		}
		else {
			$result = mysqli_query($con, "insert into ProfitPntRecord (UserId, Amount, CurrAmount, ApplyTime, ApplyIndexId, Type)
							VALUES('$userid', '$cnt', '$profit', '$now', '0', '$code3ToCredit')");
			if (!$result) {
				// !!! log error	
			}
		}

		$result = createPntsRecordTable($con);
		if (!$result) {
			// !!! log error
		}
		else {
			$result = mysqli_query($con, "insert into PntsRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
							VALUES('$userid', '$cnt', '$pnt', '$now', '$now', '$code2FromProfit')");
			if (!$result) {
				// !!! log error	
			}
		}
	}

	echo json_encode(array('error'=>'false'));	
}

function profitToSharePoint()
{
	include_once "regtest.php";

	$cnt = trim(htmlspecialchars($_POST["num"]));
	if (!isValidNum($cnt) || $cnt <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'无效的数额，请重新输入！'));
		return;
	}

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	session_start();
	$userid = $_SESSION["userId"];

	$res = mysqli_query($con, "select * from Credit where UserId='$userid'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到对应用户！'));
		return;
	}
	$row = mysqli_fetch_assoc($res);
	$shareCredit = $row["ShareCredit"];
	$profit = $row["ProfitPnt"];

	if ($profit < $cnt) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'您输入的额度超过您的消费云量，请重新输入！'));
		return;
	}

	$profit -= $cnt;
	$shareCredit += $cnt;

	$res = mysqli_query($con, "update Credit set ShareCredit='$shareCredit', ProfitPnt='$profit' where UserId='$userid'");
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'转移操作失败，请稍后重试！'));
		return;
	}

	// 添加转移记录
	{
		include "constant.php";

		$now = time();

		$result = createProfitPntRecordTable($con);
		if (!$result) {
			// !!! log error
		}
		else {
			$result = mysqli_query($con, "insert into ProfitPntRecord (UserId, Amount, CurrAmount, ApplyTime, ApplyIndexId, Type)
							VALUES('$userid', '$cnt', '$profit', '$now', '0', '$code3ToShareCredit')");
			if (!$result) {
				// !!! log error	
			}
		}

		$result = createShareCreditRecordTable($con);
		if (!$result) {
			// !!! log error
		}
		else {
			$result = mysqli_query($con, "insert into ShareCreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
							VALUES('$userid', '$cnt', '$shareCredit', '$now', '$now', '$code4FromProfit')");
			if (!$result) {
				// !!! log error	
			}
		}
	}

	echo json_encode(array('error'=>'false'));	
}
	
?>