<?php

session_start();
// check if logined. check cookie to limit login time
// check session first to avoid if user close browser and reopen, cookie is still valid but can't find session
if ((isset($_SESSION['isLogin']) && $_SESSION['isLogin'])
	&& (isset($_COOKIE['isLogin']) && $_COOKIE['isLogin'])) {
	// no code here, just continue;		
} 
else {
	$home_url = '../index.php';
	header('Location: ' . $home_url);
	exit();
}

$weAcc = '';
$isWechatSet = false;
$aliAcc = '';
$isAlipaySet = false;
$bankAcc = '';
$isBankSet = false;

include "../php/database.php";
include "../php/constant.php";
$con = connectToDB();
if ($con) {
	
	$userid = $_SESSION["userId"];	
	$res1 = mysql_query("select * from WechatAccount where UserId='$userid'");
	if ($res1) {
		if (mysql_num_rows($res1) > 0) {
			$row1 = mysql_fetch_assoc($res1);
			$weAcc = $row1["WechatAcc"];
			$isWechatSet = true;
		}
	}
	$res2 = mysql_query("select * from AlipayAccount where UserId='$userid'");
	if ($res2) {
		if (mysql_num_rows($res2) > 0) {
			$row2 = mysql_fetch_assoc($res2);
			$aliAcc = $row2["AlipayAcc"];
			$isAlipaySet = true;
		}
	}
	$res3 = mysql_query("select * from BankAccount where UserId='$userid'");
	if ($res3) {
		if (mysql_num_rows($res3) > 0) {
			$row3 = mysql_fetch_assoc($res3);
			$bankAcc = $row3["AccName"] . " " . $row3["BankAcc"] . " " . $row3["BankName"] . " " . $row3["BankBranch"];
			$isBankSet = true;
		}
	}
}

if (!$isAlipaySet && !$isWechatSet && !$isBankSet) {
	$url = 'jump.php?source=4';
	header('Location: ' . $url);
	exit;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>交易创建</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		<link rel="stylesheet" href="../css/buttons.css">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/md5.js" ></script>
		<script type="text/javascript">
			
			function tryCreateTrade()
			{
				var amount = document.getElementById("cnt").value;
				var paypwd = document.getElementById("paypwd").value;
				
				var amountReg = /^[1-9]\d*$/;
				var val = amountReg.test(amount);
				if (!amountReg.test(amount)) {
					alert("无效的金额，请重新输入！");
					document.getElementById("cnt").value = "";
					document.getElementById("cnt").focus();
					return;
				}
				if (paypwd == "") {
					alert("无效的支付密码！");
					return;
				}
				
				paypwd = md5(paypwd);
				$.post("../php/creditTrade.php", {"func":"createTrade","amount":amount,"paypwd":paypwd}, function(data){
					
					if (data.error == "false") {
						alert("创建成功！");	
						location.href = "exchange.php";
					}
					else {
						alert("创建失败: " + data.error_msg);
						document.getElementById("cnt").value = "";
						document.getElementById("paypwd").value = "";
						document.getElementById("cnt").focus();
						
						return;
					}
				}, "json");				
			}		
			
			function goback()
			{
				location.href = "exchange.php";
			}	
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-4 col-md-4"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-4 col-md-4"><h2 style="display: table-cell; text-align: center; color: white">新建交易</h2></div>
				<div class="col-xs-4 col-md-4"></div>
			</div>
		</div>

		<h4 style="margin-top: 10px;">交易额为100的整数倍，手续费为10%</h4>
		<input type="text" id="cnt" class="form-control" style="margin-bottom: 10px" value="" placeholder="请输入交易数额" />
		<input type="password" class="form-control" id="paypwd" style="margin-bottom: 10px" name="paypwd" placeholder="请输入您的支付密码！" />
		<input type="button" class="button button-border button-rounded button-primary" style="width: 100%;" value="挂单" onclick="tryCreateTrade()" />
    </body>
</html>

