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
		<title>交易所</title>
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
			
			function tryCreateTrade()
			{
				location.href = "exchangeCreate.php";
			}			
			
			function tryStartTrade(btn)
			{
				var idx = btn.id;
// 				var url = "exchageStart.php?h=" + idx;
				location.href = "exchangeStart.php?h=" + idx;
			}
			
			function gotoCreditOrder()
			{
				location.href = "exchangeOrder.php";
			}
		</script>
	</head>
	<body>
		<p align="center">交易所</p>
<!-- 		<p align="right">交易记录</p> -->
		<?php
			if ($result) {
				date_default_timezone_set('PRC');
				while ($row = mysql_fetch_array($result)) {
		?>
					<hr>
					<div>
						<p>交易编号：<?php echo $row["TradeId"]; ?></p>
						<p>卖家昵称：<?php echo $row["SellNickN"] ?></p>
						<p>总交易额：<?php echo $row["Quantity"] ?></p>
						<p>交易创建时间：<?php echo date("Y-m-d H:i:s" ,$row["CreateTime"]); ?></p>
						<p>交易过期时间：<?php echo 0; ?></p>
						<input type="button" id="<?php echo $row["IdxId"]; ?>" class="button button-border button-rounded" style="width: 50%;" value="购买" onclick="tryStartTrade(this)" />
					</div>
					<hr>
		<?php
				}
			}
		?>
		<input type="button" class="button button-glow button-border button-rounded button-primary" style="width: 100%;" value="挂单" onclick="tryCreateTrade()" />
		<input type="button" class="button button-glow button-border button-rounded button-primary" style="width: 100%;" value="交易订单" onclick="gotoCreditOrder()" />
    </body>
</html>