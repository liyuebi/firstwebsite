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

include_once "../php/database.php";
include "../php/constant.php";
include "../php/creditTrade.php";
updateUserExchangeOrder();

$con = connectToDB();

$userid = $_SESSION["userId"];
$res = false;
$res1 = false;

if ($con) {
	$res = mysqli_query($con, "select * from CreditTrade where SellerId='$userid' order by CreateTime desc");
	$res1 = mysqli_query($con, "select * from CreditTrade where BuyerId='$userid' order by ReserveTime desc ");
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
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
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
				document.getElementById("block_sell").style.display = "block";
				document.getElementById("block_buy").style.display = "none";
			}
			
			function switchToBuy()
			{
				document.getElementById("block_sell").style.display = "none";
				document.getElementById("block_buy").style.display = "block";				
			}
			
			function tryCancel(btn)
			{
				var idx = btn.id;
				if (confirm("确定要取消挂单？")) {
					$.post("../php/creditTrade.php", {"func":"cancelTrade","idx":idx}, function(data){
						
						if (data.error == "false") {
							alert("取消挂单成功！");	
							location.reload();
						}
						else {
							alert("取消挂单失败: " + data.error_msg);
							location.reload();
							
							return;
						}
					}, "json");			
				}
			}
			
			function checkSellerInfo(btn)
			{
				var idx = btn.id;
				var infoNode = document.getElementById("info_" + idx);
				if (infoNode) {
					var info = infoNode.value;
					if (info.length > 0) {
						alert(info);
						return;
					}
				}
				
				alert("没有支付信息！");
			}
			
			function tryConfirmReceive(btn)
			{
				var idx = btn.id;
				if (confirm("您确定已经受到汇款?")) {
					
					$.post("../php/creditTrade.php", {"func":"confirmReceive","idx":idx}, function(data){
						
						if (data.error == "false") {
							alert("确认支付成功！");	
							location.reload();
						}
						else {
							alert("确认支付失败: " + data.error_msg);
							location.reload();
							
							return;
						}
					}, "json");			
				}
			}
			
			function tryConfirmPayment(btn)
			{
				var idx = btn.id;
				if (confirm("确认已完成支付？")) {
					$.post("../php/creditTrade.php", {"func":"confirmPayment","idx":idx}, function(data){
						
						if (data.error == "false") {
							alert("确认支付成功！");	
							location.reload();
						}
						else {
							alert("确认支付失败: " + data.error_msg);
							location.reload();
							
							return;
						}
					}, "json");			
				}
			}
			
			function abandonPayment(btn)
			{
				var idx = btn.id;
				if (confirm("确认要放弃买入线上云量？")) {

					$.post("../php/creditTrade.php", {"func":"abandonPayment","idx":idx}, function(data){
						
						if (data.error == "false") {
							alert("取消买入成功！");	
							location.reload();
						}
						else {
							alert("取消买入失败: " + data.error_msg);
							location.reload();							
							return;
						}
					}, "json");			
				}
			}
			
			function fireComplaint(btn)
			{
				location.href = 'complS.php?t=' + <?php echo $complainTCreditTrade;?> + '&i=' + btn.id; 
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
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h3 style="text-align: center; color: white">交易所订单</h3></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>
		
		<hr>
		<table id="tag_table" class="t2">
			<tr>
<!-- 				<td id="1" width="50%" >挂单</th> -->
<!-- 				<td id="2" width="50%" >买入</th> -->
				<td id="1" width="40%" style="border-bottom: 1px solid rgba(0, 0, 0, 0); margin-left: 10%; margin-right: 5%;" >挂单</td>
				<td id="2" width="40%" style="border-bottom: 1px solid rgba(0, 0, 0, 0); margin-left: 5%; margin-right: 10%;">买入</td>
			</tr>
		</table>
		<div id="block_sell" style="display: block; margin-top: 10px;">
			<?php
				if ($res) {
					date_default_timezone_set('PRC');
					while ($row = mysqli_fetch_assoc($res)) {
			?>
						<hr>
						<div style="margin: 0 5px;">
							<p>交易编号：<?php echo $row["TradeId"]; ?></p>
							<p>交易额度：<?php if ($row["BuyCnt"] <= 0) {echo $row["Quantity"];} else {echo $row["BuyCnt"] . '/' . $row["Quantity"];} ?></p>
							<p>创建时间：<?php echo date("Y-m-d H:i" ,$row["CreateTime"]); ?></p>
							<?php 	if ($row["Status"] == $creditTradeInited) {
										if (time() - $row["CreateTime"] < 60 * 60 * $exchangeBuyHours) {
							?>
								<p>过期时间：<?php echo date("Y-m-d H:i", $row["CreateTime"] + 60 * 60 * $exchangeBuyHours); ?></p>
								<input type="button" id="<?php echo $row["IdxId"]; ?>" class="button button-border button-rounded" style="width: 50%;" value="取消挂单" onclick="tryCancel(this)" />				
							<?php		}
										else {
							?>
								<p>已过期</p>
							<?php
										}
							 	} 
									else if ($row["Status"] == $creditTradeCancelled) { ?>
								<p>已撤单</p>
							<?php 	} 
									else if ($row["Status"] == $creditTradeExpired) { ?>
								<p>已过期</p>
							<?php 	} 
									else if ($row["Status"] == $creditTradeReserved) { ?>
								<p>过期时间：<?php echo date("Y-m-d H:i", $row["ReserveTime"] + 60 * 60 * $exchangePayHours); ?></p>
								<p>等待支付</p>
							<?php 	} 
									else if ($row["Status"] == $creditTradePayed) { ?>
								<p>买家昵称：<?php echo $row["BuyerNickN"]; ?></p>
								<p>自动到账时间：<?php echo date("Y-m-d H:i", $row["PayTime"] + 60 * 60 * 24); ?></p>
								<p>买家已支付,请确认</p>
								<input type="button" id="<?php echo $row["IdxId"]; ?>" class="button button-border button-rounded" style="width: 50%;" value="确认收款" onclick="tryConfirmReceive(this)" />
							<?php 	} 
									else if ($row["Status"] == $creditTradeNotPayed) { ?>
								<p>买家昵称：<?php echo $row["BuyerNickN"]; ?></p>
								<p>超时未支付</p>
							<?php 	} 
									else if ($row["Status"] == $creditTradeAbandoned) { ?>
								<p>买家昵称：<?php echo $row["BuyerNickN"]; ?></p>
								<p>买家弃购</p>
							<?php 	} 
									else if ($row["Status"] == $creditTradeConfirmed) { ?>
								<p>买家昵称：<?php echo $row["BuyerNickN"]; ?></p>
								<p>交易完成</p>
							<?php 	} 
									else if ($row["Status"] == $creditTradeAutoConfirmed) {
							?> 
								<p>买家昵称：<?php echo $row["BuyerNickN"]; ?></p>
							<?php
										if (time() - $row["ConfirmTime"] >= 60 * 60 * $exchangeComplainHours) {
							?>
									<p>交易完成</p>	
							<?php											
										}
										else {
							?>
									<p style="display: -webkit-flex; display: flex; align-items: center; justify-content: space-between">
										<span>交易完成</span>
										<input type="button" id="<?php echo $row["IdxId"]; ?>" class="btn btn-danger" style="width: 20%" value="投诉" onclick="fireComplaint(this)" />
									</p>
							<?php		}
									} ?>
						</div>
			<?php
					}
				}
			?>
		</div>
		<div id="block_buy" style="display: none; margin-top: 3%;">
			<?php
				if ($res1) {
					date_default_timezone_set('PRC');
					while ($row = mysqli_fetch_assoc($res1)) {
			?>
						<hr>
						<div>
							<p>交易编号：<?php echo $row["TradeId"]; ?></p>
							<p>卖家昵称：<?php echo $row["SellNickN"]; ?></p>
							<p>买入额度：<?php echo $row["BuyCnt"]; ?></p>
							<p>下单时间：<?php echo date("Y-m-d H:i", $row["ReserveTime"]); ?></p>
							<?php 	if ($row["Status"] == $creditTradeReserved) { 
								
								$info = "卖家手机号：" . $row["SellPhoneNum"] . "\n";
								$str = "select * from BankAccount where UserId=" . $row['SellerId'];
								$res3 = mysqli_query($con, $str);
								if ($res3 && mysqli_num_rows($res3) > 0) {
									$row3 = mysqli_fetch_assoc($res3);
									$info = $info . "卖家账户：" . $row3["AccName"] . " " . $row3["BankAcc"] . " " . $row3["BankName"] . " " . $row3["BankBranch"] . "\n";
								}
								else {
									$info = $info . "没有支付信息。\n";
								}
								$info = $info . "请在下单后" . $exchangePayHours . "小时内完成支付，并点击支付完成按钮。";
								
							?>
								<p>支付截止时间：<?php echo date("Y-m-d H:i", $row["ReserveTime"] + 60 * 60 * $exchangePayHours); ?></p>
								<input type="button" id="<?php echo $row["IdxId"]; ?>" class="button-rounded" style="width: 32%;" value="查看卖家信息" onclick="checkSellerInfo(this)" />
								<input type="button" id="<?php echo $row["IdxId"]; ?>" class="button-rounded" style="width: 32%;" value="支付完成" onclick="tryConfirmPayment(this)" />
								<input type="button" id="<?php echo $row["IdxId"]; ?>" class="button-rounded" style="width: 32%;" value="放弃买入" onclick="abandonPayment(this)" />
								<input type='hidden' id="info_<?php echo $row["IdxId"]; ?>" value="<?php echo $info; ?>" />
							<?php 	} 
									else if ($row["Status"] == $creditTradeAbandoned) { ?>
								<p>放弃买入</p>
							<?php
									}
									else if ($row["Status"] == $creditTradePayed) { ?>
								<p>自动到账时间：<?php echo date("Y-m-d H:i", $row["PayTime"] + 60 * 60 * 24); ?></p>
								<p>已付款，等待买家确认</p>
							<?php
									}
									else if ($row["Status"] == $creditTradeNotPayed || $row["Status"] == $creditTradeReserved && time() - $row["ReserveTime"] >= 60 * 60 * 24) { ?>
								<p>超时未付款</p>
							<?php
									}
									else if ($row["Status"] == $creditTradeConfirmed || $row["Status"] == $creditTradeAutoConfirmed) { ?>
								<p>交易完成</p>
							<?php	} ?>
						</div>
			<?php
					}
				}
			?>
		</div>
    </body>
</html>