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
	else if ('cOls' == $_POST['func']) {
		closeOfflineShop();
	}
	else if ('roOls' == $_POST['func']) {
		reopenOfflineShop();
	}
	else if ("cqrc" == $_POST['func']) {
		createQRCode();
	}
	else if ("ssInA" == $_POST['func']) {
		searchForShopInAdmin();
	}
	else if ("sOLSRecord" == $_POST['func']) {
		searchOfflineShopRecord();
	}
	else if ("cwrInA" == $_POST['func']) {
		changeWithdrawRate();		
	}
}

function openOfflineShop($con, $userid, $refererId, &$error_msg)
{
	include 'constant.php';
	
	$result = createOfflineShopTable($con);
	if (!$result) {
		$error_msg = '查表失败，请稍后重试！';
		return false;
	}
	
	$result = mysqli_query($con, "select * from OfflineShop where UserId='$userid'");
	if (!$result) {
		$error_msg = '查询线下商店，请稍后重试！';
		return false;
	}
	else if (mysqli_num_rows($result) > 0) {
		$error_msg = '一个用户只能申请一个线下商家账户！';
		return false;	
	}
	
	$now = time();
	$res1 = mysqli_query($con, "insert into OfflineShop (UserId, RefererId, RegisterTime, Status)
							values($userid, $refererId, $now, $olshopRegistered)");
	if (!$res1) {
		$error_msg = '申请线下商店失败，请稍后重试！';
		return false;
	}
	
	// statistics
	include_once 'func.php';
	insertOfflineShopOpenStatistics($con, $offlineShopRegisterFee);
	
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
		
		$res = mysqli_query($con, "select * from Credit where UserId='$userid'");
		if (!$res || mysqli_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查表失败，请稍后重试！'));	
			return;
		}
		$row = mysqli_fetch_assoc($res);
		$credit = $row["Credits"];
		
		if ($credit < $offlineShopRegisterFee) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'线上云量不足，请前往交易所交易！'));	
			return;
		}

		$error_msg = '';
		if (!openOfflineShop($con, $userid, 0, $error_msg))
		{
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>$error_msg));	
			return;
		}
				
		$credit = $credit - $offlineShopRegisterFee;
		$res2 = mysqli_query($con, "update Credit set Credits='$credit' where UserId='$userid'");
		if (!$res2) {
			// !!! log error
		}
		
		$res3 = mysqli_query($con, "insert into CreditRecord (UserId, Amount, HandleFee, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
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
		
		include_once "func.php";

		$new_path = dirname(__FILE__) . '/../olLicensePic';
		if (createFolderIfNotExist($new_path)) {
			$new_name = $new_path . '/' . $imgFileName;
			move_uploaded_file($imgfile['tmp_name'], $new_name);
		}
		else {
			$imgFileName = '';
		}
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
		
		$res = mysqli_query($con, "select * from OfflineShop where ShopId='$shopId'");
		if (!$res || mysqli_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到商家记录，请稍后重试！'));
			return;
		}	
		$row = mysqli_fetch_assoc($res);
		
		if ($row["UserId"] != $userid) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'越权操作！'));
			return;
		}
		
		$res1 = mysqli_query($con, "update OfflineShop set ShopName='$name', Contacter='$man', PhoneNum='$phone', Address='$address', LicencePic='$imgFileName', ModifiedTime='$now' where ShopId='$shopId'");
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

		$res = mysqli_query($con, "select * from OfflineShop where ShopId='$shopId'");
		if (!$res || mysqli_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到商家记录，请稍后重试！'));
			return;
		}	
		$row = mysqli_fetch_assoc($res);	
		
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
		$res1 = mysqli_query($con, "update OfflineShop set ReadyForCheckTime='$now', Status='$olshopApplied' where ShopId='$shopId'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'申请失败，请稍后重试！','sql_error'=>mysqli_error($con)));
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
		$res = mysqli_query($con, "select * from OfflineShop where ShopId='$shopId'");
		if (!$res || mysqli_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到商家记录，请稍后重试！'));
			return;
		}	
		$row = mysqli_fetch_assoc($res);	

		if ($row["Status"] == $olshopAccepted) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'已经上线，请误重复操作！'));
			return;	
		}		
		
		if ($row["Status"] != $olshopApplied) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'状态出错，请稍后重试！'));
			return;	
		}
				
		$now = time();
		$res1 = mysqli_query($con, "update OfflineShop set OnlineTime='$now', Status='$olshopAccepted' where ShopId='$shopId'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'操作出错，请稍后重试！','sql_error'=>mysqli_error($con)));
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
		$res = mysqli_query($con, "select * from OfflineShop where ShopId='$shopId'");
		if (!$res || mysqli_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到商家记录，请稍后重试！'));
			return;
		}	
		$row = mysqli_fetch_assoc($res);	

		if ($row["Status"] == $olshopDeclined) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'已经拒绝请求，请误重复操作！'));
			return;	
		}		
		
		if ($row["Status"] != $olshopApplied) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'状态出错，请稍后重试！'));
			return;	
		}
				
		$now = time();
		$res1 = mysqli_query($con, "update OfflineShop set Status='$olshopDeclined' where ShopId='$shopId'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'操作出错，请稍后重试！','sql_error'=>mysqli_error()));
			return;
		}
	}
	
	echo json_encode(array('error'=>'false'));
}

function closeOfflineShop()
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
		$res = mysqli_query($con, "select * from OfflineShop where ShopId='$shopId'");
		if (!$res || mysqli_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到商家记录，请稍后重试！'));
			return;
		}	
		$row = mysqli_fetch_assoc($res);	
		
		if ($row["Status"] != $olshopAccepted) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'不是正在运营的商铺！'));
			return;	
		}
				
		$now = time();
		$res1 = mysqli_query($con, "update OfflineShop set Status='$olshopClosed' where ShopId='$shopId'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'操作出错，请稍后重试！','sql_error'=>mysqli_error()));
			return;
		}
	}
	
	echo json_encode(array('error'=>'false'));
}

function reopenOfflineShop()
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
		$res = mysqli_query($con, "select * from OfflineShop where ShopId='$shopId'");
		if (!$res || mysqli_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到商家记录，请稍后重试！'));
			return;
		}	
		$row = mysqli_fetch_assoc($res);	
		
		if ($row["Status"] != $olshopClosed && $row["Status"] != $olshopSuspended) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'不是被下线的商铺！'));
			return;	
		}
				
		$now = time();
		$res1 = mysqli_query($con, "update OfflineShop set Status='$olshopAccepted' where ShopId='$shopId'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'操作出错，请稍后重试！','sql_error'=>mysqli_error()));
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
		$res = mysqli_query($con, "select * from OfflineShop where ShopId='$cond' and Status='$olshopAccepted'");
		if (!$res || mysqli_num_rows($res) <= 0) {
			$res = mysqli_query($con, "select * from OfflineShop where Status='$olshopAccepted' and ShopName like '%$cond%'");
			if (!$res || mysqli_num_rows($res) <= 0) {
				echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'未查询到商家！'));	
				return;
			}
		}	
		
		$arr = array();
		while ($row = mysqli_fetch_assoc($res)) {
			
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

function searchForShopInAdmin()
{
	include 'constant.php';	
	include_once "admin_func.php";
	
	session_start();
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$shopId = trim(htmlspecialchars($_POST["sid"]));
	$shopName = trim(htmlspecialchars($_POST["sname"]));
	$ownerId = trim(htmlspecialchars($_POST["oid"]));

	if ("" == $shopId && "" == $shopName && "" == $ownerId) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'请填写搜索条件！'));
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
		$sql = "select * from OfflineShop where ";
		$condNum = 0;
		if ("" != $shopId) {
			$sql = $sql . "ShopId=" . $shopId;
			$condNum += 1;
		}
		if ("" != $shopName) {
			if ($condNum > 0) {
				$sql = $sql . " and ";
			}
			$sql = $sql . "ShopName like '%" . $shopName . "%'";
			$condNum += 1;
		}
		if ("" != $ownerId) {
			if ($condNum > 0) {
				$sql = $sql . " and ";
			}
			$sql = $sql . "UserId=" . $ownerId;
			$condNum += 1;
		}

		$res = mysqli_query($con, $sql);
		if (!$res) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'搜索失败，请稍后重试！',"sql"=>$sql));
			return;
		}

		$arr = array();
		while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
			
			$arr1 = array();
			
			foreach($row as $key=>$value) {

				$arr1[$key] = $value;
			}
			$arr[$row["ShopId"]] = $arr1;
		}

		echo json_encode(array('error'=>'false','num'=>mysqli_num_rows($res),'list'=>$arr));
	}
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
		
		$res = mysqli_query($con, "select * from OfflineShop where ShopId='$shopId' and Status='$olshopAccepted'");
		if (!$res || mysqli_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找商家出错，请稍后重试！'));	
			return;
		}	
		
		$row = mysqli_fetch_assoc($res);
		$sellid = $row["UserId"];

		if ($userid == $sellid) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'不能向自己的商店交易，请稍后重试！'));	
			return;
		}
		
		$res3 = mysqli_query($con, "select * from Credit where UserId='$sellid'");
		if (!$res3 || mysqli_num_rows($res3) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'查找商家个人账户出错，请稍后重试！'));	
			return;	
		} 
		$row3 = mysqli_fetch_assoc($res3);
		$sellerPnts = $row3["Pnts"];

		$now = time();
	
		$res1 = mysqli_query($con, "select * from Credit where UserId='$userid'");
		if (!$res1 || mysqli_num_rows($res1) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'账户操作出错，请稍后重试!'));	
			return;			
		}
		$row1 = mysqli_fetch_assoc($res1);
		$pnts = $row1["Pnts"];
		
		if ($pnts < $cnt) {
			$msg = '您的线下云量余额不足,仅剩' . $pnts . "！";
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>$msg));	
			return;	
		}
		
		$pnts -= $cnt;
		$res2 = mysqli_query($con, "update Credit set Pnts='$pnts' where UserId='$userid'");
		if (!$res2) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'转账操作失败，请稍后重试!'));	
			return;			
		}
		else {
			// insert pay pnts record
			$res2 = mysqli_query($con, "insert into PntsRecord (UserId, Amount, CurrAmount, ApplyTime, WithStoreId, WithUserId, Type)
								values('$userid', '$cnt', '$pnts', '$now', '$shopId', '$sellid', '$code2OlShopPay')");
			if (!$res2) {
				// !!! log error
			}
		}
		
		$fee = floor($cnt * $offlineTradeRate * 100) / 100;
		$bonusUpstream = 0;

		// // 分红给线下商家账号的推荐人
		// $refererId = $row["RefererId"];
		// if (0 != $refererId) {
						
		// 	$res5 = mysqli_query($con, "select * from Credit where UserId='$refererId'");
		// 	if ($res5 && mysqli_num_rows($res5) > 0) {
				
		// 		$row5 = mysqli_fetch_assoc($res5);
		// 		$refererPnts = $row5["Pnts"];
				
		// 		$bonusUpstream = floor($cnt * $offlineTradeUpDiviRate * 100) / 100;
		// 		$refererPnts += $bonusUpstream;
				
		// 		$res6 = mysqli_query($con, "update Credit set Pnts='$refererPnts' where UserId='$refererId'");
		// 		if (!$res6) {
		// 			// !!! log error
		// 		}
		// 		else {
		// 			// insert pnts bonus record
		// 			$res6 = mysqli_query($con, "insert into PntsRecord (UserId, Amount, CurrAmount, ApplyTime, WithStoreId, WithUserId, Type)
		// 								values('$refererId', '$bonusUpstream', '$refererPnts', '$now', '$shopId', '$sellid', '$code2OlShopBonus')");
		// 			if (!$res6) {
		// 				// !!! log error
		// 			}
		// 		}
		// 	}
		// }

		// 修改为分红给线下商家的用户账号推荐人，即不管是该商家账号被推荐时即开启还是自己开启商家账号，都需分提成上游推荐人
		$res4 = mysqli_query($con, "select * from ClientTable where UserId='$sellid'");
		if ($res4 && mysqli_num_rows($res4) > 0) {

			$row4 = mysqli_fetch_assoc($res4);
			$refererId = $row4["ReferreeId"];
			if (0 != $refererId) {
							
				$res5 = mysqli_query($con, "select * from Credit where UserId='$refererId'");
				if ($res5 && mysqli_num_rows($res5) > 0) {
					
					$row5 = mysqli_fetch_assoc($res5);
					$refererPnts = $row5["Pnts"];
					
					$bonusUpstream = floor($cnt * $offlineTradeUpDiviRate * 100) / 100;
					$refererPnts += $bonusUpstream;
					
					$res6 = mysqli_query($con, "update Credit set Pnts='$refererPnts' where UserId='$refererId'");
					if (!$res6) {
						// !!! log error
					}
					else {
						// insert pnts bonus record
						$res6 = mysqli_query($con, "insert into PntsRecord (UserId, Amount, CurrAmount, ApplyTime, WithStoreId, WithUserId, Type)
											values('$refererId', '$bonusUpstream', '$refererPnts', '$now', '$shopId', '$sellid', '$code2OlShopBonus')");
						if (!$res6) {
							// !!! log error
						}
					}
				}
			}
		}

		
		$receiveCnt = $cnt - $fee - $bonusUpstream;
		
		$sellerPnts += $receiveCnt;
		$res2 = mysqli_query($con, "update Credit set Pnts='$sellerPnts' where UserId='$sellid'");
		if (!$res2) {
			// !!! log error
		}
		else {
			// insert receive pnts record
			$res2 = mysqli_query($con, "insert into PntsRecord (UserId, Amount, CurrAmount, RelatedAmount, HandleFee, ApplyTime, WithStoreId, WithUserId, Type)
								values('$sellid', '$receiveCnt', '$sellerPnts', '$cnt', '$fee', '$now', '$shopId', '$userid', '$code2OlShopReceive')");
			if (!$res2) {
				// !!! log error
			}
		}
		
		// 更新线下商家个人统计数据
		$tradeCnt = $row["TradeTimes"] + 1;
		$tradeAmt = $row["TradeAmount"] + $cnt;
		$tradeIncome = $row["TradeIncome"] + $receiveCnt;
		$tradeFee = $row["TradeFee"] + $fee;
		$res2 = mysqli_query($con, "update OfflineShop set TradeTimes='$tradeCnt', TradeAmount='$tradeAmt', TradeIncome='$tradeIncome', TradeFee='$tradeFee' where ShopId='$shopId'");
		if (!$res2) {
			// !!! log error
		}
		
		// 添加线下商家交易数据统计
		include_once "func.php";
		insertOfflineShopTradeStatistics($con, $fee);
	}
	
	echo json_encode(array('error'=>'false'));
}

function createQRCode()
{
	include 'constant.php';
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$shopId = trim(htmlspecialchars($_POST["idx"]));
	$retUrl = '';

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		$userid = $_SESSION['userId'];

		$res = mysqli_query($con, "select * from OfflineShop where ShopId='$shopId'");
		if (!$res || mysqli_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到商家记录，请稍后重试！'));
			return;
		}	
		$row = mysqli_fetch_assoc($res);	
		
		if ($row["UserId"] != $userid) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'越权操作！'));
			return;
		}

		if ($row["Status"] != $olshopAccepted) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'请等候审核通过，再申请二维码！'));
			return;	
		}

		include_once "func.php";

		$tmpDir = dirname(__FILE__) . '/../tmp';
		$finalDir = dirname(__FILE__) . '/../olqrc';

		if (!createFolderIfNotExist($tmpDir) || !createFolderIfNotExist($finalDir)) {
			echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'没有文件访问权限，请稍后重试！'));
			return;	
		}

		$url = "http://www.lww1555.com/html/olshop.php?s=" . $shopId;
		$now = time();
		$imgFileName = $shopId . '_' . $now . '.png';
		$tmpFilePath = $tmpDir . '/' . $imgFileName;	
		$finalPath = $finalDir . '/' . $imgFileName;	
		$logo = dirname(__FILE__) . '/../img/lian-logo.jpg';
		
		include "phpqrcode/qrlib.php";
		QRcode::png($url, $tmpFilePath, QR_ECLEVEL_M, 4, 4);

		if (!file_exists($tmpFilePath))	{
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'二维码生成失败！'));
			return;		
		}

		if (file_exists($logo)) {

		    $QR = imagecreatefromstring(file_get_contents($tmpFilePath));   
		    $logo = imagecreatefromstring(file_get_contents($logo));   
		    $QR_width = imagesx($QR);//二维码图片宽度   
		    $QR_height = imagesy($QR);//二维码图片高度   
		    $logo_width = imagesx($logo);//logo图片宽度   
		    $logo_height = imagesy($logo);//logo图片高度   
		    $logo_qr_width = $QR_width / 5;   
		    $scale = $logo_width/$logo_qr_width;   
		    $logo_qr_height = $logo_height/$scale;   
		    $from_width = ($QR_width - $logo_qr_width) / 2;   
		    //重新组合图片并调整大小   
		    imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,    
			    $logo_qr_height, $logo_width, $logo_height);   

			imagepng($QR, $finalPath);

			if (!file_exists($finalPath)) {
				echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'二维码合成失败！'));
				return;		
			}

			unlink($tmpFilePath);
		}
		else {
			if (!rename($tmpFilePath, $finalPath)) {
				echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'二维码放置失败！'));
				return;		
			}
		}

		$res1 = mysqli_query($con, "update OfflineShop set QRCode='$imgFileName' where ShopId='$shopId'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'保存二维码失败，请稍后重试！'));
			return;
		}	

		$retUrl = '../olqrc/' . $imgFileName;
	}

	echo json_encode(array('error'=>'false', 'url'=>$retUrl));
}

function searchOfflineShopRecord()
{
	include 'constant.php';	
	include_once "admin_func.php";
	
	session_start();
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$shopId = trim(htmlspecialchars($_POST["sid"]));
	$recordType = trim(htmlspecialchars($_POST["type"]));	// 记录类型：1. 收款记录 2. 取现记录

	if ("" == $shopId) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'请输入要搜索的商家id！'));
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
		$res = mysqli_query($con, "select * from OfflineShop where ShopId='$shopId'");
		if (!$res || mysqli_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到商家记录，请稍后重试！'));
			return;
		}	
		$row = mysqli_fetch_assoc($res);
		$sellid = $row["UserId"];

		if (1 == $recordType) {

			$res1 = mysqli_query($con, "select * from PntsRecord where Type='$code2OlShopReceive' and UserId='$sellid' order by ApplyTime desc");
			if (!$res1) {
				echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查询交易记录出错，请稍后重试！'));
				return;
			} 

			$arr = array();
			while ($row1 = mysqli_fetch_assoc($res1)) {
				
				$arr1 = array();
				foreach($row1 as $key=>$value) {

					$arr1[$key] = $value;
				}
				array_push($arr, $arr1);
			}
			echo json_encode(array('error'=>'false','num'=>mysqli_num_rows($res1),'list'=>$arr,'amt'=>$row["TradeAmount"],'a_amt'=>$row["TradeIncome"],'w_amt'=>$row["WithdrawAmount"]));
		}
		else if (2 == $recordType) {

			if (!createPntsWithdrawTable($con)) {
				echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'数据操作失败，请稍后重试！'));
				return;				
			}

			$res2 = mysqli_query($con, "select * from PntsWdApplication where UserId='$sellid' order by ApplyTime desc");
			if (!$res2) {
				echo json_encode(array('error'=>'true','error_code'=>'33','error_msg'=>'查询提现记录出错，请稍后重试！'));
				return;
			}

			$arr = array();
			while ($row2 = mysqli_fetch_assoc($res2)) {
				
				$arr1 = array();
				foreach($row2 as $key=>$value) {

					$arr1[$key] = $value;
				}
				array_push($arr, $arr1);
			}

			echo json_encode(array('error'=>'false','num'=>mysqli_num_rows($res2),'list'=>$arr,'amt'=>$row["TradeAmount"],'a_amt'=>$row["TradeIncome"],'w_amt'=>$row["WithdrawAmount"]));
		}
		else {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'参数出错！'));
			return;
		}
	}
}

function changeWithdrawRate()
{
	include 'constant.php';	
	include_once "admin_func.php";
	
	session_start();
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$shopId = trim(htmlspecialchars($_POST["sid"]));
	$rate = trim(htmlspecialchars($_POST["r"]));

	$rate = floatval($rate);

	if ($rate < 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'提现手续费率不能小于0'));
		return;
	}	
	else if ($rate >= 1) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'提现手续费率不能大于100%'));
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
		$res = mysqli_query($con, "select * from OfflineShop where ShopId='$shopId'");
		if (!$res || mysqli_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到商家记录，请稍后重试！'));
			return;
		}	
		$row = mysqli_fetch_assoc($res);

		$res1 = mysqli_query($con, "update OfflineShop set WdFeeRate='$rate' where ShopId='$shopId'");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'修改提现手续费率失败，请稍后重试！'));
			return;
		}
	}

	echo json_encode(array('error'=>'false'));
}

?>