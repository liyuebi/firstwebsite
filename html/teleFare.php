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

include "../php/database.php";
include "../php/constant.php";
$con = connectToDB();

$userid = $_SESSION["userId"];
$result = false;

if ($con) {
	
	$result = mysql_query("select * from CreditTrade where Status='$creditTradeInited'");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>话费充值</title>
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
						
			function tryChargeCellphone()
			{
				var phonenum = document.getElementById("phonenum").value;
				var amount = document.getElementById("amount").value;
				var paypwd = document.getElementById("pwd").value;
				
				phonenum=$.trim(phonenum);
				amount=$.trim(amount);
				if (!isPhoneNumValid(phonenum)) {
					alert("无效的电话号码！");
					return;
				}
				if (amount == "") {
					alert("无效的金额！");
					return;
				}
				if (paypwd == "") {
					alert("无效的支付密码！");
					return;
				}
				
				var str = "确认为手机号 " + phonenum + " 充值 " + amount + ", 将收取手续费10%"; 
				if (confirm(str)) {
					paypwd = md5(paypwd);		
					
					$.post("../php/trade.php", {"func":"phoneCharge", "phonenum":phonenum, "amount":amount, "paypwd":paypwd}, function(data){
					
						if (data.error == "false") {
							alert("创建订单成功！");	
							document.getElementById("phonenum").value = "";
							document.getElementById("amount").value = "";
							document.getElementById("pwd").value = "";
						}
						else {
							alert("创建订单失败: " + data.error_msg);
						}
					}, "json");
				}
			}			
			
			function goback()
			{
				location.href = "virtuelife.php";
			}
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h3 style="text-align: center; color: white">话费充值</h3></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>

		<input id="phonenum" class="form-control" type="text" style="margin-top: 5px" placeholder="请输入充值手机号！" onkeypress="return onlyNumber(event)" />
		<input id="amount" class="form-control" type="text" style="margin-top: 5px" placeholder="请输入充值金额！" onkeypress="return onlyNumber(event)" /> 
		<input id="pwd" class="form-control" type="password" style="margin-top: 5px" placeholder="请输入支付密码！" />		
		<input type="button" class="btn btn-info btn-lg btn-block" style="width: 100%; margin-top: 5px" value="确认充值" onclick="tryChargeCellphone()" />
    </body>
</html>