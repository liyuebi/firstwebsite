<?php

include_once 'database.php';

if (isset($_POST['func'])) {
	
	if ("createOLSAcc" == $_POST['func']) {
		createOfflineShopAccount();
	}
	else if ("editInfo" == $_POST['func']) {
		editOLSAcc();
	}
	else if ("afr" == $_POST['func']) {
		applyForReview();
	}
}

function createOfflineShopAccount()
{	
	include 'constant.php';
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
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
		$now = time();
						
		$result = createOfflineShopTable();
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'查表失败，请稍后重试！'));	
			return;
		}	
		
		$result = mysql_query("select * from OfflineShop where UserId='$userid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'查表失败，请稍后重试！'));	
			return;
		}
		else if (mysql_num_rows($result) > 0) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'一个用户只能申请一个线下商家账户！'));	
			return;	
		}
		
		$res = mysql_query("select * from Credit where UserId='$userid'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查表失败，请稍后重试！'));	
			return;
		}
		$row = mysql_fetch_assoc($res);
		$credit = $row["Credits"];
		
		if ($credit < $offlineShopRegisterFee) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'线上云量不足，请前往交易所交易！'));	
			return;
		}

		$res1 = mysql_query("insert into OfflineShop (UserId, RegisterTime, Status)
								values($userid, $now, $olshopRegistered)");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'申请线下商店失败，请稍后重试！'));	
			return;
		}
		
		$credit = $credit - $offlineShopRegisterFee;
		$res2 = mysql_query("update Credit set Credits='$credit' where UserId='$userid'");
		if (!$res2) {
			// !!! log error
		}
		
		$res3 = mysql_query("insert into CreditRecord (UserId, Amount, HandleFee, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
											VALUES($userid, $offlineShopRegisterFee, 0, $credit, $now, $now, 0, $codeRegiOlShop)");
		if (!$res3) {
			// !!! log error
		}
		
		// statistics
		// statics for shop info
		include_once 'func.php';
		updateCreditPoolStatistics($offlineShopRegisterFee);
	}
	return;
}

function editOLSAcc()
{
	include 'regtest.php';
	include 'constant.php';
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$shopId = trim(htmlspecialchars($_POST["idx"]));
	$name = trim(htmlspecialchars($_POST["name"]));
	$man = trim(htmlspecialchars($_POST["man"]));
	$phone = trim(htmlspecialchars($_POST["phone"]));
	$address = trim(htmlspecialchars($_POST["add"]));
	$imgfile = $_FILES['file'];

	if (strlen($phone) != 0 && !isValidCellPhoneNum($phone)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'无效的手机号！'));
		return;
	}
	
	$now = time();
		
	$imgFileName = '';
	if ($imgfile != '') {
		
		$imgType = $imgfile['type'];
		$imgSize = $imgfile['size'];
		$error = $imgfile['error'];
		
		if ($error != 0) {
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'上传图片出错，出错码是 ' . $error));
			return;
		}
		
		if ($imgSize > 3 * 1024 * 1024) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'图片大小不能超过3MB！'));
			return;
		}
		
		if ($imgType != 'image/jpeg' && $imgType != 'image/png') {
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'图片格式不对，请使用jpg或png格式的图片，使用图片的格式是' . $imgType));
			return;
		}
		
		$imgname = $imgfile['name'];
		$pos = strpos($imgname, '.');
		$postname = substr($imgname, $pos);
		
		$imgFileName = $shopId . '_' . $now . $postname;
		
		$new_name = dirname(__FILE__) . '/../olLicensePic/' . $imgFileName;
		move_uploaded_file($imgfile['tmp_name'], $new_name);
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
		
		$res = mysql_query("select * from OfflineShop where ShopId='$shopId'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到商家记录，请稍后重试！'));
			return;
		}	
		$row = mysql_fetch_assoc($res);
		
		if ($row["UserId"] != $userid) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'越权操作！'));
			return;
		}
		
		$res1 = mysql_query("update OfflineShop set ShopName='$name', Contacter='$man', PhoneNum='$phone', Address='$address', LicencePic='$imgFileName', ModifiedTime='$now' where ShopId='$shopId'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'编辑失败，请稍后重试！'));
			return;
		}
 	}
	
	echo json_encode(array('error'=>'false'));	
}

function applyForReview()
{
	include 'constant.php';
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$shopId = trim(htmlspecialchars($_POST["idx"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		$userid = $_SESSION['userId'];

		$res = mysql_query("select * from OfflineShop where ShopId='$shopId'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到商家记录，请稍后重试！'));
			return;
		}	
		$row = mysql_fetch_assoc($res);	
		
		if ($row["UserId"] != $userid) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'越权操作！'));
			return;
		}
		
		if ($row["Status"] == $olshopApplied) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'您已经提交审核，请耐心等待！'));
			return;	
		}
		
		if (strlen($row["ShopName"]) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'请先填写店名！'));
			return;		
		}
		
		if (strlen($row["Contacter"]) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'请先填写联系人！'));
			return;		
		}
		
		if (strlen($row["PhoneNum"]) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'请先填写联系电话！'));
			return;		
		}
		
		if (strlen($row["Address"]) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'请先填写商家地址！'));
			return;		
		}
		
		if (strlen($row['LicencePic']) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'请先上传营业执照照片！'));
			return;		
		}
		
		$now = time();
		$res1 = mysql_query("update OfflineShop set ReadyForCheckTime='$now', Status='$olshopApplied' where ShopId='$shopId'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'申请失败，请稍后重试！','sql_error'=>mysql_error()));
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

function buyerRecieveMoney($idx, $status, $buyerId, $sellId, $quantity, $handleRate, $buyCnt, &$error_msg)
{
	include 'constant.php';
	$now = time();
	
	$res1 = mysql_query("update CreditTrade set ConfirmTime='$now', Status='$status' where IdxId='$idx'");
	if (!$res1) {
		$error_msg = '确定收货失败，请稍后重试!';	
		return false;			
	}
	
	$res3 = mysql_query("select * from Credit where UserId='$buyerId'");
	if (!$res3 || mysql_num_rows($res3) <= 0) {
		// !!! log error
	}
	else {
		
		$row3 = mysql_fetch_assoc($res3);
		$credit = $row3["Credits"];
		$credit += $buyCnt;
		
		$res4 = mysql_query("update Credit set Credits='$credit' where UserId='$buyerId'");
		if (!$res4) {
			// !!! log error
		}
		else {
			$res5 = mysql_query("insert into CreditRecord (UserId, Amount, HandleFee, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
				VALUES($buyerId, $buyCnt, 0, $credit, $now, $now, 0, $codeCreTradeRec)");		
			if (!$res5) {
				// !!! log error
			}
		}
	}
	
	$handleFee = $quantity * $handleRate;
	$actualHandleFee = $handleFee;
	if ($buyCnt != $quantity) {
		$res3 = mysql_query("select * from Credit where UserId='$sellId'");
		if (!$res3 || mysql_num_rows($res3) <= 0) {
			// !!! log error
		}
		else {
			$actualHandleFee = $buyCnt * $handleRate;
			$refund = $quantity - $buyCnt + $handleFee - $actualHandleFee;
			
			$row3 = mysql_fetch_assoc($res3);
			$credit = $row3["Credits"];
			$credit += $refund;
			
			$res4 = mysql_query("update Credit set Credits='$credit' where UserId='$sellId'");
			if (!$res4) {
				// !!! log error
			}
			else {
				$res5 = mysql_query("insert into CreditRecord (UserId, Amount, HandleFee, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
					VALUES($sellId, $refund, 0, $credit, $now, $now, 0, $codeCreTradeSucc)");		
				if (!$res5) {
					// !!! log error
				}
			}
		}
	}
	
	include_once "func.php";
	insertExchangeSuccessStatistics($buyCnt, $actualHandleFee);
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
		if ($now - $reserveTime >= 60 * 60 * $exchangePayHours) {
			
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
	
		$res1 = mysql_query("update CreditTrade set PayTime='$now', Status='$creditTradePayed' where IdxId='$idx'");
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

function confirmReceiveMoney()
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

		if ($status != $creditTradePayed) {
			echo json_encode(array('error'=>'true','error_code'=>'33','error_msg'=>'交易状态改变，请稍后重试！'));	
			return;
		}
		
		$buyerId = $row["BuyerId"];
		$buyCnt = $row["BuyCnt"];
		$quantity = $row["Quantity"];
		$handleRate = $row["HanderRate"];
		$error_msg = '';

		if (!buyerRecieveMoney($idx, $creditTradeConfirmed, $buyerId, $userid, $quantity, $handleRate, $buyCnt, $error_msg)) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>$error_msg));	
			return;			
		}
	}	
	
	echo json_encode(array('error'=>'false'));
}

?>