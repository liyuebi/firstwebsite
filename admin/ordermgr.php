<?php

include "../php/database.php";

function getTranscation()
{
	$con = connectToDB();
	if (!$con)
	{
		return false;
	}
	
	mysql_select_db("my_db", $con);
	$result = mysql_query("select * from Transcation");
	return $result;
}

$result = getTranscation();		
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
        <div>
			<table border="1">
				<tr>
					<th>用户id</th>
					<th>用户手机号</th>
					<th>用户名</th>
					<th>单价</th>
					<th>数量</th>
					<th>状态</th>
					<th>确认发货</th>
				</tr>
				<?php
					include "constant.php";
					while($row = mysql_fetch_array($result)) {
				?>
						<tr>
							<th><?php echo $row["UserId"]; ?></th>
							<th></th>
							<th></th>
							<th><?php echo $row["Price"]; ?></th>
							<th><?php echo $row["Count"] ?></th>
							<th><?php if ($OrderStatusBuy == $row["Status"]) echo "等待发货"; else if ($OrderStatusDefault == $row["Status"]) echo "等待用户确认订单"; else if ($OrderStatusDelivery == $row["Status"]) echo "已收货"; else if ($OrderStatusAccept == $row["Status"]) echo "已收货"; ?></th>
							<th><input type="button" value="确认" id=<?php echo $row["OrderId"]; ?> onclick="onConfirm(this)" /></th>
						</tr>
				<?php
					}
				?>
			</table>
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>