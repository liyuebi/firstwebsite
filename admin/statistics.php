<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include "../php/database.php";

$result = false;
$row = false;
$row1 = false;
$userCnt = array();
$totalCnt = 0;

$con = connectToDB();
if (!$con)
{
	return false;
}
	
$result = mysql_query("select * from Statistics");

$res = mysql_query("select * from TotalStatis");
if ($res) {
	$row = mysql_fetch_assoc($res);
}

$res1 = mysql_query("select * from ShortStatis");
if ($res1) {
	$row1 = mysql_fetch_assoc($res1);
}

$i = 1;
while ($i <= 10) {
	$userCnt[$i] = 0;
	
	$res2 = mysql_query("select count(*) from ClientTable where Lvl='$i'");
	if ($res2 && mysql_num_rows($res2) > 0) {
		$row2 = mysql_fetch_assoc($res2);
		$userCnt[$i] = $row2["count(*)"];
	}
	
	++$i;
}

$res3 = mysql_query("select count(*) from ClientTable");
if ($res3 && mysql_num_rows($res3) > 0) {
	$row3 = mysql_fetch_assoc($res3);
	$totalCnt = $row3["count(*)"];
}

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
			
/*
			function onConfirm(btn)
			{
// 				alert(btn.id);	
				document.getElementById(btn.id).disabled = true;
				$.post("../php/credit.php", {"func":"allowWithdraw","index":btn.id}, function(data){
					
					if (data.error == "false") {
						alert("通过申请！" + data.index);	
						location.href = "pwd.php";
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
*/
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
				<li><a href="postmgr.php">公告管理</a></li>
				<li><a href="adminmgr.php">管理员账号维护</a></li>
			</ul>
		</div>
		<div style="display: inline; float: left; padding: 10px 0 0 10px;" >
	        <div>
		        <p>总统计</p>
		        <table border="1">
			        <tr>
				        <th>积分池</th><th>用户数</th><th>账户数</th><th>总盒数</th><th>总充值</th><th>总提现</th>
			        </tr>
			        <tr>
				        <td><?php echo $row["CreditsPool"]; ?></td>
				        <td><?php echo $row["UserCount"]; ?></td>
				        <td><?php echo $row["AccountCount"]; ?></td>
				        <td><?php echo $row["SPNum"]; ?></td>
				        <td><?php echo $row["RechargeTotal"]; ?></td>
				        <td><?php echo $row["WithdrawTotal"]; ?></td>
			        </tr>
		        </table>
	        </div>
	        <div>
		        <p>即时统计</p>
		        <table border="1">
			        <tr>
				        <th>订单额</th><th>充值</th><th>提现</th><th>当日预计固定分润总额</th><th>当日预计动态分润总额</th>
			        </tr>
			        <tr>
				        <td><?php echo $row1["OrderGross"]; ?></td>
				        <td><?php echo $row1["Recharge"]; ?></td>
				        <td><?php echo $row1["Withdraw"]; ?></td>
				        <td><?php echo $row1["BonusTotal"]; ?></td>
				        <td><?php echo $row1["DBonusTotal"]; ?></td>
			        </tr>
		        </table>
	        </div>
	        <div>
		        <p>按日统计</p>
				<table border="1">
					<tr>
						<th>年</th>
						<th>月</th>
						<th>日</th>
						<th>新用户数</th>
						<th>总充值额</th>
						<th>总提现额</th>
						<th>提现手续费</th>
						<th>总转账额</th>
						<th>转账手续费</th>
						<th>订单总收入</th>
						<th>订单件数</th>
	<!-- 					<th>拒绝</th> -->
					</tr>
					<?php
						while($row = mysql_fetch_array($result)) {
					?>
							<tr>
								<th><?php echo $row["Ye"]; ?></th>
								<th><?php echo $row["Mon"]; ?></th>
								<th><?php echo $row["Day"]; ?></th>
								<th><?php echo $row["NSCount"] ?></th>
								<th><?php echo $row["RechargeTotal"]; ?></th>
								<th><?php echo $row["WithdrawTotal"]; ?></th>
								<th><?php echo $row["WithdrawFee"]; ?></th>
								<th><?php echo $row["TfTotal"]; ?></th>
								<th><?php echo $row["TfFee"]; ?></th>
								<th><?php echo $row["OrderGross"]; ?></th>
								<th><?php echo $row["SPNum"]; ?></th>
	<!-- 							<th><input type="button" value="确认" id=<?php echo $row["IndexId"]; ?> onclick="onConfirm(this)" /></th> -->
	<!-- 							<th><input type="button" value="拒绝" id=<?php echo $row["IndexId"]; ?> onclick="onDeny(this)" /></th> -->
							</tr>
					<?php
						}
					?>
				</table>
	        </div>
	        <div>
		        <p>级别人数统计</p>
		        <table border="1">
			        <tr>
				        <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th><th>10</th><th>总人数</th>
			        </tr>
					<tr>
						<?php
							$cnt = count($userCnt);
							$j = 1;
							while ($j <= $cnt) {		
						?>
								<td><?php echo $userCnt[$j]; ?></td>
						<?php		
								++$j;
							}
						?>
 						<td><?php echo $totalCnt; ?></td>
					</tr>
		        </table>
	        </div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>