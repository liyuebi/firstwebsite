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

$mycredit = 0;
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
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if ($result && mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		$mycredit = 0;
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>充值</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		<link rel="stylesheet" href="../css/buttons.css">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function onConfirm()
			{
				var amount = document.getElementById("amount").value;
				
				var amountReg = /^[1-9]\d*$/;
				var val = amountReg.test(amount);
				if (!amountReg.test(amount)) {
					alert("无效的金额，请重新输入！");
					document.getElementById("amount").value = "";
					document.getElementById("amount").focus();
					return;
				}

				var method = "0";				
// 				var method = $("input[name='method']:checked").val();
// 				if (method != "1" && method != "2" && method != "3") {
// 					alert("还没有选择支付方式！");
// 					return;
// 				}
				
				if (amount % <?php echo $refererConsumePoint; ?> != 0) {
					alert("充值金额必须是" + <?php echo $refererConsumePoint; ?> + "的倍数！");
					return;
				}
				
				$.post("../php/credit.php", {"func":"recharge","amount":amount,"method":method}, function(data){
					
					if (data.error == "false") {
						alert("申请成功！");	
						location.href = "home.php";
					}
					else {
						alert("申请失败: " + data.error_msg);
						document.getElementById("amount").value = "";
					}
				}, "json");
			}
			
			function goToPayment()
			{
				location.href = "payment.php";
			}
			
			$(function() {
				$(":radio").click(function(){
					var val = $(this).val();
					if (val == "1") {
						document.getElementById("wechat_block").style.display = "block";
						document.getElementById("alipay_block").style.display = "none";
// 						document.getElementById("bank_block").style.display = "none";
					}
					else if (val == "2") {
						document.getElementById("wechat_block").style.display = "none";
						document.getElementById("alipay_block").style.display = "block";
// 						document.getElementById("bank_block").style.display = "none";
					}
					else if (val == "3") {
						document.getElementById("wechat_block").style.display = "none";
						document.getElementById("alipay_block").style.display = "none";
// 						document.getElementById("bank_block").style.display = "block";
					}
				});
			});
			function goback() 
			{
				location.href = "home.php";
			}
		</script>
	</head>
	<body>
		<div style="height: 50px; margin-top: 10px; background-color: rgba(255, 255, 255, 0.24)">
			<h2 style="display: inline">虚拟生活</h2>
			<input type="button" style="float: right" value="返回" class="button" onclick="goback()" />
		</div>
		
		<a class="link_forward" href="bank.php">
 			<span>存储财富</span>
		</a>
		<a class="link_forward" href="teleFare.php">
			<span>话费充值</span>
		</a>
		<a class="link_forward" href="fuelFare.php">
			<span>油费充值</span>
		</a>
    </body>
</html>