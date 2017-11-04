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
$result = mysql_query("select * from Transaction where UserId='$userid' and Type!=1 order by OrderTime desc");
if (!$result) {
	return;	
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
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
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
			})
			
			function goback()
			{
				location.href = "me.php";
			}
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h3 style="display: table-cell; text-align: center; color: white">我的订单</h3></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>
		
		<div class="big_frame">
	        <div>
		        <?php
			        date_default_timezone_set('PRC');
			        while($row = mysql_fetch_array($result)) {
				        if (2 == $row["Type"]) {
		        ?>
		        		<ul class="order_block" style="background: white; margin-top: 3%;">
			        		<li>话费充值</li>
			        		<li class="right_ele"><?php echo date("Y-m-d H:i" ,$row["OrderTime"]); ?></li>
			        		<br>
			        		<li>充值号码:<?php echo $row["CellNum"]; ?></li>
			        		<li class="right_ele">金额: <?php echo $row["Price"]; ?></li>
			        		<br>
			        		<?php
				        		if ($OrderStatusBuy == $row["Status"]) {
					        ?>		
					        	<li>等待充值</li>
					        <?php
				        		}
				        		else if ($OrderStatusAccept == $row["Status"]) {
							?>
								<li>已充值</li>
							<?php					        		
				        		}
				        	?>
		        		</ul>
		        <?php
			        	}
			        	else if (3 == $row["Type"]) {
				?>
		        		<ul class="order_block" style="background: white; margin-top: 3%;">
			        		<li>加油卡充值</li>
			        		<li class="right_ele"><?php echo date("Y-m-d H:i" ,$row["OrderTime"]); ?></li>
			        		<br>
			        		<li>加油卡号:<?php echo $row["CardNum"]; ?></li>
			        		<br>
			        		<li>联系手机号:<?php echo $row["CellNum"]; ?></li>
			        		<li class="right_ele">金额: <?php echo $row["Price"]; ?></li>
			        		<br>
			        		<?php
				        		if ($OrderStatusBuy == $row["Status"]) {
					        ?>		
					        	<li>等待充值</li>
					        <?php
				        		}
				        		else if ($OrderStatusAccept == $row["Status"]) {
							?>
								<li>已充值</li>
							<?php					        		
				        		}
				        	?>
		        		</ul>
				<?php
			        	}
			        }
		        ?>
	        </div>

	
<!--
			<table id="tag_table" class="t2">
				<tr>
					<td id="1" width="40%" style="border-bottom: 1px solid rgba(0, 0, 0, 0); margin-left: 10%; margin-right: 5%;" >未完成订单</td>
					<td id="2" width="40%" style="border-bottom: 1px solid rgba(0, 0, 0, 0); margin-left: 5%; margin-right: 10%;">已完成订单</td>
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
-->
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>		