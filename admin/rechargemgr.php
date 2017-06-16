<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include "../php/database.php";

function getRechargeApplication()
{
	$con = connectToDB();
	if (!$con)
	{
		return false;
	}
	
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
			
			function enableAllBtn(id, enabled)
			{
				var btns = document.getElementsByName(id);
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
						document.getElementById("regiToken_"+data.index).innerHTML = data.regiToken;
						document.getElementById("credit_"+data.index).innerHTML = data.credit;
					}
					else {
						alert("获取蜜券失败: " + data.error_msg + " " + data.index);
					}
				}, "json");
			}
			
			function onConfirm(btn)
			{
// 				alert(btn.id);	
// 				document.getElementById(btn.id).disabled = true;
				enableAllBtn(btn.id, false);
				if (!confirm("确认已收款么？")) {
					enableAllBtn(btn.id, true);
					return;
				}
				$.post("../php/credit.php", {"func":"allowRecharge","index":btn.id}, function(data){
					
					if (data.error == "false") {
						alert("通过申请！" + data.index);	
						var str = "col_status_"+data.index;
						document.getElementById(str).innerHTML = "通过";
						document.getElementById("col_record_" + data.index).innerHTML = data.pre + " => " + data.post;
// 						location.href = "pwd.php";
					}
					else {
						alert("申请未通过: " + data.error_msg + " " + data.index);
						document.getElementById("col_record_" + data.index).innerHTML = data.error_msg;
						enableAllBtn(btn.id, true);
					}
				}, "json");
			}
			
			function onDeny(btn)
			{
				enableAllBtn(btn.id, false);
				if (!confirm("确定要拒绝此次充值申请，并删除该充值申请吗？")) {
					enableAllBtn(btn.id, true);
					return;
				}
				
				$.post("../php/credit.php", {"func":"denyRecharge","index":btn.id}, function(data){
					
					if (data.error == "false") {
						alert("删除申请成功！" + data.index);	
						var str = "col_status_"+data.index;
						document.getElementById(str).innerHTML = "已拒绝";
						document.getElementById(str).style.color = "red";
// 						location.href = "pwd.php";
					}
					else {
						alert("删除申请失败: " + data.error_msg + " " + data.index);
						document.getElementById("col_record_" + data.index).innerHTML = data.error_msg;
						enableAllBtn(btn.id, true);
					}
				}, "json");
			}
		</script>
	</head>
	<body>
		<div style="padding: 10px 0 0 10px;" >
	        <div>
				<table border="1">
					<tr>
						<th>申请编号</th>
						<th>申请时间</th>
						<th>用户id</th>
						<th>昵称</th>
						<th>用户手机号</th>
						<th>充值金额</th>
						<th>支付方式</th>
						<th>账号</th>
						<th>账号其他信息</th>
						<th>查询蜜券</th>
						<th>目前注册券</th>
						<th>目前蜜券</th>
						<th>申请状态</th>
						<th>确认订单</th>
						<th>取消订单</th>
						<th>操作记录</th>
					</tr>
					<?php
						date_default_timezone_set('PRC');
						if ($result > 0) {
							while($row = mysql_fetch_array($result)) {
					?>
								<tr>
									<td><?php echo $row["IndexId"]; ?></td>
									<td><?php echo date("Y.m.d H:i:s" ,$row["ApplyTime"]); ?></td>
									<td><?php echo $row["UserId"]; ?></td>
									<td><?php echo $row["NickName"]; ?></td>
									<td><?php echo $row["PhoneNum"]; ?></td>
									<td><?php echo $row["Amount"]; ?></td>
									<td><?php if ($row["Method"] == 1) echo "微信"; 
											  else if ($row["Method"] == 2) echo "支付宝"; 
											  else if ($row["Method"] == 3) echo "银行"; ?>
									</td>
									<td><?php echo $row["Account"]; ?></td>
									<td><?php if ($row["Method"] == 3) echo $row["BankUser"] . ' ' . $row["BankName"] . ' ' . $row["BankBranch"]; ?></td>
									<td><input type="button" value="查看积分" name=<?php echo $row["UserId"]; ?> id=<?php echo $row["IndexId"]; ?> onclick="queryCredit(this)" /></td>
									<td id="regiToken_<?php echo $row["IndexId"]; ?>"></td>
									<td id="credit_<?php echo $row["IndexId"]; ?>"></td>
									<td id="col_status_<?php echo $row["IndexId"]; ?>">未通过</td>
									<td><input type="button" value="确认充值" name="<?php echo $row["IndexId"]; ?>" id=<?php echo $row["IndexId"]; ?> onclick="onConfirm(this)" /></td>
									<th><input type="button" value="拒绝" name="<?php echo $row["IndexId"]; ?>" id=<?php echo $row["IndexId"]; ?> onclick="onDeny(this)" /></th>
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