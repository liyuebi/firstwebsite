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
<!-- 		        <p>按日统计</p> -->
				<table border="1" class="table table-striped" style="max-width: 1000px; text-align: center">
					<tr>
						<th>年</th>
						<th>月</th>
						<th>日</th>
						<th>新用户数</th>
						<th>推荐额</th>
						<th>复投额</th>
						<th>分红额</th>
						<th>交易成交额</th>
						<th>交易手续费</th>
						<th>虚拟消耗（话费／油费）</th>
						<th>虚拟消耗手续费</th>
	<!-- 					<th>拒绝</th> -->
					</tr>
					<?php
						while($row = mysql_fetch_array($result)) {
					?>
							<tr>
								<td><?php echo $row["Ye"]; ?></td>
								<td><?php echo $row["Mon"]; ?></td>
								<td><?php echo $row["Day"]; ?></td>
								<td><?php echo $row["NSCount"] ?></td>
								<td><?php echo $row["RecommendTotal"]; ?></td>
								<td><?php echo $row["ReinventTotal"]; ?></td>
								<td><?php echo $row["BonusTotal"]; ?></td>
								<td><?php echo $row["ExchangeSuccQuan"]; ?></td>
								<td><?php echo $row["ExchangeFee"]; ?></td>
								<td><?php echo $row["WithdrawTotal"]; ?></td>
								<td><?php echo $row["WithdrawFee"]; ?></td>
	<!-- 							<th><input type="button" value="确认" id=<?php echo $row["IndexId"]; ?> onclick="onConfirm(this)" /></th> -->
	<!-- 							<th><input type="button" value="拒绝" id=<?php echo $row["IndexId"]; ?> onclick="onDeny(this)" /></th> -->
							</tr>
					<?php
						}
					?>
				</table>
	        </div>
		</div>
    </body>
</html>