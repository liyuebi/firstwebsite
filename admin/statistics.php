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
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		
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
		<div style="padding: 10px 0 0 10px;" >
	        <div>
		        <p>总统计</p>
		        <table border="1" class="table table-striped" style="max-width: 1000px; text-align: center">
			    	<tr>
				        <th>云量池</th><th>慈善金</th><th>用户数</th><th>推荐总额</th><th>复投总额</th><th>分红总额</th><th>交易成交总额</th><th>交易手续费</th><th>虚拟消耗（话费／油费等）</th><th>虚拟消耗手续费</th>
					</tr>
					<tr>
				        <td><?php echo $row["CreditsPool"]; ?></td>
				        <td><?php echo $row["CharityPool"]; ?></td>
				        <td><?php echo $row["UserCount"]; ?></td>
				        <td><?php echo $row["RecommendTotal"]; ?></td>
				        <td><?php echo $row["ReinventTotal"]; ?></td>
				        <td><?php echo $row["BonusTotal"]; ?></td>
				        <td><?php echo $row["ExchangeSuccQuan"]; ?></td>
				        <td><?php echo $row["ExchangeFee"]; ?></td>
						<td><?php echo $row["WithdrawTotal"]; ?></td>
						<td><?php echo $row["WithdrawFee"]; ?></td>
			    	</tr>
		        </table>
	        </div>
<!--
	        <div>
		        <p>即时统计</p>
		        <table border="1">
			        <tr>
				        <th>订单额</th><th>充值</th><th>提现</th><th>当日预计固定分润总额</th><th>当日动态分润剩余</th>
			        </tr>
			        <tr>
				        <td><?php echo $row1["OrderGross"]; ?></td>
				        <td><?php echo $row1["Recharge"]; ?></td>
				        <td><?php echo $row1["Withdraw"]; ?></td>
				        <td><?php echo $row1["BonusTotal"]; ?></td>
				        <td><?php echo $row1["BonusLeft"]; ?></td>
			        </tr>
		        </table>
	        </div>
-->
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>