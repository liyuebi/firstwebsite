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
		</div>
    </body>
</html>