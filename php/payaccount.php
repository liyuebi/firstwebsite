<?php

include 'database.php';

if (!isset($_POST['func'])) {
	exit('非法访问！');
}

if ("setwechat" == $_POST['func']) {
	setWechat();
}
else if ("setalipay" == $_POST['func']) {
	setAlipay();
}
else if ("setbank" == $_POST['func']) {
	setBank();
}

function setWechat()
{	
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
	
	$weAcc = trim(htmlspecialchars($_POST["acc"]));
	if (strlen($weAcc) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'无效的微信账号，请重新输入！'));
		return;
	}
	
	createWechatTable();
	
	$userid = $_SESSION["userId"];
	
	$result = mysql_query("select * from WechatAccount where UserId='$userid'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查询微信账户失败，请稍后重试'));
		return;
	}
	
	if (mysql_num_rows($result) > 0) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'您的微信账户已设置，请不要重复设置！'));
		return;
	}
	
	$res = mysql_query("insert into WechatAccount (UserId, WechatAcc) 
							values('$userid', '$weAcc')");
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'添加微信账户失败，请稍后重试！'));
		return;
	}
	
	echo json_encode(array('error'=>'false'));
}

function setAlipay()
{	
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
	
	$alipayAcc = trim(htmlspecialchars($_POST["acc"]));
	if (strlen($alipayAcc) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'无效的支付宝账号，请重新输入！'));
		return;
	}
	
	createAlipayTable();
	
	$userid = $_SESSION["userId"];
	
	$result = mysql_query("select * from AlipayAccount where UserId='$userid'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查询支付宝账户失败，请稍后重试'));
		return;
	}
	
	if (mysql_num_rows($result) > 0) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'您的支付宝账户已设置，请不要重复设置！'));
		return;
	}
	
	$res = mysql_query("insert into AlipayAccount (UserId, AlipayAcc) 
							values('$userid', '$alipayAcc')");
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'添加支付宝账户失败，请稍后重试！'));
		return;
	}
	
	echo json_encode(array('error'=>'false'));
}

function setBank()
{
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
	
	$name = trim(htmlspecialchars($_POST["user"]));
	$acc = trim(htmlspecialchars($_POST["acc"]));
	$bank = trim(htmlspecialchars($_POST["bank"]));
	$branch = trim(htmlspecialchars($_POST["bra"]));
	if (strlen($name) <= 0 || strlen($acc) <= 0 || strlen($bank) <= 0 || strlen($branch) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'您有未填写的项目，请重新输入！'));
		return;
	}

	$userid = $_SESSION["userId"];
	
	createBankAccountTable();
	
	$result = mysql_query("select * from BankAccount where UserId='$userid'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查询银行账户失败，请稍后重试'));
		return;
	}
	
	if (mysql_num_rows($result) > 0) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'您的银行账户已设置，请不要重复设置！'));
		return;
	}
	
	$res = mysql_query("insert into BankAccount (UserId, BankAcc, AccName, BankName, BankBranch)
							values('$userid', '$acc', '$name', '$bank', '$branch')");
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'添加银行账户失败，请稍后重试！'));
		return;
	}
	
	echo json_encode(array('error'=>'false'));
}

?>