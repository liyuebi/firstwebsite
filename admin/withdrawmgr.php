<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include "../php/database.php";

function getWithdrawApplication()
{
	$con = connectToDB();
	if (!$con)
	{
		return false;
	}
	
	$result = mysql_query("select * from WithdrawApplication");
	return $result;
}

$result = getWithdrawApplication();		
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>取现管理</title>
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
				$.post("../php/credit.php", {"func":"allowWithdraw","index":btn.id}, function(data){
					
					if (data.error == "false") {
						alert("通过申请！" + data.index);	
					}
					else {
						alert("申请未通过: " + data.error_msg + " " + data.index);
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
				<li><a href="productmgr.php">产品管理</a></li>
				<li><a href="usermgr.php">用户管理</a></li>
				<li><a href="ordermgr.php">订单管理</a></li>
				<li><a href="rechargemgr.php">充值管理</a></li>
				<li><a href="withdrawmgr.php">取现管理</a></li>
				<li><a href="configmgr.php">配置管理</a></li>
				<li><a href="statistics.php">统计数据</a></li>
				<li><a href="configRwdRate.php">配置动态拨比</a></li>
				<li><a href="adminmgr.php">管理员账号维护</a></li>
			</ul>
		</div>
		<div style="display: inline; float: left; padding: 10px 0 0 10px;" >
	        <div>
				<table border="1">
					<tr>
						<th>申请编号</th>
						<th>申请时间</th>
						<th>用户id</th>
						<th>昵称</th>
						<th>用户手机号</th>
						<th>申请金额</th>
						<th>手续费</th>
						<th style="color: red;">实际金额</th>
						<th>收款方式</th>
						<th>账号</th>
						<th>账号其他信息</th>
						<th>状态</th>
						<th>确认</th>
<!-- 						<th>拒绝</th> -->
					</tr>
					<?php
						date_default_timezone_set('PRC');
						if ($result) {
							while($row = mysql_fetch_array($result)) {
					?>
								<tr>
									<td><?php echo $row["IndexId"]; ?></td>
									<th><?php echo date("Y.m.d H:i:s" , $row["ApplyTime"]); ?></th>
									<th><?php echo $row["UserId"]; ?></th>
									<td><?php echo $row["NickName"]; ?></td>
									<td><?php echo $row["PhoneNum"]; ?></td>
									<th><?php echo $row["ApplyAmount"]; ?></th>
									<th><?php $fee = $row["ApplyAmount"] - $row["ActualAmount"]; echo $fee; ?></th>
									<th style="color: red;"><?php echo $row["ActualAmount"]; ?></th>
									<td><?php if ($row["Method"] == 1) echo "微信"; 
											  else if ($row["Method"] == 2) echo "支付宝"; 
											  else if ($row["Method"] == 3) echo "银行"; ?>
									</td>
									<td><?php echo $row["Account"]; ?></td>
									<td><?php if ($row["Method"] == 3) echo $row["BankUser"] . ' ' . $row["BankName"] . ' ' . $row["BankBranch"]; ?></td>
									<td id="col_status_<?php echo $row["IndexId"]; ?>">未通过</td>
									<th><input type="button" value="确认" id=<?php echo $row["IndexId"]; ?> onclick="onConfirm(this)" /></th>
<!-- // 									<th><input type="button" value="拒绝" id=<?php echo $row["IndexId"]; ?> onclick="onDeny(this)" /></th> -->
								</tr>
					<?php
							}
						}
					?>
				</table>
	        </div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>