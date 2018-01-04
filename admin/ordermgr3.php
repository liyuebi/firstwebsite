<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include "../php/database.php";

$result = false;
$res1 = false;
$productList = array();

$con = connectToDB();
if (!$con)
{
	return false;
}

include "../php/constant.php";
// $result = mysqli_query($con, "select * from Transaction  where Status='$OrderStatusBuy'");
// 	$result = mysqli_query($con, "select * from Transaction");
$res1 = mysqli_query($con, "select * from Transaction where Type=4");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>订单管理</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function onConfirm(btn)
			{
				document.getElementById(btn.id).disabled = true;
				$.post("../php/trade.php", {"func":"deliveryPC1","index":btn.id}, function(data){
					
					if (data.error == "false") {
						alert("修改充值状态完成！");
						document.getElementById("status_" + data.index).innerHTML = "完成";	
					}
					else {
						alert("修改充值状态失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function onDeny(btn)
			{
				alert(btn.id);
			}
		</script>
	</head>
	<body>
		<div style="padding: 10px 0 0 10px;" >
			<div>
				<table id="tbl1" border="1">
					<tr>
						<th>下单时间</th>
						<th>用户id</th>
<!-- 						<th>产品信息</th> -->
<!-- 						<th>数量</th> -->
						<th>充值号码</th>
						<th>金额</th>
						<th>现金支付</th>
						<th>状态</th>
						<th>确认充值</th>
					</tr>
					<?php
						include "../php/constant.php";
						date_default_timezone_set('PRC');
						while($row = mysqli_fetch_assoc($res1)) {
					?>
							<tr>
								<td><?php echo date("Y.m.d H:i:s" ,$row["OrderTime"]); ?></td>
								<td><?php echo $row["UserId"]; ?></td>
<!-- 								<td><?php echo $productList[$row['ProductId']]; ?></td> -->
<!-- 								<td><?php echo $row["Count"]; ?></td> -->
								<td><?php echo $row["CellNum"]; ?></td>
								<td><?php echo $row["Price"]; ?></td>
								<td><?php echo $row["PriceInCash"]; ?></td>
								<td id='status_<?php echo $row["OrderId"]; ?>'><?php if ($OrderStatusBuy == $row["Status"]) echo "等待付款"; else if ($OrderStatusCanceled == $row["Status"]) echo "用户取消"; else if ($OrderStatusPaid == $row["Status"]) echo "已付款，等待充值"; else if ($OrderStatusDelivery == $row["Status"]) echo "已充值"; else if ($OrderStatusAccept == $row["Status"]) echo "完成"; ?></td>
<!--  								<td><input type="text" id='courierNum_<?php echo $row["OrderId"]; ?>' size='30' placeholder="请输入快递单号！" /></td> -->
								<?php 
									if ($OrderStatusPaid == $row["Status"]) {
								?>
								<td><input type="button" value="确认" id=<?php echo $row["OrderId"]; ?> onclick="onConfirm(this)" /></td>
								<?php
									}
								?>
							</tr>
					<?php
						}
					?>
				</table>
	        </div>
		</div>
    </body>
</html>