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

$userid = $_SESSION["userId"];
$idx = $_GET["h"];

$con = connectToDB();
if ($con) {
	
	$result = mysql_query("select * from CreditTrade where IdxId='$idx'");
	if ($result && mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>确认购买</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle-1.01.css" />
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
			
			function tryStartrade(btn)
			{
				var idx = btn.id;
				var amount = document.getElementById("cnt").value;
				var amountReg = /^[1-9]\d*$/;
				var val = amountReg.test(amount);
				if (!amountReg.test(amount)) {
					alert("无效的金额，请重新输入！");
					document.getElementById("cnt").value = "";
					document.getElementById("cnt").focus();
					return;
				}
				
				$.post("../php/creditTrade.php", {"func":"startTrade","amount":amount,"idx":idx}, function(data){
					
					if (data.error == "false") {
						alert("创建成功！");	
// 						location.href = "exchange.php";
					}
					else {
						alert("创建失败: " + data.error_msg);
						document.getElementById("cnt").value = "";
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
		<div style="height: 50px; margin-top: 10px; background-color: rgba(255, 255, 255, 0.24)">
			<h2 style="display: inline">开始交易</h2>
			<input type="button" style="float: right" value="返回" class="button" onclick="goback()" />
		</div>
	
		<p align="center">确认交易</p>
		
		<p>交易编号：<?php echo $row["TradeId"]; ?></p>
		<p>卖家昵称：<?php echo $row["SellNickN"] ?></p>
		<p>总交易额：<?php echo $row["Quantity"] ?></p>
		<p>交易创建时间：<?php echo date("Y-m-d H:i:s" ,$row["CreateTime"]); ?></p>
		<p>交易过期时间：<?php echo 0; ?></p>
		<hr>
		
		<p>最少购买数量为100</p>
		<input type="text" id="cnt" style="width: 100%; height: 30px; margin-bottom: 10px" value="" placeholder="请输入购买数额" />
		<input type="button" id="<?php echo $idx; ?>" class="button button-glow button-border button-rounded button-primary" style="width: 100%;" value="确认购买" onclick="tryStartrade(this)" />
    </body>
</html>
 



