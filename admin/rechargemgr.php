<?php

include "../php/database.php";

function getRechargeApplication()
{
	$con = connectToDB();
	if (!$con)
	{
		return false;
	}
	
	mysql_select_db("my_db", $con);
	$result = mysql_query("select * from RechargeApplication");
	return $result;
}

$result = getRechargeApplication();		
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>充值管理</title>
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
				$.post("../php/credit.php", {"func":"allowRecharge","index":btn.id}, function(data){
					
					if (data.error == "false") {
						alert("通过申请！" + data.index);	
						var str = "col_status_"+data.index;
						document.getElementById(str).innerHTML = "通过";
// 						location.href = "pwd.php";
					}
					else {
						alert("申请未通过: " + data.error_msg + " " + data.index);
						document.getElementById("col_record_" + data.index).innerHTML = data.error_msg;
						document.getElementById(data.index).disabled = false;
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
				<li><a href="statistics.php">统计数据</a></li>
			</ul>
		</div>
		<div style="display: inline; float: left; padding: 10px 0 0 10px;" >
	        <div>
				<table border="1">
					<tr>
						<th>申请编号</th>
						<th>申请时间</th>
						<th>用户id</th>
						<th>用户手机号</th>
						<th>用户姓名</th>
						<th>充值金额</th>
						<th>申请状态</th>
						<th>执行操作</th>
						<th>操作记录</th>
					</tr>
					<?php
						date_default_timezone_set('PRC');
						while($row = mysql_fetch_array($result)) {
					?>
							<tr>
								<th><?php echo $row["IndexId"]; ?></th>
								<th><?php echo date("Y.m.d H:i:s" ,$row["ApplyTime"]); ?></th>
								<th><?php echo $row["UserId"]; ?></th>
								<th></th>
								<th></th>
								<th><?php echo $row["Amount"]; ?></th>
								<th id="col_status_<?php echo $row["IndexId"]; ?>">未通过</th>
								<th><input type="button" value="确认" id=<?php echo $row["IndexId"]; ?> onclick="onConfirm(this)" /></th>
<!-- 								<th><input type="button" value="拒绝" id=<?php echo $row["IndexId"]; ?> onclick="onDeny(this)" /></th> -->
								<th id= "col_record_<?php echo $row["IndexId"]; ?>"></th>
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