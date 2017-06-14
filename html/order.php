<?php

include "../php/database.php";

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

$result = false;
$con = connectToDB();
if (!$con)
{
	return false;
}

include "./../php/constant.php";
$userid = $_SESSION["userId"];
$result = mysql_query("select * from Transaction where UserId='$userid' and Status!='$OrderStatusAccept'");
if (!$result) {
	return;	
}

$res1 = mysql_query("select * from Transaction where UserId='$userid' and Status='$OrderStatusAccept' order by OrderTime desc");

$prodcutName = '';
$prodcutName1 = '';

$res2 = mysql_query("select * from Product where ProductId=1");
if ($res2) {
	$row2 = mysql_fetch_assoc($res2);
	$prodcutName = $row2["ProductName"];
}

$res3 = mysql_query("select * from Product where ProductId=2");
if ($res3) {
	$row3 = mysql_fetch_assoc($res3);
	$prodcutName1 = $row3["ProductName"];
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>我的订单</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle-1.01.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function onConfirm(btn)
			{
				document.getElementById(btn.id).disabled = true;
				$.post("../php/trade.php", {"func":"accept","index":btn.id}, function(data){
					
					if (data.error == "false") {
						alert("确认收货成功！");	
// 						location.href = "pwd.php";
					}
					else {
						alert("收货失败： " + data.error_msg);
					}
				}, "json");
			}
			
			function confirmAddress(btn)
			{
				document.getElementById(btn.id).disabled = true;
				location.href = "deal.php?orderId=" + btn.id;
			}
			
			function switchToUnfinished()
			{
				document.getElementById("block_unfinish").style.display = "inline";
				document.getElementById("block_finish").style.display = "none";
			}
			
			function switchToFinished()
			{
				document.getElementById("block_unfinish").style.display = "none";
				document.getElementById("block_finish").style.display = "inline";
			}
			
			$(document).ready(function(){		
				
				document.getElementById("1").style.color = "red";
				
				$('table#tag_table td').click(function(){
					$(this).css('color','red');//点击的设置字色为红色
// 					$(this).css('border-bottom','soild red 1px');//点击的设置为绿色
					$('#tag_table td').not(this).css('color','black');//其他的全部设置为黑色
// 					$('#tag_table td').not(this).css('border-bottom','none');//其他的全部设置为红色

					if ($(this).attr("id") == "1" ) {
						switchToUnfinished();
					}
					else {
						switchToFinished();
					}
				});
			})
		</script>
	</head>
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
		<div class="big_frame">
<!-- 			<h3 align="center">我的订单</h3> -->
	
			<table id="tag_table" class="t2">
				<tr>
					<td id="1" width="50%" >未完成订单</th>
					<td id="2" width="50%" >已完成订单</th>
				</tr>
			</table>
			
	        <div id="block_unfinish" style="display: inline; margin-top: 3%;">
				<table border="1" width="100%">
					<tr>
						<th>订单号</th>
						<th>数量</th>
						<th>价格</th>
						<th>快递单号</th>
						<th>状态</th>
					</tr>
					<?php
						include "../php/constant.php";
						while($row = mysql_fetch_array($result)) {
					?>
							<tr>
								<td><?php echo $row["UserId"]; ?></td>
								<td><?php echo $row["Count"] ?></td>
								<td><?php echo $row["Price"]; ?></td>
								<td><?php echo $row["CourierNum"]; ?></td>
								<td align="center"><?php 
									if ($OrderStatusBuy == $row["Status"]) 
										echo "等待发货"; 
									else if ($OrderStatusDefault == $row["Status"]) {
	// 									echo "请确认地址"; 
										?>
										<input type="button" value="确认订单" id=<?php echo $row["OrderId"]; ?> onclick="confirmAddress(this)" />
										<?php
									}
									else if ($OrderStatusDelivery == $row["Status"]) {
										?>
										<input type="button" value="确认收货" id=<?php echo $row["OrderId"]; ?> onclick="onConfirm(this)" />
										<?php
									}
									else if ($OrderStatusAccept == $row["Status"])
										echo "已收货";
									?>
								</td>
							</tr>
					<?php
						}
					?>
				</table>
	        </div>
	        
	        <div id="block_finish" style="display: none">
		        <?php
			        date_default_timezone_set('PRC');
			        while($row1 = mysql_fetch_array($res1)) {
		        ?>
		        		<ul class="order_block" style="background: white; margin-top: 3%;">
			        		<li class="left_ele"><b><?php if ($row1["ProductId"] == 1) echo $prodcutName; else echo $prodcutName1; ?></b></li>
			        		<li class="right_ele">x <?php echo $row1["Count"]; ?></li>
			        		<br>
			        		<hr>
			        		<li class="left_ele"><?php echo date("Y-m-d H:i:s" ,$row1["OrderTime"]); ?></li>
			        		<li class="right_ele" style="float: right">使用蜜券： <?php echo ($row1["Count"] * $row1["Price"]); ?></li>
		        		</ul>
		        <?php
			        }
		        ?>
	        </div>
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>		