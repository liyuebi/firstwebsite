<?php

include 'database.php';

if (!isset($_POST['func'])) {
	exit('非法访问！');
}

if ("createTrade" == $_POST['func']) {
	createTradeOrder();
}
else if ("cancelTrade" == $_POST['func']) {
	cancelTradeOrder();
}
else if ("startTrade" == $_POST['func']) {
	startTradeOrder();
}
else if ("saveCredit" == $_POST['func']) {
	saveCredit();
}
else if ("confirmPayment" == $_POST['func']) {
	confirmTradeOrderPay();
}
else if ("abandonPayment" == $_POST['func']) {
	abandonTradeOrderPay();
}

function createTradeOrder()
{	
	include 'regtest.php';
	include 'constant.php';
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$amount = trim(htmlspecialchars($_POST["amount"]));
	
	if (!isValidMoneyAmount($amount)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'输入的金额无效，请重新输入！'));
		return;		
	}
	
/*
	if ($amount % $refererConsumePoint != 0) {
		$msg = '选择的金额必须是' . $refererConsumePoint . '的倍数！';
		echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>$msg));
		return;		
	}
*/

	if ($amount < 100) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'金额不能小于100，请重新输入！'));
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
		
		$res = mysql_query("select * from Credit where UserId='$userid'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查表失败，请稍后重试！'));	
			return;
		}
		$row = mysql_fetch_assoc($res);
		$credit = $row["Credits"];
		
		$handlefee = $amount * 0.1;
		$neededAmount = $amount + $handlefee;
		if ($neededAmount > $credit) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'您输入的金额超过您的余额，请重新输入！'));	
			return;
		}
				
		$time = time();
		$result = createCreditTradeTable();
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'查表失败，请稍后重试！'));	
			return;
		}
		
		$nickname= $_SESSION['nickname'];
		$result = mysql_query("insert into CreditTrade (TradeId, SellerId, SellNickN, Quantity, HanderRate, CreateTime, Status)
						VALUES('111', '$userid', '$nickname', '$amount', '0.1', '$time', '$creditTradeInited')");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'创建交易失败，请稍后重试！'));
			return;
		}
		
		$credit = $credit - $neededAmount;
		$result = mysql_query("update Credit set Credits='$credit'");
		if (!$result) {
			// !!! log error
		}
		
		// 修改用户的积分纪录，增加直推奖励，若失败不影响返回结果
		$result = mysql_query("insert into CreditRecord (UserId, Amount, HandleFee, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
								VALUES($userid, $amount, $handlefee, $credit, $time, $time, 0, $codeCreTradeInit)");
		
		echo json_encode(array('error'=>'false'));
	}
	return;
}

function cancelTradeOrder()
{
	include 'regtest.php';
	include 'constant.php';
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$idx = trim(htmlspecialchars($_POST["idx"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		$userid = $_SESSION['userId'];
		
		$res = mysql_query("select * from CreditTrade where IdxId='$idx'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查表交易失败，请稍后重试！'));	
			return;
		}	
		
		$row = mysql_fetch_assoc($res);
		$status = $row["Status"];

		if ($userid != $row["SellerId"]) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'查询交易出错，请稍后重试！'));	
			return;
		}

		if ($status != $creditTradeInited) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'交易状态改变，请稍后重试！'));	
			return;
		}
		
		$now = time();
		$res1 = mysql_query("update CreditTrade set CancelTime='$now', Status='$creditTradeCancelled' where IdxId='$idx'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'确认支付失败，请稍后重试！'));	
			return;			
		}

		// 退款
		$quantity = $row["Quantity"];
		$handleRate = $row["HanderRate"];
		$handleFee = $quantity * $handleRate;
		$sellid = $row["SellerId"];
		refundSeller($sellid, $quantity, $handleFee);

 	}
	
	echo json_encode(array('error'=>'false'));	
}

function startTradeOrder()
{
	include 'regtest.php';
	include 'constant.php';
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$amount = trim(htmlspecialchars($_POST["amount"]));
	$idx = trim(htmlspecialchars($_POST["idx"]));
	
	if (!isValidMoneyAmount($amount)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'输入的金额无效，请重新输入！'));
		return;		
	}
	if ($amount % 100 != 0) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'交易金额必须是100的倍数，请重新输入！'));
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
		$nickname= $_SESSION['nickname'];
		
		$res = mysql_query("select * from CreditTrade where IdxId='$idx'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查表交易失败，请稍后重试！'));	
			return;
		}	
		
		$row = mysql_fetch_assoc($res);
		$status = $row["Status"];
		
		if ($userid == $row["SellerId"]) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'不能购买自己的挂单！'));	
			return;
		}
		
		if ($status != $creditTradeInited) {
			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'交易状态改变，请稍后重试！'));	
			return;
		}
		
		$now = time();
		$createTime = $row["CreateTime"];
		if ($now - $createTime >= 60 * 60 * 24) {
			
			// 修改交易状态
			mysql_query("update CreditTrade set Status='$creditTradeExpired' where IdxId='$idx'");
			// 退款
			
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'该交易已过期，请重新选择其他交易！'));	
			return;	
		}
		
		if ($amount > $row["Quantity"]) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'超过交易总额度，请重新输入！'));	
			return;			
		}
		
		$res1 = mysql_query("update CreditTrade set BuyerId='$userid', BuyerNickN='$nickname', BuyCnt='$amount', ReserveTime='$now', Status='$creditTradeReserved' where IdxId='$idx'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'确认订单失败，请稍后重试'));	
			return;			
		}
	}
	
	echo json_encode(array('error'=>'false'));
}

function refundSeller($sellid, $quantity, $handleFee)
{
	include 'constant.php';
	
	$res = mysql_query("select * from Credit where UserId='$sellid'");
	if (!$res && mysql_num_rows($res) > 0) {
		// !!! log error
		return false;
	}
	else {
		$row = mysql_fetch_assoc($res);
		$credit = $row["Credits"];
		
		$credit = $credit + $quantity + $handleFee;
		$res1 = mysql_query("update Credit set Credits='$credit' where UserId='$sellid'");
		if (!$res1) {
			// !!! log error
			return false;
		}
		else {
			$amount = $quantity+$handleFee;
			$now = time();
			$res2 = mysql_query("insert into CreditRecord (UserId, Amount, HandleFee, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
							VALUES($sellid, $amount, 0, $credit,  $now, $now, 0, $codeCreTradeCancel)");
			if (!$res2) {
				// !!! log error
				return false;
			}
		}
	}
	
	return true;
}

function confirmTradeOrderPay()
{
	include 'regtest.php';
	include 'constant.php';
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$idx = trim(htmlspecialchars($_POST["idx"]));

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		$userid = $_SESSION['userId'];
		
		$res = mysql_query("select * from CreditTrade where IdxId='$idx'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查表交易失败，请稍后重试！'));	
			return;
		}	
		
		$row = mysql_fetch_assoc($res);
		$status = $row["Status"];

		if ($userid != $row["BuyerId"]) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'查询交易出错，请稍后重试！'));	
			return;
		}
		
		if ($status != $creditTradeReserved) {
			echo json_encode(array('error'=>'true','error_code'=>'33','error_msg'=>'交易状态改变，请稍后重试！'));	
			return;
		}

		$now = time();
		$reserveTime = $row["ReserveTime"];
		if ($now - $reserveTime >= 60 * 60 * 24) {
			
			// 修改订单状态
			mysql_query("update CreditTrade set Status='$creditTradeNotPayed' where IdxId='$idx'");
			// 退款
			$quantity = $row["Quantity"];
			$handleRate = $row["HanderRate"];
			$handleFee = $quantity * $handleRate;
			$sellid = $row["SellerId"];
			refundSeller($sellid, $quantity, $handleFee);
						
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'该交易已过期，请重新选择其他交易！'));	
			return;	
		}
	
		$res1 = mysql_query("update CreditTrade set PayTime='$now', Status='$creditTradeReserved' where IdxId='$idx'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'确认支付失败，请稍后重试！'));	
			return;			
		}
	}
	
	echo json_encode(array('error'=>'false'));
}

function abandonTradeOrderPay()
{
	include 'regtest.php';
	include 'constant.php';
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$idx = trim(htmlspecialchars($_POST["idx"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		$userid = $_SESSION['userId'];
		
		$res = mysql_query("select * from CreditTrade where IdxId='$idx'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查表交易失败，请稍后重试！'));	
			return;
		}	
		
		$row = mysql_fetch_assoc($res);
		$status = $row["Status"];

		if ($userid != $row["BuyerId"]) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'查询交易出错，请稍后重试！'));	
			return;
		}
		
		if ($status != $creditTradeReserved) {
			echo json_encode(array('error'=>'true','error_code'=>'33','error_msg'=>'交易状态改变，请稍后重试！'));	
			return;
		}

		$now = time();
	
		$res1 = mysql_query("update CreditTrade set Status='$creditTradeAbandoned' where IdxId='$idx'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'取消订单失败，请稍后重试!'));	
			return;			
		}
		
		// 退款	
		$quantity = $row["Quantity"];
		$handleRate = $row["HanderRate"];
		$handleFee = $quantity * $handleRate;
		$sellid = $row["SellerId"];
		
		refundSeller($sellid, $quantity, $handleFee);
	}
	
	echo json_encode(array('error'=>'false'));
}

function saveCredit()
{
	include 'regtest.php';
	include 'constant.php';
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$amount = trim(htmlspecialchars($_POST["amount"]));
	
	if (!isValidMoneyAmount($amount)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'输入的金额无效，请重新输入！'));
		return;		
	}	
	if ($amount % 100 != 0 || $amount < 100) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'交易金额必须是100的倍数，请重新输入！'));
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
		
		$res1 = mysql_query("select * from Credit where UserId='$userid'");
		if (!$res1 || mysql_num_rows($res1) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查表失败，请稍后重试！'));
			return;
		}
		$row1 = mysql_fetch_assoc($res1);
		$credit = $row1["Credits"];
		if ($amount > $credit) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'存储金额大于您持有的金额，请重新输入！'));
			return;	
		}
		
		$result = createCreditBankTable();
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'查表失败，请稍后重试！'));
			return;
		}
		
		$quantity = $amount * 3;
		$charity = floor($quantity * 0.05 * 100) / 100;
		$pnts = floor($quantity * 0.15 * 100) / 100;	
		$diviCnt = floor($quantity * 0.005 * 100) / 100;
		$quantity = $quantity - $charity - $pnts;
		$now = time();
		
		$res = mysql_query("insert into CreditBank (UserId, Quantity, Invest, Balance, DiviCnt, SaveTime)
								values('$userid', '$quantity', '$amount', '$quantity', '$diviCnt', '$now')");
		if (!$res) {
			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'存储线上资产失败，请稍后重试！'));
			return;	
		}
		
		$credit -= $amount;
		$vault = $row1["Vault"] + $quantity;
		$newPnts = $row1["Pnts"] + $pnts;
		$newCharity = $row1["Charity"] + $charity;
		
		$res2 = mysql_query("update Credit set Credits='$credit', Vault='$vault', Pnts='$newPnts', Charity='$newCharity' where UserId='$userid'");
		if (!$res2) {
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'更新数据失败，请稍后重试！'));
			return;	
		}

		$res3 = mysql_query("insert into CreditRecord (UserId, Amount, HandleFee, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
						VALUES($userid, $amount, 0, $credit,  $now, $now, 0, $codeSave)");
		if (!$res3) {
			// !!! log error
			return false;
		}
	}
	
	echo json_encode(array('error'=>'false'));
}
	
?>