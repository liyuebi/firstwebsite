<?php

include "./../php/database.php";

session_start();
if (!$_SESSION['isLogin']) {	
	$home_url = '../index.html';
	header('Location: ' . $home_url);
	exit();
}

function getTranscation()
{
	$con = connectToDB();
	if (!$con)
	{
		return false;
	}
	
	include "./../php/constant.php";
	$userid = $_SESSION["userId"];
	mysql_select_db("my_db", $con);
	$result = mysql_query("select * from Transcation where UserId='$userid' and Status!='$OrderStatusAccept'");
	return $result;
}

$result = getTranscation();		
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>我的订单</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
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
			
			function onDeny(btn)
			{
				alert(btn.id);
			}
		</script>
	</head>
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
		<h3>我的订单</h3>
		
        <div>
			<table border="1">
				<tr>
					<th>订单号</th>
					<th>产品信息</th>
					<th>数量</th>
					<th>价格</th>
					<th>状态</th>
					<th>确认收货</th>
				</tr>
				<?php
					while($row = mysql_fetch_array($result)) {
				?>
						<tr>
							<th><?php echo $row["UserId"]; ?></th>
							<th></th>
							<th><?php echo $row["Count"] ?></th>
							<th><?php echo $row["Price"]; ?></th>
							<th><?php if (1 == $row["Status"]) echo "等待发货"; else if (2 == $row["Status"]) echo "已发货"; else if (3 == $row["Status"]) echo "已收货"; ?></th>
							<th><input type="button" value="确认收货" id=<?php echo $row["OrderId"]; ?> onclick="onConfirm(this)" /></th>
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