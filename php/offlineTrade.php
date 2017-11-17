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
	else if ("searchShop" == $_POST['func']) {
		searchForShop();
	}
	else if ("pOLS" == $_POST['func']) {
		payOLShop();
	}
	else if ("afo" == $_POST['func']) {
		approveForOnline();
	}
	else if ("dfo" == $_POST['func']) {
		declineForOnline();
	}
}

function openOfflineShop($userid, &$error_msg)
{
	include 'constant.php';
	
	$result = createOfflineShopTable();
	if (!$result) {
		$error_msg = '查表失败，请稍后重试！';
		return false;
	}
	
	$result = mysql_query("select * from OfflineShop where UserId='$userid'");
	if (!$result) {
		$error_msg = '查询线下商店，请稍后重试！';
		return false;
	}
	else if (mysql_num_rows($result) > 0) {
		$error_msg = '一个用户只能申请一个线下商家账户！';
		return false;	
	}
	
	$now = time();
	$res1 = mysql_query("insert into OfflineShop (UserId, RegisterTime, Status)
							values($userid, $now, $olshopRegistered)");
	if (!$res1) {
		$error_msg = '申请线下商店失败，请稍后重试！';
		return false;
	}
	
	// statistics
	include_once 'func.php';
	insertOfflineShopOpenStatistics($offlineShopRegisterFee);
	
	return true;
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

		$error_msg = '';
		if (!openOfflineShop($userid, $error_msg))
		{
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>$error_msg));	
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
	}
	echo json_encode(array('error'=>'false'));	
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

function approveForOnline()
{
	include 'constant.php';	
	include_once "admin_func.php";
	
	session_start();
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$shopId = trim(htmlspecialchars($_POST["index"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		$res = mysql_query("select * from OfflineShop where ShopId='$shopId'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到商家记录，请稍后重试！'));
			return;
		}	
		$row = mysql_fetch_assoc($res);	

		if ($row["Status"] == $olshopAccepted) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'已经上线，请误重复操作！'));
			return;	
		}		
		
		if ($row["Status"] != $olshopApplied) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'状态出错，请稍后重试！'));
			return;	
		}
				
		$now = time();
		$res1 = mysql_query("update OfflineShop set OnlineTime='$now', Status='$olshopAccepted' where ShopId='$shopId'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'操作出错，请稍后重试！','sql_error'=>mysql_error()));
			return;
		}
	}
	
	echo json_encode(array('error'=>'false'));
}

function declineForOnline()
{
	include 'constant.php';	
	include_once "admin_func.php";
	
	session_start();
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$shopId = trim(htmlspecialchars($_POST["index"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		$res = mysql_query("select * from OfflineShop where ShopId='$shopId'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到商家记录，请稍后重试！'));
			return;
		}	
		$row = mysql_fetch_assoc($res);	

		if ($row["Status"] == $olshopDeclined) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'已经拒绝请求，请误重复操作！'));
			return;	
		}		
		
		if ($row["Status"] != $olshopApplied) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'状态出错，请稍后重试！'));
			return;	
		}
				
		$now = time();
		$res1 = mysql_query("update OfflineShop set Status='$olshopDeclined' where ShopId='$shopId'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'操作出错，请稍后重试！','sql_error'=>mysql_error()));
			return;
		}
	}
	
	echo json_encode(array('error'=>'false'));
}

function searchForShop()
{
	include 'constant.php';
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$cond = trim(htmlspecialchars($_POST["cond"]));

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{	
		$res = mysql_query("select * from OfflineShop where ShopId='$cond' and Status='$olshopAccepted'");
		if (!$res || mysql_num_rows($res) <= 0) {
			$res = mysql_query("select * from OfflineShop where Status='$olshopAccepted' and ShopName like '%$cond%'");
			if (!$res || mysql_num_rows($res) <= 0) {
				echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'未查询到商家！'));	
				return;
			}
		}	
		
		$arr = array();
		while ($row = mysql_fetch_array($res)) {
			
			$arr1 = array();
			
			$arr1["name"] = $row["ShopName"];
			$arr1["man"] = $row["Contacter"];
			$arr1["phone"] = $row["PhoneNum"];
			$arr1["add"] = $row["Address"];
			$arr1["pic"] = $row["LicencePic"];
			
			$arr[$row["ShopId"]] = $arr1;
		}
	}
	
	echo json_encode(array('error'=>'false','list'=>$arr));
}

function payOLShop()
{
	include 'regtest.php';
	include 'constant.php';
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$cnt = trim(htmlspecialchars($_POST["cnt"]));
	$pwd = trim(htmlspecialchars($_POST["paypwd"]));
	$shopId = trim(htmlspecialchars($_POST["sId"]));
	
	if (!isValidMoneyAmount($cnt)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'无效的金额输入，请重新输入！'));
		return;
	}
	
	if (!password_verify($pwd, $_SESSION["buypwd"])) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'支付密码出错，请重试！'));
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
		
		$res = mysql_query("select * from OfflineShop where ShopId='$shopId' and Status='$olshopAccepted'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找商家出错，请稍后重试！'));	
			return;
		}	
		
		$row = mysql_fetch_assoc($res);
		$sellid = $row["UserId"];

		if ($userid == $sellid) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'不能向自己的商店交易，请稍后重试！'));	
			return;
		}
		
		$res3 = mysql_query("select * from Credit where UserId='$sellid'");
		if (!$res3 || mysql_num_rows($res3) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'查找商家个人账户出错，请稍后重试！'));	
			return;	
		} 
		$row3 = mysql_fetch_assoc($res3);
		$sellerPnts = $row3["Pnts"];

		$now = time();
	
		$res1 = mysql_query("select * from Credit where UserId='$userid'");
		if (!$res1 || mysql_num_rows($res1) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'账户操作出错，请稍后重试!'));	
			return;			
		}
		$row1 = mysql_fetch_assoc($res1);
		$pnts = $row1["Pnts"];
		
		if ($pnts < $cnt) {
			$msg = '您的线下云量余额不足,仅剩' . $pnts . "！";
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>$msg));	
			return;	
		}
		
		$pnts -= $cnt;
		$res2 = mysql_query("update Credit set Pnts='$pnts' where UserId='$userid'");
		if (!$res2) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'转账操作失败，请稍后重试!'));	
			return;			
		}
		
		$fee = floor($cnt * $offlineTradeRate * 100) / 100;
		$bonusUpstream = 0;

		// 分红给线下商家账号的推荐人
		$res4 = mysql_query("select * from ClientTable where UserId='$sellid'");
		if ($res4 && mysql_num_rows($res4) > 0) {
			
			$row4 = mysql_fetch_assoc($res4);
			$refererId = $row4["ReferreeId"];
			
			$res5 = mysql_query("select * from Credit where UserId='$refererId'");
			if ($res5 && mysql_num_rows($res5) > 0) {
				
				$row5 = mysql_fetch_assoc($res5);
				$refererPnts = $row5["Pnts"];
				
				$bonusUpstream = floor($cnt * $offlineTradeUpDiviRate * 100) / 100;
				$refererPnts += $bonusUpstream;
				
				$res6 = mysql_query("update Credit set Pnts='$refererPnts' where UserId='$refererId'");
				if (!$res6) {
					// !!! log error
				}
			}
		}
		
		$receiveCnt = $cnt - $fee - $bonusUpstream;
		
		$sellerPnts += $receiveCnt;
		$res2 = mysql_query("update Credit set Pnts='$sellerPnts' where UserId='$sellid'");
		if (!$res2) {
			// !!! log error
		}
		
		// 更新线下商家个人统计数据
		$tradeCnt = $row["TradeTimes"] + 1;
		$tradeAmt = $row["TradeAmount"] + $cnt;
		$tradeIncome = $row["TradeIncome"] + $receiveCnt;
		$tradeFee = $row["TradeFee"] + $fee;
		$res2 = mysql_query("update OfflineShop set TradeTimes='$tradeCnt', TradeAmount='$tradeAmt', TradeIncome='$tradeIncome', TradeFee='$tradeFee' where ShopId='$shopId'");
		if (!$res2) {
			// !!! log error
		}
		
		// 添加线下商家交易数据统计
		include_once "func.php";
		insertOfflineShopTradeStatistics($fee);
	}
	
	echo json_encode(array('error'=>'false'));
}

?>