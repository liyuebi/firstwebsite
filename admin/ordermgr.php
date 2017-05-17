<?php

include "../php/database.php";

function getTransaction()
{
	$con = connectToDB();
	if (!$con)
	{
		return false;
	}
	
	$result = mysql_query("select * from Transaction");
	return $result;
}

$result = getTransaction();		
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
// 				alert(btn.id);	
				document.getElementById(btn.id).disabled = true;
				$.post("../php/trade.php", {"func":"delivery","index":btn.id}, function(data){
					
					if (data.error == "false") {
						alert("发货状态修改成功！");	
						location.href = "pwd.php";
					}
					else {
						alert("发货状态修改失败: " + data.error_msg);
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
		<div style="padding: 10px 10px 0 5px; height: 100%; display:inline; float: left; border-right: 1px solid black;">
			<ul style="list-style: none; padding: 0">
<!-- 				<li><a href="companymgr.html">企业管理</a></li> -->
				<li><a href="productmgr.html">产品管理</a></li>
				<li><a href="usermgr.html">用户管理</a></li>
				<li><a href="ordermgr.php">订单管理</a></li>
				<li><a href="rechargemgr.php">充值管理</a></li>
				<li><a href="withdrawmgr.php">取现管理</a></li>
				<li><a href="configmgr.php">配置管理</a></li>
				<li><a href="statistics.php">统计数据</a></li>
				<li><a href="configRwdRate.php">配置动态拨比</a></li>
			</ul>
		</div>
		<div style="display: inline; float: left; padding: 10px 0 0 10px;" >
	        <div>
				<table border="1">
					<tr>
						<th>下单时间</th>
						<th>用户id</th>
						<th>数量</th>
						<th>收件人</th>
						<th>收货人手机</th>
						<th>收货地址</th>
						<th>状态</th>
						<th>确认发货</th>
					</tr>
					<?php
						include "../php/constant.php";
						date_default_timezone_set('PRC');
						while($row = mysql_fetch_array($result)) {
					?>
							<tr>
								<th><?php echo date("Y.m.d H:i:s" ,$row["OrderTime"]); ?></th>
								<th><?php echo $row["UserId"]; ?></th>
								<th><?php echo $row["Count"]; ?></th>
								<th><?php echo $row["Receiver"]; ?></th>
								<th><?php echo $row["PhoneNum"]; ?></th>
								<th><?php echo $row["Address"]; ?></th>
								<th><?php if ($OrderStatusBuy == $row["Status"]) echo "等待发货"; else if ($OrderStatusDefault == $row["Status"]) echo "等待用户确认订单"; else if ($OrderStatusDelivery == $row["Status"]) echo "已收货"; else if ($OrderStatusAccept == $row["Status"]) echo "已收货"; ?></th>
								<th><input type="button" value="确认" id=<?php echo $row["OrderId"]; ?> onclick="onConfirm(this)" /></th>
							</tr>
					<?php
						}
					?>
				</table>
	        </div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>