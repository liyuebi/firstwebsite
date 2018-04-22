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
$result = mysqli_query($con, "select * from Transaction where UserId='$userid' and Type!=1 and Type!=8 order by OrderTime desc");
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
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script>
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

			function confirmPayment(btn)
			{
				btn.disabled = true;
				$.post("../php/trade.php", {"func":"confirmPC1","index":btn.id}, function(data){
					
					if (data.error == "false") {
						alert("确认支付成功！");	
						location.reload();
					}
					else {
						alert("确认支付失败： " + data.error_msg);
					}
				}, "json");
			}

			function cancelOrder(btn)
			{
				btn.disabled = true;
				$.post("../php/trade.php", {"func":"cancelPC1","index":btn.id}, function(data){
					
					if (data.error == "false") {
						alert("取消订单成功！");	
						location.reload();
					}
					else {
						alert("取消订单失败： " + data.error_msg);
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
				<div class="col-xs-6 col-md-6"><h3 style="text-align: center; color: white">我的订单</h3></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>
		
		<div class="big_frame">
	        <div>
		        <?php
			        date_default_timezone_set('PRC');
			        while($row = mysqli_fetch_assoc($result)) {
				        if (2 == $row["Type"]) {
		        ?>
		        		<ul class="order_block" style="background: white; margin-top: 3%;">
			        		<li><b>话费充值</b></li>
			        		<li class="right_ele text-info"><?php echo date("Y-m-d H:i" ,$row["OrderTime"]); ?></li>
			        		<br>
			        		<li>充值号码:<?php echo $row["CellNum"]; ?></li>
			        		<li class="right_ele">金额: <?php echo $row["Price"]; ?></li>
			        		<br>
			        		<?php
				        		if ($OrderStatusBuy == $row["Status"]) {
					        ?>		
					        	<li class="text-info">等待充值</li>
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
			        		<li><b>加油卡充值</b></li>
			        		<li class="right_ele text-info"><?php echo date("Y-m-d H:i" ,$row["OrderTime"]); ?></li>
			        		<br>
			        		<li>加油卡号:<?php echo $row["CardNum"]; ?></li>
			        		<br>
			        		<li>联系手机号:<?php echo $row["CellNum"]; ?></li>
			        		<li class="right_ele">金额: <?php echo $row["Price"]; ?></li>
			        		<br>
			        		<?php
				        		if ($OrderStatusBuy == $row["Status"]) {
					        ?>		
					        	<li class="text-info">等待充值</li>
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
			        	else if (4 == $row["Type"]) {
			    ?>
		        		<ul class="order_block" style="background: white; margin-top: 3%;">
			        		<li><b>话费充值（新用户专享）</b></li>
			        		<li class="right_ele text-info"><?php echo date("Y-m-d H:i" ,$row["OrderTime"]); ?></li>
			        		<br>
			        		<li>充值号码:<?php echo $row["CellNum"]; ?></li>
			        		<li class="right_ele">金额: <?php echo $row["Price"]; ?></li>
			        		<br>
			        		<?php
				        		if ($OrderStatusBuy == $row["Status"]) {
					        ?>		
					        	<li><span class="text-warning">等待付款: <?php echo $row["PriceInCash"]; ?></span></li>
					        	<br>
					        	<li>
					        		<span>
					        			<button class="btn btn-info" data-toggle="modal" data-target="#payModal">查看支付方式</button>
					        			<button class="btn btn-primary" id=<?php echo $row["OrderId"]; ?> onclick="confirmPayment(this)">确认支付</button>
					        			<button class="btn btn-warning" id=<?php echo $row["OrderId"]; ?> onclick="cancelOrder(this)">取消订单</button>
					        		</span>
					        	</li>
					        <?php
				        		}
				        		else if ($OrderStatusPaid == $row["Status"]) {
							?>
								<li class="text-info">等待充值</li>
					        <?php
				        		}
				        		else if ($OrderStatusCanceled == $row["Status"]) {
							?>
								<li class="text-warning">订单已取消</li>
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
        </div>
		<div class="modal fade" id="payModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel">
			<div class="modal-dialog" role="document">
		    	<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="payModalLabel">官方收款账号</h4>
			    	</div>
					<div class="modal-body">
						<p>
							<h5 class="text-info">银行账号：</h5>
							<b>6217 0020 8000 2945 776</b>
							<br>
							谢澍潜 中国建设银行 江西省婺源县天佑支行
						</p>
						<p>
							<h5 class="text-info">微信/支付宝：</h5>
							<div style="text-align: center;">
								<img src="../img/1513126186044.jpg" style="width: 60%; margin: 0 auto;">
							</div>
						</p>
					</div>
		    	</div>
			</div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>		