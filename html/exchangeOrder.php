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
$res = false;
$res1 = false;

if ($con) {
	$res = mysql_query("select * from CreditTrade where SellerId='$userid'");
	$res1 = mysql_query("select * from CreditTrade where BuyerId='$userid'");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>交易所订单</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle-1.01.css" />
		<link rel="stylesheet" href="../css/buttons.css">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			$(document).ready(function(){		
				
				document.getElementById("1").style.color = "red";
				document.getElementById("1").style.borderBottomColor = "red";
				
				$('table#tag_table td').click(function(){
					$(this).css('color','red');//点击的设置字色为红色
// 					$(this).css('border-bottom','soild red 1px');//点击的设置为绿色
					$(this).css('borderBottomColor','red');//点击的设置为绿色
					$('#tag_table td').not(this).css('color','black');//其他的全部设置为黑色
// 					$('#tag_table td').not(this).css('border-bottom','none');//其他的全部设置为红色
					$('#tag_table td').not(this).css("borderBottomColor","rgba(0, 0, 0, 0)");//其他的全部设置为黑色

					if ($(this).attr("id") == "1" ) {
						switchToSell();
					}
					else {
						switchToBuy();
					}
				});
			})
			
			function switchToSell()
			{
				document.getElementById("block_sell").style.display = "inline";
				document.getElementById("block_buy").style.display = "none";
			}
			
			function switchToBuy()
			{
				document.getElementById("block_sell").style.display = "none";
				document.getElementById("block_buy").style.display = "inline";				
			}
		</script>
	</head>
	<body>
		<p align="center">交易所订单</p>
		
		<hr>
		<table id="tag_table" class="t2">
			<tr>
<!-- 				<td id="1" width="50%" >挂单</th> -->
<!-- 				<td id="2" width="50%" >买入</th> -->
				<td id="1" width="40%" style="border-bottom: 1px solid rgba(0, 0, 0, 0); margin-left: 10%; margin-right: 5%;" >挂单</td>
				<td id="2" width="40%" style="border-bottom: 1px solid rgba(0, 0, 0, 0); margin-left: 5%; margin-right: 10%;">买入</td>
			</tr>
		</table>
		<div id="block_sell" style="display: inline; margin-top: 3%;">
			<?php
				if ($res) {
					date_default_timezone_set('PRC');
					while ($row = mysql_fetch_array($res)) {
			?>
						<hr>
						<div>
							<p>交易编号：<?php echo $row["TradeId"]; ?></p>
							<p>卖家昵称：<?php echo $row["SellNickN"] ?></p>
							<p>总交易额：<?php echo $row["Quantity"] ?></p>
							<p>交易创建时间：<?php echo date("Y-m-d H:i:s" ,$row["CreateTime"]); ?></p>
							<p>交易过期时间：<?php echo 0; ?></p>
							<?php 	if ($row["Status"] == $creditTradeInited) { ?>
								<input type="button" id="<?php echo $row["IdxId"]; ?>" class="button button-border button-rounded" style="width: 50%;" value="取消挂单" onclick="tryStartTrade(this)" />
							<?php 	} 
									else if ($row["Status"] == $creditTradeCancelled) { ?>
								<p>已撤单</p>
							<?php 	} 
									else if ($row["Status"] == $creditTradeReserved) { ?>
								<p>等待支付</p>
							<?php 	} 
									else if ($row["Status"] == $creditTradeAbandoned) { ?>
								<p>买家弃购</p>
							<?php	} ?>
						</div>
						<hr>
			<?php
					}
				}
			?>
		</div>
		<div id="block_buy" style="display: none; margin-top: 3%;">
			<?php
				if ($res1) {
					date_default_timezone_set('PRC');
					while ($row = mysql_fetch_array($res1)) {
			?>
						<hr>
						<div>
							<p>交易编号：<?php echo $row["TradeId"]; ?></p>
							<p>卖家昵称：<?php echo $row["SellNickN"] ?></p>
							<p>总交易额：<?php echo $row["Quantity"] ?></p>
							<p>交易创建时间：<?php echo date("Y-m-d H:i:s" ,$row["CreateTime"]); ?></p>
							<p>交易过期时间：<?php echo 0; ?></p>
							<?php 	if ($row["Status"] == $creditTradeReserved) { ?>
								<input type="button" id="<?php echo $row["IdxId"]; ?>" class="button button-border button-rounded" style="width: 32%;" value="查看卖家信息" onclick="tryStartTrade(this)" />
								<input type="button" id="<?php echo $row["IdxId"]; ?>" class="button button-border button-rounded" style="width: 32%;" value="支付完成" onclick="tryStartTrade(this)" />
								<input type="button" id="<?php echo $row["IdxId"]; ?>" class="button button-border button-rounded" style="width: 32%;" value="放弃买入" onclick="tryStartTrade(this)" />
							<?php 	} 
									else if ($row["Status"] == $creditTradeAbandoned) { ?>
								<p>放弃买入</p>
							<?php	} ?>
						</div>
						<hr>
			<?php
					}
				}
			?>
		</div>
    </body>
</html>