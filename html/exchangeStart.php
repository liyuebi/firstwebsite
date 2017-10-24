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
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		<link rel="stylesheet" href="../css/buttons.css">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
						
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
						alert("下单成功！");	
						location.href = "exchangeOrder.php";
					}
					else {
						alert("下单失败: " + data.error_msg);
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
				<div class="col-xs-4 col-md-4"><h2 style="display: table-cell; text-align: center; color: white">确认交易</h2></div>
				<div class="col-xs-4 col-md-4"></div>
			</div>
		</div>
		
		<p>交易编号：<?php echo $row["TradeId"]; ?></p>
		<p>卖家昵称：<?php echo $row["SellNickN"]; ?></p>
		<p>总交易额：<?php echo $row["Quantity"]; ?></p>
		<p>交易创建时间：<?php echo date("Y-m-d H:i:s" ,$row["CreateTime"]); ?></p>
		<p>交易过期时间：<?php echo 0; ?></p>
		<hr>
		
		<p>最少购买数量为100</p>
		<input type="text" id="cnt" style="width: 100%; height: 30px; margin-bottom: 10px" value="" placeholder="请输入购买数额" />
		<input type="button" id="<?php echo $idx; ?>" class="button button-glow button-border button-rounded button-primary" style="width: 100%;" value="确认购买" onclick="tryStartrade(this)" />
    </body>
</html>
 



