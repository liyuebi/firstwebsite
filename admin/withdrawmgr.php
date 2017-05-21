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
			
			function enableBtns(idx, enabled)
			{
				var btns = document.getElementsByName(idx);
				var cnt = btns.length;
				for (var i = 0; i < cnt; ++i) 
				{
					btns[i].disabled = !enabled;					
				}
			}
			
			function queryCredit(btn)
			{
				var userId = btn.name;
				var idx = btn.id;
				$.post("../php/credit.php", {"func":"getCredit","index":idx,"user":userId}, function(data){
					
					if (data.error == "false") {
						document.getElementById("credit_"+data.index).innerHTML = data.credit;
					}
					else {
						alert("获取蜜券失败: " + data.error_msg + " " + data.index);
					}
				}, "json");

			}
			
			function onConfirm(btn)
			{
				enableBtns(btn.idx, false);
				$.post("../php/credit.php", {"func":"allowWithdraw","index":btn.id}, function(data){
					
					if (data.error == "false") {
						alert("通过申请！" + data.index);	
						document.getElementById("col_status_"+data.index).innerHTML = "已通过";
					}
					else {
						alert("申请未通过: " + data.error_msg + " " + data.index);
						enableBtns(btn.idx, true);
					}
				}, "json");
			}
			
			function onDeny(btn)
			{
				enableBtns(btn.idx, false);
				$.post("../php/credit.php", {"func":"denyWithdraw","index":btn.id}, function(data){
					
					if (data.error == "false") {
						alert("拒绝成功！" + data.index);	
						document.getElementById("col_status_"+data.index).innerHTML = "已取消";
						document.getElementById("col_record_" + data.index).innerHTML = data.pre + " => " + data.post;
					}
					else {
						alert("拒绝出错: " + data.error_msg + " " + data.index);
						enableBtns(btn.idx, true);
					}
				}, "json");
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
						<th>查询蜜券</th>
						<th>目前蜜券</th>
						<th>状态</th>
						<th>确认提现</th>
						<th>拒绝</th>
						<th>操作记录</th>
					</tr>
					<?php
						date_default_timezone_set('PRC');
						if ($result) {
							while($row = mysql_fetch_array($result)) {
					?>
								<tr>
									<td><?php echo $row["IndexId"]; ?></td>
									<td><?php echo date("Y.m.d H:i:s" , $row["ApplyTime"]); ?></td>
									<td><?php echo $row["UserId"]; ?></td>
									<td><?php echo $row["NickName"]; ?></td>
									<td><?php echo $row["PhoneNum"]; ?></td>
									<td><?php echo $row["ApplyAmount"]; ?></td>
									<td><?php $fee = $row["ApplyAmount"] - $row["ActualAmount"]; echo $fee; ?></td>
									<td style="color: red;"><?php echo $row["ActualAmount"]; ?></td>
									<td><?php if ($row["Method"] == 1) echo "微信"; 
											  else if ($row["Method"] == 2) echo "支付宝"; 
											  else if ($row["Method"] == 3) echo "银行"; ?>
									</td>
									<td><?php echo $row["Account"]; ?></td>
									<td><?php if ($row["Method"] == 3) echo $row["BankUser"] . ' ' . $row["BankName"] . ' ' . $row["BankBranch"]; ?></td>
									<td><input type="button" value="查看积分" name=<?php echo $row["UserId"]; ?> id=<?php echo $row["IndexId"]; ?> onclick="queryCredit(this)" /></td>
									<td id="credit_<?php echo $row["IndexId"]; ?>"></td>
									<td id="col_status_<?php echo $row["IndexId"]; ?>">未通过</td>
									<td><input type="button" value="确认" name=<?php echo $row["IndexId"]; ?> id=<?php echo $row["IndexId"]; ?> onclick="onConfirm(this)" /></td>
 									<td><input type="button" value="拒绝" name=<?php echo $row["IndexId"]; ?> id=<?php echo $row["IndexId"]; ?> onclick="onDeny(this)" /></td>
 									<td id= "col_record_<?php echo $row["IndexId"]; ?>" style="color: red;"></td>
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